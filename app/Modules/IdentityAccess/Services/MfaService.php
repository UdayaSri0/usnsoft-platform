<?php

namespace App\Modules\IdentityAccess\Services;

use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Enums\MfaMethodType;
use App\Modules\IdentityAccess\Models\MfaMethod;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use RuntimeException;

class MfaService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
        private readonly StaffMfaService $staffMfaService,
        private readonly TotpService $totpService,
    ) {}

    /**
     * @return array{method: MfaMethod, secret: string, provisioning_uri: string}|null
     */
    public function enrollmentData(User $user): ?array
    {
        $method = $this->pendingMethod($user);

        if (! $method) {
            return null;
        }

        $secret = $this->decryptSecret($method);

        return [
            'method' => $method,
            'secret' => $secret,
            'provisioning_uri' => $this->totpService->provisioningUri($user, $secret),
        ];
    }

    /**
     * @return array{method: MfaMethod, secret: string, provisioning_uri: string}
     */
    public function startEnrollment(User $user): array
    {
        if ($this->activeMethod($user)) {
            throw new RuntimeException('MFA is already enabled for this account.');
        }

        $secret = $this->totpService->generateSecret();
        $recoveryCodes = $this->totpService->generateRecoveryCodes(
            max(1, (int) config('security.mfa.recovery_code_count', 8)),
        );

        $method = $this->pendingMethod($user);

        if (! $method) {
            $method = new MfaMethod([
                'method_type' => MfaMethodType::Totp->value,
            ]);
            $method->user()->associate($user);
        }

        if ($method->trashed()) {
            $method->restore();
        }

        $method->forceFill([
            'secret_encrypted' => Crypt::encryptString($secret),
            'recovery_codes_encrypted' => Crypt::encryptString(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)),
            'enabled_at' => null,
            'required_at' => $user->mfa_required_at ?? ($this->staffMfaService->requiresMfa($user) ? CarbonImmutable::now() : null),
            'last_verified_at' => null,
            'metadata' => [
                'issuer' => config('security.mfa.issuer', config('app.name', 'USNsoft')),
                'setup_started_at' => CarbonImmutable::now()->toIso8601String(),
            ],
        ])->save();

        $this->securityEventService->record('mfa.enrollment.started', $user, 'info', [
            'method' => MfaMethodType::Totp->value,
            'mfa_method_id' => $method->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'mfa.enrollment.started',
            action: 'start_mfa_enrollment',
            actor: $user,
            auditable: $method,
        );

        return [
            'method' => $method,
            'secret' => $secret,
            'provisioning_uri' => $this->totpService->provisioningUri($user, $secret),
        ];
    }

    /**
     * @return list<string>
     */
    public function confirmEnrollment(User $user, string $code): array
    {
        $method = $this->pendingMethod($user);

        if (! $method) {
            throw new RuntimeException('No MFA enrollment is pending for this account.');
        }

        $secret = $this->decryptSecret($method);

        if (! $this->totpService->verifyCode($secret, $code)) {
            $this->securityEventService->record('mfa.challenge.failed', $user, 'warning', [
                'stage' => 'enrollment_confirmation',
                'mfa_method_id' => $method->getKey(),
            ]);

            throw new RuntimeException('The authentication code was invalid. Please try again.');
        }

        $now = CarbonImmutable::now();
        $method->forceFill([
            'enabled_at' => $now,
            'last_verified_at' => $now,
        ])->save();

        $user->forceFill([
            'mfa_enabled_at' => $now,
        ])->save();

        $recoveryCodes = $this->decryptRecoveryCodes($method);

        $this->securityEventService->record('mfa.enrollment.completed', $user, 'info', [
            'method' => MfaMethodType::Totp->value,
            'mfa_method_id' => $method->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'mfa.enrollment.completed',
            action: 'confirm_mfa_enrollment',
            actor: $user,
            auditable: $method,
        );

        return $recoveryCodes;
    }

    /**
     * @return array{used_recovery_code: bool, recovery_codes_remaining: int}
     */
    public function verifyChallenge(User $user, string $code): array
    {
        $method = $this->activeMethod($user);

        if (! $method) {
            throw new RuntimeException('This account does not have an active MFA method.');
        }

        $normalized = Str::upper(preg_replace('/\s+/', '', $code) ?? '');
        $secret = $this->decryptSecret($method);

        if ($this->totpService->verifyCode($secret, $normalized)) {
            $method->forceFill([
                'last_verified_at' => CarbonImmutable::now(),
            ])->save();

            $this->securityEventService->record('mfa.challenge.passed', $user, 'info', [
                'mfa_method_id' => $method->getKey(),
                'used_recovery_code' => false,
            ]);

            return [
                'used_recovery_code' => false,
                'recovery_codes_remaining' => count($this->decryptRecoveryCodes($method)),
            ];
        }

        $recoveryCodes = $this->decryptRecoveryCodes($method);
        $match = collect($recoveryCodes)->search(static fn (string $storedCode): bool => hash_equals($storedCode, $normalized));

        if ($match === false) {
            $this->securityEventService->record('mfa.challenge.failed', $user, 'warning', [
                'stage' => 'challenge',
                'mfa_method_id' => $method->getKey(),
            ]);

            throw new RuntimeException('The authentication code or recovery code was invalid.');
        }

        unset($recoveryCodes[$match]);
        $recoveryCodes = array_values($recoveryCodes);

        $method->forceFill([
            'recovery_codes_encrypted' => Crypt::encryptString(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)),
            'last_verified_at' => CarbonImmutable::now(),
        ])->save();

        $this->securityEventService->record('mfa.recovery_code.used', $user, 'warning', [
            'mfa_method_id' => $method->getKey(),
            'recovery_codes_remaining' => count($recoveryCodes),
        ]);

        $this->auditLogService->record(
            eventType: 'mfa.recovery_code.used',
            action: 'use_mfa_recovery_code',
            actor: $user,
            auditable: $method,
            metadata: [
                'recovery_codes_remaining' => count($recoveryCodes),
            ],
        );

        return [
            'used_recovery_code' => true,
            'recovery_codes_remaining' => count($recoveryCodes),
        ];
    }

    /**
     * @return list<string>
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $method = $this->activeMethod($user);

        if (! $method) {
            throw new RuntimeException('No active MFA method exists for this account.');
        }

        $codes = $this->totpService->generateRecoveryCodes(
            max(1, (int) config('security.mfa.recovery_code_count', 8)),
        );

        $method->forceFill([
            'recovery_codes_encrypted' => Crypt::encryptString(json_encode($codes, JSON_THROW_ON_ERROR)),
        ])->save();

        $this->securityEventService->record('mfa.recovery_codes.regenerated', $user, 'warning', [
            'mfa_method_id' => $method->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'mfa.recovery_codes.regenerated',
            action: 'regenerate_mfa_recovery_codes',
            actor: $user,
            auditable: $method,
        );

        return $codes;
    }

    public function disable(User $actor, User $target, ?string $reason = null): void
    {
        if ($actor->getKey() !== $target->getKey() && ! $actor->hasPermission('security.mfa.manage')) {
            throw new AuthorizationException('You are not allowed to disable MFA for this account.');
        }

        if (
            $actor->getKey() === $target->getKey()
            && $this->staffMfaService->enforcementEnabled()
            && $this->staffMfaService->requiresMfa($target)
        ) {
            throw new AuthorizationException('Required staff MFA cannot be self-disabled.');
        }

        $method = $this->activeMethod($target) ?? $this->pendingMethod($target);

        if (! $method) {
            return;
        }

        $method->delete();

        $target->forceFill([
            'mfa_enabled_at' => null,
        ])->save();

        $this->securityEventService->record('mfa.disabled', $target, 'critical', [
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
        ]);

        $this->auditLogService->record(
            eventType: 'mfa.disabled',
            action: 'disable_mfa',
            actor: $actor,
            auditable: $target,
            metadata: [
                'reason' => $reason,
                'target_user_id' => $target->getKey(),
            ],
        );
    }

    public function activeMethod(User $user): ?MfaMethod
    {
        return $user->mfaMethods()
            ->where('method_type', MfaMethodType::Totp->value)
            ->whereNotNull('enabled_at')
            ->latest('enabled_at')
            ->first();
    }

    private function pendingMethod(User $user): ?MfaMethod
    {
        return $user->mfaMethods()
            ->where('method_type', MfaMethodType::Totp->value)
            ->whereNull('enabled_at')
            ->latest('updated_at')
            ->first();
    }

    private function decryptSecret(MfaMethod $method): string
    {
        return Crypt::decryptString((string) $method->secret_encrypted);
    }

    /**
     * @return list<string>
     */
    private function decryptRecoveryCodes(MfaMethod $method): array
    {
        $payload = Crypt::decryptString((string) $method->recovery_codes_encrypted);
        $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        return array_values(array_filter(
            is_array($decoded) ? $decoded : [],
            static fn (mixed $code): bool => is_string($code) && $code !== '',
        ));
    }
}

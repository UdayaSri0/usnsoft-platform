<?php

namespace App\Modules\AuditSecurity\Services;

use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;

class LoginSecurityService
{
    public function __construct(
        private readonly SecurityEventService $securityEventService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function recordSuccessfulLogin(User $user, string $sessionId): UserSessionHistory
    {
        $now = CarbonImmutable::now();
        $ip = request()?->ip();
        $userAgent = request()?->userAgent();
        $fingerprint = $this->deviceFingerprint($ip, $userAgent);

        $device = UserDevice::query()
            ->where('user_id', $user->getKey())
            ->where('device_fingerprint', $fingerprint)
            ->first();

        $isNewDevice = $device === null;

        if (! $device) {
            $device = UserDevice::query()->create([
                'user_id' => $user->getKey(),
                'device_fingerprint' => $fingerprint,
                'device_label' => $this->extractDeviceLabel($userAgent),
                'user_agent' => $userAgent,
                'ip_address' => $ip,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'last_login_at' => $now,
            ]);
        } else {
            $device->forceFill([
                'user_agent' => $userAgent,
                'ip_address' => $ip,
                'last_seen_at' => $now,
                'last_login_at' => $now,
            ])->save();
        }

        $hasHistoricalSessions = UserSessionHistory::query()
            ->where('user_id', $user->getKey())
            ->exists();

        $knownContext = UserSessionHistory::query()
            ->where('user_id', $user->getKey())
            ->where(function ($query) use ($ip, $userAgent): void {
                $query->where('ip_address', $ip)
                    ->orWhere('user_agent', $userAgent);
            })
            ->exists();

        $isSuspicious = $hasHistoricalSessions && ($isNewDevice || ! $knownContext);

        UserSessionHistory::query()
            ->where('user_id', $user->getKey())
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'updated_at' => $now,
            ]);

        $history = UserSessionHistory::query()->create([
            'user_id' => $user->getKey(),
            'session_identifier' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_id' => $device->getKey(),
            'last_activity_at' => $now,
            'logged_in_at' => $now,
            'is_current' => true,
            'metadata' => [
                'device_fingerprint' => $fingerprint,
                'is_new_device' => $isNewDevice,
            ],
        ]);

        $user->forceFill([
            'last_login_at' => $now,
            'last_login_ip' => $ip,
            'last_login_user_agent' => $userAgent,
        ])->save();

        $this->securityEventService->record(SecurityEventType::LoginSuccess, $user, 'info', [
            'session_id' => $sessionId,
            'device_id' => $device->getKey(),
            'is_new_device' => $isNewDevice,
            'internal_staff' => $user->isInternalStaff(),
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::LoginSuccess->value,
            action: 'login',
            actor: $user,
            auditable: $history,
            metadata: [
                'session_id' => $sessionId,
                'device_id' => $device->getKey(),
                'is_suspicious' => $isSuspicious,
            ],
        );

        if ($isSuspicious) {
            $this->securityEventService->record(SecurityEventType::LoginSuspicious, $user, $user->isInternalStaff() ? 'critical' : 'warning', [
                'session_id' => $sessionId,
                'device_id' => $device->getKey(),
                'reason' => $isNewDevice ? 'new_device' : 'unusual_context',
                'internal_staff' => $user->isInternalStaff(),
            ]);
        }

        return $history;
    }

    public function recordFailedLogin(?User $user, string $email, string $reason = 'invalid_credentials'): void
    {
        $email = mb_strtolower(trim($email));
        $now = CarbonImmutable::now();
        $ip = request()?->ip();

        FailedLoginAttempt::query()->create([
            'user_id' => $user?->getKey(),
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()?->userAgent(),
            'reason' => $reason,
            'occurred_at' => $now,
        ]);

        $recentFailureCount = FailedLoginAttempt::query()
            ->where('email', $email)
            ->where('ip_address', $ip)
            ->where('occurred_at', '>=', $now->subMinutes(15))
            ->count();

        $this->securityEventService->record(SecurityEventType::LoginFailed, $user, 'warning', [
            'email' => $email,
            'reason' => $reason,
            'recent_failures' => $recentFailureCount,
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::LoginFailed->value,
            action: 'login',
            actor: $user,
            metadata: [
                'email' => $email,
                'reason' => $reason,
                'recent_failures' => $recentFailureCount,
            ],
        );

        if ($recentFailureCount >= $this->suspiciousThreshold()) {
            $this->securityEventService->record(SecurityEventType::LoginSuspicious, $user, 'critical', [
                'email' => $email,
                'reason' => 'repeated_failed_login',
                'recent_failures' => $recentFailureCount,
            ]);
        }
    }

    public function recordInactiveAccountAttempt(User $user, string $email, string $reason): void
    {
        $this->recordFailedLogin($user, $email, $reason);

        $this->securityEventService->record(SecurityEventType::LoginSuspicious, $user, 'critical', [
            'email' => mb_strtolower(trim($email)),
            'reason' => $reason,
        ]);
    }

    public function recordLockout(string $email): void
    {
        $email = mb_strtolower(trim($email));

        $this->securityEventService->record(SecurityEventType::LoginThrottled, null, 'warning', [
            'email' => $email,
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::LoginThrottled->value,
            action: 'login',
            metadata: ['email' => $email],
        );
    }

    public function recordLogout(User $user, ?string $sessionId): void
    {
        $now = CarbonImmutable::now();

        if ($sessionId) {
            UserSessionHistory::query()
                ->where('user_id', $user->getKey())
                ->where('session_identifier', $sessionId)
                ->whereNull('logged_out_at')
                ->update([
                    'logged_out_at' => $now,
                    'last_activity_at' => $now,
                    'is_current' => false,
                    'updated_at' => $now,
                ]);
        }

        $this->securityEventService->record(SecurityEventType::LogoutSuccess, $user, 'info', [
            'session_id' => $sessionId,
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::LogoutSuccess->value,
            action: 'logout',
            actor: $user,
            metadata: ['session_id' => $sessionId],
        );
    }

    public function invalidateOtherSessions(User $user, string $currentSessionId): int
    {
        $now = CarbonImmutable::now();

        $invalidated = UserSessionHistory::query()
            ->where('user_id', $user->getKey())
            ->where('session_identifier', '!=', $currentSessionId)
            ->whereNull('invalidated_at')
            ->update([
                'invalidated_at' => $now,
                'is_current' => false,
                'updated_at' => $now,
            ]);

        if ($invalidated > 0) {
            $this->securityEventService->record('session.invalidate.others', $user, 'warning', [
                'count' => $invalidated,
            ]);

            $this->auditLogService->record(
                eventType: 'session.invalidate.others',
                action: 'invalidate_other_sessions',
                actor: $user,
                metadata: ['count' => $invalidated],
            );
        }

        return $invalidated;
    }

    public function touchSession(User $user, ?string $sessionId): void
    {
        if (! $sessionId) {
            return;
        }

        $now = CarbonImmutable::now();

        $session = UserSessionHistory::query()
            ->where('user_id', $user->getKey())
            ->where('session_identifier', $sessionId)
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first();

        if (! $session) {
            return;
        }

        $session->forceFill([
            'last_activity_at' => $now,
            'is_current' => true,
        ])->save();

        if ($session->device_id) {
            UserDevice::query()
                ->whereKey($session->device_id)
                ->update([
                    'last_seen_at' => $now,
                    'updated_at' => $now,
                ]);
        }
    }

    private function suspiciousThreshold(): int
    {
        return (int) config('security.suspicious_login_threshold', 5);
    }

    private function deviceFingerprint(?string $ip, ?string $userAgent): string
    {
        return hash('sha256', mb_strtolower((string) $userAgent).'|'.$ip);
    }

    private function extractDeviceLabel(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        return mb_substr($userAgent, 0, 80);
    }
}

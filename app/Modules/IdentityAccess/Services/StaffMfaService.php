<?php

namespace App\Modules\IdentityAccess\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Session\Session;

class StaffMfaService
{
    public const CHALLENGE_PASSED_AT_SESSION_KEY = 'security.mfa.challenge_passed_at';
    public const INTENDED_URL_SESSION_KEY = 'security.mfa.intended_url';

    public function requiresMfa(User $user): bool
    {
        if ($user->mfa_required_at !== null) {
            return true;
        }

        return $user->hasAnyRole((array) config('security.mfa.required_roles', []));
    }

    public function isCompliant(User $user): bool
    {
        if (! $this->requiresMfa($user)) {
            return true;
        }

        if ($user->mfa_enabled_at !== null) {
            return true;
        }

        return $user->mfaMethods()
            ->whereNotNull('enabled_at')
            ->exists();
    }

    public function shouldEnforceNow(User $user): bool
    {
        return $this->enforcementEnabled()
            && $this->requiresMfa($user)
            && ! $this->isCompliant($user);
    }

    public function requiresChallenge(User $user, Session $session): bool
    {
        if (! $this->enforcementEnabled() || ! $this->requiresMfa($user) || ! $this->isCompliant($user)) {
            return false;
        }

        $passedAt = $session->get(self::CHALLENGE_PASSED_AT_SESSION_KEY);

        if (! is_string($passedAt) || $passedAt === '') {
            return true;
        }

        $expiresAt = CarbonImmutable::parse($passedAt)
            ->addMinutes(max(1, (int) config('security.mfa.challenge_timeout_minutes', 720)));

        if ($expiresAt->isPast()) {
            $this->clearSessionState($session);

            return true;
        }

        return false;
    }

    public function markChallengePassed(Session $session): void
    {
        $session->put(self::CHALLENGE_PASSED_AT_SESSION_KEY, CarbonImmutable::now()->toIso8601String());
    }

    public function rememberIntendedUrl(Session $session, string $url): void
    {
        if (! $session->has(self::INTENDED_URL_SESSION_KEY)) {
            $session->put(self::INTENDED_URL_SESSION_KEY, $url);
        }
    }

    public function pullIntendedUrl(Session $session, string $fallback): string
    {
        $url = $session->pull(self::INTENDED_URL_SESSION_KEY);

        return is_string($url) && $url !== '' ? $url : $fallback;
    }

    public function clearSessionState(Session $session): void
    {
        $session->forget([
            self::CHALLENGE_PASSED_AT_SESSION_KEY,
            self::INTENDED_URL_SESSION_KEY,
        ]);
    }

    public function enforcementEnabled(): bool
    {
        return (bool) config('security.enforce_internal_mfa', false);
    }
}

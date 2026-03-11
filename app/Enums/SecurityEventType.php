<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case LoginSucceeded = 'login_succeeded';
    case LoginFailed = 'login_failed';
    case SuspiciousLogin = 'suspicious_login';
    case PasswordResetRequested = 'password_reset_requested';
    case PasswordResetCompleted = 'password_reset_completed';
    case MfaChallengePassed = 'mfa_challenge_passed';
    case MfaChallengeFailed = 'mfa_challenge_failed';
    case PermissionDenied = 'permission_denied';
    case AccountLocked = 'account_locked';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

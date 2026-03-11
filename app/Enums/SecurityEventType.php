<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case LoginSuccess = 'login.success';
    case LoginFailed = 'login.failed';
    case LoginThrottled = 'login.throttled';
    case LoginSuspicious = 'login.suspicious';
    case LogoutSuccess = 'logout.success';
    case PasswordResetRequested = 'password.reset.requested';
    case PasswordResetCompleted = 'password.reset.completed';
    case EmailVerificationSent = 'email.verification.sent';
    case EmailVerificationCompleted = 'email.verification.completed';
    case RoleChanged = 'role.changed';
    case PermissionChanged = 'permission.changed';
    case AccountCreated = 'account.created';
    case AccountDeactivated = 'account.deactivated';
    case AccountReactivated = 'account.reactivated';
    case AccountDeletionRequested = 'account.deletion.requested';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

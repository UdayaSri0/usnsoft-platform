<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case LoginSuccess = 'login.success';
    case LoginFailed = 'login.failed';
    case LoginThrottled = 'login.throttled';
    case LoginSuspicious = 'login.suspicious';
    case LogoutSuccess = 'logout.success';
    case SessionTimeout = 'session.timeout';
    case SessionInvalidateOthers = 'session.invalidate.others';
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
    case MfaEnrollmentStarted = 'mfa.enrollment.started';
    case MfaEnrollmentCompleted = 'mfa.enrollment.completed';
    case MfaChallengePassed = 'mfa.challenge.passed';
    case MfaChallengeFailed = 'mfa.challenge.failed';
    case MfaRecoveryCodeUsed = 'mfa.recovery_code.used';
    case MfaRecoveryCodesRegenerated = 'mfa.recovery_codes.regenerated';
    case MfaDisabled = 'mfa.disabled';
    case AntiSpamFailed = 'anti_spam.failed';
    case ProtectedDownloadDenied = 'protected_file.download.denied';
    case ProtectedDownloadAuthorized = 'protected_file.download.authorized';
    case ProtectedRequestAttachmentAccessed = 'protected_file.request_attachment.accessed';
    case ProtectedJobApplicationAccessed = 'protected_file.job_application.accessed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

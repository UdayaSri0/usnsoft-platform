<?php

namespace App\Providers;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;
use App\Modules\AuditSecurity\Models\SecurityEvent;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserLoginHistory;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Modules\AuditSecurity\Policies\AuditLogPolicy;
use App\Modules\AuditSecurity\Policies\SecurityEventPolicy;
use App\Modules\AuditSecurity\Policies\UserDevicePolicy;
use App\Modules\AuditSecurity\Policies\UserSessionHistoryPolicy;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Models\MfaMethod;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\SocialAccount;
use App\Modules\IdentityAccess\Models\UserOAuthAccount;
use App\Modules\IdentityAccess\Policies\AccountDeletionRequestPolicy;
use App\Modules\IdentityAccess\Policies\PermissionPolicy;
use App\Modules\IdentityAccess\Policies\RolePolicy;
use App\Modules\IdentityAccess\Policies\UserPolicy;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Media\Models\MediaAttachment;
use App\Modules\Products\Policies\ProtectedDownloadPolicy;
use App\Modules\SiteSettings\Models\SiteSetting;
use App\Modules\Workflow\Models\ApprovalRequest;
use App\Modules\Workflow\Models\StatusHistory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function (?User $user): ?bool {
            if (! $user) {
                return null;
            }

            return $user->hasRole(CoreRole::SuperAdmin) ? true : null;
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(AccountDeletionRequest::class, AccountDeletionRequestPolicy::class);
        Gate::policy(UserSessionHistory::class, UserSessionHistoryPolicy::class);
        Gate::policy(UserDevice::class, UserDevicePolicy::class);
        Gate::policy(SecurityEvent::class, SecurityEventPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::define('admin.access', static function (User $user): bool {
            return $user->isInternalStaff() && $user->hasPermission('admin.access');
        });

        Gate::define('superadmin.access', static function (User $user): bool {
            return $user->hasRole(CoreRole::SuperAdmin);
        });

        Gate::define('downloads.protected.access', [ProtectedDownloadPolicy::class, 'access']);

        Gate::define('requests.create', static function (User $user): bool {
            return $user->hasVerifiedEmail()
                && $user->isActiveForAuthentication()
                && $user->hasPermission('requests.create');
        });

        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
            'permission' => Permission::class,
            'site_setting' => SiteSetting::class,
            'media_asset' => MediaAsset::class,
            'media_attachment' => MediaAttachment::class,
            'approval_request' => ApprovalRequest::class,
            'status_history' => StatusHistory::class,
            'audit_log' => AuditLog::class,
            'security_event' => SecurityEvent::class,
            'social_account' => SocialAccount::class,
            'user_oauth_account' => UserOAuthAccount::class,
            'mfa_method' => MfaMethod::class,
            'user_login_history' => UserLoginHistory::class,
            'user_session_history' => UserSessionHistory::class,
            'user_device' => UserDevice::class,
            'failed_login_attempt' => FailedLoginAttempt::class,
            'account_deletion_request' => AccountDeletionRequest::class,
        ]);
    }
}

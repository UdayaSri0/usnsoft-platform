<?php

namespace App\Providers;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Policies\RolePolicy;
use App\Modules\IdentityAccess\Policies\UserPolicy;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Media\Models\MediaAttachment;
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

        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
            'site_setting' => SiteSetting::class,
            'media_asset' => MediaAsset::class,
            'media_attachment' => MediaAttachment::class,
            'approval_request' => ApprovalRequest::class,
            'status_history' => StatusHistory::class,
            'audit_log' => AuditLog::class,
        ]);
    }
}

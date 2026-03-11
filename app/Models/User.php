<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Modules\IdentityAccess\Concerns\HasRolesAndPermissions;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Models\MfaMethod;
use App\Modules\IdentityAccess\Models\UserOAuthAccount;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRolesAndPermissions;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'phone',
        'password',
        'status',
        'is_internal',
        'mfa_enabled_at',
        'mfa_required_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'suspended_at',
        'deactivated_at',
        'deactivated_by',
        'deactivation_reason',
        'deletion_requested_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => AccountStatus::class,
            'is_internal' => 'boolean',
            'mfa_enabled_at' => 'datetime',
            'mfa_required_at' => 'datetime',
            'last_login_at' => 'datetime',
            'suspended_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(CoreRole::SuperAdmin);
    }

    public function isInternalStaff(): bool
    {
        if ($this->is_internal) {
            return true;
        }

        return $this->hasAnyRole(CoreRole::internalRoles());
    }

    public function isActiveForAuthentication(): bool
    {
        return $this->status === AccountStatus::Active
            && $this->deactivated_at === null
            && $this->suspended_at === null
            && $this->deleted_at === null;
    }

    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(UserOAuthAccount::class);
    }

    public function sessionHistories(): HasMany
    {
        return $this->hasMany(UserSessionHistory::class);
    }

    public function loginHistory(): HasMany
    {
        return $this->sessionHistories();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function failedLoginAttempts(): HasMany
    {
        return $this->hasMany(FailedLoginAttempt::class);
    }

    public function mfaMethods(): HasMany
    {
        return $this->hasMany(MfaMethod::class);
    }

    public function deletionRequests(): HasMany
    {
        return $this->hasMany(AccountDeletionRequest::class);
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'deactivated_by');
    }
}

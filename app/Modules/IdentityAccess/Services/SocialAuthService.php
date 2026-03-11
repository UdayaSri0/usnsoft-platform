<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\UserOAuthAccount;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function isGoogleConfigured(): bool
    {
        return filled(Config::get('services.google.client_id'))
            && filled(Config::get('services.google.client_secret'))
            && filled(Config::get('services.google.redirect'));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var RedirectResponse $redirect */
        $redirect = Socialite::driver('google')->redirect();

        return $redirect;
    }

    public function fetchGoogleUser(): SocialiteUser
    {
        return Socialite::driver('google')->user();
    }

    public function loginOrCreateFromGoogle(SocialiteUser $socialiteUser): User
    {
        $provider = 'google';
        $providerUserId = (string) $socialiteUser->getId();

        $oauth = UserOAuthAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($oauth) {
            $user = $oauth->user;

            if (! $user || ! $user->isActiveForAuthentication()) {
                throw new DomainException('This account is not available for authentication.');
            }

            $oauth->forceFill(['last_used_at' => CarbonImmutable::now()])->save();

            return $user;
        }

        $providerEmail = mb_strtolower(trim((string) $socialiteUser->getEmail()));
        $providerEmailVerified = $this->isProviderEmailVerified($socialiteUser);

        if ($providerEmail === '' || ! $providerEmailVerified) {
            throw new DomainException('Google account email must be verified before sign in.');
        }

        $user = User::query()
            ->withTrashed()
            ->whereRaw('LOWER(email) = ?', [$providerEmail])
            ->first();
        $createdFromOauth = false;

        if (! $user) {
            $user = User::query()->create([
                'name' => $socialiteUser->getName() ?: 'Google User',
                'email' => $providerEmail,
                'password' => Hash::make(str()->random(40)),
                'email_verified_at' => CarbonImmutable::now(),
                'status' => AccountStatus::Active,
                'is_internal' => false,
            ]);

            $defaultRole = Role::query()->firstOrCreate(
                ['name' => CoreRole::User->value],
                [
                    'display_name' => 'User',
                    'description' => 'Default public account role',
                    'is_core' => true,
                    'is_internal' => false,
                ],
            );

            $user->assignRole($defaultRole);
            $createdFromOauth = true;

            $this->securityEventService->record(SecurityEventType::AccountCreated, $user, 'info', [
                'context' => 'google_oauth_registration',
            ]);
        }

        if (! $user->isActiveForAuthentication()) {
            throw new DomainException('This account is not available for authentication.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => CarbonImmutable::now()])->save();
        }

        $oauth = UserOAuthAccount::query()->create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
            'provider_email' => $providerEmail,
            'provider_email_verified' => $providerEmailVerified,
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'token_expires_at' => isset($socialiteUser->expiresIn)
                ? CarbonImmutable::now()->addSeconds((int) $socialiteUser->expiresIn)
                : null,
            'last_used_at' => CarbonImmutable::now(),
            'metadata' => [
                'avatar_url' => $socialiteUser->getAvatar(),
                'nickname' => $socialiteUser->getNickname(),
            ],
        ]);

        $this->auditLogService->record(
            eventType: 'oauth.account.linked',
            action: 'link_google_account',
            actor: $user,
            auditable: $oauth,
            metadata: [
                'provider' => $provider,
                'provider_email_verified' => $providerEmailVerified,
            ],
        );

        if ($createdFromOauth) {
            $this->auditLogService->record(
                eventType: SecurityEventType::AccountCreated->value,
                action: 'register_public_user_via_google',
                actor: $user,
                auditable: $user,
                metadata: ['provider' => $provider],
            );
        }

        return $user;
    }

    private function isProviderEmailVerified(SocialiteUser $socialiteUser): bool
    {
        $raw = is_array($socialiteUser->user ?? null) ? $socialiteUser->user : [];
        $value = $raw['email_verified'] ?? $raw['verified_email'] ?? false;

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}

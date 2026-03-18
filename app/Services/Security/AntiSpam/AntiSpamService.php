<?php

namespace App\Services\Security\AntiSpam;

use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Security\AntiSpam\Contracts\AntiSpamProvider;
use Illuminate\Http\Request;

class AntiSpamService
{
    public function __construct(
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function enabledFor(string $formKey): bool
    {
        return (bool) config("anti_spam.forms.{$formKey}", false);
    }

    public function honeypotField(): string
    {
        return (string) config('anti_spam.honeypot_field', 'company_website');
    }

    public function challengeField(): string
    {
        if ($this->providerName() === 'turnstile') {
            return (string) config('anti_spam.providers.turnstile.response_field', 'cf-turnstile-response');
        }

        return 'anti_spam_token';
    }

    public function shouldRenderWidget(string $formKey): bool
    {
        return $this->enabledFor($formKey)
            && $this->providerName() === 'turnstile'
            && is_string(config('anti_spam.providers.turnstile.site_key'))
            && trim((string) config('anti_spam.providers.turnstile.site_key')) !== '';
    }

    public function turnstileSiteKey(): ?string
    {
        $siteKey = config('anti_spam.providers.turnstile.site_key');

        return is_string($siteKey) && trim($siteKey) !== '' ? trim($siteKey) : null;
    }

    public function providerName(): string
    {
        return (string) config('anti_spam.default', 'null');
    }

    public function verifyRequest(Request $request, string $formKey, ?User $actor = null): AntiSpamVerificationResult
    {
        if (! $this->enabledFor($formKey)) {
            return AntiSpamVerificationResult::pass([
                'form' => $formKey,
                'provider' => 'disabled',
            ]);
        }

        $honeypotValue = $request->input($this->honeypotField());
        if (is_string($honeypotValue) && trim($honeypotValue) !== '') {
            $result = AntiSpamVerificationResult::fail('Submission was rejected as spam.', [
                'form' => $formKey,
                'reason' => 'honeypot_triggered',
            ]);

            $this->recordFailure($actor, $result, $request);

            return $result;
        }

        $result = $this->provider()->verify(
            $request->input($this->challengeField()),
            $request->ip(),
            $request->userAgent(),
            [
                'form' => $formKey,
                'provider' => $this->providerName(),
            ],
        );

        if (! $result->passed) {
            $this->recordFailure($actor, $result, $request);
        }

        return $result;
    }

    private function provider(): AntiSpamProvider
    {
        return match ($this->providerName()) {
            'turnstile' => new TurnstileAntiSpamProvider,
            default => new NullAntiSpamProvider,
        };
    }

    private function recordFailure(?User $actor, AntiSpamVerificationResult $result, Request $request): void
    {
        $this->securityEventService->record('anti_spam.failed', $actor, 'warning', [
            ...$result->context,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

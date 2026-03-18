<?php

namespace App\Services\Security\AntiSpam;

use App\Services\Security\AntiSpam\Contracts\AntiSpamProvider;
use Illuminate\Support\Facades\Http;

class TurnstileAntiSpamProvider implements AntiSpamProvider
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function verify(?string $token, ?string $ipAddress, ?string $userAgent, array $context = []): AntiSpamVerificationResult
    {
        $secretKey = (string) config('anti_spam.providers.turnstile.secret_key');
        $verifyUrl = (string) config('anti_spam.providers.turnstile.verify_url');

        if ($secretKey === '') {
            return AntiSpamVerificationResult::fail('Anti-spam provider is not configured correctly.', $context);
        }

        if (! is_string($token) || trim($token) === '') {
            return AntiSpamVerificationResult::fail('Anti-spam verification is required.', $context);
        }

        $response = Http::asForm()->post($verifyUrl, [
            'secret' => $secretKey,
            'response' => trim($token),
            'remoteip' => $ipAddress,
        ]);

        if (! $response->successful()) {
            return AntiSpamVerificationResult::fail('Anti-spam verification service did not respond successfully.', $context);
        }

        $payload = $response->json();

        if (($payload['success'] ?? false) !== true) {
            return AntiSpamVerificationResult::fail('Anti-spam verification failed.', [
                ...$context,
                'error_codes' => $payload['error-codes'] ?? [],
            ]);
        }

        return AntiSpamVerificationResult::pass([
            ...$context,
            'challenge_ts' => $payload['challenge_ts'] ?? null,
            'hostname' => $payload['hostname'] ?? null,
            'action' => $payload['action'] ?? null,
        ]);
    }
}

<?php

namespace App\Services\Security\AntiSpam;

use App\Services\Security\AntiSpam\Contracts\AntiSpamProvider;

class NullAntiSpamProvider implements AntiSpamProvider
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function verify(?string $token, ?string $ipAddress, ?string $userAgent, array $context = []): AntiSpamVerificationResult
    {
        return AntiSpamVerificationResult::pass($context);
    }
}

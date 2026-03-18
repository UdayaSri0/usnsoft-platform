<?php

namespace App\Services\Security\AntiSpam\Contracts;

use App\Services\Security\AntiSpam\AntiSpamVerificationResult;

interface AntiSpamProvider
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function verify(?string $token, ?string $ipAddress, ?string $userAgent, array $context = []): AntiSpamVerificationResult;
}

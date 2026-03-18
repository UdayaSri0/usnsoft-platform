<?php

namespace App\Services\Security\AntiSpam;

class AntiSpamVerificationResult
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly bool $passed,
        public readonly ?string $message = null,
        public readonly array $context = [],
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public static function pass(array $context = []): self
    {
        return new self(true, null, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function fail(string $message, array $context = []): self
    {
        return new self(false, $message, $context);
    }
}

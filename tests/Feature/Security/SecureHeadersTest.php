<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecureHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied_to_web_responses(): void
    {
        config([
            'security.headers.enabled' => true,
            'security.headers.frame_options' => 'SAMEORIGIN',
            'security.headers.referrer_policy' => 'strict-origin-when-cross-origin',
            'security.headers.permissions_policy' => 'camera=(), geolocation=(), microphone=()',
        ]);

        $response = $this->get(route('login'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
    }
}

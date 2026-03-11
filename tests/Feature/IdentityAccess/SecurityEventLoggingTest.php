<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\SecurityEventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityEventLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_and_suspicious_login_events_are_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'security@example.com',
        ]);

        $this->post('/login', [
            'email' => 'security@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::LoginFailed->value,
            'user_id' => $user->getKey(),
        ]);

        $this->withHeaders(['User-Agent' => 'Device-One'])
            ->post('/login', [
                'email' => 'security@example.com',
                'password' => 'password',
            ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::LoginSuccess->value,
            'user_id' => $user->getKey(),
        ]);

        $this->post('/logout')->assertRedirect('/');

        $this->withHeaders(['User-Agent' => 'Device-Two'])
            ->post('/login', [
                'email' => 'security@example.com',
                'password' => 'password',
            ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::LoginSuspicious->value,
            'user_id' => $user->getKey(),
        ]);

        $this->assertDatabaseHas('user_session_histories', [
            'user_id' => $user->getKey(),
        ]);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->getKey(),
        ]);
    }

    public function test_throttled_logins_are_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
        ]);

        foreach (range(1, 6) as $attempt) {
            $this->post('/login', [
                'email' => 'locked@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::LoginThrottled->value,
        ]);
    }
}

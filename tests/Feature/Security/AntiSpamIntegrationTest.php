<?php

namespace Tests\Feature\Security;

use App\Enums\CoreRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class AntiSpamIntegrationTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_career_application_rejects_submission_when_turnstile_verification_fails(): void
    {
        $this->seedPublicContentCore();
        Storage::fake('local');

        config([
            'anti_spam.default' => 'turnstile',
            'anti_spam.providers.turnstile.secret_key' => 'test-secret',
            'anti_spam.forms.careers_application' => true,
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $job = $this->createJob($superAdmin, [
            'title' => 'Application Security Engineer',
            'slug' => 'application-security-engineer',
        ]);

        $this->from(route('careers.show', ['job' => $job->slug]))
            ->post(route('careers.apply', ['job' => $job->slug]), [
                'full_name' => 'Jane Applicant',
                'email' => 'jane@example.test',
                'cf-turnstile-response' => 'bad-token',
                'cv' => UploadedFile::fake()->create('resume.pdf', 64, 'application/pdf'),
            ])
            ->assertRedirect(route('careers.show', ['job' => $job->slug]))
            ->assertSessionHasErrors('anti_spam');

        $this->assertDatabaseHas('security_events', [
            'event_type' => 'anti_spam.failed',
        ]);
    }
}

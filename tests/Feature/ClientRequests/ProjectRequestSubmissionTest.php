<?php

namespace Tests\Feature\ClientRequests;

use App\Enums\CoreRole;
use App\Modules\ClientRequests\Notifications\StaffProjectRequestSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\ClientRequests\Concerns\InteractsWithClientRequests;
use Tests\TestCase;

class ProjectRequestSubmissionTest extends TestCase
{
    use InteractsWithClientRequests;
    use RefreshDatabase;

    public function test_guest_cannot_access_or_submit_project_request_flow(): void
    {
        $this->seedClientRequestCore();

        $this->get(route('client-requests.create'))
            ->assertRedirect(route('login'));

        $this->post(route('client-requests.store'), $this->projectRequestPayload())
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_submit_project_request(): void
    {
        $this->seedClientRequestCore();

        $user = $this->makeUserWithRole(CoreRole::User, verified: false);

        $this->actingAs($user)
            ->get(route('client-requests.create'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->actingAs($user)
            ->post(route('client-requests.store'), $this->projectRequestPayload($user))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->assertDatabaseCount('project_requests', 0);
    }

    public function test_verified_user_can_submit_request_with_initial_history_audit_attachment_and_notifications(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $salesManager = $this->makeUserWithRole(CoreRole::SalesManager);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $file = $this->uploadedPdf();

        $response = $this->actingAs($requester)->post(route('client-requests.store'), array_merge(
            $this->projectRequestPayload($requester),
            ['attachments' => [$file]],
        ));

        $projectRequest = \App\Modules\ClientRequests\Models\ProjectRequest::query()
            ->with(['currentStatus', 'attachments'])
            ->firstOrFail();

        $response->assertRedirect(route('client-requests.show', ['projectRequest' => $projectRequest]));
        $this->assertSame('submitted', $projectRequest->currentStatus->code);
        $this->assertCount(1, $projectRequest->attachments);

        $this->assertDatabaseHas('project_requests', [
            'id' => $projectRequest->getKey(),
            'user_id' => $requester->getKey(),
            'project_title' => 'Protected request intake build',
        ]);

        $this->assertDatabaseHas('status_histories', [
            'statusable_type' => 'project_request',
            'statusable_id' => $projectRequest->getKey(),
            'to_state' => 'submitted',
            'visibility' => 'requester_visible',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'project_request',
            'auditable_id' => $projectRequest->getKey(),
            'event_type' => 'requests.created',
        ]);

        $this->assertDatabaseHas('project_request_attachments', [
            'project_request_id' => $projectRequest->getKey(),
            'original_name' => 'scope.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $salesManager->getKey(),
            'notifiable_type' => $salesManager->getMorphClass(),
            'type' => StaffProjectRequestSubmittedNotification::class,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $superAdmin->getKey(),
            'notifiable_type' => $superAdmin->getMorphClass(),
            'type' => StaffProjectRequestSubmittedNotification::class,
        ]);
    }

    public function test_submission_validation_rejects_missing_required_fields(): void
    {
        $this->seedClientRequestCore();

        $user = $this->makeUserWithRole(CoreRole::User);

        $this->actingAs($user)
            ->from(route('client-requests.create'))
            ->post(route('client-requests.store'), [])
            ->assertRedirect(route('client-requests.create'))
            ->assertSessionHasErrors([
                'requester_name',
                'contact_email',
                'project_title',
                'project_summary',
                'project_description',
                'project_type',
            ]);
    }

    public function test_submission_validation_rejects_oversized_files(): void
    {
        $this->seedClientRequestCore();

        $user = $this->makeUserWithRole(CoreRole::User);
        $oversizedFile = UploadedFile::fake()->create('oversized.pdf', 26000, 'application/pdf');

        $this->actingAs($user)
            ->from(route('client-requests.create'))
            ->post(route('client-requests.store'), array_merge(
                $this->projectRequestPayload($user),
                ['attachments' => [$oversizedFile]],
            ))
            ->assertRedirect(route('client-requests.create'))
            ->assertSessionHasErrors('attachments.0');
    }

    public function test_submission_validation_rejects_invalid_file_types(): void
    {
        $this->seedClientRequestCore();

        $user = $this->makeUserWithRole(CoreRole::User);
        $invalidFile = UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream');

        $this->actingAs($user)
            ->from(route('client-requests.create'))
            ->post(route('client-requests.store'), array_merge(
                $this->projectRequestPayload($user),
                ['attachments' => [$invalidFile]],
            ))
            ->assertRedirect(route('client-requests.create'))
            ->assertSessionHasErrors('attachments.0');
    }
}

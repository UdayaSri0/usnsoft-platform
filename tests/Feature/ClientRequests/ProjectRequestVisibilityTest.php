<?php

namespace Tests\Feature\ClientRequests;

use App\Enums\CoreRole;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Services\ProjectRequestCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ClientRequests\Concerns\InteractsWithClientRequests;
use Tests\TestCase;

class ProjectRequestVisibilityTest extends TestCase
{
    use InteractsWithClientRequests;
    use RefreshDatabase;

    public function test_user_can_only_see_their_own_requests(): void
    {
        $this->seedClientRequestCore();

        $owner = $this->makeUserWithRole(CoreRole::User);
        $otherUser = $this->makeUserWithRole(CoreRole::User);
        $ownerRequest = $this->submitProjectRequest($owner, ['project_title' => 'Owner request']);
        $otherRequest = $this->submitProjectRequest($otherUser, ['project_title' => 'Other request']);

        $this->actingAs($owner)
            ->get(route('client-requests.index'))
            ->assertOk()
            ->assertSee('Owner request')
            ->assertDontSee('Other request');

        $this->actingAs($owner)
            ->get(route('client-requests.show', ['projectRequest' => $otherRequest]))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('client-requests.show', ['projectRequest' => $ownerRequest]))
            ->assertOk();
    }

    public function test_internal_comments_are_hidden_from_requester_but_requester_visible_comments_are_shown(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $salesManager = $this->makeUserWithRole(CoreRole::SalesManager);
        $projectRequest = $this->submitProjectRequest($requester);
        $commentService = app(ProjectRequestCommentService::class);

        $commentService->add(
            projectRequest: $projectRequest,
            author: $salesManager,
            body: 'Internal qualification note that must remain hidden from the requester.',
            visibility: ProjectRequestCommentVisibility::Internal,
        );

        $commentService->add(
            projectRequest: $projectRequest,
            author: $salesManager,
            body: 'Visible update for the requester about next review steps.',
            visibility: ProjectRequestCommentVisibility::RequesterVisible,
        );

        $this->actingAs($requester)
            ->get(route('client-requests.show', ['projectRequest' => $projectRequest]))
            ->assertOk()
            ->assertSee('Visible update for the requester about next review steps.')
            ->assertDontSee('Internal qualification note that must remain hidden from the requester.');
    }

    public function test_project_request_attachments_are_protected_and_only_authorized_owner_can_download(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $otherUser = $this->makeUserWithRole(CoreRole::User);
        $projectRequest = $this->submitProjectRequest($requester, attachments: [$this->uploadedPdf()]);
        $attachment = $projectRequest->attachments()->firstOrFail();

        $this->get(route('client-requests.attachments.show', ['projectRequest' => $projectRequest, 'attachment' => $attachment]))
            ->assertRedirect(route('login'));

        $this->actingAs($otherUser)
            ->get(route('client-requests.attachments.show', ['projectRequest' => $projectRequest, 'attachment' => $attachment]))
            ->assertForbidden();

        $response = $this->actingAs($requester)
            ->get(route('client-requests.attachments.show', ['projectRequest' => $projectRequest, 'attachment' => $attachment]));

        $response->assertOk();
        $response->assertDownload('scope.pdf');
        $this->assertNull($response->headers->get('Location'));

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'project_request_attachment',
            'auditable_id' => $attachment->getKey(),
            'event_type' => 'requests.attachment.downloaded',
        ]);
    }
}

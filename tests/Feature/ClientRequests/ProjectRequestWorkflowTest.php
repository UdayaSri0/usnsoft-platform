<?php

namespace Tests\Feature\ClientRequests;

use App\Enums\CoreRole;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use App\Modules\ClientRequests\Notifications\RequesterProjectRequestStatusChangedNotification;
use App\Modules\ClientRequests\Services\ProjectRequestCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ClientRequests\Concerns\InteractsWithClientRequests;
use Tests\TestCase;

class ProjectRequestWorkflowTest extends TestCase
{
    use InteractsWithClientRequests;
    use RefreshDatabase;

    public function test_authorized_staff_can_access_request_queue_but_unauthorized_staff_cannot(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $salesManager = $this->makeUserWithRole(CoreRole::SalesManager);
        $developer = $this->makeUserWithRole(CoreRole::Developer);
        $projectRequest = $this->submitProjectRequest($requester, ['project_title' => 'Queue visibility request']);

        $this->actingAs($salesManager)
            ->get(route('admin.client-requests.index'))
            ->assertOk()
            ->assertSee('Queue visibility request');

        $this->actingAs($developer)
            ->get(route('admin.client-requests.index'))
            ->assertForbidden();

        $this->actingAs($salesManager)
            ->get(route('admin.client-requests.show', ['projectRequest' => $projectRequest]))
            ->assertOk();
    }

    public function test_status_changes_append_history_write_audit_logs_and_notify_requester(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $salesManager = $this->makeUserWithRole(CoreRole::SalesManager);
        $projectRequest = $this->submitProjectRequest($requester);
        $underReview = $this->requestStatus(ProjectRequestSystemStatus::UnderReview->value);

        $this->actingAs($salesManager)
            ->post(route('admin.client-requests.status.transition', ['projectRequest' => $projectRequest]), [
                'status_id' => $underReview->getKey(),
                'change_note' => 'Review started by sales.',
            ])
            ->assertRedirect();

        $projectRequest->refresh();

        $this->assertSame($underReview->getKey(), $projectRequest->current_status_id);
        $this->assertDatabaseHas('status_histories', [
            'statusable_type' => 'project_request',
            'statusable_id' => $projectRequest->getKey(),
            'from_state' => 'submitted',
            'to_state' => 'under_review',
        ]);

        $this->assertSame(2, $projectRequest->statusHistories()->count());

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'project_request',
            'auditable_id' => $projectRequest->getKey(),
            'event_type' => 'requests.status.changed',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->getKey(),
            'notifiable_type' => $requester->getMorphClass(),
            'type' => RequesterProjectRequestStatusChangedNotification::class,
        ]);
    }

    public function test_only_privileged_staff_can_create_and_apply_custom_statuses(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $salesManager = $this->makeUserWithRole(CoreRole::SalesManager);
        $projectRequest = $this->submitProjectRequest($requester);

        $this->actingAs($salesManager)
            ->post(route('admin.client-requests.statuses.store'), [
                'name' => 'Awaiting Vendor Response',
                'code' => 'awaiting_vendor_response',
                'system_status' => ProjectRequestSystemStatus::UnderReview->value,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.client-requests.statuses.store'), [
                'name' => 'Awaiting Vendor Response',
                'code' => 'awaiting_vendor_response',
                'system_status' => ProjectRequestSystemStatus::UnderReview->value,
                'visible_to_requester' => false,
                'sort_order' => 250,
                'badge_tone' => 'warning',
            ])
            ->assertRedirect();

        $customStatus = $this->requestStatus('awaiting_vendor_response');

        $this->actingAs($salesManager)
            ->post(route('admin.client-requests.status.transition', ['projectRequest' => $projectRequest]), [
                'status_id' => $customStatus->getKey(),
                'change_note' => 'Attempting to use a custom status without permission.',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.client-requests.status.transition', ['projectRequest' => $projectRequest]), [
                'status_id' => $customStatus->getKey(),
                'change_note' => 'Custom workflow note.',
            ])
            ->assertRedirect();

        $this->assertSame($customStatus->getKey(), $projectRequest->fresh()->current_status_id);
    }

    public function test_comment_visibility_changes_are_audited_and_exposed_only_after_update(): void
    {
        $this->seedClientRequestCore();

        $requester = $this->makeUserWithRole(CoreRole::User);
        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $projectRequest = $this->submitProjectRequest($requester);
        $comment = app(ProjectRequestCommentService::class)->add(
            projectRequest: $projectRequest,
            author: $admin,
            body: 'Internal follow-up note that will later be shared with the requester.',
            visibility: ProjectRequestCommentVisibility::Internal,
        );

        $this->actingAs($requester)
            ->get(route('client-requests.show', ['projectRequest' => $projectRequest]))
            ->assertDontSee('Internal follow-up note that will later be shared with the requester.');

        $this->actingAs($admin)
            ->put(route('admin.client-requests.comments.visibility.update', ['comment' => $comment]), [
                'visibility_type' => ProjectRequestCommentVisibility::RequesterVisible->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'project_request_comment',
            'auditable_id' => $comment->getKey(),
            'event_type' => 'requests.comment.visibility_changed',
        ]);

        $this->actingAs($requester)
            ->get(route('client-requests.show', ['projectRequest' => $projectRequest]))
            ->assertSee('Internal follow-up note that will later be shared with the requester.');
    }
}

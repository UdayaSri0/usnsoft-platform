<?php

namespace App\Modules\ClientRequests\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Enums\ProjectRequestType;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Requests\ProjectRequestStatusTransitionRequest;
use App\Modules\ClientRequests\Services\ProjectRequestTimelineService;
use App\Modules\ClientRequests\Services\ProjectRequestWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectRequestController extends Controller
{
    public function __construct(
        private readonly ProjectRequestTimelineService $timelineService,
        private readonly ProjectRequestWorkflowService $workflowService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProjectRequest::class);

        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $submittedFrom = $request->string('submitted_from')->toString();
        $submittedTo = $request->string('submitted_to')->toString();

        $projectRequests = ProjectRequest::query()
            ->with(['currentStatus', 'requester'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($searchQuery) use ($q): void {
                    $searchQuery
                        ->where('project_title', 'like', '%'.$q.'%')
                        ->orWhere('project_summary', 'like', '%'.$q.'%')
                        ->orWhere('contact_email', 'like', '%'.$q.'%')
                        ->orWhere('company_name', 'like', '%'.$q.'%')
                        ->orWhere('requester_name', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->whereHas('currentStatus', fn ($statusQuery) => $statusQuery->where('code', $status)))
            ->when($type !== '', fn ($query) => $query->where('project_type', $type))
            ->when($submittedFrom !== '', fn ($query) => $query->whereDate('submitted_at', '>=', $submittedFrom))
            ->when($submittedTo !== '', fn ($query) => $query->whereDate('submitted_at', '<=', $submittedTo))
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.client-requests.index', [
            'projectRequests' => $projectRequests,
            'statuses' => ProjectRequestStatus::query()->ordered()->get(),
            'projectTypes' => ProjectRequestType::cases(),
            'filters' => compact('q', 'status', 'type', 'submittedFrom', 'submittedTo'),
        ]);
    }

    public function show(Request $request, ProjectRequest $projectRequest): View
    {
        $this->authorize('view', $projectRequest);

        $projectRequest->load([
            'requester',
            'currentStatus',
            'attachments.uploader',
            'comments.author',
            'comments.visibilityChanger',
            'statusHistories.changer',
            'events.actor',
        ]);

        $comments = $projectRequest->comments->sortByDesc('created_at')->values();
        $commentIds = $comments->pluck('id')->all();
        $attachmentIds = $projectRequest->attachments->pluck('id')->all();

        $auditLogs = $request->user()->can('viewAudit', $projectRequest)
            ? AuditLog::query()
                ->with('actor')
                ->where(function ($query) use ($attachmentIds, $commentIds, $projectRequest): void {
                    $query->where(function ($requestQuery) use ($projectRequest): void {
                        $requestQuery
                            ->where('auditable_type', $projectRequest->getMorphClass())
                            ->where('auditable_id', $projectRequest->getKey());
                    });

                    if ($commentIds !== []) {
                        $query->orWhere(function ($commentQuery) use ($commentIds): void {
                            $commentQuery
                                ->where('auditable_type', 'project_request_comment')
                                ->whereIn('auditable_id', $commentIds);
                        });
                    }

                    if ($attachmentIds !== []) {
                        $query->orWhere(function ($attachmentQuery) use ($attachmentIds): void {
                            $attachmentQuery
                                ->where('auditable_type', 'project_request_attachment')
                                ->whereIn('auditable_id', $attachmentIds);
                        });
                    }
                })
                ->orderByDesc('occurred_at')
                ->limit(50)
                ->get()
            : collect();

        return view('admin.client-requests.show', [
            'projectRequest' => $projectRequest,
            'availableStatuses' => $this->workflowService->availableStatusesFor($request->user()),
            'internalComments' => $comments
                ->filter(static fn ($comment): bool => $comment->visibility_type === ProjectRequestCommentVisibility::Internal)
                ->values(),
            'requesterVisibleComments' => $comments
                ->filter(static fn ($comment): bool => $comment->visibility_type === ProjectRequestCommentVisibility::RequesterVisible)
                ->values(),
            'requesterTimeline' => $this->timelineService->forRequester($projectRequest),
            'auditLogs' => $auditLogs,
        ]);
    }

    public function transitionStatus(ProjectRequestStatusTransitionRequest $request, ProjectRequest $projectRequest): RedirectResponse
    {
        $this->authorize('updateStatus', $projectRequest);

        $status = ProjectRequestStatus::query()->findOrFail((int) $request->validated()['status_id']);

        $this->workflowService->changeStatus(
            projectRequest: $projectRequest,
            toStatus: $status,
            actor: $request->user(),
            note: $request->string('change_note')->toString() ?: null,
        );

        return back()->with('status', 'project-request-status-updated');
    }
}

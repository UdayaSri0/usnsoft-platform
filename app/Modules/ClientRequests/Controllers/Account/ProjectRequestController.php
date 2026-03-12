<?php

namespace App\Modules\ClientRequests\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Enums\ProjectRequestType;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Requests\ProjectRequestStoreRequest;
use App\Modules\ClientRequests\Services\ProjectRequestSubmissionService;
use App\Modules\ClientRequests\Services\ProjectRequestTimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProjectRequestController extends Controller
{
    public function __construct(
        private readonly ProjectRequestSubmissionService $submissionService,
        private readonly ProjectRequestTimelineService $timelineService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        return view('client-requests.index', [
            'projectRequests' => ProjectRequest::query()
                ->ownedBy($request->user())
                ->with('currentStatus')
                ->when($status !== '', fn ($query) => $query->whereHas('currentStatus', fn ($statusQuery) => $statusQuery->where('code', $status)))
                ->when($type !== '', fn ($query) => $query->where('project_type', $type))
                ->orderByDesc('submitted_at')
                ->paginate(12)
                ->withQueryString(),
            'statuses' => ProjectRequestStatus::query()
                ->ordered()
                ->get(),
            'projectTypes' => ProjectRequestType::cases(),
            'filters' => compact('status', 'type'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ProjectRequest::class);

        return view('client-requests.create', [
            'projectTypes' => ProjectRequestType::cases(),
            'user' => $request->user(),
            'allowedExtensions' => collect((array) config('client_requests.allowed_extensions', []))
                ->map(static fn (string $extension): string => strtoupper($extension))
                ->implode(', '),
            'maxUploadMb' => (int) ceil(((int) config('client_requests.max_upload_kb', 25600)) / 1024),
        ]);
    }

    public function store(ProjectRequestStoreRequest $request): RedirectResponse
    {
        $projectRequest = $this->submissionService->submit(
            user: $request->user(),
            payload: $request->validated(),
            attachments: $request->file('attachments', []),
        );

        return redirect()
            ->route('client-requests.show', ['projectRequest' => $projectRequest])
            ->with('status', 'project-request-submitted');
    }

    public function show(Request $request, ProjectRequest $projectRequest): View
    {
        $this->authorize('view', $projectRequest);

        $projectRequest->load([
            'currentStatus',
            'events',
            'statusHistories.changer',
        ]);

        $requesterVisibleComments = $projectRequest->comments()
            ->requesterVisible()
            ->with('author')
            ->get();

        $visibleAttachments = $projectRequest->attachments()
            ->where('visible_to_requester', true)
            ->get();

        return view('client-requests.show', [
            'projectRequest' => $projectRequest,
            'timeline' => $this->timelineService->forRequester($projectRequest),
            'requesterVisibleComments' => $requesterVisibleComments,
            'visibleAttachments' => $visibleAttachments,
            'canAddComment' => $request->user()->can('createPublicComment', $projectRequest),
            'displaySections' => $this->displaySections($projectRequest),
        ]);
    }

    /**
     * @return Collection<int, array{label: string, value: mixed}>
     */
    private function displaySections(ProjectRequest $projectRequest): Collection
    {
        return collect([
            ['label' => 'Requester', 'value' => $projectRequest->requester_name],
            ['label' => 'Company', 'value' => $projectRequest->company_name],
            ['label' => 'Contact Email', 'value' => $projectRequest->contact_email],
            ['label' => 'Contact Phone', 'value' => $projectRequest->contact_phone],
            ['label' => 'Project Type', 'value' => $projectRequest->project_type?->label()],
            ['label' => 'Budget', 'value' => $projectRequest->budget !== null ? '$'.number_format((float) $projectRequest->budget, 2) : null],
            ['label' => 'Deadline', 'value' => $projectRequest->deadline?->format('M j, Y')],
            ['label' => 'Submitted', 'value' => $projectRequest->submitted_at?->format('M j, Y g:i A')],
        ])->filter(static fn (array $item): bool => filled($item['value']))->values();
    }
}

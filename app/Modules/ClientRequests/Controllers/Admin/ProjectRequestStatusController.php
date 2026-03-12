<?php

namespace App\Modules\ClientRequests\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Requests\ProjectRequestStatusStoreRequest;
use App\Modules\ClientRequests\Requests\ProjectRequestStatusUpdateRequest;
use App\Modules\ClientRequests\Services\ProjectRequestStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectRequestStatusController extends Controller
{
    public function __construct(
        private readonly ProjectRequestStatusService $statusService,
    ) {}

    public function index(): View
    {
        $this->authorize('manageStatuses', ProjectRequest::class);

        return view('admin.client-requests.statuses.index', [
            'statuses' => ProjectRequestStatus::query()->ordered()->get(),
            'systemStatuses' => ProjectRequestSystemStatus::cases(),
        ]);
    }

    public function store(ProjectRequestStatusStoreRequest $request): RedirectResponse
    {
        $this->authorize('manageStatuses', ProjectRequest::class);

        $this->statusService->createCustomStatus($request->user(), $request->validated());

        return back()->with('status', 'project-request-status-created');
    }

    public function update(ProjectRequestStatusUpdateRequest $request, ProjectRequestStatus $status): RedirectResponse
    {
        $this->authorize('manageStatuses', ProjectRequest::class);
        abort_if($status->is_system, 403);

        $this->statusService->updateCustomStatus($status, $request->user(), $request->validated());

        return back()->with('status', 'project-request-status-updated');
    }
}

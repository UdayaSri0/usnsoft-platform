<?php

namespace App\Modules\ClientRequests\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Requests\ProjectRequestCommentStoreRequest;
use App\Modules\ClientRequests\Services\ProjectRequestCommentService;
use Illuminate\Http\RedirectResponse;

class ProjectRequestCommentController extends Controller
{
    public function __construct(
        private readonly ProjectRequestCommentService $commentService,
    ) {}

    public function store(ProjectRequestCommentStoreRequest $request, ProjectRequest $projectRequest): RedirectResponse
    {
        $this->authorize('createPublicComment', $projectRequest);

        $this->commentService->add(
            projectRequest: $projectRequest,
            author: $request->user(),
            body: $request->string('body')->toString(),
            visibility: ProjectRequestCommentVisibility::RequesterVisible,
        );

        return redirect()
            ->route('client-requests.show', ['projectRequest' => $projectRequest])
            ->with('status', 'project-request-comment-created');
    }
}

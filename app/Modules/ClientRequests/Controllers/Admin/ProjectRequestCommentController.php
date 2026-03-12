<?php

namespace App\Modules\ClientRequests\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestComment;
use App\Modules\ClientRequests\Requests\ProjectRequestCommentStoreRequest;
use App\Modules\ClientRequests\Requests\ProjectRequestCommentVisibilityRequest;
use App\Modules\ClientRequests\Services\ProjectRequestCommentService;
use Illuminate\Http\RedirectResponse;

class ProjectRequestCommentController extends Controller
{
    public function __construct(
        private readonly ProjectRequestCommentService $commentService,
    ) {}

    public function storeInternal(ProjectRequestCommentStoreRequest $request, ProjectRequest $projectRequest): RedirectResponse
    {
        $this->authorize('createInternalComment', $projectRequest);

        $this->commentService->add(
            projectRequest: $projectRequest,
            author: $request->user(),
            body: $request->string('body')->toString(),
            visibility: ProjectRequestCommentVisibility::Internal,
        );

        return back()->with('status', 'project-request-internal-comment-created');
    }

    public function storeRequesterVisible(ProjectRequestCommentStoreRequest $request, ProjectRequest $projectRequest): RedirectResponse
    {
        $this->authorize('createPublicComment', $projectRequest);

        $this->commentService->add(
            projectRequest: $projectRequest,
            author: $request->user(),
            body: $request->string('body')->toString(),
            visibility: ProjectRequestCommentVisibility::RequesterVisible,
        );

        return back()->with('status', 'project-request-requester-comment-created');
    }

    public function updateVisibility(ProjectRequestCommentVisibilityRequest $request, ProjectRequestComment $comment): RedirectResponse
    {
        $this->authorize('changeVisibility', $comment);

        $this->commentService->changeVisibility(
            comment: $comment,
            visibility: ProjectRequestCommentVisibility::from($request->string('visibility_type')->toString()),
            actor: $request->user(),
        );

        return back()->with('status', 'project-request-comment-visibility-updated');
    }
}

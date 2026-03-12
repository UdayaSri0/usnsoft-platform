<?php

namespace App\Modules\ClientRequests\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestAttachment;
use App\Modules\ClientRequests\Services\ProjectRequestAttachmentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectRequestAttachmentController extends Controller
{
    public function __construct(
        private readonly ProjectRequestAttachmentService $attachmentService,
    ) {}

    public function showForRequester(Request $request, ProjectRequest $projectRequest, ProjectRequestAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->project_request_id === $projectRequest->getKey(), 404);
        $this->authorize('view', $attachment);

        return $this->attachmentService->download($attachment, $request->user());
    }

    public function showForStaff(Request $request, ProjectRequestAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        return $this->attachmentService->download($attachment, $request->user());
    }
}

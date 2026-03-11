<?php

namespace App\Modules\Pages\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ContentWorkflowState;
use App\Modules\Pages\Models\PageVersion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalQueueController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cms.approvals.view_queue'), 403);

        $versions = PageVersion::query()
            ->with(['page', 'page.creator'])
            ->whereIn('workflow_state', [
                ContentWorkflowState::InReview->value,
                ContentWorkflowState::Approved->value,
                ContentWorkflowState::Scheduled->value,
            ])
            ->orderByRaw("case when workflow_state = 'in_review' then 0 else 1 end")
            ->orderByDesc('submitted_at')
            ->paginate(25);

        return view('admin.cms.approvals.index', [
            'versions' => $versions,
        ]);
    }
}

<?php

namespace App\Modules\Workflow\Actions;

use App\Contracts\Approval\RequiresApproval;
use App\Models\User;
use App\Services\Approval\ApprovalWorkflowService;
use App\Support\Action;
use Illuminate\Database\Eloquent\Model;

class SubmitForApprovalAction extends Action
{
    public function __construct(
        private readonly ApprovalWorkflowService $approvalWorkflowService,
    ) {}

    /**
     * @param  Model&RequiresApproval  $approvable
     * @param  array<string, mixed>  $metadata
     */
    public function execute(Model $approvable, User $requestedBy, ?string $comment = null, array $metadata = []): void
    {
        $this->approvalWorkflowService->submit($approvable, $requestedBy, $comment, $metadata);
    }
}

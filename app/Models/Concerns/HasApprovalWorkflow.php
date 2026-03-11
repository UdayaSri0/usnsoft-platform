<?php

namespace App\Models\Concerns;

use App\Enums\ApprovalState;
use App\Modules\Workflow\Models\ApprovalRequest;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasApprovalWorkflow
{
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function getApprovalState(): ?ApprovalState
    {
        $state = $this->getAttribute('approval_state');

        return is_string($state) ? ApprovalState::tryFrom($state) : null;
    }

    public function setApprovalState(ApprovalState $state): void
    {
        $this->setAttribute('approval_state', $state->value);
    }
}

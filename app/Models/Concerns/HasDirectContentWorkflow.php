<?php

namespace App\Models\Concerns;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Modules\Seo\Models\SeoMeta;
use App\Modules\Workflow\Models\ApprovalRecord;
use App\Modules\Workflow\Models\StatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasDirectContentWorkflow
{
    use HasApprovalWorkflow;

    public function getWorkflowState(): ?ContentWorkflowState
    {
        $state = $this->getAttribute('workflow_state');

        if ($state instanceof ContentWorkflowState) {
            return $state;
        }

        return is_string($state) ? ContentWorkflowState::tryFrom($state) : null;
    }

    public function setWorkflowState(ContentWorkflowState $state): void
    {
        $this->setAttribute('workflow_state', $state->value);
    }

    public function getApprovalState(): ?ApprovalState
    {
        $state = $this->getAttribute('approval_state');

        if ($state instanceof ApprovalState) {
            return $state;
        }

        return is_string($state) ? ApprovalState::tryFrom($state) : null;
    }

    public function setApprovalState(ApprovalState $state): void
    {
        $this->setAttribute('approval_state', $state->value);
    }

    public function getVisibilityState(): ?VisibilityState
    {
        $state = $this->getAttribute('visibility');

        if ($state instanceof VisibilityState) {
            return $state;
        }

        return is_string($state) ? VisibilityState::tryFrom($state) : null;
    }

    public function isPublished(): bool
    {
        return $this->getWorkflowState() === ContentWorkflowState::Published
            && $this->getApprovalState() === ApprovalState::Approved
            && $this->getVisibilityState()?->isPublic() === true;
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')->orderByDesc('changed_at');
    }

    public function approvalRecords(): MorphMany
    {
        return $this->morphMany(ApprovalRecord::class, 'approvable')->orderByDesc('created_at');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('visibility', VisibilityState::Public->value)
            ->where('workflow_state', ContentWorkflowState::Published->value)
            ->where('approval_state', ApprovalState::Approved->value)
            ->whereNotNull('published_at');
    }
}

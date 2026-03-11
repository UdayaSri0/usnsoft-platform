<?php

namespace App\Models\Concerns;

use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;

trait IsPublishable
{
    public function getWorkflowState(): ?ContentWorkflowState
    {
        $state = $this->getAttribute('workflow_state');

        return is_string($state) ? ContentWorkflowState::tryFrom($state) : null;
    }

    public function setWorkflowState(ContentWorkflowState $state): void
    {
        $this->setAttribute('workflow_state', $state->value);
    }

    public function getVisibilityState(): ?VisibilityState
    {
        $state = $this->getAttribute('visibility');

        return is_string($state) ? VisibilityState::tryFrom($state) : null;
    }

    public function isPublished(): bool
    {
        return $this->getWorkflowState() === ContentWorkflowState::Published
            && $this->getVisibilityState()?->isPublic() === true;
    }
}

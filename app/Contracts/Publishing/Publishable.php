<?php

namespace App\Contracts\Publishing;

use App\Enums\ContentWorkflowState;

interface Publishable
{
    public function getWorkflowState(): ?ContentWorkflowState;

    public function setWorkflowState(ContentWorkflowState $state): void;

    public function isPublished(): bool;
}

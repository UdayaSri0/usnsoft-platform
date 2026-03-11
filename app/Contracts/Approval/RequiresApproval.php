<?php

namespace App\Contracts\Approval;

use App\Enums\ApprovalState;

interface RequiresApproval
{
    public function getApprovalState(): ?ApprovalState;

    public function setApprovalState(ApprovalState $state): void;
}

<?php

namespace App\Modules\Pages\Actions;

use App\Models\User;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Services\CmsWorkflowService;
use App\Support\Action;

class SubmitPageVersionForReviewAction extends Action
{
    public function __construct(
        private readonly CmsWorkflowService $cmsWorkflowService,
    ) {}

    public function execute(PageVersion $version, User $actor, ?string $notes = null): void
    {
        $this->cmsWorkflowService->submitForReview($version, $actor, $notes);
    }
}

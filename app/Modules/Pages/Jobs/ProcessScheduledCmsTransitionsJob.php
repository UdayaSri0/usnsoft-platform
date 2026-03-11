<?php

namespace App\Modules\Pages\Jobs;

use App\Modules\Pages\Services\CmsWorkflowService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessScheduledCmsTransitionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(CmsWorkflowService $cmsWorkflowService): void
    {
        $cmsWorkflowService->processScheduledTransitions();
    }
}

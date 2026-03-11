<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Modules\Pages\Services\CmsWorkflowService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cms:process-scheduled-pages', function (CmsWorkflowService $cmsWorkflowService) {
    $results = $cmsWorkflowService->processScheduledTransitions();

    $this->info("Scheduled publish executed: {$results['published']}");
    $this->info("Scheduled archive executed: {$results['archived']}");
})->purpose('Publish or archive CMS page versions based on schedule timestamps');

Schedule::command('cms:process-scheduled-pages')->everyMinute()->withoutOverlapping();

<?php

use App\Modules\Pages\Services\CmsWorkflowService;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Careers\Models\Job;
use App\Modules\Faq\Models\Faq;
use App\Modules\Products\Services\ProductWorkflowService;
use App\Modules\Showcase\Models\Achievement;
use App\Modules\Showcase\Models\Partner;
use App\Modules\Showcase\Models\TeamMember;
use App\Modules\Showcase\Models\Testimonial;
use App\Modules\Showcase\Models\TimelineEntry;
use App\Services\Content\DirectContentWorkflowService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cms:process-scheduled-pages', function (CmsWorkflowService $cmsWorkflowService) {
    $results = $cmsWorkflowService->processScheduledTransitions();

    $this->info("Scheduled publish executed: {$results['published']}");
    $this->info("Scheduled archive executed: {$results['archived']}");
})->purpose('Publish or archive CMS page versions based on schedule timestamps');

Artisan::command('products:process-scheduled-versions', function (ProductWorkflowService $productWorkflowService) {
    $results = $productWorkflowService->processScheduledTransitions();

    $this->info("Scheduled product publish executed: {$results['published']}");
    $this->info("Scheduled product archive executed: {$results['archived']}");
})->purpose('Publish or archive product versions based on schedule timestamps');

Artisan::command('content:process-scheduled-public-content', function (DirectContentWorkflowService $workflowService) {
    $publishableModels = [
        'blog' => BlogPost::class,
        'faq' => Faq::class,
        'careers' => Job::class,
        'testimonials' => Testimonial::class,
        'partners' => Partner::class,
        'team' => TeamMember::class,
        'timeline' => TimelineEntry::class,
        'achievements' => Achievement::class,
    ];

    $totals = ['published' => 0, 'archived' => 0];

    foreach ($publishableModels as $label => $modelClass) {
        $results = $workflowService->processScheduledTransitions($modelClass);

        $totals['published'] += $results['published'];
        $totals['archived'] += $results['archived'];

        if ($results['published'] > 0 || $results['archived'] > 0) {
            $this->info(ucfirst($label).": published {$results['published']}, archived {$results['archived']}");
        }
    }

    $this->info("Scheduled public content publish executed: {$totals['published']}");
    $this->info("Scheduled public content archive executed: {$totals['archived']}");
})->purpose('Publish or archive scheduled public content modules based on workflow timestamps');

Schedule::command('cms:process-scheduled-pages')->everyMinute()->withoutOverlapping();
Schedule::command('products:process-scheduled-versions')->everyMinute()->withoutOverlapping();
Schedule::command('content:process-scheduled-public-content')->everyMinute()->withoutOverlapping();

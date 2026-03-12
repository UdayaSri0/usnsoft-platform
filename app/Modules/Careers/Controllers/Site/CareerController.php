<?php

namespace App\Modules\Careers\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Requests\JobApplicationStoreRequest;
use App\Modules\Careers\Services\CareerCatalogService;
use App\Modules\Careers\Services\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function __construct(
        private readonly CareerCatalogService $careerCatalogService,
        private readonly JobApplicationService $jobApplicationService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'department' => $request->string('department')->toString(),
            'employment_type' => $request->string('employment_type')->toString(),
            'location' => $request->string('location')->toString(),
        ];

        return view('careers.index', [
            'jobs' => $this->careerCatalogService->publicListing($filters),
            'departments' => Job::query()->publiclyVisible()->whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'employmentTypes' => Job::query()->publiclyVisible()->whereNotNull('employment_type')->distinct()->orderBy('employment_type')->pluck('employment_type'),
            'locations' => Job::query()->publiclyVisible()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location'),
            'filters' => $filters,
            'seo' => [
                'meta_title' => 'Careers | '.config('app.name', 'USNsoft'),
                'meta_description' => 'Browse open roles at USNsoft and apply through the protected careers workflow.',
                'canonical_url' => route('careers.index'),
                'og_title' => 'Careers | '.config('app.name', 'USNsoft'),
                'og_description' => 'Explore current opportunities at USNsoft and submit applications through the protected careers workflow.',
            ],
        ]);
    }

    public function show(Job $job): View
    {
        $resolved = $this->careerCatalogService->resolvePublicJob($job->slug);

        abort_unless($resolved, 404);

        $descriptionSource = $resolved->seoMeta?->meta_description ?? $resolved->summary ?? $resolved->description;

        return view('careers.show', [
            'job' => $resolved,
            'seo' => [
                'meta_title' => $resolved->seoMeta?->meta_title ?? $resolved->title.' | Careers | '.config('app.name', 'USNsoft'),
                'meta_description' => Str::limit(trim(strip_tags((string) $descriptionSource)), 160),
                'canonical_url' => route('careers.show', ['job' => $resolved->slug]),
                'og_title' => $resolved->seoMeta?->og_title ?? $resolved->title.' | '.config('app.name', 'USNsoft'),
                'og_description' => Str::limit(trim(strip_tags((string) ($resolved->seoMeta?->og_description ?? $descriptionSource))), 200),
            ],
        ]);
    }

    public function apply(JobApplicationStoreRequest $request, Job $job): RedirectResponse
    {
        $resolved = $this->careerCatalogService->resolvePublicJob($job->slug);

        abort_unless($resolved, 404);

        $this->jobApplicationService->submit($resolved, $request->validated(), $request->allFiles());

        return redirect()
            ->route('careers.show', ['job' => $resolved->slug])
            ->with('status', 'application-submitted');
    }
}

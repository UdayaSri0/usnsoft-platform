<?php

namespace App\Modules\Careers\Controllers\Admin;

use App\Enums\ContentWorkflowState;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentWorkflowActionRequest;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Requests\JobStoreRequest;
use App\Modules\Careers\Services\JobService;
use App\Services\Content\DirectContentWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        private readonly JobService $jobService,
        private readonly DirectContentWorkflowService $workflowService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Job::class);

        $status = $request->string('status')->toString();
        $department = $request->string('department')->toString();
        $employmentType = $request->string('employment_type')->toString();
        $q = $request->string('q')->toString();

        $jobs = Job::query()
            ->search($q)
            ->when($status !== '', fn ($query) => $query->where('workflow_state', $status))
            ->when($department !== '', fn ($query) => $query->where('department', $department))
            ->when($employmentType !== '', fn ($query) => $query->where('employment_type', $employmentType))
            ->withCount('applications')
            ->orderByDesc('featured_flag')
            ->orderBy('deadline')
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.careers.index', [
            'jobs' => $jobs,
            'workflowStates' => ContentWorkflowState::cases(),
            'departments' => Job::query()->whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'employmentTypes' => Job::query()->whereNotNull('employment_type')->distinct()->orderBy('employment_type')->pluck('employment_type'),
            'filters' => compact('status', 'department', 'employmentType', 'q'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Job::class);

        return view('admin.careers.create', ['job' => null]);
    }

    public function store(JobStoreRequest $request): RedirectResponse
    {
        $job = $this->jobService->store(new Job, $request->user(), $request->validated());

        return redirect()
            ->route('admin.careers.edit', ['job' => $job->getKey()])
            ->with('status', 'job-created');
    }

    public function edit(Job $job): View
    {
        $this->authorize('view', $job);

        return view('admin.careers.edit', ['job' => $job->load('seoMeta')]);
    }

    public function update(JobStoreRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $this->jobService->store($job, $request->user(), $request->validated());

        return redirect()
            ->route('admin.careers.edit', ['job' => $job->getKey()])
            ->with('status', 'job-updated');
    }

    public function submitForReview(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('submitForReview', $job);

        $this->workflowService->submitForReview($job, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'job-submitted');
    }

    public function approve(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('approve', $job);

        $this->workflowService->approve($job, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'job-approved');
    }

    public function reject(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('reject', $job);

        $this->workflowService->reject($job, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'job-rejected');
    }

    public function schedule(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('schedule', $job);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->workflowService->schedulePublish(
            content: $job,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', 'job-scheduled');
    }

    public function publish(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('publish', $job);

        $this->workflowService->publishNow($job, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'job-published');
    }

    public function archive(ContentWorkflowActionRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('archive', $job);

        $this->workflowService->archive($job, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'job-archived');
    }
}

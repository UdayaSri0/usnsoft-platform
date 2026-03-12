<?php

namespace App\Modules\Careers\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Careers\Enums\JobApplicationStatus;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Models\JobApplication;
use App\Modules\Careers\Models\JobApplicationFile;
use App\Modules\Careers\Requests\JobApplicationNoteRequest;
use App\Modules\Careers\Requests\JobApplicationStatusRequest;
use App\Modules\Careers\Services\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function __construct(
        private readonly JobApplicationService $jobApplicationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $status = $request->string('status')->toString();
        $job = $request->string('job')->toString();
        $q = $request->string('q')->toString();

        $applications = JobApplication::query()
            ->with('job')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($searchQuery) use ($q): void {
                    $searchQuery
                        ->where('full_name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($job !== '', fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('slug', $job)))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.careers.applications.index', [
            'applications' => $applications,
            'jobs' => Job::query()->orderBy('title')->get(),
            'statuses' => JobApplicationStatus::cases(),
            'filters' => compact('status', 'job', 'q'),
        ]);
    }

    public function show(JobApplication $application): View
    {
        $this->authorize('view', $application);

        return view('admin.careers.applications.show', [
            'application' => $application->load(['job', 'files', 'notes.author', 'statusHistories']),
            'statuses' => JobApplicationStatus::cases(),
        ]);
    }

    public function updateStatus(JobApplicationStatusRequest $request, JobApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->jobApplicationService->updateStatus(
            application: $application,
            actor: $request->user(),
            status: JobApplicationStatus::from($request->string('status')->toString()),
            note: $request->string('note')->toString() ?: null,
        );

        return back()->with('status', 'application-status-updated');
    }

    public function addNote(JobApplicationNoteRequest $request, JobApplication $application): RedirectResponse
    {
        $this->authorize('addNote', $application);

        $this->jobApplicationService->addNote($application, $request->user(), $request->string('note_body')->toString());

        return back()->with('status', 'application-note-added');
    }

    public function download(JobApplicationFile $file)
    {
        $this->authorize('view', $file);

        return $this->jobApplicationService->download($file, request()->user());
    }
}

<?php

namespace App\Modules\Careers\Services;

use App\Enums\FileScanStatus;
use App\Enums\VisibilityState;
use App\Models\User;
use App\Modules\Careers\Enums\JobApplicationFileType;
use App\Modules\Careers\Enums\JobApplicationStatus;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Models\JobApplication;
use App\Modules\Careers\Models\JobApplicationFile;
use App\Modules\Careers\Models\JobApplicationNote;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\Workflow\Models\StatusHistory;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly SecurityEventService $securityEventService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile|array<int, UploadedFile>|null>  $files
     */
    public function submit(Job $job, array $attributes, array $files): JobApplication
    {
        if (! $job->isOpenForApplications()) {
            throw ValidationException::withMessages([
                'job' => 'Applications for this role are closed.',
            ]);
        }

        return $this->database->transaction(function () use ($attributes, $files, $job): JobApplication {
            $application = JobApplication::query()->create([
                'job_id' => $job->getKey(),
                'full_name' => trim((string) $attributes['full_name']),
                'email' => trim((string) $attributes['email']),
                'phone' => isset($attributes['phone']) ? trim((string) $attributes['phone']) : null,
                'address' => isset($attributes['address']) ? trim((string) $attributes['address']) : null,
                'cover_message' => isset($attributes['cover_message']) ? trim((string) $attributes['cover_message']) : null,
                'portfolio_url' => $attributes['portfolio_url'] ?? null,
                'linkedin_url' => $attributes['linkedin_url'] ?? null,
                'github_url' => $attributes['github_url'] ?? null,
                'status' => JobApplicationStatus::Submitted,
                'submitted_at' => CarbonImmutable::now(),
            ]);

            $cv = $files['cv'] ?? null;
            if ($cv instanceof UploadedFile) {
                $this->storeUploadedFile($application, $cv, JobApplicationFileType::Cv);
            }

            $coverLetter = $files['cover_letter'] ?? null;
            if ($coverLetter instanceof UploadedFile) {
                $this->storeUploadedFile($application, $coverLetter, JobApplicationFileType::CoverLetter);
            }

            $supportingDocuments = $files['supporting_documents'] ?? [];
            foreach (is_array($supportingDocuments) ? $supportingDocuments : [] as $document) {
                if ($document instanceof UploadedFile) {
                    $this->storeUploadedFile($application, $document, JobApplicationFileType::SupportingDocument);
                }
            }

            $this->auditLogService->record(
                eventType: 'careers.application.submitted',
                action: 'submit_job_application',
                auditable: $application,
                newValues: [
                    'job_id' => $job->getKey(),
                    'email' => $application->email,
                    'status' => $application->status->value,
                ],
                metadata: [
                    'job_slug' => $job->slug,
                ],
            );

            return $application->fresh(['job', 'files']);
        });
    }

    public function updateStatus(JobApplication $application, User $actor, JobApplicationStatus $status, ?string $note = null): void
    {
        $from = $application->status;

        $this->database->transaction(function () use ($actor, $application, $from, $note, $status): void {
            $application->forceFill([
                'status' => $status,
                'last_status_changed_by' => $actor->getKey(),
                'reviewed_at' => CarbonImmutable::now(),
            ])->save();

            StatusHistory::query()->create([
                'statusable_type' => $application->getMorphClass(),
                'statusable_id' => $application->getKey(),
                'from_state' => $from?->value,
                'to_state' => $status->value,
                'changed_by' => $actor->getKey(),
                'reason' => $note,
                'metadata' => ['job_id' => $application->job_id],
                'changed_at' => CarbonImmutable::now(),
            ]);

            $this->auditLogService->record(
                eventType: 'careers.application.status_changed',
                action: 'update_job_application_status',
                actor: $actor,
                auditable: $application,
                oldValues: ['status' => $from?->value],
                newValues: ['status' => $status->value],
                metadata: ['note' => $note],
            );
        });
    }

    public function addNote(JobApplication $application, User $actor, string $noteBody): JobApplicationNote
    {
        $note = $application->notes()->create([
            'author_user_id' => $actor->getKey(),
            'note_body' => trim($noteBody),
            'is_internal' => true,
        ]);

        $this->auditLogService->record(
            eventType: 'careers.application.note_added',
            action: 'add_job_application_note',
            actor: $actor,
            auditable: $application,
            metadata: ['job_application_note_id' => $note->getKey()],
        );

        return $note;
    }

    public function download(JobApplicationFile $file, User $actor): StreamedResponse
    {
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        $this->auditLogService->record(
            eventType: 'careers.application.file_downloaded',
            action: 'download_job_application_file',
            actor: $actor,
            auditable: $file,
            metadata: ['job_application_id' => $file->job_application_id],
        );

        $this->securityEventService->record('protected_file.job_application.accessed', $actor, 'info', [
            'job_application_id' => $file->job_application_id,
            'job_application_file_id' => $file->getKey(),
        ]);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    private function storeUploadedFile(JobApplication $application, UploadedFile $file, JobApplicationFileType $type): JobApplicationFile
    {
        $disk = (string) config('careers.upload_disk', 'local');
        $extension = Str::lower((string) $file->getClientOriginalExtension());
        $allowedExtensions = array_map('strtolower', (array) config('careers.allowed_extensions', []));
        $mimeType = $file->getMimeType() ?: $file->getClientMimeType();
        $allowedMimeTypes = array_map('strtolower', (array) config('careers.allowed_mime_types', []));

        if (! in_array($extension, $allowedExtensions, true) || ! in_array(Str::lower((string) $mimeType), $allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'files' => 'Unsupported applicant file type.',
            ]);
        }

        $directory = 'careers/applications/'.$application->getKey();
        $storedName = (string) Str::uuid().($extension !== '' ? '.'.$extension : '');
        $path = $file->storeAs($directory, $storedName, ['disk' => $disk]);
        $checksum = @hash_file('sha256', $file->getRealPath() ?: '') ?: null;
        $sizeBytes = (int) $file->getSize();

        $mediaAsset = MediaAsset::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $storedName,
            'original_name' => $file->getClientOriginalName(),
            'extension' => $extension !== '' ? $extension : null,
            'mime_type' => $mimeType,
            'visibility' => VisibilityState::Protected,
            'size_bytes' => $sizeBytes,
            'checksum_sha256' => $checksum,
            'metadata' => [
                'source' => 'job_application_file',
                'job_application_id' => $application->getKey(),
            ],
        ]);

        return JobApplicationFile::query()->create([
            'job_application_id' => $application->getKey(),
            'media_asset_id' => $mediaAsset->getKey(),
            'file_type' => $type->value,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'extension' => $extension !== '' ? $extension : null,
            'size_bytes' => $sizeBytes,
            'checksum_sha256' => $checksum,
            'malware_scan_status' => FileScanStatus::Pending,
            'malware_scan_meta' => ['scanner' => 'not_configured'],
            'uploaded_at' => CarbonImmutable::now(),
        ]);
    }
}

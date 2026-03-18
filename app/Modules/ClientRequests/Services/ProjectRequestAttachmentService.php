<?php

namespace App\Modules\ClientRequests\Services;

use App\Enums\VisibilityState;
use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestAttachmentCategory;
use App\Modules\ClientRequests\Enums\ProjectRequestAttachmentScanStatus;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestAttachment;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectRequestAttachmentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function storeUploadedFile(ProjectRequest $projectRequest, UploadedFile $file, ?User $actor = null): ProjectRequestAttachment
    {
        $disk = (string) config('client_requests.upload_disk', 'local');
        $extension = Str::lower((string) $file->getClientOriginalExtension());
        $allowedExtensions = array_map('strtolower', (array) config('client_requests.allowed_extensions', []));
        $mimeType = $file->getMimeType() ?: $file->getClientMimeType();
        $allowedMimeTypes = array_map('strtolower', (array) config('client_requests.allowed_mime_types', []));

        if (! in_array($extension, $allowedExtensions, true) || ! in_array(Str::lower((string) $mimeType), $allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'attachments' => 'One or more files use an unsupported type.',
            ]);
        }

        $directory = 'client-requests/'.$projectRequest->uuid;
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
            'uploaded_by' => $actor?->getKey(),
            'metadata' => [
                'source' => 'project_request_attachment',
                'project_request_id' => $projectRequest->getKey(),
            ],
        ]);

        $attachment = ProjectRequestAttachment::query()->create([
            'project_request_id' => $projectRequest->getKey(),
            'media_asset_id' => $mediaAsset->getKey(),
            'uploaded_by_user_id' => $actor?->getKey(),
            'category' => $this->classify($extension, (string) $mimeType),
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk' => $disk,
            'directory' => $directory,
            'path' => $path,
            'mime_type' => $mimeType,
            'extension' => $extension !== '' ? $extension : null,
            'size_bytes' => $sizeBytes,
            'checksum_sha256' => $checksum,
            'malware_scan_status' => ProjectRequestAttachmentScanStatus::Pending,
            'malware_scan_meta' => [
                'scanner' => 'not_configured',
            ],
            'visible_to_requester' => true,
        ]);

        $this->auditLogService->record(
            eventType: 'requests.attachment.uploaded',
            action: 'upload_project_request_attachment',
            actor: $actor,
            auditable: $attachment,
            newValues: [
                'original_name' => $attachment->original_name,
                'category' => $attachment->category->value,
                'size_bytes' => $attachment->size_bytes,
            ],
            metadata: [
                'project_request_id' => $projectRequest->getKey(),
            ],
        );

        return $attachment;
    }

    public function download(ProjectRequestAttachment $attachment, User $actor): StreamedResponse
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        $this->auditLogService->record(
            eventType: 'requests.attachment.downloaded',
            action: 'download_project_request_attachment',
            actor: $actor,
            auditable: $attachment,
            metadata: [
                'project_request_id' => $attachment->project_request_id,
            ],
        );

        $this->securityEventService->record('protected_file.request_attachment.accessed', $actor, 'info', [
            'project_request_id' => $attachment->project_request_id,
            'attachment_id' => $attachment->getKey(),
        ]);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    private function classify(string $extension, string $mimeType): ProjectRequestAttachmentCategory
    {
        $mimeType = Str::lower($mimeType);

        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            return ProjectRequestAttachmentCategory::Pdf;
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) || Str::startsWith($mimeType, 'image/')) {
            return ProjectRequestAttachmentCategory::Screenshot;
        }

        if (in_array($extension, ['mp3', 'wav', 'm4a', 'ogg', 'webm'], true) || Str::startsWith($mimeType, 'audio/')) {
            return ProjectRequestAttachmentCategory::VoiceNote;
        }

        if (in_array($extension, ['doc', 'docx', 'rtf', 'odt', 'txt'], true)) {
            return ProjectRequestAttachmentCategory::ScopeDocument;
        }

        if (in_array($extension, ['csv'], true)) {
            return ProjectRequestAttachmentCategory::Attachment;
        }

        return ProjectRequestAttachmentCategory::Other;
    }
}

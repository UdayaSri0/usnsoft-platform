<?php

namespace App\Modules\ClientRequests\Models;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestAttachmentCategory;
use App\Modules\ClientRequests\Enums\ProjectRequestAttachmentScanStatus;
use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectRequestAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'project_request_id',
        'media_asset_id',
        'uploaded_by_user_id',
        'category',
        'original_name',
        'stored_name',
        'disk',
        'directory',
        'path',
        'mime_type',
        'extension',
        'size_bytes',
        'checksum_sha256',
        'malware_scan_status',
        'malware_scan_meta',
        'visible_to_requester',
    ];

    protected function casts(): array
    {
        return [
            'category' => ProjectRequestAttachmentCategory::class,
            'malware_scan_status' => ProjectRequestAttachmentScanStatus::class,
            'malware_scan_meta' => 'array',
            'visible_to_requester' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (self $attachment): void {
            if (! $attachment->uuid) {
                $attachment->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function projectRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectRequest::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

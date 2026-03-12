<?php

namespace App\Modules\Careers\Models;

use App\Enums\FileScanStatus;
use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'media_asset_id',
        'file_type',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size_bytes',
        'checksum_sha256',
        'malware_scan_status',
        'malware_scan_meta',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'malware_scan_status' => FileScanStatus::class,
            'malware_scan_meta' => 'array',
            'uploaded_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}

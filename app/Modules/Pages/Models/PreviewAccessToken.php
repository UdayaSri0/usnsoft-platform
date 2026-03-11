<?php

namespace App\Modules\Pages\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviewAccessToken extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'page_version_id',
        'token_hash',
        'generated_by',
        'expires_at',
        'last_accessed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function pageVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }
}

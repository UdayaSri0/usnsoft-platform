<?php

namespace App\Modules\Pages\Models;

use App\Modules\Pages\Enums\PageType;
use App\Modules\Seo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'key',
        'page_type',
        'title_current',
        'slug_current',
        'path_current',
        'is_home',
        'is_system_page',
        'is_locked_slug',
        'is_active',
        'current_draft_version_id',
        'current_published_version_id',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $page): void {
            if (! $page->uuid) {
                $page->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'page_type' => PageType::class,
            'is_home' => 'boolean',
            'is_system_page' => 'boolean',
            'is_locked_slug' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class);
    }

    public function currentDraftVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'current_draft_version_id');
    }

    public function currentPublishedVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'current_published_version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function nextVersionNumber(): int
    {
        return ((int) $this->versions()->max('version_number')) + 1;
    }
}

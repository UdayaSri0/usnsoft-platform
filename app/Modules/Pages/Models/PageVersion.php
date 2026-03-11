<?php

namespace App\Modules\Pages\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\InteractsWithMedia;
use App\Models\Concerns\IsPublishable;
use App\Modules\Seo\Models\SeoMeta;
use App\Modules\Workflow\Models\ApprovalRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageVersion extends Model implements Publishable, RequiresApproval
{
    use HasApprovalWorkflow;
    use HasFactory;
    use InteractsWithMedia;
    use IsPublishable;
    use SoftDeletes;

    protected $fillable = [
        'page_id',
        'version_number',
        'title',
        'slug',
        'path',
        'summary',
        'workflow_state',
        'approval_state',
        'change_notes',
        'seo_snapshot_json',
        'layout_settings_json',
        'preview_token_hash',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'scheduled_publish_at',
        'scheduled_unpublish_at',
        'archived_by',
        'archived_at',
        'based_on_version_id',
        'preview_confirmed_by',
        'preview_confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'seo_snapshot_json' => 'array',
            'layout_settings_json' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'scheduled_unpublish_at' => 'datetime',
            'archived_at' => 'datetime',
            'preview_confirmed_at' => 'datetime',
        ];
    }

    public function getWorkflowState(): ?ContentWorkflowState
    {
        return $this->workflow_state;
    }

    public function setWorkflowState(ContentWorkflowState $state): void
    {
        $this->workflow_state = $state;
    }

    public function getApprovalState(): ?ApprovalState
    {
        return $this->approval_state;
    }

    public function setApprovalState(ApprovalState $state): void
    {
        $this->approval_state = $state;
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageVersionBlock::class)->orderBy('sort_order');
    }

    public function basedOnVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'based_on_version_id');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function approvalRecords(): MorphMany
    {
        return $this->morphMany(ApprovalRecord::class, 'approvable');
    }

    public function isPublished(): bool
    {
        return $this->workflow_state === ContentWorkflowState::Published;
    }
}

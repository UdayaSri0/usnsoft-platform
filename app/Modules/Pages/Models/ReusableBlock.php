<?php

namespace App\Modules\Pages\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\IsPublishable;
use App\Modules\Seo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReusableBlock extends Model implements Publishable, RequiresApproval
{
    use HasApprovalWorkflow;
    use HasFactory;
    use IsPublishable;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'block_definition_id',
        'workflow_state',
        'approval_state',
        'data_json',
        'layout_json',
        'visibility_json',
        'notes',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $block): void {
            if (! $block->uuid) {
                $block->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'data_json' => 'array',
            'layout_json' => 'array',
            'visibility_json' => 'array',
            'approved_at' => 'datetime',
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

    public function blockDefinition(): BelongsTo
    {
        return $this->belongsTo(BlockDefinition::class);
    }

    public function pageVersionBlocks(): HasMany
    {
        return $this->hasMany(PageVersionBlock::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function isPublished(): bool
    {
        return $this->workflow_state === ContentWorkflowState::Published;
    }
}

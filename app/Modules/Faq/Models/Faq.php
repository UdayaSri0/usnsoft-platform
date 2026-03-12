<?php

namespace App\Modules\Faq\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Models\Concerns\HasDirectContentWorkflow;
use App\Models\User;
use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model implements Publishable, RequiresApproval
{
    use HasDirectContentWorkflow;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'faq_category_id',
        'linked_product_id',
        'question',
        'answer',
        'sort_order',
        'featured_flag',
        'visibility',
        'workflow_state',
        'approval_state',
        'change_notes',
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
        'preview_confirmed_by',
        'preview_confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'featured_flag' => 'boolean',
            'visibility' => VisibilityState::class,
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'scheduled_unpublish_at' => 'datetime',
            'archived_at' => 'datetime',
            'preview_confirmed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function linkedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'linked_product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($term): void {
            $searchQuery
                ->where('question', 'like', '%'.$term.'%')
                ->orWhere('answer', 'like', '%'.$term.'%');
        });
    }
}

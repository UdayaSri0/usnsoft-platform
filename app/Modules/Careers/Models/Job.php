<?php

namespace App\Modules\Careers\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Models\Concerns\HasDirectContentWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model implements Publishable, RequiresApproval
{
    use HasDirectContentWorkflow;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'location',
        'employment_type',
        'department',
        'level',
        'deadline',
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
            'featured_flag' => 'boolean',
            'visibility' => VisibilityState::class,
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'deadline' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'scheduled_unpublish_at' => 'datetime',
            'archived_at' => 'datetime',
            'preview_confirmed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class)->latest('submitted_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOpenForApplications(): bool
    {
        return $this->isPublished()
            && ($this->deadline === null || $this->deadline->isFuture());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($term): void {
            $searchQuery
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('summary', 'like', '%'.$term.'%')
                ->orWhere('location', 'like', '%'.$term.'%')
                ->orWhere('department', 'like', '%'.$term.'%')
                ->orWhere('level', 'like', '%'.$term.'%');
        });
    }
}

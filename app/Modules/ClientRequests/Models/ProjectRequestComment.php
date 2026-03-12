<?php

namespace App\Modules\ClientRequests\Models;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_request_id',
        'author_user_id',
        'body',
        'visibility_type',
        'is_system_generated',
        'edited_by_user_id',
        'edited_at',
        'visibility_changed_by_user_id',
        'visibility_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'visibility_type' => ProjectRequestCommentVisibility::class,
            'is_system_generated' => 'boolean',
            'edited_at' => 'datetime',
            'visibility_changed_at' => 'datetime',
        ];
    }

    public function projectRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectRequest::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    public function visibilityChanger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visibility_changed_by_user_id');
    }

    public function scopeRequesterVisible(Builder $query): Builder
    {
        return $query->where('visibility_type', ProjectRequestCommentVisibility::RequesterVisible->value);
    }
}

<?php

namespace App\Modules\ClientRequests\Models;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Enums\ProjectRequestEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequestEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_request_id',
        'actor_user_id',
        'event_type',
        'visibility_type',
        'title',
        'body',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ProjectRequestEventType::class,
            'visibility_type' => ProjectRequestCommentVisibility::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function projectRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function scopeRequesterVisible(Builder $query): Builder
    {
        return $query->where('visibility_type', ProjectRequestCommentVisibility::RequesterVisible->value);
    }
}

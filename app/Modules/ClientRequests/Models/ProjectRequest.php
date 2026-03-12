<?php

namespace App\Modules\ClientRequests\Models;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestType;
use App\Modules\Workflow\Models\StatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'current_status_id',
        'requester_name',
        'company_name',
        'contact_email',
        'contact_phone',
        'project_title',
        'project_summary',
        'project_description',
        'budget',
        'deadline',
        'project_type',
        'requested_features',
        'preferred_tech_stack',
        'preferred_meeting_availability',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'deadline' => 'date',
            'project_type' => ProjectRequestType::class,
            'requested_features' => 'array',
            'preferred_tech_stack' => 'array',
            'preferred_meeting_availability' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (self $projectRequest): void {
            if (! $projectRequest->uuid) {
                $projectRequest->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectRequestStatus::class, 'current_status_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectRequestComment::class)->latest('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectRequestAttachment::class)->latest('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ProjectRequestEvent::class)->orderByDesc('occurred_at');
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')->orderByDesc('changed_at');
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }
}

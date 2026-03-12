<?php

namespace App\Modules\ClientRequests\Models;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRequestStatus extends Model
{
    use HasFactory;

    protected $table = 'request_statuses';

    protected $fillable = [
        'code',
        'name',
        'is_system',
        'is_default',
        'is_terminal',
        'system_status',
        'sort_order',
        'badge_tone',
        'visible_to_requester',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_default' => 'boolean',
            'is_terminal' => 'boolean',
            'sort_order' => 'integer',
            'visible_to_requester' => 'boolean',
            'system_status' => ProjectRequestSystemStatus::class,
        ];
    }

    public function projectRequests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class, 'current_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSystemCode(Builder $query, ProjectRequestSystemStatus $status): Builder
    {
        return $query->where('system_status', $status->value);
    }
}

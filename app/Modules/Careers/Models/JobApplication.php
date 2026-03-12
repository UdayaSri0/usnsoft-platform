<?php

namespace App\Modules\Careers\Models;

use App\Models\User;
use App\Modules\Careers\Enums\JobApplicationStatus;
use App\Modules\Workflow\Models\StatusHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'full_name',
        'email',
        'phone',
        'address',
        'cover_message',
        'portfolio_url',
        'linkedin_url',
        'github_url',
        'status',
        'last_status_changed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(JobApplicationFile::class)->orderBy('uploaded_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(JobApplicationNote::class)->latest('created_at');
    }

    public function lastStatusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_status_changed_by');
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')->orderByDesc('changed_at');
    }
}

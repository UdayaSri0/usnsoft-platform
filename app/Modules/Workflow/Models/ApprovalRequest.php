<?php

namespace App\Modules\Workflow\Models;

use App\Enums\ApprovalState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'requested_by',
        'reviewed_by',
        'approval_state',
        'submitted_at',
        'reviewed_at',
        'comment',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'approval_state' => ApprovalState::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

<?php

namespace App\Modules\Workflow\Models;

use App\Enums\ApprovalAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'action',
        'from_status',
        'to_status',
        'actor_id',
        'notes',
        'metadata_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => ApprovalAction::class,
            'metadata_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

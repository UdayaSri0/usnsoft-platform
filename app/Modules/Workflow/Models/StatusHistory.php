<?php

namespace App\Modules\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'statusable_type',
        'statusable_id',
        'from_state',
        'to_state',
        'visibility',
        'changed_by',
        'reason',
        'metadata',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    public function statusable(): MorphTo
    {
        return $this->morphTo();
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

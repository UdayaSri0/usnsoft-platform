<?php

namespace App\Modules\AuditSecurity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSessionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_identifier',
        'ip_address',
        'user_agent',
        'device_id',
        'last_activity_at',
        'logged_in_at',
        'logged_out_at',
        'invalidated_at',
        'is_current',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'invalidated_at' => 'datetime',
            'is_current' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }
}

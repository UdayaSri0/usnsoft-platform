<?php

namespace App\Modules\AuditSecurity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_label',
        'device_fingerprint',
        'user_agent',
        'ip_address',
        'first_seen_at',
        'last_seen_at',
        'last_login_at',
        'is_trusted',
        'last_seen_country',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_trusted' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessionHistories(): HasMany
    {
        return $this->hasMany(UserSessionHistory::class, 'device_id');
    }
}

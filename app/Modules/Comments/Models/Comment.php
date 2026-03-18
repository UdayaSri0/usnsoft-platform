<?php

namespace App\Modules\Comments\Models;

use App\Models\User;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'body',
        'status',
        'submitted_at',
        'approved_at',
        'moderated_at',
        'moderated_by',
        'moderation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved->value);
    }

    public function targetLabel(): string
    {
        return match ($this->commentable_type) {
            'blog_post' => 'Blog Post',
            'product' => 'Product',
            default => Str::headline(str_replace('_', ' ', (string) $this->commentable_type)),
        };
    }

    public function targetTitle(): string
    {
        return match (true) {
            $this->commentable instanceof BlogPost => $this->commentable->title,
            $this->commentable instanceof Product => $this->commentable->name_current,
            default => 'Unknown target',
        };
    }
}

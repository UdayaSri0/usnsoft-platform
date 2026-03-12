<?php

namespace App\Modules\Showcase\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Models\Concerns\HasDirectContentWorkflow;
use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model implements Publishable, RequiresApproval
{
    use HasDirectContentWorkflow;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'slug',
        'role_title',
        'short_bio',
        'full_bio',
        'photo_media_id',
        'public_email',
        'public_phone',
        'linkedin_url',
        'github_url',
        'website_url',
        'featured_flag',
        'sort_order',
        'visibility',
        'workflow_state',
        'approval_state',
        'change_notes',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'scheduled_publish_at',
        'scheduled_unpublish_at',
        'archived_by',
        'archived_at',
        'preview_confirmed_by',
        'preview_confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'featured_flag' => 'boolean',
            'sort_order' => 'integer',
            'visibility' => VisibilityState::class,
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'scheduled_unpublish_at' => 'datetime',
            'archived_at' => 'datetime',
            'preview_confirmed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'photo_media_id');
    }
}

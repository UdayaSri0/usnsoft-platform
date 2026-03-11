<?php

namespace App\Modules\Pages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageVersionBlock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'page_version_id',
        'block_definition_id',
        'reusable_block_id',
        'region_key',
        'sort_order',
        'internal_name',
        'is_enabled',
        'visibility_json',
        'layout_json',
        'data_json',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'visibility_json' => 'array',
            'layout_json' => 'array',
            'data_json' => 'array',
        ];
    }

    public function pageVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class);
    }

    public function blockDefinition(): BelongsTo
    {
        return $this->belongsTo(BlockDefinition::class);
    }

    public function reusableBlock(): BelongsTo
    {
        return $this->belongsTo(ReusableBlock::class);
    }
}

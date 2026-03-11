<?php

namespace App\Modules\Pages\Models;

use App\Modules\Pages\Enums\BlockEditorMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'icon',
        'schema_json',
        'default_data_json',
        'default_layout_json',
        'editor_mode',
        'is_reusable_allowed',
        'is_active',
        'is_system',
        'sort_order',
        'rendering_view',
        'rendering_component_class',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'default_data_json' => 'array',
            'default_layout_json' => 'array',
            'editor_mode' => BlockEditorMode::class,
            'is_reusable_allowed' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function reusableBlocks(): HasMany
    {
        return $this->hasMany(ReusableBlock::class);
    }

    public function pageVersionBlocks(): HasMany
    {
        return $this->hasMany(PageVersionBlock::class);
    }
}

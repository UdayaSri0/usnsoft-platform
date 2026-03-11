<?php

namespace App\Modules\Pages\Requests;

use App\Modules\Pages\Enums\PageType;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Page::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $reservedPaths = config('cms.reserved_paths', []);

        return [
            'key' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('pages', 'key')],
            'page_type' => ['required', Rule::in(PageType::values())],
            'is_home' => ['nullable', 'boolean'],
            'is_system_page' => ['nullable', 'boolean'],
            'is_locked_slug' => ['nullable', 'boolean'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9\-\/]+$/', Rule::notIn($reservedPaths)],
            'path' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'path_current')],
            'summary' => ['nullable', 'string', 'max:2000'],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'layout_settings_json' => ['nullable', 'array'],
            'seo_snapshot_json' => ['nullable', 'array'],
            'blocks' => ['nullable', 'array', 'max:80'],
            'blocks.*.block_type' => ['required_with:blocks', 'string', Rule::exists('block_definitions', 'key')->where('is_active', true)],
            'blocks.*.reusable_block_id' => ['nullable', 'integer', Rule::exists('reusable_blocks', 'id')],
            'blocks.*.region_key' => ['nullable', 'string', 'max:80'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'blocks.*.internal_name' => ['nullable', 'string', 'max:160'],
            'blocks.*.is_enabled' => ['nullable', 'boolean'],
            'blocks.*.visibility' => ['nullable', 'array'],
            'blocks.*.layout' => ['nullable', 'array'],
            'blocks.*.data' => ['nullable', 'array'],
            'blocks.*.data_json' => ['nullable', 'string'],
        ];
    }
}

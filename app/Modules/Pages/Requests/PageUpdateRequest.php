<?php

namespace App\Modules\Pages\Requests;

use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page
            && ($this->user()?->can('update', $page) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $page = $this->route('page');
        $ignorePageId = $page instanceof Page ? $page->getKey() : null;
        $reservedPaths = config('cms.reserved_paths', []);

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9\-\/]+$/', Rule::notIn($reservedPaths)],
            'path' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'path_current')->ignore($ignorePageId)],
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

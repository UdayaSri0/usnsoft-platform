<?php

namespace App\Modules\Pages\Requests;

use App\Modules\Pages\Models\ReusableBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReusableBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $reusableBlock = $this->route('reusableBlock');

        if ($reusableBlock instanceof ReusableBlock) {
            return $this->user()?->can('update', $reusableBlock) ?? false;
        }

        return $this->user()?->can('create', ReusableBlock::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $reusableBlock = $this->route('reusableBlock');
        $ignoreId = $reusableBlock instanceof ReusableBlock ? $reusableBlock->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9\-]+$/', Rule::unique('reusable_blocks', 'slug')->ignore($ignoreId)],
            'block_definition_id' => ['required', 'integer', Rule::exists('block_definitions', 'id')
                ->where('is_active', true)
                ->where('is_reusable_allowed', true)],
            'data' => ['nullable', 'array'],
            'data_json' => ['nullable', 'string'],
            'layout' => ['nullable', 'array'],
            'visibility' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

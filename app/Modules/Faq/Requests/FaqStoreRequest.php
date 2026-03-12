<?php

namespace App\Modules\Faq\Requests;

use App\Enums\VisibilityState;
use App\Modules\Faq\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $faq = $this->route('faq');

        if ($faq instanceof Faq) {
            return $this->user()?->can('update', $faq) ?? false;
        }

        return $this->user()?->can('create', Faq::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'faq_category_id' => ['nullable', 'exists:faq_categories,id'],
            'linked_product_id' => ['nullable', 'exists:products,id'],
            'question' => ['required', 'string', 'max:280'],
            'answer' => ['required', 'string', 'max:12000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'featured_flag' => ['nullable', 'boolean'],
            'visibility' => ['required', Rule::in(VisibilityState::values())],
            'change_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

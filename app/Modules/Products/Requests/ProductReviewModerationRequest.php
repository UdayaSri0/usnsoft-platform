<?php

namespace App\Modules\Products\Requests;

use App\Modules\Products\Enums\ProductReviewState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductReviewModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'state' => ['required', Rule::in(ProductReviewState::values())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

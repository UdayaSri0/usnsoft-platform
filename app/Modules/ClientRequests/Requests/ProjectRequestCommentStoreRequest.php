<?php

namespace App\Modules\ClientRequests\Requests;

use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequestCommentStoreRequest extends FormRequest
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
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'visibility_type' => ['nullable', Rule::in(ProjectRequestCommentVisibility::values())],
        ];
    }
}

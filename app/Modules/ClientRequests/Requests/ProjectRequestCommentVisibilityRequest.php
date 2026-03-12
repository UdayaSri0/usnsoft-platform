<?php

namespace App\Modules\ClientRequests\Requests;

use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequestCommentVisibilityRequest extends FormRequest
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
            'visibility_type' => ['required', Rule::in(ProjectRequestCommentVisibility::values())],
        ];
    }
}

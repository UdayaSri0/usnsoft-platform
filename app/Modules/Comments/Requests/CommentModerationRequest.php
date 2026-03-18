<?php

namespace App\Modules\Comments\Requests;

use App\Modules\Comments\Enums\CommentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentModerationRequest extends FormRequest
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
            'status' => ['required', Rule::in(CommentStatus::values())],
            'moderation_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

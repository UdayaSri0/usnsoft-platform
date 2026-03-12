<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentWorkflowActionRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:2000'],
            'schedule_publish_at' => ['nullable', 'date'],
            'schedule_unpublish_at' => ['nullable', 'date', 'after:schedule_publish_at'],
            'preview_confirmed' => ['nullable', 'boolean'],
        ];
    }
}

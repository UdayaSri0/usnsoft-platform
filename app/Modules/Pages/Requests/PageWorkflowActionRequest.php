<?php

namespace App\Modules\Pages\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageWorkflowActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'schedule_publish_at' => ['nullable', 'date', 'after:now'],
            'schedule_unpublish_at' => ['nullable', 'date', 'after:schedule_publish_at'],
            'preview_confirmed' => ['nullable', 'boolean'],
        ];
    }
}

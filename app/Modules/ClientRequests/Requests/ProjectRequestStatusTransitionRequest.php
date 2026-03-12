<?php

namespace App\Modules\ClientRequests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequestStatusTransitionRequest extends FormRequest
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
            'status_id' => ['required', 'integer', 'exists:request_statuses,id'],
            'change_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

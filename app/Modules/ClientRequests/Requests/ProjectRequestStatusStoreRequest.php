<?php

namespace App\Modules\ClientRequests\Requests;

use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use App\Modules\ClientRequests\Models\ProjectRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequestStatusStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageStatuses', ProjectRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9\-_]+$/', Rule::unique('request_statuses', 'code')],
            'system_status' => ['required', Rule::in(ProjectRequestSystemStatus::values())],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'badge_tone' => ['nullable', 'string', 'max:30'],
            'visible_to_requester' => ['nullable', 'boolean'],
            'is_terminal' => ['nullable', 'boolean'],
        ];
    }
}

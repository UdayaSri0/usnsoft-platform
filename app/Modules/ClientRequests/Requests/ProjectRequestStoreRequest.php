<?php

namespace App\Modules\ClientRequests\Requests;

use App\Modules\ClientRequests\Enums\ProjectRequestType;
use App\Modules\ClientRequests\Models\ProjectRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProjectRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedExtensions = config('client_requests.allowed_extensions', []);
        $allowedMimeTypes = config('client_requests.allowed_mime_types', []);

        return [
            'requester_name' => ['required', 'string', 'max:160'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'project_title' => ['required', 'string', 'max:190'],
            'project_summary' => ['required', 'string', 'max:500'],
            'project_description' => ['required', 'string', 'min:50', 'max:12000'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'project_type' => ['required', Rule::in(ProjectRequestType::values())],
            'requested_features' => ['nullable', 'string', 'max:5000'],
            'preferred_tech_stack' => ['nullable', 'string', 'max:3000'],
            'preferred_meeting_availability' => ['nullable', 'string', 'max:3000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => [
                'file',
                'max:'.((int) config('client_requests.max_upload_kb', 25600)),
                'extensions:'.implode(',', $allowedExtensions),
                'mimetypes:'.implode(',', $allowedMimeTypes),
                File::types($allowedExtensions),
            ],
        ];
    }
}

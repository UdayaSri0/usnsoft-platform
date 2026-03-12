<?php

namespace App\Modules\ClientRequests\Requests;

use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use Illuminate\Validation\Rule;

class ProjectRequestStatusUpdateRequest extends ProjectRequestStatusStoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $status = $this->route('status');

        $rules['code'] = [
            'nullable',
            'string',
            'max:80',
            'regex:/^[a-z0-9\-_]+$/',
            Rule::unique('request_statuses', 'code')->ignore($status instanceof ProjectRequestStatus ? $status->getKey() : null),
        ];

        return $rules;
    }
}

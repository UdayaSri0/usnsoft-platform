<?php

namespace App\Modules\Careers\Requests;

use App\Modules\Careers\Enums\JobApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobApplicationStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(JobApplicationStatus::values())],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

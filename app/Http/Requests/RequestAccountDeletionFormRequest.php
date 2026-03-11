<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestAccountDeletionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('account.requestDeletion') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

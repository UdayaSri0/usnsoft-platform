<?php

namespace App\Modules\IdentityAccess\Requests;

use App\Enums\CoreRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InternalAccountStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(CoreRole::SuperAdmin) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(static fn ($query) => $query->where('is_internal', true)),
            ],
        ];
    }
}

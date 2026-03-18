<?php

namespace App\Modules\IdentityAccess\Requests\Admin;

use App\Enums\CoreRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManagedAccountStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isInternalStaff()
            && (
                $user->hasPermission('users.create')
                || $user->hasPermission('staff.create')
                || $user->hasRole(CoreRole::SuperAdmin)
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:40'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}

<?php

namespace App\Modules\IdentityAccess\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagedAccountUpdateRequest extends FormRequest
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
        $target = $this->route('user');
        $targetId = $target instanceof User ? $target->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetId)],
            'phone' => ['nullable', 'string', 'max:40'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
        ];
    }
}

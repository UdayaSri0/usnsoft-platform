@php($selectedRoleId = old('role_id', isset($account) ? $account->roles->first()?->getKey() : null))

<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $account->name ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $account->email ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $account->phone ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>
    </div>

    @if ($roles->isNotEmpty())
        <div>
            <x-input-label for="role_id" value="Role" />
            <x-select-input id="role_id" name="role_id" class="mt-2 block w-full" required>
                <option value="">Select a role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->getKey() }}" @selected((string) $selectedRoleId === (string) $role->getKey())>
                        {{ $role->display_name ?? \Illuminate\Support\Str::headline($role->name) }}
                    </option>
                @endforeach
            </x-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
        </div>
    @endif

    @if (($createMode ?? false) === true)
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="password" value="Initial Password" />
                <x-text-input id="password" name="password" type="password" class="mt-2 block w-full" required />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" required />
            </div>
        </div>
    @endif
</div>

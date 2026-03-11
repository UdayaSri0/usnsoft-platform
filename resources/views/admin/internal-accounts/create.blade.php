<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="{{ __('Create Internal Account') }}"
            description="Only SuperAdmin can provision privileged internal accounts. Keep role selection and password handling explicit."
            eyebrow="Identity access"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-narrow">
            <div class="usn-card">
                @if (session('status') === 'internal-account-created')
                    <x-ui.alert tone="success" title="{{ __('Internal account created successfully.') }}" class="mb-6" />
                @endif

                <form method="POST" action="{{ route('admin.internal-accounts.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="role_id" :value="__('Role')" />
                        <x-select-input id="role_id" name="role_id" class="mt-2 block w-full" required>
                            <option value="">{{ __('Select a role') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </x-select-input>
                        <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-2 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" required />
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-primary-button>{{ __('Create Account') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

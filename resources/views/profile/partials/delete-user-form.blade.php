<section class="space-y-6">
    <header>
        <h2 class="font-display text-lg font-semibold text-slate-900">
            {{ __('Request Account Deletion') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __('Submit a deletion request for your account. Your account will not be permanently deleted immediately, and the request will be reviewed by internal staff.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Request Deletion') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-semibold text-slate-900">
                {{ __('Confirm account deletion request') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                {{ __('Please confirm your password to create an account deletion request. Your data will remain until the request is reviewed.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="reason" value="{{ __('Reason (optional)') }}" />
                <textarea
                    id="reason"
                    name="reason"
                    class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                    rows="3"
                    placeholder="{{ __('Tell us why you want to delete your account') }}"
                >{{ old('reason') }}</textarea>
                <x-input-error :messages="$errors->userDeletion->get('reason')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Submit Request') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

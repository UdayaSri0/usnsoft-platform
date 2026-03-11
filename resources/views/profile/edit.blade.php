<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="usn-heading">Profile Settings</h2>
            <p class="usn-subheading">Manage identity, security credentials, and account lifecycle requests.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'deletion-requested')
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">Your account deletion request has been submitted for review.</div>
            @endif

            <div class="usn-card">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="usn-card">
                @include('profile.partials.update-password-form')
            </div>

            <div class="usn-card">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

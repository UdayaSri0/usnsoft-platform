<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Profile Settings"
            description="Manage identity, security credentials, and account lifecycle requests."
            eyebrow="Account"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status') === 'deletion-requested')
                <x-ui.alert tone="warning" title="Deletion request submitted">
                    Your account deletion request has been submitted for review.
                </x-ui.alert>
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

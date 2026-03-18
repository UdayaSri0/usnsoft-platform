<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Create Account"
            description="Role options are filtered by the actor's allowed creation scope."
            eyebrow="Identity Access"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-narrow">
            <section class="usn-card">
                <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-6">
                    @csrf

                    @include('admin.accounts._form', [
                        'createMode' => true,
                        'roles' => $roles,
                    ])

                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('admin.accounts.index') }}" class="usn-btn-secondary">Back to Accounts</a>
                        <x-primary-button>Create Account</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>

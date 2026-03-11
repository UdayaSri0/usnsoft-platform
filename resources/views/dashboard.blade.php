<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="{{ __('Dashboard') }}"
            description="Legacy Breeze dashboard placeholder. The primary authenticated experience is the account dashboard."
            eyebrow="Legacy"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide">
            <div class="usn-card">
                <p class="text-sm text-slate-700">{{ __("You're logged in.") }}</p>
            </div>
        </div>
    </div>
</x-app-layout>

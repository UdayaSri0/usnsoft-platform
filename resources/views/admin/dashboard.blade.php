<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-2xl font-semibold text-slate-900">Internal Admin Area</h2>
                <p class="mt-1 text-sm text-slate-500">Role-aware operations dashboard for content and access management.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:grid-cols-2 lg:grid-cols-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.cms.pages.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">CMS</p>
                <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Pages</h3>
                <p class="mt-2 text-sm text-slate-600">Draft, review, publish lifecycle.</p>
            </a>

            <a href="{{ route('admin.cms.approvals.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Workflow</p>
                <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Approval Queue</h3>
                <p class="mt-2 text-sm text-slate-600">Review pending version submissions.</p>
            </a>

            <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Content</p>
                <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Reusable Blocks</h3>
                <p class="mt-2 text-sm text-slate-600">Shared approved block library.</p>
            </a>

            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.cms.block-definitions.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">System</p>
                    <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Block Definitions</h3>
                    <p class="mt-2 text-sm text-slate-600">SuperAdmin metadata governance.</p>
                </a>
            @endif
        </div>

        <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-slate-900">Security Notice</h3>
                <p class="mt-2 text-sm text-slate-600">
                    All privileged actions are permission-checked and audited. Publishing and approval actions are restricted by role and policy.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>

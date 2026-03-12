<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Internal Admin Area"
            description="Role-aware operations workspace for content, approvals, access management, and future support tooling."
            eyebrow="Admin"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @if (auth()->user()->hasPermission('products.view'))
                    <a href="{{ route('admin.products.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Products</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Catalog</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Manage product drafts, release details, protected downloads, and review moderation.</p>
                    </a>
                @endif

                <a href="{{ route('admin.cms.pages.index') }}" class="usn-card-link">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">CMS</p>
                    <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Pages</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Draft, review, approval, and publish management.</p>
                </a>

                <a href="{{ route('admin.cms.approvals.index') }}" class="usn-card-link">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Workflow</p>
                    <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Approval Queue</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Review pending content submissions and publishing decisions.</p>
                </a>

                <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="usn-card-link">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Content</p>
                    <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Reusable Blocks</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Shared content primitives for consistent page composition.</p>
                </a>

                @if (auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.cms.block-definitions.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">System</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Block Definitions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Metadata governance for safe editor block types.</p>
                    </a>
                @endif
            </div>

            <x-ui.alert tone="warning" title="Security notice">
                All privileged actions remain permission-checked and audited. Publishing, approvals, and internal account changes should continue to respect SuperAdmin and policy boundaries.
            </x-ui.alert>
        </div>
    </div>
</x-app-layout>

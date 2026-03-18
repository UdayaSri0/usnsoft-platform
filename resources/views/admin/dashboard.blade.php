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
                @if (auth()->user()->hasPermission('requests.viewAny'))
                    <a href="{{ route('admin.client-requests.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Client Requests</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Intake Queue</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Review incoming project requests, update statuses, and separate internal from requester-visible communication.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('users.viewAny'))
                    <a href="{{ route('admin.accounts.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Accounts</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Identity Management</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Create user accounts, enforce role boundaries, review verification state, and manage activation safely.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('comments.viewAny'))
                    <a href="{{ route('admin.comments.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Comments</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Moderation Queue</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Approve, reject, hide, or flag public comments without exposing internal moderation notes publicly.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('products.view'))
                    <a href="{{ route('admin.products.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Products</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Catalog</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Manage product drafts, release details, protected downloads, and review moderation.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('security.logs.view') || auth()->user()->hasPermission('security.events.view') || auth()->user()->hasPermission('security.failedLogins.view') || auth()->user()->hasPermission('security.mfa.view'))
                    <a href="{{ route('admin.security.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Security</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Security Center</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Review failed logins, suspicious access, staff MFA compliance, audit trails, and sensitive session visibility.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('blog.view'))
                    <a href="{{ route('admin.blog.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Editorial</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Blog & News</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Draft, review, schedule, and publish newsroom content through the approval chain.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('faq.view'))
                    <a href="{{ route('admin.faq.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Support Content</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">FAQ</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Maintain approved answers for public support content and reusable FAQ blocks.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('careers.view'))
                    <a href="{{ route('admin.careers.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Hiring</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Careers</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Manage approved roles, publishing windows, and careers landing content.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('careers.applications.view'))
                    <a href="{{ route('admin.careers.applications.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Applicants</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Protected Queue</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Review protected applicant records, files, notes, and hiring status changes.</p>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('showcase.testimonials.manage') || auth()->user()->hasPermission('showcase.partners.manage') || auth()->user()->hasPermission('showcase.team.manage') || auth()->user()->hasPermission('showcase.timeline.manage') || auth()->user()->hasPermission('showcase.achievements.manage'))
                    <a href="{{ route('admin.showcase.testimonials.index') }}" class="usn-card-link">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Brand Content</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Showcase</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Testimonials, partners, team, timeline, and achievements for About and trust sections.</p>
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

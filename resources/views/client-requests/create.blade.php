<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Client Request"
            description="Structured request intake for verified users. The final module can plug into this screen without changing the surrounding workflow patterns."
            eyebrow="Protected flow"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            <x-ui.alert tone="info" title="Verified access confirmed">
                This route remains protected by authentication, active-account checks, verification, and the request permission boundary.
            </x-ui.alert>

            <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <section class="usn-card">
                    <span class="usn-badge-info">Request preparation</span>
                    <h2 class="mt-5 font-display text-2xl font-semibold text-slate-950">Capture the right context before implementation starts</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        The current screen is a safe UI placeholder. It keeps the intended information architecture visible without introducing uncontrolled runtime form behavior.
                    </p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Business scope</p>
                            <p class="mt-2 text-sm text-slate-700">Requested outcome, urgency, and business constraints.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Platform context</p>
                            <p class="mt-2 text-sm text-slate-700">Current systems, roles, approvals, and integration dependencies.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Security expectations</p>
                            <p class="mt-2 text-sm text-slate-700">Protected downloads, approval boundaries, or privileged actions that matter.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Delivery cadence</p>
                            <p class="mt-2 text-sm text-slate-700">Timeline, milestones, and post-launch support expectations.</p>
                        </div>
                    </div>
                </section>

                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Planned safe intake form</h2>
                    <p class="mt-2 text-sm text-slate-600">Visual placeholder only. Wire this to the future request module rather than raw external handlers.</p>

                    <div class="mt-6 grid gap-4">
                        <div class="usn-skeleton h-12"></div>
                        <div class="usn-skeleton h-12"></div>
                        <div class="usn-skeleton h-12"></div>
                        <div class="usn-skeleton h-32"></div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" class="usn-btn-primary" disabled>Submit request</button>
                        <a href="{{ route('dashboard') }}" class="usn-btn-secondary">Back to dashboard</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

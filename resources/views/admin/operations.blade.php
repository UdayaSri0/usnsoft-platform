<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Operations"
            description="Internal operator-facing placeholder for future support, maintenance, and recovery tooling."
            eyebrow="Admin"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            <x-ui.alert tone="warning" title="Operational placeholder">
                Keep future tooling here aligned with role checks, audit logging, and the platform runbooks. Avoid hidden superuser shortcuts.
            </x-ui.alert>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Runtime health</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Queue, scheduler, cache, and asset state checks belong here once operational widgets are wired in.</p>
                </section>
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Recovery actions</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Expose only safe operator actions and point destructive tasks back to documented runbooks.</p>
                </section>
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Support visibility</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Incident context, user-impact notes, and escalation references should remain understandable for non-developers.</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Request Statuses"
            description="Manage the default request status vocabulary and controlled custom workflow extensions."
            eyebrow="Client Requests"
        >
            <x-slot name="actions">
                <a href="{{ route('admin.client-requests.index') }}" class="usn-btn-secondary">Back to Requests</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            @if (session('status'))
                <div class="xl:col-span-2">
                    <x-ui.alert tone="success" :title="session('status')" />
                </div>
            @endif

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Create custom status</h2>
                <p class="mt-2 text-sm text-slate-600">Custom statuses map back to a system status so later reporting and analytics stay stable.</p>

                <form method="POST" action="{{ route('admin.client-requests.statuses.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="status_name" value="Display Name" />
                        <x-text-input id="status_name" name="name" class="mt-2 block w-full" :value="old('name')" required />
                    </div>
                    <div>
                        <x-input-label for="status_code" value="Code (optional)" />
                        <x-text-input id="status_code" name="code" class="mt-2 block w-full" :value="old('code')" />
                    </div>
                    <div>
                        <x-input-label for="status_system_status" value="System Status Mapping" />
                        <x-select-input id="status_system_status" name="system_status" class="mt-2 block w-full" required>
                            @foreach ($systemStatuses as $systemStatus)
                                <option value="{{ $systemStatus->value }}" @selected(old('system_status') === $systemStatus->value)>{{ $systemStatus->label() }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="status_sort_order" value="Sort Order" />
                            <x-text-input id="status_sort_order" name="sort_order" type="number" min="0" class="mt-2 block w-full" :value="old('sort_order', 500)" />
                        </div>
                        <div>
                            <x-input-label for="status_badge_tone" value="Badge Tone" />
                            <x-select-input id="status_badge_tone" name="badge_tone" class="mt-2 block w-full">
                                <option value="">Muted</option>
                                <option value="info" @selected(old('badge_tone') === 'info')>Info</option>
                                <option value="warning" @selected(old('badge_tone') === 'warning')>Warning</option>
                                <option value="success" @selected(old('badge_tone') === 'success')>Success</option>
                                <option value="danger" @selected(old('badge_tone') === 'danger')>Danger</option>
                            </x-select-input>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="visible_to_requester" value="1" @checked(old('visible_to_requester')) class="usn-checkbox">
                        Visible to requester
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_terminal" value="1" @checked(old('is_terminal')) class="usn-checkbox">
                        Terminal status
                    </label>

                    <button type="submit" class="usn-btn-primary">Create status</button>
                </form>
            </section>

            <section class="space-y-6">
                <div class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">System statuses</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($statuses->where('is_system', true) as $status)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-client-request-status-badge :status="$status" />
                                        <span class="usn-badge-muted">{{ $status->code }}</span>
                                    </div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $status->visible_to_requester ? 'Requester visible' : 'Internal' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Custom statuses</h2>
                    <div class="mt-5 space-y-4">
                        @forelse ($statuses->where('is_system', false) as $status)
                            <form method="POST" action="{{ route('admin.client-requests.statuses.update', ['status' => $status]) }}" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_140px_auto]">
                                    <div>
                                        <x-input-label :for="'status-name-'.$status->getKey()" value="Name" />
                                        <x-text-input :id="'status-name-'.$status->getKey()" name="name" class="mt-2 block w-full" :value="$status->name" />
                                    </div>
                                    <div>
                                        <x-input-label :for="'status-code-'.$status->getKey()" value="Code" />
                                        <x-text-input :id="'status-code-'.$status->getKey()" name="code" class="mt-2 block w-full" :value="$status->code" />
                                    </div>
                                    <div>
                                        <x-input-label :for="'status-sort-'.$status->getKey()" value="Sort" />
                                        <x-text-input :id="'status-sort-'.$status->getKey()" name="sort_order" type="number" min="0" class="mt-2 block w-full" :value="$status->sort_order" />
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="usn-btn-secondary w-full">Save</button>
                                    </div>
                                    <div>
                                        <x-input-label :for="'status-system-'.$status->getKey()" value="System Mapping" />
                                        <x-select-input :id="'status-system-'.$status->getKey()" name="system_status" class="mt-2 block w-full">
                                            @foreach ($systemStatuses as $systemStatus)
                                                <option value="{{ $systemStatus->value }}" @selected($status->system_status === $systemStatus)>{{ $systemStatus->label() }}</option>
                                            @endforeach
                                        </x-select-input>
                                    </div>
                                    <div>
                                        <x-input-label :for="'status-tone-'.$status->getKey()" value="Badge Tone" />
                                        <x-select-input :id="'status-tone-'.$status->getKey()" name="badge_tone" class="mt-2 block w-full">
                                            <option value="">Muted</option>
                                            <option value="info" @selected($status->badge_tone === 'info')>Info</option>
                                            <option value="warning" @selected($status->badge_tone === 'warning')>Warning</option>
                                            <option value="success" @selected($status->badge_tone === 'success')>Success</option>
                                            <option value="danger" @selected($status->badge_tone === 'danger')>Danger</option>
                                        </x-select-input>
                                    </div>
                                    <div class="flex items-end gap-4 md:col-span-2 xl:col-span-2">
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" name="visible_to_requester" value="1" @checked($status->visible_to_requester) class="usn-checkbox">
                                            Visible to requester
                                        </label>
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" name="is_terminal" value="1" @checked($status->is_terminal) class="usn-checkbox">
                                            Terminal
                                        </label>
                                    </div>
                                </div>
                            </form>
                        @empty
                            <x-ui.empty-state title="No custom statuses yet" description="Create custom statuses only when the default request states are not specific enough for staff operations." />
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

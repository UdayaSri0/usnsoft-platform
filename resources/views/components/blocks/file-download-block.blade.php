@php($files = is_array($data['files'] ?? null) ? $data['files'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Downloads'" :intro="$data['body'] ?? null" eyebrow="Resources" />

    <div class="space-y-3">
        @foreach ($files as $file)
            <div class="usn-card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-slate-950 dark:text-slate-50">{{ $file['label'] ?? 'File' }}</p>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Managed asset links stay authorization-aware and avoid unsafe direct execution or arbitrary external embedding.</p>
                </div>

                @if (($file['require_login'] ?? false) || (($file['access_mode'] ?? 'protected') === 'protected')
                    <span class="usn-badge-brand">Protected</span>
                @else
                    <span class="usn-badge-success">Public</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

@php($files = is_array($data['files'] ?? null) ? $data['files'] : [])
<div class="space-y-4">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] }}</h2>
    @endif
    @if (!empty($data['body']))
        <p class="text-sm text-slate-600">{{ $data['body'] }}</p>
    @endif

    <div class="space-y-2">
        @foreach ($files as $file)
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
                <span>{{ $file['label'] ?? 'File' }}</span>
                @if (($file['require_login'] ?? false) || (($file['access_mode'] ?? 'protected') === 'protected')
                    <span class="rounded-lg bg-slate-900 px-2 py-1 text-xs font-semibold text-white">Protected</span>
                @else
                    <span class="rounded-lg bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Public</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

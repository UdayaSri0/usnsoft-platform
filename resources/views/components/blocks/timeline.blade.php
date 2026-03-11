@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Timeline' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <ol class="space-y-4">
        @foreach ($items as $item)
            <li class="relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @if (!empty($item['date']))
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">{{ $item['date'] }}</p>
                @endif
                <h3 class="mt-1 font-display text-lg font-semibold text-slate-900">{{ $item['title'] ?? 'Milestone' }}</h3>
                @if (!empty($item['body']))
                    <p class="mt-2 text-sm text-slate-600">{{ $item['body'] }}</p>
                @endif
            </li>
        @endforeach
    </ol>
</div>

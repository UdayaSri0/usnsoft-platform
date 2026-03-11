@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Team' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($items as $item)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 h-16 w-16 rounded-full bg-slate-200"></div>
                <h3 class="font-display text-lg font-semibold text-slate-900">{{ $item['name'] ?? 'Team Member' }}</h3>
                @if (!empty($item['role']))
                    <p class="text-xs uppercase tracking-wide text-sky-700">{{ $item['role'] }}</p>
                @endif
                @if (!empty($item['bio']))
                    <p class="mt-3 text-sm text-slate-600">{{ $item['bio'] }}</p>
                @endif
            </article>
        @endforeach
    </div>
</div>

@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'FAQ' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="space-y-3">
        @foreach ($items as $item)
            <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <summary class="cursor-pointer list-none font-display text-base font-semibold text-slate-900">{{ $item['question'] ?? 'Question' }}</summary>
                <p class="mt-3 text-sm text-slate-600">{{ $item['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</div>

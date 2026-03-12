@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Timeline'" :intro="$data['intro'] ?? null" eyebrow="Journey" />

    <ol class="space-y-4">
        @foreach ($items as $item)
            <li class="relative rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/90">
                @if (!empty($item['date']))
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">{{ $item['date'] }}</p>
                @endif
                <h3 class="mt-2 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $item['title'] ?? 'Milestone' }}</h3>
                @if (!empty($item['body']))
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item['body'] }}</p>
                @endif
            </li>
        @endforeach
    </ol>
</div>

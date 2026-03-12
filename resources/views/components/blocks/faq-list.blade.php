@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="FAQ"
        :title="$data['title'] ?? 'Frequently asked questions'"
        :intro="$data['intro'] ?? null"
    />

    <div class="space-y-3">
        @foreach ($items as $item)
            <details class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/90">
                <summary class="flex cursor-pointer list-none items-start justify-between gap-4 font-display text-lg font-semibold text-slate-950 dark:text-slate-50">
                    <span>{{ $item['question'] ?? 'Question' }}</span>
                    <span class="mt-1 text-slate-400 dark:text-slate-500">+</span>
                </summary>
                <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</div>

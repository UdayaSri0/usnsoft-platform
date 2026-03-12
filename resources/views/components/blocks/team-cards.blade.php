@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Team'" :intro="$data['intro'] ?? null" eyebrow="Leadership" />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($items as $item)
            <article class="usn-card h-full">
                <div class="mb-5 h-16 w-16 rounded-full bg-[linear-gradient(135deg,_#0f5f92,_#dbeafe)]"></div>
                <h3 class="font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $item['name'] ?? 'Team Member' }}</h3>
                @if (!empty($item['role']))
                    <p class="mt-2 text-xs uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">{{ $item['role'] }}</p>
                @endif
                @if (!empty($item['bio']))
                    <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item['bio'] }}</p>
                @endif
                @if (!empty($item['profile_url']))
                    <a href="{{ $item['profile_url'] }}" class="mt-5 inline-flex usn-link">View profile</a>
                @endif
            </article>
        @endforeach
    </div>
</div>

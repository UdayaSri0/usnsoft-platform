@php
    $url = $data['video_url'] ?? null;
@endphp
<div class="space-y-4">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="Video" inverted />
    @endif

    <div class="overflow-hidden rounded-3xl border border-slate-300 bg-black/90 shadow-lg">
        @if ($url)
            <iframe
                src="{{ $url }}"
                class="aspect-video w-full"
                title="{{ $data['title'] ?? 'Video' }}"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>
        @else
            <div class="aspect-video grid place-items-center text-sm text-slate-300">Video URL not configured.</div>
        @endif
    </div>

    @if (!empty($data['caption']))
        <p class="text-sm text-current/75">{{ $data['caption'] }}</p>
    @endif
</div>

@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'usn-page-header']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="usn-overline">{{ $eyebrow }}</p>
        @endif

        <h1 class="usn-heading">{{ $title }}</h1>

        @if ($description)
            <p class="usn-subheading">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="usn-page-actions">
            {{ $actions }}
        </div>
    @endisset
</div>

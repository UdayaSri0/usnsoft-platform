<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Review Moderation"
            description="Only approved reviews appear publicly and contribute to the visible rating aggregate."
            eyebrow="Product Platform"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <form method="GET" action="{{ route('admin.products.reviews.index') }}" class="usn-toolbar">
                <div class="grid flex-1 gap-3 md:grid-cols-3">
                    <x-text-input name="q" :value="$filters['q']" placeholder="Search reviews, users, products" />
                    <x-select-input name="state">
                        <option value="">All moderation states</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->value }}" @selected($filters['state'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="product">
                        <option value="">All products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->slug_current }}" @selected($filters['product'] === $product->slug_current)>{{ $product->name_current }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="usn-btn-primary">Filter</button>
                    <a href="{{ route('admin.products.reviews.index') }}" class="usn-btn-secondary">Reset</a>
                </div>
            </form>

            <div class="space-y-4">
                @forelse ($reviews as $review)
                    <article class="usn-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="usn-badge-info">{{ $review->product?->name_current ?? 'Deleted product' }}</span>
                                    <span class="usn-badge-warning">{{ \Illuminate\Support\Str::headline($review->moderation_state->value) }}</span>
                                    <span class="text-sm text-amber-600">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', max(0, 5 - $review->rating)) }}</span>
                                </div>
                                <h2 class="mt-4 font-display text-xl font-semibold text-slate-950">{{ $review->title ?: 'Untitled review' }}</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-700">{{ $review->body }}</p>
                                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    {{ $review->user?->name ?? 'Unknown user' }} · {{ $review->user?->email ?? 'unknown' }}
                                    @if ($review->submitted_at)
                                        · submitted {{ $review->submitted_at->format('M j, Y g:i A') }}
                                    @endif
                                </p>
                                @if ($review->moderation_notes)
                                    <p class="mt-3 text-sm text-slate-500">Moderation note: {{ $review->moderation_notes }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.products.reviews.moderate', ['review' => $review->getKey()]) }}" class="w-full max-w-md rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label :for="'review-state-'.$review->getKey()" value="Moderation state" />
                                        <x-select-input :id="'review-state-'.$review->getKey()" name="state" class="mt-2 block w-full">
                                            @foreach ($states as $state)
                                                <option value="{{ $state->value }}" @selected($review->moderation_state === $state)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                                            @endforeach
                                        </x-select-input>
                                    </div>
                                    <div>
                                        <x-input-label :for="'review-notes-'.$review->getKey()" value="Notes" />
                                        <x-textarea-input :id="'review-notes-'.$review->getKey()" name="notes" rows="3" class="mt-2 block w-full">{{ $review->moderation_notes }}</x-textarea-input>
                                    </div>
                                    <button type="submit" class="usn-btn-primary w-full">Apply moderation</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No reviews found" description="Reviews will appear here after users submit them through verified product flows." class="usn-card" />
                @endforelse
            </div>

            {{ $reviews->links() }}
        </div>
    </div>
</x-app-layout>

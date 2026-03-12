@php
    $initialTab = $tabs[0]['key'] ?? 'overview';
    $reviewEligibility = is_array($reviewEligibility ?? null) ? $reviewEligibility : [];
    $featuredImage = $version->featuredImage ?? $product->featuredImage;
    $featuredImageUrl = $featuredImage && $featuredImage->disk === 'public'
        ? asset('storage/'.$featuredImage->path)
        : null;
    $clientRequestUrl = auth()->check() && auth()->user()->can('requests.create')
        ? route('client-requests.create')
        : url('/client-request');
    $downloadVisibilityMessage = static function (\App\Modules\Products\Enums\ProductDownloadVisibility $visibility): string {
        return match ($visibility) {
            \App\Modules\Products\Enums\ProductDownloadVisibility::Authenticated => 'Login required',
            \App\Modules\Products\Enums\ProductDownloadVisibility::Verified => 'Verified account required',
            \App\Modules\Products\Enums\ProductDownloadVisibility::Internal => 'Internal staff only',
            \App\Modules\Products\Enums\ProductDownloadVisibility::Entitled => 'Approved entitlement required',
        };
    };
    $reviewReasonMessage = match ($reviewEligibility['reason'] ?? null) {
        'login_required' => 'Log in to submit a review.',
        'inactive_account' => 'This account is not active for reviews.',
        'verified_email_required' => 'Verify your email address to review.',
        'reviews_disabled' => 'Reviews are disabled for this product.',
        'existing_review' => 'You already have an active review for this product.',
        'verification_required' => 'Download or approved verification is required before reviewing.',
        default => null,
    };
@endphp

<x-layouts.public :seo="$seo" :is-preview="$isPreview ?? false">
    <section class="usn-section-lg usn-surface-default">
        <div class="usn-container-wide space-y-8">
            @if (session('status') === 'product-review-submitted')
                <x-ui.alert tone="success" title="Review submitted">
                    Your review has been submitted for moderation. It will appear publicly after approval.
                </x-ui.alert>
            @endif

            @if ($version->product_visibility === \App\Modules\Products\Enums\ProductVisibility::Unlisted && empty($isPreview))
                <x-ui.alert tone="warning" title="Unlisted product">
                    This product is accessible by direct link but does not appear in normal public listings or indexed search.
                </x-ui.alert>
            @endif

            <div class="grid gap-8 xl:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="usn-badge-info">{{ $version->category?->name ?? 'Product' }}</span>
                        <span class="usn-badge-muted">{{ \Illuminate\Support\Str::headline($version->product_kind->value) }}</span>
                        @if ($version->featured_flag)
                            <span class="usn-badge-brand">Featured</span>
                        @endif
                        @if ($product->approved_review_count > 0)
                            <span class="usn-badge-success">{{ number_format((float) $product->average_rating, 1) }}/5 rating</span>
                        @endif
                    </div>

                    <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-slate-950 dark:text-slate-50 sm:text-5xl">{{ $version->name }}</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600 dark:text-slate-200">{{ $version->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $version->full_description), 180) }}</p>

                    <div class="mt-6 flex flex-wrap gap-4 text-sm text-slate-600 dark:text-slate-300">
                        @if ($version->current_version)
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100">Current version: {{ $version->current_version }}</span>
                        @endif
                        @foreach ($version->platforms as $platform)
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100">{{ \Illuminate\Support\Str::headline($platform->platform->value) }}</span>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if ($primaryDownload)
                            @auth
                                @if (empty($isPreview) && auth()->user()->can('download', [$product, $primaryDownload]))
                                    <a href="{{ route('products.downloads.show', ['product' => $product->slug_current, 'download' => $primaryDownload->getKey()]) }}" class="usn-btn-primary">
                                        {{ $primaryDownload->download_mode === \App\Modules\Products\Enums\ProductDownloadMode::ManualRequest ? 'Open request flow' : 'Access download' }}
                                    </a>
                                @else
                                    <span class="usn-btn-secondary cursor-not-allowed opacity-70">{{ $downloadVisibilityMessage($primaryDownload->visibility) }}</span>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="usn-btn-primary">Log in to download</a>
                            @endauth
                        @endif

                        @if ($version->documentation_link)
                            <a href="{{ $version->documentation_link }}" target="_blank" rel="noopener" class="usn-btn-secondary">Documentation</a>
                        @endif

                        @if ($version->github_link)
                            <a href="{{ $version->github_link }}" target="_blank" rel="noopener" class="usn-btn-secondary">GitHub</a>
                        @endif
                    </div>
                </div>

                <aside class="usn-card">
                    @if ($featuredImageUrl)
                        <img src="{{ $featuredImageUrl }}" alt="" class="h-64 w-full rounded-[1.6rem] object-cover">
                    @else
                        <div class="flex h-64 items-center justify-center rounded-[1.6rem] bg-[linear-gradient(145deg,_#082f49,_#0f172a)] text-center text-lg font-semibold text-white">
                            {{ $version->name }}
                        </div>
                    @endif

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Visibility</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ \Illuminate\Support\Str::headline($version->product_visibility->value) }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">Public information and download entitlement remain separate controls.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Download policy</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ \Illuminate\Support\Str::headline($version->download_visibility->value) }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">Downloads always require authenticated access and policy enforcement.</p>
                        </div>
                        @if ($product->approved_review_count > 0)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Verified review signal</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $product->approved_review_count }} approved review{{ $product->approved_review_count === 1 ? '' : 's' }}</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">Averages include approved reviews only.</p>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="usn-section-sm">
        <div class="usn-container-wide">
            <div x-data="{ tab: '{{ $initialTab }}' }" class="space-y-6">
                <div class="overflow-x-auto pb-2">
                    <div class="inline-flex min-w-full gap-2 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm dark:border-slate-800 dark:bg-slate-950/80 sm:min-w-0">
                        @foreach ($tabs as $tab)
                            <button
                                type="button"
                                class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
                                :class="tab === '{{ $tab['key'] }}'
                                    ? 'bg-slate-950 text-white shadow-sm dark:bg-sky-400 dark:text-slate-950'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                                @click="tab = '{{ $tab['key'] }}'"
                            >
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <section x-show="tab === 'overview'" x-cloak class="usn-card">
                    <div class="grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <h2 class="usn-title">Overview</h2>
                            @if ($version->full_description)
                                <div class="usn-prose mt-4">{!! $version->full_description !!}</div>
                            @endif

                            @if ($version->rich_body)
                                <div class="usn-prose mt-6">{!! $version->rich_body !!}</div>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Pricing</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ \Illuminate\Support\Str::headline($version->pricing_mode->value) }}</p>
                                @if ($version->pricing_text)
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-200">{{ $version->pricing_text }}</p>
                                @endif
                            </div>

                            @if ($version->tags->isNotEmpty())
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Tags</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($version->tags as $tag)
                                            <span class="usn-badge-muted">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($relatedProducts->isNotEmpty())
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Related products</p>
                                    <div class="mt-4 grid gap-3">
                                        @foreach ($relatedProducts as $related)
                                            <a href="{{ route('products.show', ['product' => $related->slug_current]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-950/80 dark:hover:border-slate-600">
                                                <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $related->name_current }}</p>
                                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ $related->short_description_current }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                @if ($version->screenshots->isNotEmpty())
                    <section x-show="tab === 'screenshots'" x-cloak class="usn-card">
                        <h2 class="usn-title">Screenshots</h2>
                        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($version->screenshots as $screenshot)
                                @php
                                    $screenshotUrl = $screenshot->mediaAsset && $screenshot->mediaAsset->disk === 'public'
                                        ? asset('storage/'.$screenshot->mediaAsset->path)
                                        : null;
                                @endphp
                                <figure class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/70">
                                    @if ($screenshotUrl)
                                        <img src="{{ $screenshotUrl }}" alt="{{ $screenshot->caption ?: $version->name }}" class="h-64 w-full object-cover">
                                    @else
                                        <div class="flex h-64 items-center justify-center bg-slate-100 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-300">Preview unavailable</div>
                                    @endif
                                    @if ($screenshot->caption)
                                        <figcaption class="px-4 py-3 text-sm text-slate-600 dark:text-slate-200">{{ $screenshot->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($videoEmbedUrl || $version->video_url)
                    <section x-show="tab === 'video'" x-cloak class="usn-card">
                        <h2 class="usn-title">Video</h2>
                        @if ($videoEmbedUrl)
                            <div class="mt-6 overflow-hidden rounded-[1.8rem] border border-slate-200 bg-slate-950 shadow-sm">
                                <div class="aspect-video">
                                    <iframe
                                        src="{{ $videoEmbedUrl }}"
                                        title="{{ $version->name }} video preview"
                                        class="h-full w-full"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            </div>
                        @else
                            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-900/70">
                                <p class="text-sm text-slate-600 dark:text-slate-200">The configured video host is allowed, but it does not have an embeddable format here.</p>
                                <a href="{{ $version->video_url }}" target="_blank" rel="noopener" class="mt-4 inline-flex text-sm font-semibold text-sky-800 hover:text-sky-950">Open video in a new tab</a>
                            </div>
                        @endif
                    </section>
                @endif

                @if (($version->changelog_visible && $version->changelog) || ($version->release_notes_visible && $version->release_notes))
                    <section x-show="tab === 'changelog'" x-cloak class="usn-card">
                        <h2 class="usn-title">Changelog and release notes</h2>
                        <div class="mt-6 grid gap-6 xl:grid-cols-2">
                            @if ($version->release_notes_visible && $version->release_notes)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Release notes</p>
                                    <div class="usn-prose mt-4">{!! $version->release_notes !!}</div>
                                </div>
                            @endif
                            @if ($version->changelog_visible && $version->changelog)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Changelog</p>
                                    <div class="usn-prose mt-4">{!! $version->changelog !!}</div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($version->documentation_link || $version->github_link)
                    <section x-show="tab === 'documentation'" x-cloak class="usn-card">
                        <h2 class="usn-title">Documentation</h2>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @if ($version->documentation_link)
                                <a href="{{ $version->documentation_link }}" target="_blank" rel="noopener" class="usn-card-link">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Primary docs</p>
                                    <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Open documentation</h3>
                                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Reference setup, release notes, and implementation guidance for this product.</p>
                                </a>
                            @endif
                            @if ($version->github_link)
                                <a href="{{ $version->github_link }}" target="_blank" rel="noopener" class="usn-card-link">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Source and releases</p>
                                    <h3 class="mt-3 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">Open GitHub</h3>
                                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Inspect release artifacts, source history, and issue tracking where available.</p>
                                </a>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($version->faqItems->isNotEmpty())
                    <section x-show="tab === 'faq'" x-cloak class="usn-card">
                        <h2 class="usn-title">Frequently asked questions</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($version->faqItems as $faq)
                                <details class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <summary class="cursor-pointer list-none text-base font-semibold text-slate-900 dark:text-slate-50">{{ $faq->question }}</summary>
                                    <div class="usn-prose mt-4">{!! $faq->answer !!}</div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($version->reviews_enabled || $approvedReviews->isNotEmpty())
                    <section id="reviews" x-show="tab === 'reviews'" x-cloak class="usn-card">
                        <div class="grid gap-8 xl:grid-cols-[1fr_0.9fr]">
                            <div>
                                <h2 class="usn-title">Approved reviews</h2>
                                @if ($approvedReviews->isEmpty())
                                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-200">No approved reviews are visible yet. Review publication remains moderation-aware.</p>
                                @else
                                    <div class="mt-6 space-y-4">
                                        @foreach ($approvedReviews as $review)
                                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $review->title ?: 'Verified product review' }}</p>
                                                    <span class="text-sm text-amber-600">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', max(0, 5 - $review->rating)) }}</span>
                                                </div>
                                                <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $review->body }}</p>
                                                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                                    {{ $review->user?->name ?? 'Verified user' }}
                                                    @if ($review->published_at)
                                                        &middot; {{ $review->published_at->format('M j, Y') }}
                                                    @endif
                                                </p>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-900/70">
                                <h3 class="font-display text-2xl font-semibold text-slate-950 dark:text-slate-50">Submit a review</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Only verified users can submit one active review per product. Reviews appear publicly only after moderation approval.</p>

                                @if (! empty($isPreview))
                                    <x-ui.alert tone="warning" title="Preview mode">
                                        Review submission is disabled while previewing an unpublished version.
                                    </x-ui.alert>
                                @elseif (! $version->reviews_enabled)
                                    <x-ui.alert tone="warning" title="Reviews disabled">
                                        Review collection is disabled for this product version.
                                    </x-ui.alert>
                                @elseif (($reviewEligibility['allowed'] ?? false) === true)
                                    <form method="POST" action="{{ route('products.reviews.store', ['product' => $product->slug_current]) }}" class="mt-6 space-y-4">
                                        @csrf
                                        <div>
                                            <x-input-label for="rating" value="Rating" />
                                            <x-select-input id="rating" name="rating" class="mt-2 block w-full">
                                                @foreach (range(5, 1) as $rating)
                                                    <option value="{{ $rating }}" @selected((int) old('rating', 5) === $rating)>{{ $rating }} / 5</option>
                                                @endforeach
                                            </x-select-input>
                                            <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="title" value="Title" />
                                            <x-text-input id="title" name="title" class="mt-2 block w-full" :value="old('title')" />
                                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="body" value="Review" />
                                            <x-textarea-input id="body" name="body" rows="6" class="mt-2 block w-full">{{ old('body') }}</x-textarea-input>
                                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                                            <x-input-error :messages="$errors->get('review')" class="mt-2" />
                                        </div>
                                        <button type="submit" class="usn-btn-primary">Submit review</button>
                                    </form>
                                @else
                                    <div class="mt-6 space-y-4">
                                        @guest
                                            <a href="{{ route('login') }}" class="usn-btn-primary">Log in to review</a>
                                        @endguest

                                        @if ($reviewReasonMessage)
                                            <x-ui.alert tone="info" title="Review eligibility">
                                                {{ $reviewReasonMessage }}
                                            </x-ui.alert>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                @if ($downloads->isNotEmpty())
                    <section x-show="tab === 'downloads'" x-cloak class="usn-card">
                        <h2 class="usn-title">Downloads</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Downloads stay separate from page visibility. Public product pages can expose release information while the actual download remains authenticated and policy-checked.</p>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($downloads as $download)
                                <article class="rounded-[1.6rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::headline($download->download_mode->value) }}</p>
                                            <h3 class="mt-2 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $download->label }}</h3>
                                        </div>
                                        @if ($download->is_primary)
                                            <span class="usn-badge-brand">Primary</span>
                                        @endif
                                    </div>

                                    @if ($download->description)
                                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">{{ $download->description }}</p>
                                    @endif

                                    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        @if ($download->version_label)
                                            <span>{{ $download->version_label }}</span>
                                        @endif
                                        <span>&middot; {{ $downloadVisibilityMessage($download->visibility) }}</span>
                                    </div>

                                    <div class="mt-6 flex flex-wrap gap-3">
                                        @auth
                                            @if (empty($isPreview) && auth()->user()->can('download', [$product, $download]))
                                                <a href="{{ route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]) }}" class="usn-btn-primary">
                                                    {{ $download->download_mode === \App\Modules\Products\Enums\ProductDownloadMode::ManualRequest ? 'Open request flow' : 'Access download' }}
                                                </a>
                                            @else
                                                <span class="usn-btn-secondary cursor-not-allowed opacity-70">{{ $downloadVisibilityMessage($download->visibility) }}</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="usn-btn-primary">Log in to access</a>
                                        @endauth
                                    </div>

                                    @if ($download->notes)
                                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-300">{{ $download->notes }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($version->support_contact || $downloads->contains(fn ($download) => $download->download_mode === \App\Modules\Products\Enums\ProductDownloadMode::ManualRequest))
                    <section x-show="tab === 'support'" x-cloak class="usn-card">
                        <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
                            <div>
                                <h2 class="usn-title">Support and request handling</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Use controlled request paths for anything that should not be exposed as a direct file delivery.</p>

                                @if ($version->support_contact)
                                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Support contact</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $version->support_contact }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-[1.8rem] border border-slate-200 bg-[radial-gradient(circle_at_top,_rgba(14,116,144,0.1),_transparent_45%),linear-gradient(180deg,_#ffffff,_#f8fafc)] p-6 dark:border-slate-800 dark:bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_45%),linear-gradient(180deg,_rgba(15,23,42,0.96),_rgba(2,6,23,0.98))]">
                                <h3 class="font-display text-2xl font-semibold text-slate-950 dark:text-slate-50">Need a controlled release handoff?</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">Manual-request download modes stay policy-aware and redirect users into a request/contact workflow instead of pretending a file exists.</p>
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ $clientRequestUrl }}" class="usn-btn-primary">Open client request</a>
                                    <a href="{{ url('/contact') }}" class="usn-btn-secondary">Contact USNsoft</a>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </section>
</x-layouts.public>

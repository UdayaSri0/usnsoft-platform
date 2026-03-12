<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="($item ? 'Edit ' : 'New ').rtrim($resourceLabel, 's')" :description="$item ? ($item->title ?? $item->name ?? $item->full_name ?? $item->client_name) : 'Draft showcase content'" eyebrow="Showcase">
            @if ($item)
                <x-slot name="actions">
                    <span class="usn-badge-warning">{{ $item->workflow_state->value }}</span>
                    <span class="usn-badge-info">Approval: {{ $item->approval_state->value }}</span>
                </x-slot>
            @endif
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <form method="POST" action="{{ $item ? route($routeBase.'.update', ['item' => $item->getKey()]) : route($routeBase.'.store') }}" class="space-y-8">
                @csrf
                @if ($item)
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <x-ui.alert tone="danger" title="Validation errors">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <section class="usn-card">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @switch($resourceKey)
                            @case('testimonial')
                                <div><x-input-label for="client_name" value="Client name" /><x-text-input id="client_name" name="client_name" class="mt-2 block w-full" :value="old('client_name', $item?->client_name)" required /></div>
                                <div><x-input-label for="company_name" value="Company" /><x-text-input id="company_name" name="company_name" class="mt-2 block w-full" :value="old('company_name', $item?->company_name)" /></div>
                                <div><x-input-label for="role_title" value="Role / title" /><x-text-input id="role_title" name="role_title" class="mt-2 block w-full" :value="old('role_title', $item?->role_title)" /></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="quote" value="Quote" /><x-textarea-input id="quote" name="quote" rows="6" class="mt-2 block w-full">{{ old('quote', $item?->quote) }}</x-textarea-input></div>
                                <div><x-input-label for="avatar_media_id" value="Avatar media ID" /><x-text-input id="avatar_media_id" name="avatar_media_id" class="mt-2 block w-full" :value="old('avatar_media_id', $item?->avatar_media_id)" /></div>
                                <div><x-input-label for="rating" value="Rating" /><x-text-input id="rating" type="number" name="rating" class="mt-2 block w-full" :value="old('rating', $item?->rating)" /></div>
                                @break
                            @case('partner')
                                <div><x-input-label for="name" value="Name" /><x-text-input id="name" name="name" class="mt-2 block w-full" :value="old('name', $item?->name)" required /></div>
                                <div><x-input-label for="slug" value="Slug" /><x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $item?->slug)" /></div>
                                <div><x-input-label for="category" value="Category" /><x-text-input id="category" name="category" class="mt-2 block w-full" :value="old('category', $item?->category)" /></div>
                                <div><x-input-label for="website_url" value="Website URL" /><x-text-input id="website_url" name="website_url" class="mt-2 block w-full" :value="old('website_url', $item?->website_url)" /></div>
                                <div><x-input-label for="logo_media_id" value="Logo media ID" /><x-text-input id="logo_media_id" name="logo_media_id" class="mt-2 block w-full" :value="old('logo_media_id', $item?->logo_media_id)" /></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="summary" value="Summary" /><x-textarea-input id="summary" name="summary" rows="4" class="mt-2 block w-full">{{ old('summary', $item?->summary) }}</x-textarea-input></div>
                                @break
                            @case('team_member')
                                <div><x-input-label for="full_name" value="Full name" /><x-text-input id="full_name" name="full_name" class="mt-2 block w-full" :value="old('full_name', $item?->full_name)" required /></div>
                                <div><x-input-label for="slug" value="Slug" /><x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $item?->slug)" required /></div>
                                <div><x-input-label for="role_title" value="Role / title" /><x-text-input id="role_title" name="role_title" class="mt-2 block w-full" :value="old('role_title', $item?->role_title)" required /></div>
                                <div><x-input-label for="photo_media_id" value="Photo media ID" /><x-text-input id="photo_media_id" name="photo_media_id" class="mt-2 block w-full" :value="old('photo_media_id', $item?->photo_media_id)" /></div>
                                <div><x-input-label for="public_email" value="Public email" /><x-text-input id="public_email" name="public_email" class="mt-2 block w-full" :value="old('public_email', $item?->public_email)" /></div>
                                <div><x-input-label for="public_phone" value="Public phone" /><x-text-input id="public_phone" name="public_phone" class="mt-2 block w-full" :value="old('public_phone', $item?->public_phone)" /></div>
                                <div><x-input-label for="linkedin_url" value="LinkedIn URL" /><x-text-input id="linkedin_url" name="linkedin_url" class="mt-2 block w-full" :value="old('linkedin_url', $item?->linkedin_url)" /></div>
                                <div><x-input-label for="github_url" value="GitHub URL" /><x-text-input id="github_url" name="github_url" class="mt-2 block w-full" :value="old('github_url', $item?->github_url)" /></div>
                                <div><x-input-label for="website_url" value="Website URL" /><x-text-input id="website_url" name="website_url" class="mt-2 block w-full" :value="old('website_url', $item?->website_url)" /></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="short_bio" value="Short bio" /><x-textarea-input id="short_bio" name="short_bio" rows="4" class="mt-2 block w-full">{{ old('short_bio', $item?->short_bio) }}</x-textarea-input></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="full_bio" value="Full bio" /><x-textarea-input id="full_bio" name="full_bio" rows="8" class="mt-2 block w-full">{{ old('full_bio', $item?->full_bio) }}</x-textarea-input></div>
                                @break
                            @case('timeline_entry')
                                <div><x-input-label for="title" value="Title" /><x-text-input id="title" name="title" class="mt-2 block w-full" :value="old('title', $item?->title)" required /></div>
                                <div><x-input-label for="event_date" value="Event date" /><x-text-input id="event_date" type="datetime-local" name="event_date" class="mt-2 block w-full" :value="old('event_date', $item?->event_date?->format('Y-m-d\\TH:i'))" /></div>
                                <div><x-input-label for="date_label" value="Date label" /><x-text-input id="date_label" name="date_label" class="mt-2 block w-full" :value="old('date_label', $item?->date_label)" /></div>
                                <div><x-input-label for="image_media_id" value="Image media ID" /><x-text-input id="image_media_id" name="image_media_id" class="mt-2 block w-full" :value="old('image_media_id', $item?->image_media_id)" /></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="summary" value="Summary" /><x-textarea-input id="summary" name="summary" rows="4" class="mt-2 block w-full">{{ old('summary', $item?->summary) }}</x-textarea-input></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="description" value="Description" /><x-textarea-input id="description" name="description" rows="8" class="mt-2 block w-full">{{ old('description', $item?->description) }}</x-textarea-input></div>
                                @break
                            @case('achievement')
                                <div><x-input-label for="title" value="Title" /><x-text-input id="title" name="title" class="mt-2 block w-full" :value="old('title', $item?->title)" required /></div>
                                <div><x-input-label for="slug" value="Slug" /><x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $item?->slug)" /></div>
                                <div><x-input-label for="achievement_date" value="Achievement date" /><x-text-input id="achievement_date" type="datetime-local" name="achievement_date" class="mt-2 block w-full" :value="old('achievement_date', $item?->achievement_date?->format('Y-m-d\\TH:i'))" /></div>
                                <div><x-input-label for="image_media_id" value="Image media ID" /><x-text-input id="image_media_id" name="image_media_id" class="mt-2 block w-full" :value="old('image_media_id', $item?->image_media_id)" /></div>
                                <div><x-input-label for="category" value="Category" /><x-text-input id="category" name="category" class="mt-2 block w-full" :value="old('category', $item?->category)" /></div>
                                <div><x-input-label for="metric_value" value="Metric value" /><x-text-input id="metric_value" name="metric_value" class="mt-2 block w-full" :value="old('metric_value', $item?->metric_value)" /></div>
                                <div><x-input-label for="metric_prefix" value="Metric prefix" /><x-text-input id="metric_prefix" name="metric_prefix" class="mt-2 block w-full" :value="old('metric_prefix', $item?->metric_prefix)" /></div>
                                <div><x-input-label for="metric_suffix" value="Metric suffix" /><x-text-input id="metric_suffix" name="metric_suffix" class="mt-2 block w-full" :value="old('metric_suffix', $item?->metric_suffix)" /></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="summary" value="Summary" /><x-textarea-input id="summary" name="summary" rows="4" class="mt-2 block w-full">{{ old('summary', $item?->summary) }}</x-textarea-input></div>
                                <div class="md:col-span-2 xl:col-span-3"><x-input-label for="description" value="Description" /><x-textarea-input id="description" name="description" rows="8" class="mt-2 block w-full">{{ old('description', $item?->description) }}</x-textarea-input></div>
                                @break
                        @endswitch

                        <div><x-input-label for="visibility" value="Visibility" /><x-select-input id="visibility" name="visibility" class="mt-2 block w-full">@foreach (\App\Enums\VisibilityState::cases() as $visibility)<option value="{{ $visibility->value }}" @selected(old('visibility', $item?->visibility?->value ?? \App\Enums\VisibilityState::Public->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>@endforeach</x-select-input></div>
                        <div><x-input-label for="sort_order" value="Sort order" /><x-text-input id="sort_order" type="number" name="sort_order" class="mt-2 block w-full" :value="old('sort_order', $item?->sort_order ?? 0)" /></div>
                        <div class="flex items-end"><label class="inline-flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="featured_flag" value="1" @checked((bool) old('featured_flag', $item?->featured_flag ?? false)) class="usn-checkbox">Featured</label></div>
                        <div class="md:col-span-2 xl:col-span-3"><x-input-label for="change_notes" value="Change notes" /><x-textarea-input id="change_notes" name="change_notes" rows="3" class="mt-2 block w-full">{{ old('change_notes', $item?->change_notes) }}</x-textarea-input></div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route($routeBase.'.index') }}" class="usn-btn-secondary">Cancel</a>
                    <x-primary-button>{{ $item ? 'Save Draft' : 'Create Draft' }}</x-primary-button>
                </div>
            </form>

            @if ($item)
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Workflow Actions</h2>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if ($item->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                            @can('submitForReview', $item)
                                <form method="POST" action="{{ route($routeBase.'.submit-review', ['item' => $item->getKey()]) }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="notes" placeholder="Review note" class="usn-input min-h-11 w-72">
                                    <x-primary-button>Submit for Review</x-primary-button>
                                </form>
                            @endcan
                        @endif

                        @if ($item->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                            @can('approve', $item)
                                <form method="POST" action="{{ route($routeBase.'.versions.approve', ['item' => $item->getKey()]) }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="notes" placeholder="Approval note" class="usn-input min-h-11 w-72">
                                    <x-primary-button>Approve</x-primary-button>
                                </form>
                            @endcan
                            @can('reject', $item)
                                <form method="POST" action="{{ route($routeBase.'.versions.reject', ['item' => $item->getKey()]) }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="notes" placeholder="Revision note" class="usn-input min-h-11 w-72">
                                    <x-danger-button>Reject to Draft</x-danger-button>
                                </form>
                            @endcan
                        @endif

                        @if ($item->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                            @can('publish', $item)
                                <form method="POST" action="{{ route($routeBase.'.versions.publish', ['item' => $item->getKey()]) }}">
                                    @csrf
                                    <x-primary-button>Publish</x-primary-button>
                                </form>
                            @endcan
                            @can('schedule', $item)
                                <form method="POST" action="{{ route($routeBase.'.versions.schedule', ['item' => $item->getKey()]) }}" class="flex gap-2">
                                    @csrf
                                    <input type="datetime-local" name="schedule_publish_at" required class="usn-input min-h-11">
                                    <x-secondary-button>Schedule</x-secondary-button>
                                </form>
                            @endcan
                        @endif

                        @if (in_array($item->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                            @can('archive', $item)
                                <form method="POST" action="{{ route($routeBase.'.versions.archive', ['item' => $item->getKey()]) }}">
                                    @csrf
                                    <x-danger-button>Archive</x-danger-button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>

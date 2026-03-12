@php
    $seo = old('seo');

    if (! is_array($seo)) {
        $seo = [
            'meta_title' => $job?->seoMeta?->meta_title,
            'meta_description' => $job?->seoMeta?->meta_description,
            'canonical_url' => $job?->seoMeta?->canonical_url,
            'og_title' => $job?->seoMeta?->og_title,
            'og_description' => $job?->seoMeta?->og_description,
        ];
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
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
        <h2 class="font-display text-xl font-semibold text-slate-950">Job Details</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-2 block w-full" :value="old('title', $job?->title)" required />
            </div>
            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $job?->slug)" required />
            </div>
            <div>
                <x-input-label for="visibility" value="Visibility" />
                <x-select-input id="visibility" name="visibility" class="mt-2 block w-full">
                    @foreach (\App\Enums\VisibilityState::cases() as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('visibility', $job?->visibility?->value ?? \App\Enums\VisibilityState::Public->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="department" value="Department" />
                <x-text-input id="department" name="department" class="mt-2 block w-full" :value="old('department', $job?->department)" />
            </div>
            <div>
                <x-input-label for="employment_type" value="Employment type" />
                <x-text-input id="employment_type" name="employment_type" class="mt-2 block w-full" :value="old('employment_type', $job?->employment_type)" />
            </div>
            <div>
                <x-input-label for="level" value="Level" />
                <x-text-input id="level" name="level" class="mt-2 block w-full" :value="old('level', $job?->level)" />
            </div>
            <div>
                <x-input-label for="location" value="Location" />
                <x-text-input id="location" name="location" class="mt-2 block w-full" :value="old('location', $job?->location)" />
            </div>
            <div>
                <x-input-label for="deadline" value="Deadline" />
                <x-text-input id="deadline" type="datetime-local" name="deadline" class="mt-2 block w-full" :value="old('deadline', $job?->deadline?->format('Y-m-d\\TH:i'))" />
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="featured_flag" value="1" @checked((bool) old('featured_flag', $job?->featured_flag ?? false)) class="usn-checkbox">
                    Featured job
                </label>
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="summary" value="Summary" />
                <x-textarea-input id="summary" name="summary" rows="4" class="mt-2 block w-full">{{ old('summary', $job?->summary) }}</x-textarea-input>
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="description" value="Description" />
                <x-textarea-input id="description" name="description" rows="10" class="mt-2 block w-full">{{ old('description', $job?->description) }}</x-textarea-input>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">SEO</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="seo_meta_title" value="Meta title" />
                <x-text-input id="seo_meta_title" name="seo[meta_title]" class="mt-2 block w-full" :value="$seo['meta_title'] ?? ''" />
            </div>
            <div>
                <x-input-label for="seo_canonical_url" value="Canonical URL" />
                <x-text-input id="seo_canonical_url" name="seo[canonical_url]" class="mt-2 block w-full" :value="$seo['canonical_url'] ?? ''" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="seo_meta_description" value="Meta description" />
                <x-textarea-input id="seo_meta_description" name="seo[meta_description]" rows="3" class="mt-2 block w-full">{{ $seo['meta_description'] ?? '' }}</x-textarea-input>
            </div>
            <div>
                <x-input-label for="seo_og_title" value="OG title" />
                <x-text-input id="seo_og_title" name="seo[og_title]" class="mt-2 block w-full" :value="$seo['og_title'] ?? ''" />
            </div>
            <div>
                <x-input-label for="seo_og_description" value="OG description" />
                <x-textarea-input id="seo_og_description" name="seo[og_description]" rows="3" class="mt-2 block w-full">{{ $seo['og_description'] ?? '' }}</x-textarea-input>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="change_notes" value="Change notes" />
                <x-textarea-input id="change_notes" name="change_notes" rows="3" class="mt-2 block w-full">{{ old('change_notes', $job?->change_notes) }}</x-textarea-input>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.careers.index') }}" class="usn-btn-secondary">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>

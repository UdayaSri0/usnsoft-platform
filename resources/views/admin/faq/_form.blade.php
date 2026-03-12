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
        <h2 class="font-display text-xl font-semibold text-slate-950">FAQ Content</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="question" value="Question" />
                <x-text-input id="question" name="question" class="mt-2 block w-full" :value="old('question', $faq?->question)" required />
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="answer" value="Answer" />
                <x-textarea-input id="answer" name="answer" rows="8" class="mt-2 block w-full">{{ old('answer', $faq?->answer) }}</x-textarea-input>
            </div>
            <div>
                <x-input-label for="faq_category_id" value="Category" />
                <x-select-input id="faq_category_id" name="faq_category_id" class="mt-2 block w-full">
                    <option value="">General</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->getKey() }}" @selected((string) old('faq_category_id', $faq?->faq_category_id) === (string) $category->getKey())>{{ $category->name }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="linked_product_id" value="Linked product" />
                <x-select-input id="linked_product_id" name="linked_product_id" class="mt-2 block w-full">
                    <option value="">None</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->getKey() }}" @selected((string) old('linked_product_id', $faq?->linked_product_id) === (string) $product->getKey())>{{ $product->name_current }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="visibility" value="Visibility" />
                <x-select-input id="visibility" name="visibility" class="mt-2 block w-full">
                    @foreach (\App\Enums\VisibilityState::cases() as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('visibility', $faq?->visibility?->value ?? \App\Enums\VisibilityState::Public->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="sort_order" value="Sort order" />
                <x-text-input id="sort_order" type="number" name="sort_order" class="mt-2 block w-full" :value="old('sort_order', $faq?->sort_order ?? 0)" />
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="featured_flag" value="1" @checked((bool) old('featured_flag', $faq?->featured_flag ?? false)) class="usn-checkbox">
                    Featured
                </label>
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="change_notes" value="Change notes" />
                <x-textarea-input id="change_notes" name="change_notes" rows="3" class="mt-2 block w-full">{{ old('change_notes', $faq?->change_notes) }}</x-textarea-input>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.faq.index') }}" class="usn-btn-secondary">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>

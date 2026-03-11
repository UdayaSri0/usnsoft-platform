<x-layouts.public :seo="['meta_title' => 'USNsoft Platform']" :is-preview="false">
    <section class="py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white p-10 shadow-sm">
                <p class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">CMS not published yet</p>
                <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-slate-900">USNsoft CMS Foundation Is Ready</h1>
                <p class="mt-4 max-w-3xl text-base text-slate-600">
                    No published Home page version exists yet. Create or publish page content from the internal CMS panel to make the public site fully dynamic.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Login</a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Open Admin</a>
                    <a href="{{ route('admin.cms.pages.index') }}" class="inline-flex rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Open CMS Pages</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>

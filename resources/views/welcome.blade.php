<x-layouts.public :seo="['meta_title' => 'USNsoft Platform']" :is-preview="false">
    <section class="usn-section-xl">
        <div class="usn-container-wide">
            <div class="rounded-[2rem] border border-slate-200/80 bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.12),_transparent_34%),linear-gradient(180deg,_#ffffff,_#f8fafc)] p-8 shadow-sm sm:p-12">
                <span class="usn-overline">CMS not published yet</span>
                <h1 class="mt-6 max-w-4xl font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">
                    USNsoft is ready for dynamic public content and internal publishing workflows.
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
                    No published Home page version exists yet. Create or publish page content from the internal CMS to switch this placeholder into the real public homepage.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="usn-btn-secondary">Log in</a>
                    <a href="{{ route('admin.dashboard') }}" class="usn-btn-primary">Open admin</a>
                    <a href="{{ route('admin.cms.pages.index') }}" class="usn-btn-secondary">Open CMS pages</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>

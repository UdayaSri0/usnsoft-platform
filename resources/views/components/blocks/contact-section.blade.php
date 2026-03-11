<div class="grid gap-8 lg:grid-cols-2">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Contact' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-3 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif

        <dl class="mt-6 space-y-3 text-sm text-slate-700">
            @if (!empty($data['address']))<div><dt class="font-semibold">Address</dt><dd>{{ $data['address'] }}</dd></div>@endif
            @if (!empty($data['phone']))<div><dt class="font-semibold">Phone</dt><dd>{{ $data['phone'] }}</dd></div>@endif
            @if (!empty($data['email']))<div><dt class="font-semibold">Email</dt><dd>{{ $data['email'] }}</dd></div>@endif
            @if (!empty($data['hours']))<div><dt class="font-semibold">Hours</dt><dd>{{ $data['hours'] }}</dd></div>@endif
        </dl>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if (($data['show_form'] ?? false) === true)
            <h3 class="font-display text-lg font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $data['form_type'] ?? 'contact')) }} Form</h3>
            <p class="mt-2 text-sm text-slate-600">Configured to use internal form handlers. Public request handlers are connected in module-specific stages.</p>
            <a href="{{ url('/contact') }}" class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Open Contact</a>
        @else
            <p class="text-sm text-slate-600">Contact form is hidden for this section.</p>
        @endif
    </div>
</div>

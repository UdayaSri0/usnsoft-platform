<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
    <div class="usn-container-wide flex min-h-[4.75rem] items-center justify-between gap-4">
        <div class="flex items-center gap-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-900 text-xs font-bold text-white">US</span>
                <span>
                    <span class="block font-display text-base font-semibold text-slate-900">USNsoft</span>
                    <span class="block text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Authenticated Workspace</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">Profile</x-nav-link>
                <x-nav-link :href="route('account.sessions.index')" :active="request()->routeIs('account.sessions.*')">Sessions</x-nav-link>
                <x-nav-link :href="route('account.devices.index')" :active="request()->routeIs('account.devices.*')">Devices</x-nav-link>

                @if (Auth::user()->isInternalStaff())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Admin</x-nav-link>
                    @if (Auth::user()->hasPermission('cms.pages.view'))
                        <x-nav-link :href="route('admin.cms.pages.index')" :active="request()->routeIs('admin.cms.*')">CMS</x-nav-link>
                    @endif
                    @if (Auth::user()->hasPermission('products.view'))
                        <x-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">Products</x-nav-link>
                    @endif
                @endif
            </div>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ url('/') }}" class="usn-btn-secondary">Public Site</a>

            <x-dropdown align="right" width="64">
                <x-slot name="trigger">
                    <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-900 text-xs font-bold text-white">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                        <span>{{ Auth::user()->name }}</span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Account</div>
                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                    <x-dropdown-link :href="route('account.sessions.index')">Session History</x-dropdown-link>
                    <x-dropdown-link :href="route('account.devices.index')">Device History</x-dropdown-link>

                    @if (Auth::user()->isInternalStaff())
                        <div class="mt-2 border-t border-slate-200 pt-2">
                            <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Internal</div>
                            <x-dropdown-link :href="route('admin.dashboard')">Admin Dashboard</x-dropdown-link>
                            @if (Auth::user()->hasPermission('cms.pages.view'))
                                <x-dropdown-link :href="route('admin.cms.pages.index')">CMS Pages</x-dropdown-link>
                            @endif
                            @if (Auth::user()->hasPermission('products.view'))
                                <x-dropdown-link :href="route('admin.products.index')">Products</x-dropdown-link>
                            @endif
                            @if (Auth::user()->hasPermission('cms.approvals.view_queue'))
                                <x-dropdown-link :href="route('admin.cms.approvals.index')">Approval Queue</x-dropdown-link>
                            @endif
                        </div>
                    @endif

                    <div class="mt-2 border-t border-slate-200 pt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                        </form>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>

        <button type="button" @click="open = !open" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-300 bg-white text-slate-600 lg:hidden" aria-label="Toggle authenticated navigation" :aria-expanded="open.toString()">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="open ? 'M6 18L18 6M6 6l12 12' : 'M4 7h16M4 12h16M4 17h16'" />
            </svg>
        </button>
    </div>

    <div x-cloak x-show="open" x-transition class="border-t border-slate-200 bg-white/95 lg:hidden">
        <div class="usn-container-wide space-y-3 py-4">
            <div class="space-y-1 text-sm">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">Profile</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('account.sessions.index')" :active="request()->routeIs('account.sessions.*')">Sessions</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('account.devices.index')" :active="request()->routeIs('account.devices.*')">Devices</x-responsive-nav-link>
            @if (Auth::user()->isInternalStaff())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Admin</x-responsive-nav-link>
                @if (Auth::user()->hasPermission('cms.pages.view'))
                    <x-responsive-nav-link :href="route('admin.cms.pages.index')" :active="request()->routeIs('admin.cms.*')">CMS</x-responsive-nav-link>
                @endif
                @if (Auth::user()->hasPermission('products.view'))
                    <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">Products</x-responsive-nav-link>
                @endif
            @endif
            <x-responsive-nav-link :href="url('/')">Public Site</x-responsive-nav-link>
        </div>

        <div class="usn-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="usn-btn-secondary w-full">Log Out</button>
        </form>
        </div>
    </div>
</nav>

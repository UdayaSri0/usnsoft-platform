<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white/90 backdrop-blur-lg">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-xs font-bold text-white">US</span>
                <span class="font-display text-base font-semibold text-slate-900">USNsoft</span>
            </a>

            <div class="hidden items-center gap-5 text-sm font-medium text-slate-600 lg:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">Profile</x-nav-link>
                <x-nav-link :href="route('account.sessions.index')" :active="request()->routeIs('account.sessions.*')">Sessions</x-nav-link>
                <x-nav-link :href="route('account.devices.index')" :active="request()->routeIs('account.devices.*')">Devices</x-nav-link>

                @if (Auth::user()->isInternalStaff())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Admin</x-nav-link>
                    @if (Auth::user()->hasPermission('cms.pages.view'))
                        <x-nav-link :href="route('admin.cms.pages.index')" :active="request()->routeIs('admin.cms.*')">CMS</x-nav-link>
                    @endif
                @endif
            </div>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ url('/') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400">Public Site</a>

            <x-dropdown align="right" width="64">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
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

        <button @click="open = !open" class="inline-flex items-center rounded-xl border border-slate-300 p-2 text-slate-600 lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="open ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-transition class="border-t border-slate-200 bg-white px-4 py-3 lg:hidden">
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
            @endif
            <x-responsive-nav-link :href="url('/')">Public Site</x-responsive-nav-link>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="usn-btn-secondary w-full">Log Out</button>
        </form>
    </div>
</nav>

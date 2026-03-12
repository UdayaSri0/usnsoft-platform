@props([
    'align' => 'right',
])

<div
    x-data="themeToggle({ align: @js($align) })"
    x-id="['theme-menu']"
    class="relative"
    @click.outside="open = false"
    @keydown.escape.stop="open = false"
>
    <button
        type="button"
        class="usn-theme-trigger"
        @click="toggleMenu()"
        aria-expanded="false"
        aria-label="Appearance settings"
        :aria-controls="$id('theme-menu')"
        :aria-expanded="open.toString()"
        :aria-label="triggerAriaLabel"
        aria-haspopup="dialog"
    >
        <span class="usn-theme-trigger-icon" aria-hidden="true">
            <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m14.114 6.364-1.591-1.591M8.477 8.477 6.886 6.886m10.228 1.591 1.591-1.591M8.477 15.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <svg class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1 1 11.21 3c-.17.57-.26 1.17-.26 1.79A7.5 7.5 0 0 0 18.46 12c.9 0 1.78-.16 2.54-.47Z" />
            </svg>
        </span>

        <span class="hidden min-w-0 flex-col items-start leading-tight sm:flex">
            <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Theme</span>
            <span class="truncate text-sm font-medium" x-text="triggerLabel">Theme</span>
        </span>

        <svg class="hidden h-4 w-4 shrink-0 text-slate-400 sm:block dark:text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :id="$id('theme-menu')"
        :class="menuAlignmentClasses"
        class="absolute z-50 mt-3 w-80 max-w-[calc(100vw-2rem)]"
    >
        <div class="usn-menu-panel space-y-1 p-2" role="dialog" aria-label="Theme options">
            <div class="px-3 pb-2 pt-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Appearance</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Stored locally in this browser only.</p>
            </div>

            <div class="space-y-1" role="group" aria-label="Select a theme">
                @foreach (['light', 'dark', 'system'] as $theme)
                    <button
                        type="button"
                        class="usn-theme-option"
                        :class="{ 'usn-theme-option-active': isSelected('{{ $theme }}') }"
                        :aria-pressed="isSelected('{{ $theme }}').toString()"
                        @click="setTheme('{{ $theme }}')"
                    >
                        <span class="usn-theme-option-icon" aria-hidden="true">
                            @if ($theme === 'light')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m14.114 6.364-1.591-1.591M8.477 8.477 6.886 6.886m10.228 1.591 1.591-1.591M8.477 15.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z" />
                                </svg>
                            @elseif ($theme === 'dark')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1 1 11.21 3c-.17.57-.26 1.17-.26 1.79A7.5 7.5 0 0 0 18.46 12c.9 0 1.78-.16 2.54-.47Z" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 5.25A2.25 2.25 0 0 1 6 3h12a2.25 2.25 0 0 1 2.25 2.25v8.5A2.25 2.25 0 0 1 18 16h-4.5l-3.75 3v-3H6a2.25 2.25 0 0 1-2.25-2.25v-8.5Z" />
                                </svg>
                            @endif
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold" x-text="labelFor('{{ $theme }}')"></span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400" x-text="descriptionFor('{{ $theme }}')"></span>
                        </span>

                        <span class="usn-theme-option-check" :class="{ 'opacity-100': isSelected('{{ $theme }}'), 'opacity-0': !isSelected('{{ $theme }}') }" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 5.29a.75.75 0 0 1 .006 1.06l-7.25 7.333a.75.75 0 0 1-1.074-.01l-3.096-3.213a.75.75 0 1 1 1.08-1.04l2.563 2.66 6.72-6.796a.75.75 0 0 1 1.05.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

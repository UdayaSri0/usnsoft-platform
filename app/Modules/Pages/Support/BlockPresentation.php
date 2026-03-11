<?php

namespace App\Modules\Pages\Support;

class BlockPresentation
{
    /**
     * @param  array<string, mixed>  $layout
     */
    public function wrapperClass(array $layout): string
    {
        $spacing = match ($layout['spacing'] ?? 'md') {
            'none' => 'py-0',
            'sm' => 'py-6',
            'lg' => 'py-16',
            'xl' => 'py-20',
            default => 'py-10',
        };

        $theme = match ($layout['theme_variant'] ?? 'light') {
            'dark' => 'bg-slate-900 text-slate-100',
            'brand' => 'bg-gradient-to-br from-blue-950 via-sky-950 to-slate-900 text-white',
            'accent' => 'bg-cyan-50 text-slate-900',
            'neutral' => 'bg-slate-50 text-slate-900',
            default => 'bg-white text-slate-900',
        };

        return trim("{$theme} {$spacing}");
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public function containerClass(array $layout): string
    {
        return match ($layout['container_width'] ?? 'contained') {
            'full' => 'w-full',
            'wide' => 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
            default => 'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8',
        };
    }

    /**
     * @param  array<string, mixed>  $visibility
     */
    public function visibilityClass(array $visibility): string
    {
        $desktop = (bool) ($visibility['desktop'] ?? true);
        $tablet = (bool) ($visibility['tablet'] ?? true);
        $mobile = (bool) ($visibility['mobile'] ?? true);

        if (! $desktop && ! $tablet && ! $mobile) {
            return 'hidden';
        }

        if ($desktop && $tablet && $mobile) {
            return '';
        }

        $classes = [];

        if (! $mobile) {
            $classes[] = 'hidden sm:block';
        }

        if (! $tablet) {
            $classes[] = 'sm:hidden lg:block';
        }

        if (! $desktop) {
            $classes[] = 'lg:hidden';
        }

        return implode(' ', array_unique($classes));
    }
}

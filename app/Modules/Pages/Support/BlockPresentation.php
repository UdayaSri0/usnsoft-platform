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
            'sm' => 'usn-section-sm',
            'lg' => 'usn-section-lg',
            'xl' => 'usn-section-xl',
            default => 'usn-section',
        };

        $theme = match ($layout['theme_variant'] ?? 'light') {
            'dark' => 'usn-surface-dark',
            'brand' => 'usn-surface-brand',
            'accent' => 'usn-surface-accent',
            'neutral' => 'usn-surface-muted',
            default => 'usn-surface-default',
        };

        return trim("{$theme} {$spacing}");
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public function containerClass(array $layout): string
    {
        return match ($layout['container_width'] ?? 'contained') {
            'full' => 'usn-container-fluid',
            'wide' => 'usn-container-wide',
            default => 'usn-container',
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

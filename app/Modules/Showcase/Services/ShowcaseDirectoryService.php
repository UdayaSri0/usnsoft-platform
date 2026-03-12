<?php

namespace App\Modules\Showcase\Services;

use App\Modules\Showcase\Models\Achievement;
use App\Modules\Showcase\Models\Partner;
use App\Modules\Showcase\Models\TeamMember;
use App\Modules\Showcase\Models\Testimonial;
use App\Modules\Showcase\Models\TimelineEntry;
use Illuminate\Support\Collection;

class ShowcaseDirectoryService
{
    /**
     * @return Collection<int, Testimonial>
     */
    public function testimonials(string $mode = 'recent', int $limit = 6): Collection
    {
        return Testimonial::query()
            ->publiclyVisible()
            ->with('avatar')
            ->when($mode === 'featured', fn ($query) => $query->where('featured_flag', true))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Partner>
     */
    public function partners(bool $featuredOnly = false, int $limit = 18): Collection
    {
        return Partner::query()
            ->publiclyVisible()
            ->with('logo')
            ->when($featuredOnly, fn ($query) => $query->where('featured_flag', true))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, TeamMember>
     */
    public function teamMembers(string $mode = 'all', int $limit = 8): Collection
    {
        return TeamMember::query()
            ->publiclyVisible()
            ->with('photo')
            ->when($mode === 'featured', fn ($query) => $query->where('featured_flag', true))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->orderBy('full_name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, TimelineEntry>
     */
    public function timelineEntries(string $mode = 'all', int $limit = 10): Collection
    {
        return TimelineEntry::query()
            ->publiclyVisible()
            ->with('image')
            ->when($mode === 'featured', fn ($query) => $query->where('featured_flag', true))
            ->orderBy('event_date')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function achievements(string $mode = 'all', int $limit = 6): Collection
    {
        return Achievement::query()
            ->publiclyVisible()
            ->with('image')
            ->when($mode === 'featured', fn ($query) => $query->where('featured_flag', true))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->orderByDesc('achievement_date')
            ->limit($limit)
            ->get();
    }
}

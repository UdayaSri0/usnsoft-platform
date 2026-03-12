<?php

namespace App\Modules\Careers\Services;

use App\Modules\Careers\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CareerCatalogService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function publicListing(array $filters = []): LengthAwarePaginator
    {
        $query = Job::query()
            ->publiclyVisible()
            ->search(is_string($filters['q'] ?? null) ? $filters['q'] : null)
            ->where(function (Builder $deadlineQuery): void {
                $deadlineQuery
                    ->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            });

        if (is_string($filters['department'] ?? null) && trim($filters['department']) !== '') {
            $query->where('department', trim((string) $filters['department']));
        }

        if (is_string($filters['employment_type'] ?? null) && trim($filters['employment_type']) !== '') {
            $query->where('employment_type', trim((string) $filters['employment_type']));
        }

        if (is_string($filters['location'] ?? null) && trim($filters['location']) !== '') {
            $query->where('location', trim((string) $filters['location']));
        }

        return $query
            ->orderByDesc('featured_flag')
            ->orderBy('deadline')
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function resolvePublicJob(string $slug): ?Job
    {
        return Job::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->where(function (Builder $deadlineQuery): void {
                $deadlineQuery
                    ->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            })
            ->with('seoMeta.ogImage')
            ->first();
    }

    /**
     * @return Collection<int, Job>
     */
    public function openJobs(
        int $limit = 6,
        bool $featuredOnly = false,
        ?string $department = null,
        ?string $employmentType = null,
    ): Collection
    {
        return Job::query()
            ->publiclyVisible()
            ->when($featuredOnly, fn (Builder $query) => $query->where('featured_flag', true))
            ->when($department !== null && $department !== '', fn (Builder $query) => $query->where('department', $department))
            ->when($employmentType !== null && $employmentType !== '', fn (Builder $query) => $query->where('employment_type', $employmentType))
            ->where(function (Builder $deadlineQuery): void {
                $deadlineQuery
                    ->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            })
            ->orderByDesc('featured_flag')
            ->orderBy('deadline')
            ->limit($limit)
            ->get();
    }
}

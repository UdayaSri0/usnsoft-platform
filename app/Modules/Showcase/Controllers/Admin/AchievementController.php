<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Models\User;
use App\Modules\Showcase\Models\Achievement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class AchievementController extends AbstractShowcaseController
{
    protected function modelClass(): string
    {
        return Achievement::class;
    }

    protected function resourceKey(): string
    {
        return 'achievement';
    }

    protected function resourceLabel(): string
    {
        return 'Achievements';
    }

    protected function routeBase(): string
    {
        return 'admin.showcase.achievements';
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('title', 'like', '%'.$term.'%')
            ->orWhere('summary', 'like', '%'.$term.'%')
            ->orWhere('category', 'like', '%'.$term.'%'));
    }

    protected function rules(Model $item): array
    {
        return array_merge($this->sharedRules(), [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('achievements', 'slug')->ignore($item->getKey())],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'achievement_date' => ['nullable', 'date'],
            'image_media_id' => ['nullable', 'exists:media_assets,id'],
            'metric_value' => ['nullable', 'string', 'max:120'],
            'metric_prefix' => ['nullable', 'string', 'max:20'],
            'metric_suffix' => ['nullable', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);
    }

    protected function persist(Model $item, User $actor, array $validated): void
    {
        $oldValues = $item->exists ? ['title' => $item->title] : [];

        $item->fill([
            'title' => trim((string) $validated['title']),
            'slug' => $this->contentSanitizer->sanitizeNullableText($validated['slug'] ?? null),
            'summary' => $this->contentSanitizer->sanitizeNullableText($validated['summary'] ?? null),
            'description' => $this->contentSanitizer->sanitizeRichText($validated['description'] ?? null),
            'achievement_date' => $validated['achievement_date'] ?? null,
            'image_media_id' => $validated['image_media_id'] ?? null,
            'metric_value' => $this->contentSanitizer->sanitizeNullableText($validated['metric_value'] ?? null),
            'metric_prefix' => $this->contentSanitizer->sanitizeNullableText($validated['metric_prefix'] ?? null),
            'metric_suffix' => $this->contentSanitizer->sanitizeNullableText($validated['metric_suffix'] ?? null),
            'category' => $this->contentSanitizer->sanitizeNullableText($validated['category'] ?? null),
            'featured_flag' => (bool) ($validated['featured_flag'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'visibility' => $validated['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($validated['change_notes'] ?? null),
            'created_by' => $item->created_by ?? $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: $item->wasRecentlyCreated ? 'showcase.achievement.created' : 'showcase.achievement.updated',
            action: $item->wasRecentlyCreated ? 'create_achievement' : 'update_achievement',
            actor: $actor,
            auditable: $item,
            oldValues: $oldValues,
            newValues: ['title' => $item->title],
        );
    }
}

<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Models\User;
use App\Modules\Showcase\Models\TimelineEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TimelineEntryController extends AbstractShowcaseController
{
    protected function modelClass(): string
    {
        return TimelineEntry::class;
    }

    protected function resourceKey(): string
    {
        return 'timeline_entry';
    }

    protected function resourceLabel(): string
    {
        return 'Timeline Entries';
    }

    protected function routeBase(): string
    {
        return 'admin.showcase.timeline';
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('title', 'like', '%'.$term.'%')
            ->orWhere('summary', 'like', '%'.$term.'%')
            ->orWhere('description', 'like', '%'.$term.'%'));
    }

    protected function rules(Model $item): array
    {
        return array_merge($this->sharedRules(), [
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['nullable', 'date'],
            'date_label' => ['nullable', 'string', 'max:80'],
            'image_media_id' => ['nullable', 'exists:media_assets,id'],
        ]);
    }

    protected function persist(Model $item, User $actor, array $validated): void
    {
        $oldValues = $item->exists ? ['title' => $item->title] : [];

        $item->fill([
            'title' => trim((string) $validated['title']),
            'summary' => $this->contentSanitizer->sanitizeNullableText($validated['summary'] ?? null),
            'description' => $this->contentSanitizer->sanitizeRichText($validated['description'] ?? null),
            'event_date' => $validated['event_date'] ?? null,
            'date_label' => $this->contentSanitizer->sanitizeNullableText($validated['date_label'] ?? null),
            'image_media_id' => $validated['image_media_id'] ?? null,
            'featured_flag' => (bool) ($validated['featured_flag'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'visibility' => $validated['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($validated['change_notes'] ?? null),
            'created_by' => $item->created_by ?? $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: $item->wasRecentlyCreated ? 'showcase.timeline_entry.created' : 'showcase.timeline_entry.updated',
            action: $item->wasRecentlyCreated ? 'create_timeline_entry' : 'update_timeline_entry',
            actor: $actor,
            auditable: $item,
            oldValues: $oldValues,
            newValues: ['title' => $item->title],
        );
    }
}

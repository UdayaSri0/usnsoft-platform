<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Models\User;
use App\Modules\Showcase\Models\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PartnerController extends AbstractShowcaseController
{
    protected function modelClass(): string
    {
        return Partner::class;
    }

    protected function resourceKey(): string
    {
        return 'partner';
    }

    protected function resourceLabel(): string
    {
        return 'Partners';
    }

    protected function routeBase(): string
    {
        return 'admin.showcase.partners';
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('name', 'like', '%'.$term.'%')
            ->orWhere('category', 'like', '%'.$term.'%')
            ->orWhere('summary', 'like', '%'.$term.'%'));
    }

    protected function rules(Model $item): array
    {
        return array_merge($this->sharedRules(), [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('partners', 'slug')->ignore($item->getKey())],
            'logo_media_id' => ['nullable', 'exists:media_assets,id'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);
    }

    protected function persist(Model $item, User $actor, array $validated): void
    {
        $oldValues = $item->exists ? ['name' => $item->name] : [];

        $item->fill([
            'name' => trim((string) $validated['name']),
            'slug' => $this->contentSanitizer->sanitizeNullableText($validated['slug'] ?? null),
            'logo_media_id' => $validated['logo_media_id'] ?? null,
            'website_url' => $this->contentSanitizer->sanitizeUrl($validated['website_url'] ?? null),
            'summary' => $this->contentSanitizer->sanitizeNullableText($validated['summary'] ?? null),
            'category' => $this->contentSanitizer->sanitizeNullableText($validated['category'] ?? null),
            'featured_flag' => (bool) ($validated['featured_flag'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'visibility' => $validated['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($validated['change_notes'] ?? null),
            'created_by' => $item->created_by ?? $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: $item->wasRecentlyCreated ? 'showcase.partner.created' : 'showcase.partner.updated',
            action: $item->wasRecentlyCreated ? 'create_partner' : 'update_partner',
            actor: $actor,
            auditable: $item,
            oldValues: $oldValues,
            newValues: ['name' => $item->name],
        );
    }
}

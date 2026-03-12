<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Models\User;
use App\Modules\Showcase\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestimonialController extends AbstractShowcaseController
{
    protected function modelClass(): string
    {
        return Testimonial::class;
    }

    protected function resourceKey(): string
    {
        return 'testimonial';
    }

    protected function resourceLabel(): string
    {
        return 'Testimonials';
    }

    protected function routeBase(): string
    {
        return 'admin.showcase.testimonials';
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('client_name', 'like', '%'.$term.'%')
            ->orWhere('company_name', 'like', '%'.$term.'%')
            ->orWhere('quote', 'like', '%'.$term.'%'));
    }

    protected function rules(Model $item): array
    {
        return array_merge($this->sharedRules(), [
            'client_name' => ['required', 'string', 'max:160'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'role_title' => ['nullable', 'string', 'max:160'],
            'quote' => ['required', 'string', 'max:5000'],
            'avatar_media_id' => ['nullable', 'exists:media_assets,id'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);
    }

    protected function persist(Model $item, User $actor, array $validated): void
    {
        $oldValues = $item->exists ? ['client_name' => $item->client_name] : [];

        $item->fill([
            'client_name' => trim((string) $validated['client_name']),
            'company_name' => $this->contentSanitizer->sanitizeNullableText($validated['company_name'] ?? null),
            'role_title' => $this->contentSanitizer->sanitizeNullableText($validated['role_title'] ?? null),
            'quote' => $this->contentSanitizer->sanitizeNullableText($validated['quote'] ?? null),
            'avatar_media_id' => $validated['avatar_media_id'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'featured_flag' => (bool) ($validated['featured_flag'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'visibility' => $validated['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($validated['change_notes'] ?? null),
            'created_by' => $item->created_by ?? $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: $item->wasRecentlyCreated ? 'showcase.testimonial.created' : 'showcase.testimonial.updated',
            action: $item->wasRecentlyCreated ? 'create_testimonial' : 'update_testimonial',
            actor: $actor,
            auditable: $item,
            oldValues: $oldValues,
            newValues: ['client_name' => $item->client_name],
        );
    }
}

<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Models\User;
use App\Modules\Showcase\Models\TeamMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TeamMemberController extends AbstractShowcaseController
{
    protected function modelClass(): string
    {
        return TeamMember::class;
    }

    protected function resourceKey(): string
    {
        return 'team_member';
    }

    protected function resourceLabel(): string
    {
        return 'Team Members';
    }

    protected function routeBase(): string
    {
        return 'admin.showcase.team';
    }

    protected function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('full_name', 'like', '%'.$term.'%')
            ->orWhere('role_title', 'like', '%'.$term.'%')
            ->orWhere('short_bio', 'like', '%'.$term.'%'));
    }

    protected function rules(Model $item): array
    {
        return array_merge($this->sharedRules(), [
            'full_name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('team_members', 'slug')->ignore($item->getKey())],
            'role_title' => ['required', 'string', 'max:160'],
            'short_bio' => ['nullable', 'string', 'max:2000'],
            'full_bio' => ['nullable', 'string', 'max:10000'],
            'photo_media_id' => ['nullable', 'exists:media_assets,id'],
            'public_email' => ['nullable', 'email:rfc', 'max:255'],
            'public_phone' => ['nullable', 'string', 'max:60'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'github_url' => ['nullable', 'url', 'max:2048'],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);
    }

    protected function persist(Model $item, User $actor, array $validated): void
    {
        $oldValues = $item->exists ? ['full_name' => $item->full_name] : [];

        $item->fill([
            'full_name' => trim((string) $validated['full_name']),
            'slug' => trim((string) $validated['slug']),
            'role_title' => trim((string) $validated['role_title']),
            'short_bio' => $this->contentSanitizer->sanitizeNullableText($validated['short_bio'] ?? null),
            'full_bio' => $this->contentSanitizer->sanitizeRichText($validated['full_bio'] ?? null),
            'photo_media_id' => $validated['photo_media_id'] ?? null,
            'public_email' => $this->contentSanitizer->sanitizeNullableText($validated['public_email'] ?? null),
            'public_phone' => $this->contentSanitizer->sanitizeNullableText($validated['public_phone'] ?? null),
            'linkedin_url' => $this->contentSanitizer->sanitizeUrl($validated['linkedin_url'] ?? null),
            'github_url' => $this->contentSanitizer->sanitizeUrl($validated['github_url'] ?? null),
            'website_url' => $this->contentSanitizer->sanitizeUrl($validated['website_url'] ?? null),
            'featured_flag' => (bool) ($validated['featured_flag'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'visibility' => $validated['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($validated['change_notes'] ?? null),
            'created_by' => $item->created_by ?? $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: $item->wasRecentlyCreated ? 'showcase.team_member.created' : 'showcase.team_member.updated',
            action: $item->wasRecentlyCreated ? 'create_team_member' : 'update_team_member',
            actor: $actor,
            auditable: $item,
            oldValues: $oldValues,
            newValues: ['full_name' => $item->full_name],
        );
    }
}

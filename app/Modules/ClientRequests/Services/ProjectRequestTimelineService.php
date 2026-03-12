<?php

namespace App\Modules\ClientRequests\Services;

use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Models\ProjectRequest;
use Illuminate\Support\Collection;

class ProjectRequestTimelineService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forRequester(ProjectRequest $projectRequest): Collection
    {
        $statusItems = $projectRequest->statusHistories
            ->where('visibility', ProjectRequestCommentVisibility::RequesterVisible->value)
            ->map(static function ($history): array {
                return [
                    'kind' => 'status',
                    'title' => 'Status updated',
                    'body' => $history->metadata['to_status_name'] ?? $history->to_state,
                    'note' => $history->reason,
                    'occurred_at' => $history->changed_at,
                ];
            });

        $commentItems = $projectRequest->comments
            ->filter(static fn ($comment): bool => $comment->visibility_type === ProjectRequestCommentVisibility::RequesterVisible)
            ->map(static function ($comment): array {
                return [
                    'kind' => 'comment',
                    'title' => 'Visible comment',
                    'body' => $comment->body,
                    'note' => $comment->author?->name,
                    'occurred_at' => $comment->created_at,
                ];
            });

        $eventItems = $projectRequest->events
            ->filter(static fn ($event): bool => $event->visibility_type === ProjectRequestCommentVisibility::RequesterVisible)
            ->map(static function ($event): array {
                return [
                    'kind' => 'event',
                    'title' => $event->title,
                    'body' => $event->body,
                    'note' => null,
                    'occurred_at' => $event->occurred_at,
                ];
            });

        return $statusItems
            ->concat($commentItems)
            ->concat($eventItems)
            ->sortByDesc('occurred_at')
            ->values();
    }
}

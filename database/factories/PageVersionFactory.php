<?php

namespace Database\Factories;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageVersion>
 */
class PageVersionFactory extends Factory
{
    protected $model = PageVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->slug();

        return [
            'page_id' => Page::factory(),
            'version_number' => 1,
            'title' => fake()->sentence(3),
            'slug' => $slug,
            'path' => '/'.$slug,
            'summary' => fake()->optional()->paragraph(),
            'workflow_state' => ContentWorkflowState::Draft,
            'approval_state' => ApprovalState::Draft,
            'change_notes' => null,
            'seo_snapshot_json' => [
                'meta_title' => fake()->sentence(4),
                'meta_description' => fake()->sentence(10),
            ],
            'layout_settings_json' => [
                'container_width' => 'contained',
            ],
        ];
    }

    public function inReview(): static
    {
        return $this->state(fn (): array => [
            'workflow_state' => ContentWorkflowState::InReview,
            'approval_state' => ApprovalState::PendingReview,
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'workflow_state' => ContentWorkflowState::Approved,
            'approval_state' => ApprovalState::Approved,
            'approved_at' => now(),
            'preview_confirmed_at' => now(),
        ]);
    }
}

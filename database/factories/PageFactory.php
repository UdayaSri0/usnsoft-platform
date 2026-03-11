<?php

namespace Database\Factories;

use App\Modules\Pages\Enums\PageType;
use App\Modules\Pages\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->words(2, true));

        return [
            'uuid' => (string) Str::uuid(),
            'key' => null,
            'page_type' => PageType::Custom,
            'title_current' => fake()->sentence(3),
            'slug_current' => $slug,
            'path_current' => '/'.$slug,
            'is_home' => false,
            'is_system_page' => false,
            'is_locked_slug' => false,
            'is_active' => true,
            'current_draft_version_id' => null,
            'current_published_version_id' => null,
        ];
    }
}

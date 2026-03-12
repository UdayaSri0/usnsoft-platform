<?php

namespace App\Modules\Blog\Requests;

use App\Enums\VisibilityState;
use App\Modules\Blog\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BlogPost::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'author_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9\-\/]+$/', Rule::unique('blog_posts', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'featured_image_media_id' => ['nullable', 'exists:media_assets,id'],
            'featured_flag' => ['nullable', 'boolean'],
            'visibility' => ['required', Rule::in(VisibilityState::values())],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'tag_ids' => ['nullable', 'array', 'max:20'],
            'tag_ids.*' => ['integer', 'exists:blog_tags,id'],
            'related_post_ids' => ['nullable', 'array', 'max:12'],
            'related_post_ids.*' => ['integer', 'exists:blog_posts,id'],
            'blocks' => ['nullable', 'array', 'max:60'],
            'blocks.*.block_type' => ['required_with:blocks', 'string', Rule::exists('block_definitions', 'key')->where('is_active', true)],
            'blocks.*.reusable_block_id' => ['nullable', 'integer', Rule::exists('reusable_blocks', 'id')],
            'blocks.*.region_key' => ['nullable', 'string', 'max:80'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'blocks.*.internal_name' => ['nullable', 'string', 'max:160'],
            'blocks.*.is_enabled' => ['nullable', 'boolean'],
            'blocks.*.visibility' => ['nullable', 'array'],
            'blocks.*.layout' => ['nullable', 'array'],
            'blocks.*.data' => ['nullable', 'array'],
            'blocks.*.data_json' => ['nullable', 'string'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_media_id' => ['nullable', 'exists:media_assets,id'],
            'seo.robots_index' => ['nullable', 'boolean'],
            'seo.robots_follow' => ['nullable', 'boolean'],
            'seo.schema_type' => ['nullable', 'string', 'max:80'],
        ];
    }
}

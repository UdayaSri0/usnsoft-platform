<?php

namespace App\Modules\Products\Requests;

use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductPlatform;
use App\Modules\Products\Enums\ProductPricingMode;
use App\Modules\Products\Enums\ProductVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('products', 'slug_current')],
            'product_kind' => ['required', Rule::in(ProductKind::values())],
            'short_description' => ['nullable', 'string', 'max:600'],
            'full_description' => ['nullable', 'string', 'max:5000'],
            'rich_body' => ['nullable', 'string', 'max:30000'],
            'featured_flag' => ['nullable', 'boolean'],
            'product_visibility' => ['required', Rule::in(ProductVisibility::values())],
            'download_visibility' => ['required', Rule::in(ProductDownloadVisibility::values())],
            'pricing_mode' => ['required', Rule::in(ProductPricingMode::values())],
            'pricing_text' => ['nullable', 'string', 'max:160'],
            'current_version' => ['nullable', 'string', 'max:120'],
            'release_notes' => ['nullable', 'string', 'max:30000'],
            'changelog' => ['nullable', 'string', 'max:30000'],
            'documentation_link' => ['nullable', 'url', 'max:2048'],
            'github_link' => ['nullable', 'url', 'max:2048'],
            'support_contact' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'featured_image_media_id' => ['nullable', 'exists:media_assets,id'],
            'release_notes_visible' => ['nullable', 'boolean'],
            'changelog_visible' => ['nullable', 'boolean'],
            'reviews_enabled' => ['nullable', 'boolean'],
            'review_requires_verification' => ['nullable', 'boolean'],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'tag_ids' => ['nullable', 'array', 'max:20'],
            'tag_ids.*' => ['integer', 'exists:product_tags,id'],
            'supported_platforms' => ['nullable', 'array', 'max:12'],
            'supported_platforms.*' => ['string', Rule::in(ProductPlatform::values())],
            'related_product_ids' => ['nullable', 'array', 'max:12'],
            'related_product_ids.*' => ['integer', 'exists:products,id'],
            'faq_items' => ['nullable', 'array', 'max:20'],
            'faq_items.*.question' => ['required_with:faq_items.*.answer', 'nullable', 'string', 'max:255'],
            'faq_items.*.answer' => ['required_with:faq_items.*.question', 'nullable', 'string', 'max:5000'],
            'faq_items.*.is_visible' => ['nullable', 'boolean'],
            'screenshots' => ['nullable', 'array', 'max:20'],
            'screenshots.*.media_asset_id' => ['required_with:screenshots', 'string', 'exists:media_assets,id'],
            'screenshots.*.caption' => ['nullable', 'string', 'max:255'],
            'downloads' => ['nullable', 'array', 'max:20'],
            'downloads.*.label' => ['required_with:downloads.*.download_mode', 'nullable', 'string', 'max:160'],
            'downloads.*.description' => ['nullable', 'string', 'max:1000'],
            'downloads.*.version_label' => ['nullable', 'string', 'max:120'],
            'downloads.*.download_mode' => ['nullable', Rule::in(ProductDownloadMode::values())],
            'downloads.*.visibility' => ['nullable', Rule::in(ProductDownloadVisibility::values())],
            'downloads.*.external_url' => ['nullable', 'url', 'max:2048'],
            'downloads.*.media_asset_id' => ['nullable', 'string', 'exists:media_assets,id'],
            'downloads.*.is_primary' => ['nullable', 'boolean'],
            'downloads.*.review_eligible' => ['nullable', 'boolean'],
            'downloads.*.notes' => ['nullable', 'string', 'max:1000'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_media_id' => ['nullable', 'string', 'exists:media_assets,id'],
            'seo.robots_index' => ['nullable', 'boolean'],
            'seo.robots_follow' => ['nullable', 'boolean'],
            'seo.schema_type' => ['nullable', 'string', 'max:80'],
        ];
    }
}

# CMS Block Schemas

## Source Of Truth
Block schema metadata lives in `config/cms.php` under `definitions`.

Each block definition includes:
- `name`, `category`, `description`
- `editor_mode` (`basic`, `advanced`, `superadmin_only`)
- `is_reusable_allowed`
- `rendering_view`
- `default_data`
- `default_layout`
- validation `rules`

Seeder `CmsBlockDefinitionSeeder` syncs these definitions into `block_definitions`.

## Implemented Block Types
- `hero`
- `slider`
- `cta`
- `rich_text`
- `image_gallery`
- `video_embed`
- `feature_grid`
- `product_grid`
- `testimonial_list`
- `partner_logos`
- `timeline`
- `team_cards`
- `faq_list`
- `stat_counters`
- `contact_section`
- `form_block`
- `file_download_block`
- `blog_teaser`
- `services_block`

## Safety Rules
- No arbitrary runtime PHP/Blade/JS execution
- No custom code block
- No raw iframe HTML paste
- Rich text allows only sanitized tags
- Link schemes are allowlisted (`http`, `https`, `mailto`, `tel`)
- Video URLs are validated and provider restricted
- Structured layout tokens are used instead of raw class injection

## Layout Token Strategy
Defined in `config/cms.php -> layout_tokens`:
- `container_width`: `contained`, `wide`, `full`
- `theme_variant`: `light`, `dark`, `brand`, `accent`, `neutral`
- `spacing`: `none`, `sm`, `md`, `lg`, `xl`
- `text_alignment`: `left`, `center`, `right`
- `card_style`: `flat`, `elevated`, `bordered`
- `media_position`: `left`, `right`, `top`, `background`
- `animation_style`: `none`, `fade`, `slide-up`, `scale-subtle`
- `background_type`: `none`, `solid`, `image`, `gradient`
- `overlay_intensity`: `none`, `subtle`, `medium`, `strong`

## Rendering Contract
`PageRenderService` passes each block to Blade with:
- `data` (validated payload)
- `layout` (safe layout tokens)
- `visibility` (device visibility flags)
- `meta` (definition/reusable references)

Fallback view:
- `resources/views/components/blocks/fallback.blade.php`

## Adding A New Block Safely
1. Add definition entry in `config/cms.php`.
2. Add validation rules and defaults.
3. Add Blade view renderer under `resources/views/components/blocks`.
4. Re-run block definition seed sync.
5. Add/update tests for validation + rendering + permissions.

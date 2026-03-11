# Pages Module

CMS domain for dynamic page management in the USNsoft monolith.

## Scope

- Page identity (`pages`)
- Versioned editorial workflow (`page_versions`)
- Structured safe block composition (`page_version_blocks`, `block_definitions`, `reusable_blocks`)
- Preview tokens (`preview_access_tokens`)
- Approval history (`approval_records` + existing `approval_requests`)
- Per-version SEO snapshots and reusable SEO meta relation

## Main Services

- `CmsWorkflowService`: draft/review/approval/schedule/publish/archive transitions
- `BlockValidationService`: schema-based validation and sanitization
- `BlockSanitizerService`: safe rich-text/link/video cleaning
- `PageRenderService`: published rendering payload + cache
- `PreviewTokenService`: signed-like preview token issuance and verification

## Security

- No arbitrary PHP/Blade/JS execution from CMS content
- Rich text is sanitized server-side
- Video embeds are provider allowlisted
- Block data is schema validated by block type
- Preview requires authorization or a time-limited token
- Publishing requires approval + preview confirmation

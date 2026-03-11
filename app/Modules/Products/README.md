# Products Module

Phase 1 product publishing platform for USNsoft.

This module now covers:
- public product discovery and filtering
- approval-aware product drafts and version publishing
- protected and external download delivery
- verified review eligibility and moderation
- preview links, scheduling, SEO metadata, and related product content

The current repository uses custom Blade/Tailwind admin workflows rather than Filament resources.
The domain/services layer is structured so a Filament admin surface can be added later without reworking the core product models, policies, or services.

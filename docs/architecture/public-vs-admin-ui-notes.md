# Public Vs Admin UI Notes

The public site and internal/admin UI should feel related, but they should not be forced into one identical shell.

## What Belongs To Public Site Styling

- Brand-forward hero sections
- Marketing/product/service presentation
- Public navigation and footer patterns
- Broader spacing and more editorial content flow
- Public CTA emphasis

## What Belongs To Admin Or Custom Authenticated Flows

- Faster, denser operational layouts
- Clear state chips, tables, alerts, and workflow controls
- Self-service account screens
- CMS editing surfaces and approval tools
- Internal helper screens such as operations placeholders and protected resource flows

## What Must Stay Consistent Across Both

- Color tokens and semantic state meaning
- Typography families and general hierarchy
- Button, badge, alert, and form control behavior
- Focus-visible treatment
- Border radius and shadow language
- Responsive spacing rhythm

## Practical Rule

Public UI can be more expressive and atmospheric. Admin/custom authenticated UI should be calmer and more task-focused. Both should still look like they belong to the same company and the same platform.

## For Future Filament Or Livewire Work

- Reuse the same tokens and interaction rules from `resources/css/app.css`
- Prefer maintainable theme alignment over one-off CSS overrides
- Do not let admin package defaults drift into a separate visual identity from the public site

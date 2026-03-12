# Design System Overview

The shared UI foundation lives primarily in `resources/css/app.css` and the reusable Blade components under `resources/views/components/`.

## Color Tokens

Core CSS variables:

- `--usn-bg`
- `--usn-surface`
- `--usn-surface-muted`
- `--usn-text`
- `--usn-muted`
- `--usn-border`
- `--usn-primary`
- `--usn-primary-strong`
- `--usn-accent`
- `--usn-success`
- `--usn-warning`
- `--usn-danger`
- `--usn-info`

Semantic intent:

- Primary actions: dark slate / brand-weighted buttons
- Supporting actions: bordered white secondary buttons
- Info: sky
- Success: emerald
- Warning: amber
- Danger: rose

## Theme Preference

- The UI theme is controlled per browser/device instance with the `usnsoft-theme` localStorage key
- Supported values: `light`, `dark`, `system`
- The root layout applies the `dark` class on `<html>` before paint so the chosen theme survives reloads without a flash
- This is a local UI preference only; it is not stored in the database and does not change the site for other users

## Typography

- `font-display`: `Sora`
- `font-sans`: `Manrope`
- Use `usn-display` for large public heroes
- Use `usn-title` or `usn-heading` for section/page titles
- Use `usn-subheading` or `usn-copy` for supporting copy

## Spacing

Containers:

- `usn-container`
- `usn-container-wide`
- `usn-container-narrow`
- `usn-container-fluid`

Sections:

- `usn-section-sm`
- `usn-section`
- `usn-section-lg`
- `usn-section-xl`

## Buttons

- `usn-btn-primary`
- `usn-btn-secondary`
- `usn-btn-ghost`
- `usn-btn-danger`

Rules:

- Primary is for the main action only
- Secondary is for navigation, cancel, and alternate actions
- Danger is only for destructive or high-risk actions

## Cards And Panels

- `usn-card` for normal elevated surfaces
- `usn-card-link` for clickable cards
- `usn-card-muted` for softer informational panels
- `usn-card-dark` for dark/inverted surfaces
- `usn-table-shell` for data wrappers

## Forms And Inputs

- `x-text-input`
- `x-select-input`
- `x-textarea-input`
- `usn-checkbox`
- `usn-file-input`

Rules:

- Keep field heights and radii consistent
- Use `x-input-error` directly below the relevant field
- Avoid one-off utility piles for `select` and `textarea`

## Tables And Lists

- Wrap tables in `usn-table-shell`
- Use `usn-table-scroll` for horizontal overflow on smaller screens
- Use `usn-badge-*` variants for status instead of ad hoc color chips

## Alerts And Empty States

- `x-ui.alert` for info/success/warning/danger messages
- `x-ui.empty-state` for no-data or placeholder sections

## Accessibility Rules

- Keep visible focus states; do not remove them
- Ensure mobile nav and menus expose toggled states
- Prefer readable line lengths and avoid overly dense paragraph widths
- Use semantic button types and links consistently
- Treat contrast as a baseline requirement, especially for badges and alerts

## Responsive Rules

- Public and internal tables must support horizontal overflow
- Navigation must remain usable on mobile
- Multi-column cards should collapse to single-column without clipping
- Button groups should wrap cleanly on small screens

## Do

- Reuse the shared components and CSS classes
- Preserve the corporate, polished, security-first tone
- Keep public and internal flows visually related through shared tokens

## Do Not

- Introduce new one-off component styles for a single page unless they become reusable
- Add unsafe raw executable admin content
- Fork the admin UI into a completely separate visual system from the public site

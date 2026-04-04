# acf/

Reusable ACF helper functions for Spektra WordPress integrations.

## Purpose

Shared utility functions that convert ACF field data to Spektra platform shapes. Used by all clients — client-specific field groups live in each client's `infra/acf/` overlay.

## Planned helpers

- `spektra_acf_image_to_media()` — ACF image (array return) → Spektra `Media` shape
- `spektra_acf_sizes_to_variants()` — ACF sizes → `MediaVariant[]`
- `spektra_acf_group_to_cta()` — ACF group field → `CallToAction` shape

## Rules

- NO client field group definitions here
- NO field registration (`acf_add_local_field_group`) — that belongs in client overlay
- Helper functions only — pure data transformation

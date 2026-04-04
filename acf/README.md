# acf/

Reusable ACF helper functions for Spektra WordPress integrations.

## Purpose

Shared utility functions that convert ACF field data to Spektra platform shapes. Used by all clients — client-specific field groups live in each client's `infra/acf/` overlay.

## Structure

```
acf/
└── helpers.php    # spektra_acf_image_to_media, spektra_acf_sizes_to_variants, spektra_acf_group_to_cta
```

## Helpers

| Function | Input | Output | Status |
|---|---|---|---|
| `spektra_acf_image_to_media(array $image)` | ACF image array | `Media` shape | scaffold — Phase 6.1 |
| `spektra_acf_sizes_to_variants(array $sizes)` | ACF sizes sub-array | `MediaVariant[]` | scaffold — Phase 6.1 |
| `spektra_acf_group_to_cta(array $group)` | ACF group field | `CallToAction` | scaffold — Phase 6.1 |

## Rules

- NO client field group definitions here
- NO field registration (`acf_add_local_field_group`) — that belongs in client overlay
- Helper functions only — pure data transformation

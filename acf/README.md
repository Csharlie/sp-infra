# acf/

Reusable ACF helper functions for Spektra WordPress integrations.

## Purpose

Shared utility functions that convert ACF field data to Spektra platform shapes. Used by all clients — client-specific field groups live in each client's `infra/acf/` overlay.

## Structure

```
acf/
├── helpers.php       # spektra_get_field, spektra_acf_image_to_media, spektra_acf_sizes_to_variants, spektra_acf_group_to_cta
└── media-helper.php  # spektra_normalize_media
```

## Helpers (helpers.php)

| Function | Input | Output |
|---|---|---|
| `spektra_get_field(string $selector, $post_id, $default)` | ACF field selector | Field value or `$default` (never `false`) |
| `spektra_acf_image_to_media(?array $image)` | ACF image array | `Media` shape or `null` |
| `spektra_acf_sizes_to_variants(array $sizes)` | ACF sizes sub-array | `MediaVariant[]` |
| `spektra_acf_group_to_cta(?array $group)` | ACF group field | `CallToAction` shape or `null` |

## Media Helper (media-helper.php)

| Function | Input | Output |
|---|---|---|
| `spektra_normalize_media($value)` | ACF image array, URL string, or empty | `Media` shape or `null` |

## Platform Shapes

```
Media:         { src, alt, width?, height?, variants?, mimeType? }
MediaVariant:  { name, source: { url, width?, height? } }
CallToAction:  { text, href? }
```

## Rules

- NO client field group definitions here
- NO field registration (`acf_add_local_field_group`) — that belongs in client overlay
- Helper functions only — pure data transformation

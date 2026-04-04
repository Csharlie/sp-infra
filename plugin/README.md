# plugin/

Reusable `spektra-api` WordPress REST plugin.

## Purpose

Generic WP REST endpoint that serves SiteData-compatible JSON. Client-specific logic loaded from external config (client infra overlay).

## Structure

```
plugin/
└── spektra-api/
    ├── spektra-api.php              # Plugin header, autoload, hooks, config loading
    └── includes/
        ├── class-rest-controller.php    # register_rest_route (spektra/v1/site)
        ├── class-response-builder.php   # SiteData JSON assembly (Phase 7)
        └── class-cors.php               # CORS + preflight (Phase 5.3)
```

## Rules

- NO client-specific code (no `benettcar`, no `bc-*`)
- Config loaded from external path (symlink or env var)
- Read-only API — no write endpoints

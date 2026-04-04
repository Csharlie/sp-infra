# seed/

Seed pipeline tools — populate WordPress from client static data.

## Purpose

Convert client `site.ts` static data to WP-CLI importable format, then import into WordPress.

## Pipeline

```
site.ts (client static data)
  → export-seed.ts (Node script)
    → seed.json (generated, gitignored)
      → WP-CLI import
        → WordPress DB (ACF field values)
```

## Planned files

- `export-seed.ts` — site.ts → seed.json converter
- `import-seed.sh` / `import-seed.ps1` — WP-CLI import script
- `package.json` — Seed tool dependencies

## Rules

- `seed.json` is generated and gitignored
- Import is idempotent (can run multiple times)

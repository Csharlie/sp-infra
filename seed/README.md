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

## Structure

```
seed/
├── export-seed.ts     # site.ts → seed.json converter (scaffold — Phase 10.4)
├── package.json       # @spektra/seed — tsx dependency
└── README.md
```

## Rules

- `seed.json` is generated and gitignored
- Import is idempotent (can run multiple times)

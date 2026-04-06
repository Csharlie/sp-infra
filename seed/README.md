# seed/

Seed pipeline tools — populate WordPress from client static data.

## Purpose

Convert client `site.ts` static data to WP-CLI importable format, then import into WordPress.

> **P8.5 boundary döntés**: az export és mapping a kliens repóban él
> (`<client>/infra/seed/`), nem itt. Az sp-infra/seed/ az import és
> verify toolingot tartalmazza.
> Lásd: [content-parity-bootstrap.md](../../sp-docs/content-parity-bootstrap.md) §3.2.

## Pipeline

```
site.ts (client static data)
  → export-seed.ts (kliens repo — <client>/infra/seed/)
    → seed.json (generated, gitignored)
      → import-seed.sh (sp-infra/seed/)
        → WordPress DB (ACF field values)
      → verify-parity.ts (sp-infra/seed/)
        → PASS / FAIL
```

## Structure

```
seed/
├── export-seed.ts     # DEPRECATED scaffold — törlendő P8.5.4-ben
│                      # Canonical hely: <client>/infra/seed/export-seed.ts
├── import-seed.sh     # (Phase 8.5.4) WP-CLI import script
├── verify-parity.ts   # (Phase 8.5.5) Parity check — site.ts vs WP API
├── package.json       # @spektra/seed — tsx dependency
└── README.md
```

## Rules

- `seed.json` is generated and gitignored
- Import is idempotent (can run multiple times)
- Export logic lives in client repo (client-specific mapping)

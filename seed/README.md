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
      → import-seed.php (sp-infra/seed/ — wp eval-file, ACF API)
        → WordPress DB (ACF field values)
      → verify-parity.ts (sp-infra/seed/)
        → PASS / FAIL
```

## Structure

```
seed/
├── import-seed.php    # ACF-aware importer (wp eval-file) — P8.5.4
├── dump-acf.php       # Dump current WP ACF state as JSON — P8.5.5
├── verify-parity.ts   # Parity check: seed.json vs wp-state.json — P8.5.5
├── package.json       # @spektra/seed — tsx dependency
└── README.md
```

## Verification workflow

```bash
# 1. Generate seed (in client repo)
pnpm seed:export

# 2. Import into WP
wp eval-file import-seed.php

# 3. Dump current WP state
wp eval-file dump-acf.php

# 4. Verify parity
npx tsx verify-parity.ts --verbose
```

## Rules

- `seed.json` is generated and gitignored
- Import is idempotent (can run multiple times)
- Export logic lives in client repo (client-specific mapping)

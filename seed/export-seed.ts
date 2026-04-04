#!/usr/bin/env node
/**
 * export-seed.ts — Convert client site.ts to seed.json for WP-CLI import.
 *
 * Pipeline:
 *   site.ts (SiteData) → seed.json (ACF field key/value pairs)
 *
 * Usage:
 *   npx tsx export-seed.ts --input <path-to-site.ts> --output seed.json
 *
 * Phase 10.4: real implementation.
 *
 * @package Spektra\Seed
 */

// Phase 10.4: implementation
// - Read SiteData from site.ts
// - Map each section to ACF field key/value pairs
// - Write seed.json (flat ACF format, WP-CLI compatible)

console.log('export-seed: not implemented yet — Phase 10.4');
process.exit(1);

#!/usr/bin/env node
/**
 * verify-parity.ts — Compare seed.json (expected) against WP ACF state (actual).
 *
 * Pipeline:
 *   seed.json (expected)  ──┐
 *                           ├── compare → PASS / FAIL
 *   wp-state.json (actual) ─┘
 *
 * wp-state.json is produced by dump-acf.php (wp eval-file).
 *
 * Usage:
 *   npx tsx verify-parity.ts [options]
 *
 * Options:
 *   --seed <path>       Path to seed.json   (default: ./seed.json)
 *   --state <path>      Path to wp-state.json (default: ./wp-state.json)
 *   --verbose           Print every field comparison
 *   --strict            Treat media URL mismatch as FAIL (default: structural match only)
 *
 * Parity rules (from content-parity-bootstrap.md §4.1):
 *   - Text fields: exact match (character-level)
 *   - CTA fields: exact match
 *   - Image fields: structural match (URL may differ, alt must match)
 *   - Navigation: out of scope (config-driven)
 *   - Section order: N/A for flat seed (sections are implicit via field prefixes)
 *
 * Exit code:
 *   0 = PASS (all fields match)
 *   1 = FAIL (mismatches found)
 *
 * Phase: P8.5.5
 *
 * @package Spektra\Seed
 */

import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

// ── CLI args ─────────────────────────────────────────────────────

const args = process.argv.slice(2)
const verbose = args.includes('--verbose')
const strict = args.includes('--strict')

function getArgValue(flag: string): string | undefined {
  const idx = args.indexOf(flag)
  return idx >= 0 ? args[idx + 1] : undefined
}

const seedPath = resolve(getArgValue('--seed') ?? './seed.json')
const statePath = resolve(getArgValue('--state') ?? './wp-state.json')

// ── Load files ───────────────────────────────────────────────────

interface SeedJson {
  post_id: string
  site_options: Record<string, string>
  fields: Record<string, unknown>
}

function loadJson(path: string, label: string): SeedJson {
  try {
    const raw = readFileSync(path, 'utf-8')
    return JSON.parse(raw) as SeedJson
  } catch (e) {
    const msg = e instanceof Error ? e.message : String(e)
    console.error(`[FATAL] Cannot load ${label}: ${path}\n  ${msg}`)
    process.exit(1)
  }
}

const seed = loadJson(seedPath, 'seed.json')
const state = loadJson(statePath, 'wp-state.json')

// ── Comparison engine ────────────────────────────────────────────

interface FieldResult {
  key: string
  status: 'match' | 'mismatch' | 'missing' | 'media-ok' | 'media-fail'
  expected?: unknown
  actual?: unknown
  detail?: string
}

const results: FieldResult[] = []

/**
 * Detect if a value looks like an image field: { url, alt } object.
 */
function isImageValue(v: unknown): v is { url: string; alt: string } {
  return (
    typeof v === 'object' &&
    v !== null &&
    'url' in v &&
    'alt' in v &&
    typeof (v as Record<string, unknown>).url === 'string'
  )
}

/**
 * Deep-compare two values. For image fields, apply media exception.
 */
function compareField(key: string, expected: unknown, actual: unknown): FieldResult {
  // Image field — media exception
  if (isImageValue(expected)) {
    if (!isImageValue(actual)) {
      return { key, status: 'media-fail', expected, actual, detail: 'actual is not image shape' }
    }
    const altMatch = expected.alt === actual.alt
    const urlMatch = expected.url === actual.url
    if (!altMatch) {
      return { key, status: 'media-fail', expected, actual, detail: `alt mismatch: "${expected.alt}" vs "${actual.alt}"` }
    }
    if (!urlMatch && strict) {
      return { key, status: 'media-fail', expected, actual, detail: `URL mismatch (strict): "${expected.url}" vs "${actual.url}"` }
    }
    return { key, status: 'media-ok', detail: urlMatch ? 'exact' : 'alt match, URL differs (OK)' }
  }

  // Repeater (array)
  if (Array.isArray(expected)) {
    if (!Array.isArray(actual)) {
      return { key, status: 'mismatch', expected: `[${expected.length} items]`, actual, detail: 'expected array, got non-array' }
    }
    if (expected.length !== actual.length) {
      return { key, status: 'mismatch', expected: `[${expected.length} items]`, actual: `[${actual.length} items]`, detail: 'row count mismatch' }
    }
    // Compare each row
    const rowMismatches: string[] = []
    for (let i = 0; i < expected.length; i++) {
      const expRow = expected[i] as Record<string, unknown>
      const actRow = actual[i] as Record<string, unknown>
      if (typeof expRow !== 'object' || typeof actRow !== 'object') {
        rowMismatches.push(`row[${i}]: type mismatch`)
        continue
      }
      for (const subKey of Object.keys(expRow)) {
        const subResult = compareField(`${key}[${i}].${subKey}`, expRow[subKey], actRow[subKey])
        if (subResult.status === 'mismatch' || subResult.status === 'missing' || subResult.status === 'media-fail') {
          rowMismatches.push(`row[${i}].${subKey}: ${subResult.detail ?? subResult.status}`)
        }
      }
    }
    if (rowMismatches.length > 0) {
      return { key, status: 'mismatch', detail: rowMismatches.join('; ') }
    }
    return { key, status: 'match', detail: `${expected.length} rows OK` }
  }

  // Group (object)
  if (typeof expected === 'object' && expected !== null) {
    if (typeof actual !== 'object' || actual === null) {
      return { key, status: 'mismatch', expected: '{...}', actual, detail: 'expected object, got non-object' }
    }
    const expObj = expected as Record<string, unknown>
    const actObj = actual as Record<string, unknown>
    const subMismatches: string[] = []
    for (const subKey of Object.keys(expObj)) {
      const subResult = compareField(`${key}.${subKey}`, expObj[subKey], actObj[subKey])
      if (subResult.status === 'mismatch' || subResult.status === 'missing' || subResult.status === 'media-fail') {
        subMismatches.push(`${subKey}: ${subResult.detail ?? subResult.status}`)
      }
    }
    if (subMismatches.length > 0) {
      return { key, status: 'mismatch', detail: subMismatches.join('; ') }
    }
    return { key, status: 'match', detail: `group OK` }
  }

  // Scalar — exact match (normalize to string for WP meta comparison)
  const expStr = String(expected)
  const actStr = String(actual)
  if (expStr === actStr) {
    return { key, status: 'match' }
  }

  // Bare image reference: seed stores a URL/path string, dump stores a WP media URL.
  // Apply media exception: both non-empty means the image was sideloaded OK.
  if (looksLikeImageRef(expStr) && actStr !== '' && actStr !== 'undefined' && actStr !== 'null') {
    if (strict && expStr !== actStr) {
      return { key, status: 'media-fail', expected, actual, detail: `URL mismatch (strict)` }
    }
    return { key, status: 'media-ok', detail: 'image ref, URL differs (OK)' }
  }

  return { key, status: 'mismatch', expected, actual, detail: `"${expStr}" vs "${actStr}"` }
}

/**
 * Detect whether a string looks like an image reference (URL or local path).
 */
function looksLikeImageRef(s: string): boolean {
  if (/\.(jpe?g|png|webp|gif|svg|avif)(\?.*)?$/i.test(s)) return true
  if (s.includes('images.unsplash.com')) return true
  return false
}

// ── Compare site_options ─────────────────────────────────────────

console.log('=== Site Options ===\n')

for (const [key, expected] of Object.entries(seed.site_options)) {
  const actual = state.site_options?.[key]
  if (actual === undefined) {
    results.push({ key: `option:${key}`, status: 'missing', expected, detail: 'not found in WP state' })
  } else {
    results.push(compareField(`option:${key}`, expected, actual))
  }
}

// ── Compare fields ───────────────────────────────────────────────

console.log('=== ACF Fields ===\n')

for (const [key, expected] of Object.entries(seed.fields)) {
  const actual = state.fields?.[key]
  if (actual === undefined) {
    results.push({ key, status: 'missing', expected, detail: 'not found in WP state' })
  } else {
    results.push(compareField(key, expected, actual))
  }
}

// Check for extra fields in WP state not in seed
for (const key of Object.keys(state.fields ?? {})) {
  if (!(key in seed.fields)) {
    if (verbose) {
      console.log(`  [EXTRA] ${key} — in WP but not in seed (ignored)`)
    }
  }
}

// ── Report ───────────────────────────────────────────────────────

console.log('')

const matches = results.filter((r) => r.status === 'match' || r.status === 'media-ok')
const mismatches = results.filter((r) => r.status === 'mismatch' || r.status === 'media-fail')
const missing = results.filter((r) => r.status === 'missing')

// Print all results if verbose, otherwise just failures
for (const r of results) {
  if (verbose || r.status === 'mismatch' || r.status === 'missing' || r.status === 'media-fail') {
    const icon =
      r.status === 'match' ? '✓' :
      r.status === 'media-ok' ? '≈' :
      r.status === 'missing' ? '?' :
      '✗'
    const detail = r.detail ? ` — ${r.detail}` : ''
    console.log(`  ${icon} ${r.key}${detail}`)
  }
}

// Summary
console.log('\n' + '─'.repeat(60))
console.log(`Total: ${results.length} fields`)
console.log(`  ✓ Match:    ${matches.length}`)
console.log(`  ✗ Mismatch: ${mismatches.length}`)
console.log(`  ? Missing:  ${missing.length}`)

const pass = mismatches.length === 0 && missing.length === 0

console.log(`\n${'═'.repeat(60)}`)
console.log(`  PARITY CHECK: ${pass ? 'PASS ✓' : 'FAIL ✗'}`)
console.log(`${'═'.repeat(60)}\n`)

process.exit(pass ? 0 : 1)

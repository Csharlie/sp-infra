<?php
/**
 * import-seed.php — ACF-aware seed importer.
 *
 * Reads seed.json and uses ACF's update_field() API to write values.
 * This ensures repeater, group, and image fields are handled correctly
 * by ACF's internal storage mechanism (row counts, sub-field prefixes).
 *
 * Usage:
 *   wp eval-file import-seed.php [seed.json path] [--dry-run] [--verbose]
 *
 * Must be run in a WordPress context (WP-CLI).
 * ACF must be active.
 *
 * Supported field kinds (from seed.json):
 *   - scalar: simple text/number/boolean → update_field(key, value, post_id)
 *   - repeater: array of row objects → update_field(key, rows, post_id)
 *   - group: flat object → update_field(key, object, post_id)
 *   - image: {url, alt} → stored as URL string via update_field()
 *
 * This importer is GENERIC — it does not know about bc-* field names.
 * All client-specific knowledge lives in the seed.json (produced by the
 * client's export-seed.ts + mapping.ts).
 *
 * Phase: P8.5.4
 *
 * @package Spektra\Seed
 */

defined( 'ABSPATH' ) || exit;

// ── CLI args ─────────────────────────────────────────────────────

$seed_path = $args[0] ?? __DIR__ . '/seed.json';
$dry_run   = in_array( '--dry-run', $args ?? [], true );
$verbose   = in_array( '--verbose', $args ?? [], true );

// ── Validate environment ─────────────────────────────────────────

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'ACF is not active. Cannot import seed without Advanced Custom Fields.' );
}

// ── Read seed ────────────────────────────────────────────────────

if ( ! file_exists( $seed_path ) ) {
	WP_CLI::error( "Seed file not found: {$seed_path}" );
}

$raw = file_get_contents( $seed_path );
if ( $raw === false ) {
	WP_CLI::error( "Failed to read seed file: {$seed_path}" );
}

$seed = json_decode( $raw, true );
if ( ! is_array( $seed ) ) {
	WP_CLI::error( 'Invalid seed.json — expected JSON object.' );
}

// ── Resolve post ID ──────────────────────────────────────────────

$post_id_raw = $seed['post_id'] ?? null;

if ( $post_id_raw === 'front_page' ) {
	$post_id = (int) get_option( 'page_on_front', 0 );
	if ( $post_id === 0 ) {
		WP_CLI::error( 'No front page set. Configure Settings → Reading → Static Front Page first.' );
	}
	WP_CLI::log( "Resolved front_page → post ID {$post_id}" );
} elseif ( is_numeric( $post_id_raw ) ) {
	$post_id = (int) $post_id_raw;
} else {
	WP_CLI::error( "Invalid post_id in seed.json: {$post_id_raw}" );
}

// ── Import site options ──────────────────────────────────────────

$site_options = $seed['site_options'] ?? [];
$options_count = 0;

foreach ( $site_options as $key => $value ) {
	if ( ! is_string( $key ) || $key === '' ) {
		continue;
	}

	if ( $dry_run ) {
		WP_CLI::log( "[DRY-RUN] wp option update {$key} = " . summarize( $value ) );
	} else {
		update_option( $key, $value );
		if ( $verbose ) {
			WP_CLI::log( "[OPTION] {$key} = " . summarize( $value ) );
		}
	}
	$options_count++;
}

// ── Import ACF fields ────────────────────────────────────────────

$fields = $seed['fields'] ?? [];
$field_count = 0;
$error_count = 0;

foreach ( $fields as $key => $value ) {
	if ( ! is_string( $key ) || $key === '' ) {
		continue;
	}

	$kind = detect_kind( $value );

	if ( $dry_run ) {
		WP_CLI::log( "[DRY-RUN] [{$kind}] update_field( '{$key}', " . summarize( $value ) . ", {$post_id} )" );
		$field_count++;
		continue;
	}

	// Image fields: extract URL, store as string.
	// ACF image field with return_format=array will get a URL string,
	// which spektra_normalize_media() can handle.
	$acf_value = $value;
	if ( $kind === 'image' && is_array( $value ) && isset( $value['url'] ) ) {
		$acf_value = $value['url'];
	}

	$result = update_field( $key, $acf_value, $post_id );

	if ( $result === false ) {
		WP_CLI::warning( "[FAIL] {$key} — update_field returned false" );
		$error_count++;
	} else {
		if ( $verbose ) {
			WP_CLI::log( "[OK] [{$kind}] {$key} = " . summarize( $acf_value ) );
		}
		$field_count++;
	}
}

// ── Summary ──────────────────────────────────────────────────────

$mode = $dry_run ? 'DRY-RUN' : 'IMPORTED';
WP_CLI::success( "{$mode}: {$options_count} options, {$field_count} fields" . ( $error_count > 0 ? ", {$error_count} errors" : '' ) );

// ── Helpers ──────────────────────────────────────────────────────

/**
 * Detect field kind from the seed value shape.
 */
function detect_kind( $value ): string {
	if ( ! is_array( $value ) ) {
		return 'scalar';
	}

	// Image: { url, alt }
	if ( isset( $value['url'] ) && isset( $value['alt'] ) && count( $value ) <= 3 ) {
		return 'image';
	}

	// Repeater: sequential array of objects
	if ( array_is_list( $value ) ) {
		return 'repeater';
	}

	// Group: associative array
	return 'group';
}

/**
 * Create a human-readable summary of a value for logging.
 */
function summarize( $value ): string {
	if ( is_string( $value ) ) {
		return strlen( $value ) > 60 ? '"' . substr( $value, 0, 57 ) . '..."' : '"' . $value . '"';
	}
	if ( is_numeric( $value ) ) {
		return (string) $value;
	}
	if ( is_bool( $value ) ) {
		return $value ? 'true' : 'false';
	}
	if ( is_array( $value ) ) {
		if ( array_is_list( $value ) ) {
			return '[' . count( $value ) . ' items]';
		}
		return '{' . implode( ', ', array_keys( $value ) ) . '}';
	}
	return gettype( $value );
}

<?php
/**
 * dump-acf.php — Dump current WP ACF field values as JSON.
 *
 * Reads the same field keys found in seed.json and outputs
 * the current WordPress values in the same structure.
 * This produces wp-state.json for verify-parity.ts.
 *
 * Usage:
 *   wp eval-file dump-acf.php [seed.json path] [--output <path>]
 *
 * Must be run in a WordPress context (WP-CLI).
 * ACF must be active.
 *
 * Phase: P8.5.5
 *
 * @package Spektra\Seed
 */

defined( 'ABSPATH' ) || exit;

// ── CLI args ─────────────────────────────────────────────────────

$seed_path  = $args[0] ?? __DIR__ . '/seed.json';
// Accept both --output and output (WP-CLI intercepts -- prefixed flags)
$output_idx = array_search( '--output', $args ?? [], true );
if ( $output_idx === false ) {
	$output_idx = array_search( 'output', $args ?? [], true );
}
$output_path = ( $output_idx !== false && isset( $args[ $output_idx + 1 ] ) )
	? $args[ $output_idx + 1 ]
	: __DIR__ . '/wp-state.json';

// ── Validate environment ─────────────────────────────────────────

if ( ! function_exists( 'get_field' ) ) {
	WP_CLI::error( 'ACF is not active. Cannot dump fields without Advanced Custom Fields.' );
}

// ── Read seed (to know which keys to dump) ───────────────────────

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

// ── Dump site options ────────────────────────────────────────────

$state = [
	'post_id'      => $seed['post_id'],
	'site_options' => [],
	'fields'       => [],
];

foreach ( array_keys( $seed['site_options'] ?? [] ) as $key ) {
	$state['site_options'][ $key ] = get_option( $key, '' );
}

// ── Dump ACF fields ──────────────────────────────────────────────

// Pre-scan: identify image fields in seed (value is {url, alt} object).
// These are stored as plain URL strings in WP, with a companion _alt field.
// We reconstruct the {url, alt} shape so verify-parity.ts can compare them.
$image_keys = [];
foreach ( $seed['fields'] ?? [] as $key => $seed_value ) {
	if ( is_array( $seed_value ) && isset( $seed_value['url'] ) && isset( $seed_value['alt'] ) ) {
		$image_keys[ $key ] = true;
	}
}

foreach ( array_keys( $seed['fields'] ?? [] ) as $key ) {
	$value = get_field( $key, $post_id, false );

	// get_field with format=false returns raw DB values.
	// For repeater fields, ACF stores them differently — use formatted value.
	if ( $value === null || $value === false ) {
		$value = get_field( $key, $post_id );
	}

	// Reconstruct {url, alt} shape for image fields.
	// import-seed.php stores image as URL string; the alt lives in a separate _alt field.
	if ( isset( $image_keys[ $key ] ) && is_string( $value ) ) {
		$alt_key   = $key . '_alt';
		$alt_value = get_field( $alt_key, $post_id, false );
		if ( $alt_value === null || $alt_value === false ) {
			$alt_value = get_field( $alt_key, $post_id ) ?? '';
		}
		$value = [ 'url' => $value, 'alt' => (string) $alt_value ];
	}

	$state['fields'][ $key ] = $value;
}

// ── Write output ─────────────────────────────────────────────────

$json = json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

if ( file_put_contents( $output_path, $json ) === false ) {
	WP_CLI::error( "Failed to write: {$output_path}" );
}

$field_count = count( $state['fields'] );
$option_count = count( $state['site_options'] );
WP_CLI::success( "Dumped {$field_count} fields + {$option_count} options → {$output_path}" );

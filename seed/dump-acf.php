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
$output_idx = array_search( '--output', $args ?? [], true );
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

foreach ( array_keys( $seed['fields'] ?? [] ) as $key ) {
	$value = get_field( $key, $post_id, false );

	// get_field with format=false returns raw DB values.
	// For repeater fields, ACF stores them differently — use formatted value.
	if ( $value === null || $value === false ) {
		$value = get_field( $key, $post_id );
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

<?php
/**
 * verify-endpoint.php — Endpoint-level smoke test.
 *
 * Queries /spektra/v1/site internally (via rest_do_request) and checks
 * that all image fields resolved to valid URLs, not integers or nulls.
 *
 * This catches the gap between storage parity (seed.json ↔ wp-state.json)
 * and the live response that create-adapter.ts / React actually consumes.
 *
 * Usage:
 *   wp eval-file verify-endpoint.php [--verbose]
 *
 * Exit codes:
 *   0 = PASS (all image fields are URLs)
 *   1 = FAIL (at least one broken image field)
 *
 * Phase: P9.2
 *
 * @package Spektra\Seed
 */

defined( 'ABSPATH' ) || exit;

$verbose = in_array( 'verbose', $args ?? [], true ) || in_array( '--verbose', $args ?? [], true );

// ── Query endpoint ───────────────────────────────────────────────

$request  = new WP_REST_Request( 'GET', '/spektra/v1/site' );
$response = rest_do_request( $request );

if ( $response->is_error() ) {
	WP_CLI::error( '/spektra/v1/site returned error: ' . $response->as_error()->get_error_message() );
}

$data     = $response->get_data();
$sections = $data['pages'][0]['sections'] ?? [];

if ( empty( $sections ) ) {
	WP_CLI::error( 'No sections found in endpoint response.' );
}

// ── Check image fields ───────────────────────────────────────────

$pass  = 0;
$fail  = 0;
$checks = [];

/**
 * Assert that a value is a valid URL string.
 * Returns true if valid, false if broken (integer, null, empty, non-URL).
 */
function is_valid_image_url( $value ): bool {
	if ( ! is_string( $value ) || $value === '' ) {
		return false;
	}
	// Must start with http:// or https:// (WP media URLs).
	return (bool) preg_match( '#^https?://#i', $value );
}

/**
 * Assert that a value is a valid Media shape (object with src URL).
 */
function is_valid_media_shape( $value ): bool {
	if ( ! is_array( $value ) ) {
		return false;
	}
	return is_valid_image_url( $value['src'] ?? null );
}

/**
 * Record a check result.
 */
function check( string $path, bool $ok, $actual, array &$checks, int &$pass, int &$fail ): void {
	$checks[] = [ 'path' => $path, 'ok' => $ok, 'actual' => $actual ];
	if ( $ok ) {
		$pass++;
	} else {
		$fail++;
	}
}

foreach ( $sections as $sec ) {
	$id    = $sec['id'] ?? '?';
	$sdata = $sec['data'] ?? [];

	// Data-driven: walk all fields and check anything that looks like an image.
	// Handles: Media shapes ({src: "..."}) and plain URL strings in known image keys.
	$image_keys = [ 'image', 'backgroundImage', 'logo', 'src', 'photo', 'avatar', 'cover' ];

	// Top-level image fields
	foreach ( $sdata as $key => $value ) {
		if ( in_array( $key, $image_keys, true ) ) {
			if ( is_array( $value ) && isset( $value['src'] ) ) {
				check( "{$id}.{$key}", is_valid_media_shape( $value ), $value, $checks, $pass, $fail );
			} elseif ( is_string( $value ) || $value === null ) {
				check( "{$id}.{$key}", is_valid_image_url( $value ), $value, $checks, $pass, $fail );
			}
		}

		// Array of items (brands, images, members, etc.) — check nested image fields
		if ( is_array( $value ) && ! isset( $value['src'] ) ) {
			foreach ( $value as $i => $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				foreach ( $item as $k => $v ) {
					if ( ! in_array( $k, $image_keys, true ) ) {
						continue;
					}
					if ( is_array( $v ) && isset( $v['src'] ) ) {
						check( "{$id}.{$key}[{$i}].{$k}", is_valid_media_shape( $v ), $v, $checks, $pass, $fail );
					} elseif ( is_string( $v ) || $v === null ) {
						check( "{$id}.{$key}[{$i}].{$k}", is_valid_image_url( $v ), $v, $checks, $pass, $fail );
					}
				}
			}
		}
	}
}

// ── Output ───────────────────────────────────────────────────────

foreach ( $checks as $c ) {
	$icon = $c['ok'] ? '✔' : '✖';
	$color = $c['ok'] ? '' : ' ← BROKEN';
	$detail = '';
	if ( ! $c['ok'] || $verbose ) {
		if ( is_array( $c['actual'] ) ) {
			$detail = ' → ' . ( $c['actual']['src'] ?? json_encode( $c['actual'] ) );
		} elseif ( $c['actual'] === null ) {
			$detail = ' → null';
		} else {
			$detail = ' → ' . var_export( $c['actual'], true );
		}
	}
	WP_CLI::log( "  [{$icon}] {$c['path']}{$detail}{$color}" );
}

WP_CLI::log( '' );
WP_CLI::log( "Endpoint smoke: {$pass} OK, {$fail} BROKEN" );

if ( $fail > 0 ) {
	WP_CLI::error( "Endpoint smoke FAILED — {$fail} image field(s) not resolved to URLs." );
} else {
	WP_CLI::success( "Endpoint smoke PASS — all {$pass} image fields are valid URLs." );
}

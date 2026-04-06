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
	$id   = $sec['id'] ?? '?';
	$sdata = $sec['data'] ?? [];

	switch ( $id ) {
		case 'bc-hero':
			$bg = $sdata['backgroundImage'] ?? null;
			check( "bc-hero.backgroundImage", is_valid_media_shape( $bg ), $bg, $checks, $pass, $fail );
			break;

		case 'bc-brand':
			foreach ( $sdata['brands'] ?? [] as $i => $brand ) {
				$logo = $brand['logo'] ?? null;
				check( "bc-brand.brands[{$i}].logo", is_valid_image_url( $logo ), $logo, $checks, $pass, $fail );
			}
			break;

		case 'bc-gallery':
			foreach ( $sdata['images'] ?? [] as $i => $img ) {
				$src = $img['src'] ?? null;
				check( "bc-gallery.images[{$i}].src", is_valid_image_url( $src ), $src, $checks, $pass, $fail );
			}
			break;

		case 'bc-team':
			foreach ( $sdata['members'] ?? [] as $i => $member ) {
				$image = $member['image'] ?? null;
				check( "bc-team.members[{$i}].image", is_valid_media_shape( $image ), $image, $checks, $pass, $fail );
			}
			break;

		case 'bc-about':
			$img = $sdata['image'] ?? null;
			check( "bc-about.image", is_valid_media_shape( $img ), $img, $checks, $pass, $fail );
			break;
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

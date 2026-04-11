<?php
/**
 * Spektra ACF Section Data — Generic registry & dispatch.
 *
 * Provides a section builder registry that client overlays populate.
 * The shared infra never contains client-specific builder functions.
 *
 * Builder contract (enforced by convention, not interface):
 * - Required field missing → return null (section skipped by caller)
 * - Optional field missing → key present with null/empty/default value
 * - Image fields normalized via spektra_normalize_media() → Media | null
 * - Output keys are camelCase (matches platform TypeScript contracts)
 *
 * Depends on: helpers.php (spektra_get_field), media-helper.php (spektra_normalize_media).
 *
 * Phase history:
 *   P7.3: initial implementation — 10 bc-* section builders (hardcoded).
 *   P7.4: media normalization — ACF image → canonical Media shape.
 *   P7.4.1: bc-brand.logo rolled back to URL string (frontend contract).
 *   P11.2: section builder delegation — generic registry, builders moved to client overlay.
 *
 * @package Spektra\ACF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Section builder registry.
 *
 * Keys: section slug (e.g. 'bc-hero').
 * Values: callable( string $prefix, int $post_id ): ?array
 *
 * @var array<string, callable>
 */
$GLOBALS['spektra_section_builders'] ??= [];

/**
 * Register a section builder callback.
 *
 * Client overlays call this to register their section builders.
 * Each builder receives the ACF field prefix and post ID, and returns
 * a data array (or null if required fields are missing).
 *
 * @param string   $type     Section slug (e.g. 'bc-hero').
 * @param callable $callback Builder function: fn(string $prefix, int $post_id): ?array
 */
function spektra_register_section_builder( string $type, callable $callback ): void {
	$GLOBALS['spektra_section_builders'][ $type ] = $callback;
}

/**
 * Get section data for a given section type.
 *
 * The prefix (e.g. 'bc_hero_') is derived from the slug by replacing
 * dashes with underscores and appending '_'. This matches the ACF field
 * naming convention used in client overlay acf/sections/ definitions.
 *
 * @param string $type    Section slug (e.g. 'bc-hero').
 * @param int    $post_id WordPress post ID.
 * @return array|null Section data array, or null if no builder registered / required fields missing.
 */
function spektra_get_section_data( string $type, int $post_id ): ?array {
	$builders = $GLOBALS['spektra_section_builders'];

	if ( ! isset( $builders[ $type ] ) ) {
		return null;
	}

	$prefix = str_replace( '-', '_', $type ) . '_';

	return $builders[ $type ]( $prefix, $post_id );
}

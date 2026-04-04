<?php
/**
 * Spektra ACF Helpers — Reusable ACF field data converters.
 *
 * Converts ACF field output arrays to Spektra platform shapes
 * (Media, MediaVariant[], CallToAction). Used by all clients.
 *
 * Rules:
 * - NO client-specific logic (no bc-*, no benettcar)
 * - NO field group registration here — that belongs in client overlay
 * - Pure data transformation functions only
 *
 * @package Spektra\ACF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert an ACF image field (array return format) to a Spektra Media shape.
 *
 * @param array|null $image ACF image array (url, alt, width, height, sizes, mime_type).
 * @return array|null Spektra Media shape, or null if image is empty.
 *
 * Phase 6.1: real implementation.
 * Phase 7.4: integrated into response builder for full Media normalization.
 */
function spektra_acf_image_to_media( ?array $image ): ?array {
	if ( empty( $image['url'] ) ) {
		return null;
	}

	// Phase 6.1: full implementation.
	return [
		'src'      => $image['url'],
		'alt'      => $image['alt'] ?? '',
		'width'    => $image['width'] ?? null,
		'height'   => $image['height'] ?? null,
		'variants' => spektra_acf_sizes_to_variants( $image['sizes'] ?? [] ),
		'mimeType' => $image['mime_type'] ?? null,
	];
}

/**
 * Convert ACF image sizes array to Spektra MediaVariant[] shape.
 *
 * @param array $sizes ACF sizes sub-array (from image field).
 * @return array MediaVariant[] — each: { url, width, height }.
 *
 * Phase 6.1: real implementation.
 */
function spektra_acf_sizes_to_variants( array $sizes ): array {
	// Phase 6.1: iterate named size keys (thumbnail, medium, large, etc.)
	// and return [ [ 'url' => ..., 'width' => ..., 'height' => ... ], ... ]
	return [];
}

/**
 * Convert an ACF group field to a Spektra CallToAction shape.
 *
 * Expected group subfield names: label, url, target.
 *
 * @param array|null $group ACF group field value.
 * @return array|null Spektra CallToAction shape, or null if group is empty.
 *
 * Phase 6.1: real implementation.
 */
function spektra_acf_group_to_cta( ?array $group ): ?array {
	if ( empty( $group['label'] ) && empty( $group['url'] ) ) {
		return null;
	}

	// Phase 6.1: full implementation.
	return [
		'label'  => $group['label'] ?? '',
		'url'    => $group['url'] ?? '',
		'target' => $group['target'] ?? '_self',
	];
}

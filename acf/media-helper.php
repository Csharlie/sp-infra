<?php
/**
 * Spektra Media Helper — Universal media normalizer.
 *
 * Converts various ACF media field outputs to the Spektra Media shape.
 * Handles: ACF image array, plain URL string, null/empty.
 *
 * Platform contract (Media):
 *   { src: string, alt: string, width?: number, height?: number, variants?: MediaVariant[], mimeType?: string }
 *
 * @package Spektra\ACF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalize any media value to a Spektra Media shape.
 *
 * Accepts:
 *   - ACF image array (url, alt, width, height, sizes, mime_type) → full Media shape
 *   - Plain URL string → minimal Media shape (src only)
 *   - null, false, empty string, empty array → null
 *
 * @param mixed $value ACF field value (image array, URL string, or empty).
 * @return array|null Spektra Media shape, or null if empty.
 */
function spektra_normalize_media( $value ): ?array {
	if ( $value === null || $value === false || $value === '' ) {
		return null;
	}

	// ACF image array (return format = array).
	if ( is_array( $value ) ) {
		if ( empty( $value['url'] ) ) {
			return null;
		}
		return spektra_acf_image_to_media( $value );
	}

	// Plain URL string (return format = url, or manual input).
	if ( is_string( $value ) && $value !== '' ) {
		return [
			'src'      => $value,
			'alt'      => '',
			'width'    => null,
			'height'   => null,
			'variants' => [],
			'mimeType' => null,
		];
	}

	return null;
}

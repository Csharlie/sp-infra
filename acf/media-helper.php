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
 *   - Plain URL string → minimal Media shape (src + alt override if provided)
 *   - null, false, empty string, empty array → null
 *
 * @param mixed  $value    ACF field value (image array, URL string, or empty).
 * @param string $alt_override Optional alt text override — used when alt is stored in a separate field (P8.5.4).
 * @return array|null Spektra Media shape, or null if empty.
 */
function spektra_normalize_media( $value, string $alt_override = '' ): ?array {
	if ( $value === null || $value === false || $value === '' ) {
		return null;
	}

	// ACF image array (return format = array).
	if ( is_array( $value ) ) {
		if ( empty( $value['url'] ) ) {
			return null;
		}
		$media = spektra_acf_image_to_media( $value );
		if ( $alt_override !== '' ) {
			$media['alt'] = $alt_override;
		}
		return $media;
	}

	// Plain URL string (return format = url, or manual input).
	if ( is_string( $value ) && $value !== '' ) {
		return [
			'src'      => $value,
			'alt'      => $alt_override,
			'width'    => null,
			'height'   => null,
			'variants' => [],
			'mimeType' => null,
		];
	}

	return null;
}

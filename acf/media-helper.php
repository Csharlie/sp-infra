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

	// Attachment ID (integer or numeric string) — resolve from WP media library.
	// This happens when ACF stores the sideloaded attachment ID and get_field()
	// doesn't format it back to the full image array (common in repeater sub-fields).
	if ( is_numeric( $value ) && (int) $value > 0 ) {
		$att_id   = (int) $value;
		$img_data = wp_get_attachment_image_src( $att_id, 'full' );
		if ( ! $img_data ) {
			return null;
		}
		return [
			'src'      => $img_data[0],
			'alt'      => $alt_override ?: ( get_post_meta( $att_id, '_wp_attachment_image_alt', true ) ?: '' ),
			'width'    => $img_data[1],
			'height'   => $img_data[2],
			'variants' => [],
			'mimeType' => get_post_mime_type( $att_id ) ?: null,
		];
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

/**
 * Resolve any ACF image value to a plain URL string.
 *
 * Accepts: ACF image array, attachment ID (int/numeric string), URL string.
 * Returns: URL string, or empty string if unresolvable.
 *
 * Use this for fields where the frontend expects a plain URL (not a Media shape),
 * e.g. bc-brand logos, bc-gallery image src.
 */
function spektra_resolve_image_url( $value ): string {
	if ( $value === null || $value === false || $value === '' ) {
		return '';
	}
	if ( is_array( $value ) ) {
		return (string) ( $value['url'] ?? '' );
	}
	if ( is_numeric( $value ) && (int) $value > 0 ) {
		return wp_get_attachment_url( (int) $value ) ?: '';
	}
	if ( is_string( $value ) ) {
		return $value;
	}
	return '';
}

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
 * Safe ACF field getter with default value support.
 *
 * Wraps get_field() so callers get $default instead of false/null on missing fields.
 *
 * @param string     $selector Field name or key.
 * @param int|false  $post_id  Post ID, or false for current post.
 * @param mixed      $default  Value to return when field is empty.
 * @return mixed Field value, or $default if empty.
 */
function spektra_get_field( string $selector, $post_id = false, $default = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $selector, $post_id );

	if ( $value === null || $value === false || $value === '' ) {
		return $default;
	}

	return $value;
}

/**
 * Convert an ACF image field (array return format) to a Spektra Media shape.
 *
 * @param array|null $image ACF image array (url, alt, width, height, sizes, mime_type).
 * @return array|null Spektra Media shape, or null if image is empty.
 */
function spektra_acf_image_to_media( ?array $image ): ?array {
	if ( empty( $image['url'] ) ) {
		return null;
	}

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
 * ACF sizes array structure:
 *   'thumbnail'        => 'https://...thumbnail.jpg',
 *   'thumbnail-width'  => 150,
 *   'thumbnail-height' => 150,
 *   'medium'           => 'https://...medium.jpg',
 *   'medium-width'     => 300,
 *   'medium-height'    => 200,
 *   ...
 *
 * Platform contract (MediaVariant):
 *   { name: string, source: { url: string, width?: number, height?: number } }
 *
 * @param array $sizes ACF sizes sub-array (from image field).
 * @return array MediaVariant[]
 */
function spektra_acf_sizes_to_variants( array $sizes ): array {
	$variants = [];
	$seen     = [];

	foreach ( $sizes as $key => $value ) {
		// Skip width/height keys — we only process the URL keys.
		if ( str_ends_with( $key, '-width' ) || str_ends_with( $key, '-height' ) ) {
			continue;
		}

		// $key is the size name (e.g. 'thumbnail', 'medium', 'large')
		// $value is the URL string.
		if ( ! is_string( $value ) || $value === '' ) {
			continue;
		}

		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;

		$variants[] = [
			'name'   => $key,
			'source' => [
				'url'    => $value,
				'width'  => $sizes[ $key . '-width' ] ?? null,
				'height' => $sizes[ $key . '-height' ] ?? null,
			],
		];
	}

	return $variants;
}

/**
 * Convert an ACF group field to a Spektra CallToAction shape.
 *
 * Platform contract (CallToAction):
 *   { text: string, href?: string }
 *
 * ACF subfield mapping: label → text, url → href.
 *
 * @param array|null $group ACF group field value.
 * @return array|null Spektra CallToAction shape, or null if group is empty.
 */
function spektra_acf_group_to_cta( ?array $group ): ?array {
	if ( empty( $group['label'] ) && empty( $group['url'] ) ) {
		return null;
	}

	return [
		'text' => $group['label'] ?? '',
		'href' => $group['url'] ?? '',
	];
}

<?php
/**
 * Spektra ACF Section Data Builders — Benettcar (bc-*) sections.
 *
 * Dispatches section slug to per-section builder function.
 * Each builder reads ACF fields from the given post and returns a data array.
 *
 * Rules:
 * - Required field missing → return null (section skipped by caller)
 * - Optional field missing → key present with null/empty/default value
 * - Image fields normalized via spektra_normalize_media() → Media | null
 *   (exceptions: bc-gallery images[].src and bc-brand brands[].logo stay
 *   as URL strings — frontend schemas expect string, P8 mapper handles it)
 * - Output keys are camelCase (matches platform TypeScript contracts)
 * - bc-* specific — acknowledged as P11.3 technical debt
 *
 * Depends on: helpers.php (spektra_get_field), media-helper.php (spektra_normalize_media).
 *
 * Phase history:
 *   P7.3: initial implementation — 10 bc-* section builders.
 *   P7.4: media normalization — ACF image → canonical Media shape.
 *   P7.4.1: bc-brand.logo rolled back to URL string (frontend contract).
 *
 * @package Spektra\ACF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get section data for a given section type.
 *
 * The prefix (e.g. 'bc_hero_') is derived from the slug by replacing
 * dashes with underscores and appending '_'. This matches the ACF field
 * naming convention used in sp-benettcar/infra/acf/sections/.
 *
 * @param string $type    Section slug (e.g. 'bc-hero').
 * @param int    $post_id WordPress post ID.
 * @return array|null Section data array, or null if unknown / required fields missing.
 */
function spektra_get_section_data( string $type, int $post_id ): ?array {
	$prefix = str_replace( '-', '_', $type ) . '_';

	return match ( $type ) {
		'bc-hero'       => spektra_build_bc_hero( $prefix, $post_id ),
		'bc-brand'      => spektra_build_bc_brand( $prefix, $post_id ),
		'bc-gallery'    => spektra_build_bc_gallery( $prefix, $post_id ),
		'bc-services'   => spektra_build_bc_services( $prefix, $post_id ),
		'bc-service'    => spektra_build_bc_service( $prefix, $post_id ),
		'bc-about'      => spektra_build_bc_about( $prefix, $post_id ),
		'bc-team'       => spektra_build_bc_team( $prefix, $post_id ),
		'bc-assistance' => spektra_build_bc_assistance( $prefix, $post_id ),
		'bc-contact'    => spektra_build_bc_contact( $prefix, $post_id ),
		'bc-map'        => spektra_build_bc_map( $prefix, $post_id ),
		default         => null,
	};
}

// ── bc-hero ──────────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_hero_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_hero( string $p, int $pid ): ?array {
	$title = spektra_get_field( $p . 'title', $pid );
	$desc  = spektra_get_field( $p . 'description', $pid );

	if ( $title === null || $desc === null ) {
		return null;
	}

	$data = [
		'title'           => $title,
		'subtitle'        => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'description'     => $desc,
		'backgroundImage' => spektra_normalize_media( spektra_get_field( $p . 'background_image', $pid ) ),
	];

	$pct = spektra_get_field( $p . 'primary_cta_text', $pid );
	$pch = spektra_get_field( $p . 'primary_cta_href', $pid );
	if ( $pct !== null || $pch !== null ) {
		$data['primaryCTA'] = [
			'text' => $pct ?? '',
			'href' => $pch ?? '',
		];
	}

	$sct = spektra_get_field( $p . 'secondary_cta_text', $pid );
	$sch = spektra_get_field( $p . 'secondary_cta_href', $pid );
	if ( $sct !== null || $sch !== null ) {
		$data['secondaryCTA'] = [
			'text' => $sct ?? '',
			'href' => $sch ?? '',
		];
	}

	return $data;
}

// ── bc-brand ─────────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_brand_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_brand( string $p, int $pid ): ?array {
	$brands = spektra_get_field( $p . 'brands', $pid );

	if ( empty( $brands ) || ! is_array( $brands ) ) {
		return null;
	}

	return [
		'title'       => spektra_get_field( $p . 'title', $pid, '' ),
		'description' => spektra_get_field( $p . 'description', $pid, '' ),
		'brands'      => array_map( function ( array $row ): array {
			$logo = $row['logo'] ?? null;
			return [
				'name'   => $row['name'] ?? '',
				'logo'   => is_array( $logo ) ? ( $logo['url'] ?? '' ) : ( $logo ?? '' ),
				'alt'    => $row['alt'] ?? '',
				'invert' => (bool) ( $row['invert'] ?? false ),
			];
		}, $brands ),
	];
}

// ── bc-gallery ───────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_gallery_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_gallery( string $p, int $pid ): ?array {
	$title  = spektra_get_field( $p . 'title', $pid );
	$images = spektra_get_field( $p . 'images', $pid );

	if ( $title === null || empty( $images ) || ! is_array( $images ) ) {
		return null;
	}

	return [
		'title'          => $title,
		'subtitle'       => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'showCategories' => (bool) spektra_get_field( $p . 'show_categories', $pid, false ),
		'images'         => array_map( function ( array $row ): array {
			return [
				'src'      => $row['src'] ?? null,
				'alt'      => $row['alt'] ?? '',
				'category' => $row['category'] ?? '',
				'caption'  => $row['caption'] ?? '',
			];
		}, $images ),
	];
}

// ── bc-services ──────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_services_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_services( string $p, int $pid ): ?array {
	$title    = spektra_get_field( $p . 'title', $pid );
	$services = spektra_get_field( $p . 'services', $pid );

	if ( $title === null || empty( $services ) || ! is_array( $services ) ) {
		return null;
	}

	return [
		'title'    => $title,
		'subtitle' => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'services' => array_map( function ( array $row ): array {
			return [
				'title'       => $row['title'] ?? '',
				'icon'        => $row['icon'] ?? '',
				'description' => $row['description'] ?? '',
			];
		}, $services ),
	];
}

// ── bc-service ───────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_service_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_service( string $p, int $pid ): ?array {
	$title    = spektra_get_field( $p . 'title', $pid );
	$desc     = spektra_get_field( $p . 'description', $pid );
	$services = spektra_get_field( $p . 'services', $pid );
	$brands   = spektra_get_field( $p . 'brands', $pid );

	if ( $title === null || $desc === null ) {
		return null;
	}
	if ( empty( $services ) || ! is_array( $services ) ) {
		return null;
	}
	if ( empty( $brands ) || ! is_array( $brands ) ) {
		return null;
	}

	$data = [
		'title'       => $title,
		'subtitle'    => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'description' => $desc,
		'services'    => array_map( function ( array $row ): array {
			return [ 'label' => $row['label'] ?? '' ];
		}, $services ),
		'brands'      => array_map( function ( array $row ): string {
			return $row['name'] ?? '';
		}, $brands ),
	];

	$contact = spektra_get_field( $p . 'contact', $pid );
	if ( is_array( $contact ) ) {
		$c = [
			'title'        => $contact['title'] ?? '',
			'description'  => $contact['description'] ?? '',
			'phone'        => $contact['phone'] ?? '',
			'bookingNote'  => $contact['booking_note'] ?? '',
			'hours'        => $contact['hours'] ?? '',
			'weekendHours' => $contact['weekend_hours'] ?? '',
		];

		$mct = $contact['message_cta_text'] ?? null;
		$mch = $contact['message_cta_href'] ?? null;
		if ( $mct !== null || $mch !== null ) {
			$c['messageCta'] = [
				'text' => $mct ?? '',
				'href' => $mch ?? '',
			];
		}

		$data['contact'] = $c;
	}

	return $data;
}

// ── bc-about ─────────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_about_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_about( string $p, int $pid ): ?array {
	$title   = spektra_get_field( $p . 'title', $pid );
	$content = spektra_get_field( $p . 'content', $pid );

	if ( $title === null || empty( $content ) || ! is_array( $content ) ) {
		return null;
	}

	$data = [
		'title'         => $title,
		'subtitle'      => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'content'       => array_map( function ( array $row ): string {
			return $row['paragraph'] ?? '';
		}, $content ),
		'image'         => spektra_normalize_media( spektra_get_field( $p . 'image', $pid ) ),
		'imagePosition' => spektra_get_field( $p . 'image_position', $pid, 'right' ),
		'colorScheme'   => spektra_get_field( $p . 'color_scheme', $pid, 'light' ),
	];

	$stats = spektra_get_field( $p . 'stats', $pid );
	$data['stats'] = is_array( $stats ) ? array_map( function ( array $row ): array {
		return [
			'value' => $row['value'] ?? '',
			'label' => $row['label'] ?? '',
		];
	}, $stats ) : [];

	$cta_text = spektra_get_field( $p . 'cta_text', $pid );
	$cta_href = spektra_get_field( $p . 'cta_href', $pid );
	if ( $cta_text !== null || $cta_href !== null ) {
		$data['cta'] = [
			'text' => $cta_text ?? '',
			'href' => $cta_href ?? '',
		];
	}

	return $data;
}

// ── bc-team ──────────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_team_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_team( string $p, int $pid ): ?array {
	$title   = spektra_get_field( $p . 'title', $pid );
	$members = spektra_get_field( $p . 'members', $pid );

	if ( $title === null || empty( $members ) || ! is_array( $members ) ) {
		return null;
	}

	return [
		'title'       => $title,
		'subtitle'    => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'description' => spektra_get_field( $p . 'description', $pid, '' ),
		'members'     => array_map( function ( array $row ): array {
			return [
				'name'  => $row['name'] ?? '',
				'role'  => $row['role'] ?? '',
				'image' => spektra_normalize_media( $row['image'] ?? null ),
				'phone' => $row['phone'] ?? '',
				'email' => $row['email'] ?? '',
			];
		}, $members ),
	];
}

// ── bc-assistance ────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_assistance_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_assistance( string $p, int $pid ): ?array {
	$title = spektra_get_field( $p . 'title', $pid );

	if ( $title === null ) {
		return null;
	}

	$data = [
		'title'       => $title,
		'subtitle'    => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'description' => spektra_get_field( $p . 'description', $pid, '' ),
		'serviceArea' => spektra_get_field( $p . 'service_area', $pid, '' ),
	];

	$data['requestLabel'] = spektra_get_field( $p . 'request_label', $pid, '' );
	$data['requestHref']  = spektra_get_field( $p . 'request_href', $pid, '' );

	return $data;
}

// ── bc-contact ───────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_contact_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_contact( string $p, int $pid ): ?array {
	$title = spektra_get_field( $p . 'title', $pid );

	if ( $title === null ) {
		return null;
	}

	$data = [
		'title'       => $title,
		'subtitle'    => spektra_get_field( $p . 'subtitle', $pid, '' ),
		'description' => spektra_get_field( $p . 'description', $pid, '' ),
		'colorScheme' => spektra_get_field( $p . 'color_scheme', $pid, 'light' ),
	];

	$info = spektra_get_field( $p . 'info', $pid );
	if ( is_array( $info ) ) {
		$data['contactInfo'] = [
			'phone'   => $info['phone'] ?? '',
			'email'   => $info['email'] ?? '',
			'address' => $info['address'] ?? '',
		];
	}

	return $data;
}

// ── bc-map ───────────────────────────────────────────────────────

/**
 * @param string $p   ACF field prefix (bc_map_).
 * @param int    $pid Post ID.
 */
function spektra_build_bc_map( string $p, int $pid ): ?array {
	$query = spektra_get_field( $p . 'query', $pid );

	if ( $query === null ) {
		return null;
	}

	return [
		'title'  => spektra_get_field( $p . 'title', $pid, '' ),
		'query'  => $query,
		'height' => (int) spektra_get_field( $p . 'height', $pid, 400 ),
	];
}

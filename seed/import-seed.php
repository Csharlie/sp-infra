<?php
/**
 * import-seed.php — ACF-aware seed importer.
 *
 * Reads seed.json and uses ACF's update_field() API to write values.
 * This ensures repeater, group, and image fields are handled correctly
 * by ACF's internal storage mechanism (row counts, sub-field prefixes).
 *
 * Usage:
 *   wp eval-file import-seed.php [seed.json path] [--dry-run] [--verbose]
 *
 * Must be run in a WordPress context (WP-CLI).
 * ACF must be active.
 *
 * Supported field kinds (from seed.json):
 *   - scalar: simple text/number/boolean → update_field(key, value, post_id)
 *   - repeater: array of row objects → update_field(key, rows, post_id)
 *   - group: flat object → update_field(key, object, post_id)
 *   - image: {url, alt} → sideloaded to WP media library, stored as attachment ID
 *
 * Image sideloading:
 *   External URLs (https://...) are downloaded into the media library.
 *   Local project paths (src/assets/...) are uploaded from the client repo
 *   (requires --client-dir / client-dir argument).
 *   Idempotent: re-running won't create duplicate attachments (keyed by source URL/path).
 *
 * This importer is GENERIC — it does not know about bc-* field names.
 * All client-specific knowledge lives in the seed.json (produced by the
 * client's export-seed.ts + mapping.ts).
 *
 * Phase: P8.5.4 → P9.1 (image sideload)
 *
 * @package Spektra\Seed
 */

defined( 'ABSPATH' ) || exit;

// ── CLI args ─────────────────────────────────────────────────────

$seed_path = $args[0] ?? __DIR__ . '/seed.json';
// Accept both --dry-run and dry-run (WP-CLI intercepts -- prefixed flags)
$dry_run   = in_array( '--dry-run', $args ?? [], true ) || in_array( 'dry-run', $args ?? [], true );
$verbose   = in_array( '--verbose', $args ?? [], true ) || in_array( 'verbose', $args ?? [], true );

// Client dir — needed to resolve local asset paths (e.g. src/assets/brands/...)
$client_dir = null;
foreach ( $args ?? [] as $i => $arg ) {
	if ( ( $arg === '--client-dir' || $arg === 'client-dir' ) && isset( $args[ $i + 1 ] ) ) {
		$client_dir = rtrim( $args[ $i + 1 ], '/\\' );
		break;
	}
}

// ── Validate environment ─────────────────────────────────────────

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'ACF is not active. Cannot import seed without Advanced Custom Fields.' );
}

// ── Read seed ────────────────────────────────────────────────────

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

// ── Import site options ──────────────────────────────────────────

$site_options = $seed['site_options'] ?? [];
$options_count = 0;

foreach ( $site_options as $key => $value ) {
	if ( ! is_string( $key ) || $key === '' ) {
		continue;
	}

	if ( $dry_run ) {
		WP_CLI::log( "[DRY-RUN] wp option update {$key} = " . summarize( $value ) );
	} else {
		update_option( $key, $value );
		if ( $verbose ) {
			WP_CLI::log( "[OPTION] {$key} = " . summarize( $value ) );
		}
	}
	$options_count++;
}

// ── Import ACF fields ────────────────────────────────────────────

// Ensure WP media functions are available for sideloading.
if ( ! $dry_run ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

$fields = $seed['fields'] ?? [];
$field_count = 0;
$error_count = 0;
$sideload_count = 0;

foreach ( $fields as $key => $value ) {
	if ( ! is_string( $key ) || $key === '' ) {
		continue;
	}

	$kind = detect_kind( $value );

	if ( $dry_run ) {
		WP_CLI::log( "[DRY-RUN] [{$kind}] update_field( '{$key}', " . summarize( $value ) . ", {$post_id} )" );
		$field_count++;
		continue;
	}

	// Prepare value: sideload images into WP media library.
	// This recursively walks repeater/group values to handle nested images.
	$acf_value = prepare_for_import( $value, $post_id, $client_dir, $verbose, $sideload_count );

	$result = update_field( $key, $acf_value, $post_id );

	if ( $result === false ) {
		WP_CLI::warning( "[FAIL] {$key} — update_field returned false" );
		$error_count++;
	} else {
		if ( $verbose ) {
			WP_CLI::log( "[OK] [{$kind}] {$key} = " . summarize( $acf_value ) );
		}
		$field_count++;
	}
}

// ── Summary ──────────────────────────────────────────────────────

$mode = $dry_run ? 'DRY-RUN' : 'IMPORTED';
$summary = "{$mode}: {$options_count} options, {$field_count} fields";
if ( $sideload_count > 0 ) {
	$summary .= ", {$sideload_count} images sideloaded";
}
if ( $error_count > 0 ) {
	$summary .= ", {$error_count} errors";
}
WP_CLI::success( $summary );

// ── Helpers ──────────────────────────────────────────────────────

/**
 * Prepare a seed value for ACF import.
 *
 * - Image {url, alt} → sideloaded to media library, returns attachment ID
 * - Bare image string (URL or local path) → sideloaded, returns attachment ID
 * - Repeater (list of rows) → recursively prepare each row
 * - Group (assoc array) → recursively prepare each value
 * - Scalar → returned as-is
 */
function prepare_for_import( $value, int $post_id, ?string $client_dir, bool $verbose, int &$sideload_count ) {
	$kind = detect_kind( $value );

	if ( $kind === 'image' ) {
		return sideload_image( $value, $post_id, $client_dir, $verbose, $sideload_count );
	}

	if ( $kind === 'repeater' ) {
		return array_map( function ( $row ) use ( $post_id, $client_dir, $verbose, &$sideload_count ) {
			if ( ! is_array( $row ) ) {
				return $row;
			}
			$prepared = [];
			foreach ( $row as $sub_key => $sub_value ) {
				$prepared[ $sub_key ] = prepare_for_import( $sub_value, $post_id, $client_dir, $verbose, $sideload_count );
			}
			return $prepared;
		}, $value );
	}

	if ( $kind === 'group' ) {
		$prepared = [];
		foreach ( $value as $sub_key => $sub_value ) {
			$prepared[ $sub_key ] = prepare_for_import( $sub_value, $post_id, $client_dir, $verbose, $sideload_count );
		}
		return $prepared;
	}

	// Bare image reference: a plain string that looks like an image URL or local path.
	// This handles repeater sub-fields (brand logos, team photos, gallery src)
	// where the seed stores a URL/path string but ACF expects an attachment ID.
	if ( is_string( $value ) && $value !== '' && looks_like_image_ref( $value ) ) {
		$image_value = [ 'url' => $value, 'alt' => '' ];
		return sideload_image( $image_value, $post_id, $client_dir, $verbose, $sideload_count );
	}

	return $value;
}

/**
 * Detect whether a plain string looks like an image reference
 * (external URL or local project path pointing to an image file).
 *
 * Used to catch image values in repeater sub-fields where the seed
 * stores a bare string instead of a {url, alt} object.
 */
function looks_like_image_ref( string $value ): bool {
	// Common image extensions (with optional query string for CDN URLs).
	if ( preg_match( '/\.(jpe?g|png|webp|gif|svg|avif)(\?.*)?$/i', $value ) ) {
		return true;
	}

	// Known image CDN services that serve images without file extensions.
	if ( str_contains( $value, 'images.unsplash.com' ) ) {
		return true;
	}

	return false;
}

/**
 * Sideload an image {url, alt} into the WP media library.
 *
 * Returns the attachment ID on success.
 * Idempotent: if the same source URL/path was already sideloaded, reuses the attachment.
 *
 * Handles:
 *   - External URLs (https://...) → download_url + media_handle_sideload
 *   - Local project paths (src/assets/...) → copy from client dir + wp_insert_attachment
 */
function sideload_image( array $image_value, int $post_id, ?string $client_dir, bool $verbose, int &$sideload_count ): int {
	$url = $image_value['url'] ?? '';
	$alt = $image_value['alt'] ?? '';

	if ( $url === '' ) {
		return 0;
	}

	// Idempotency: check if we already sideloaded this source.
	$existing = get_posts( [
		'post_type'   => 'attachment',
		'meta_key'    => '_spektra_source_url',
		'meta_value'  => $url,
		'numberposts' => 1,
		'fields'      => 'ids',
	] );

	if ( ! empty( $existing ) ) {
		if ( $verbose ) {
			WP_CLI::log( "  [CACHED] {$url} → attachment #{$existing[0]}" );
		}
		return $existing[0];
	}

	// External URL — download + sideload.
	if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
		$attachment_id = sideload_external_url( $url, $alt, $post_id );
	} else {
		// Local project path — upload from client dir.
		$attachment_id = sideload_local_file( $url, $alt, $post_id, $client_dir );
	}

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::warning( "  [SIDELOAD FAIL] {$url} — " . $attachment_id->get_error_message() );
		return 0;
	}

	// Tag the attachment with its source for idempotency.
	update_post_meta( $attachment_id, '_spektra_source_url', $url );

	// Set alt text on the attachment post meta.
	if ( $alt !== '' ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}

	$sideload_count++;
	if ( $verbose ) {
		WP_CLI::log( "  [SIDELOAD] {$url} → attachment #{$attachment_id}" );
	}

	return $attachment_id;
}

/**
 * Download an external URL into the WP media library.
 *
 * @return int|WP_Error Attachment ID or error.
 */
function sideload_external_url( string $url, string $alt, int $post_id ) {
	$tmp = download_url( $url );

	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	// Derive filename from URL path.
	$url_path = wp_parse_url( $url, PHP_URL_PATH );
	$filename = $url_path ? basename( $url_path ) : 'image';

	// Unsplash and similar URLs may lack a file extension.
	if ( ! pathinfo( $filename, PATHINFO_EXTENSION ) ) {
		$mime = mime_content_type( $tmp );
		$ext_map = [
			'image/jpeg' => '.jpg',
			'image/png'  => '.png',
			'image/webp' => '.webp',
			'image/gif'  => '.gif',
		];
		$filename .= $ext_map[ $mime ] ?? '.jpg';
	}

	$file_array = [
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp,
	];

	$attachment_id = media_handle_sideload( $file_array, $post_id, $alt );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
	}

	return $attachment_id;
}

/**
 * Upload a local file from the client project into the WP media library.
 *
 * @param string      $relative_path  Path relative to client dir (e.g. "src/assets/brands/vw-logo.jpg").
 * @param string      $alt            Alt text for the image.
 * @param int         $post_id        Parent post ID.
 * @param string|null $client_dir     Absolute path to the client project root.
 * @return int|WP_Error Attachment ID or error.
 */
function sideload_local_file( string $relative_path, string $alt, int $post_id, ?string $client_dir ) {
	if ( $client_dir === null ) {
		return new \WP_Error( 'no_client_dir', "Cannot resolve local path '{$relative_path}' — no --client-dir provided" );
	}

	$full_path = $client_dir . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path );

	if ( ! file_exists( $full_path ) ) {
		return new \WP_Error( 'file_not_found', "Local file not found: {$full_path}" );
	}

	// Copy to temp location (media_handle_sideload needs a temp file).
	$tmp = wp_tempnam( basename( $full_path ) );
	copy( $full_path, $tmp );

	$file_array = [
		'name'     => sanitize_file_name( basename( $full_path ) ),
		'tmp_name' => $tmp,
	];

	$attachment_id = media_handle_sideload( $file_array, $post_id, $alt );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
	}

	return $attachment_id;
}

/**
 * Detect field kind from the seed value shape.
 */
function detect_kind( $value ): string {
	if ( ! is_array( $value ) ) {
		return 'scalar';
	}

	// Image: { url, alt }
	if ( isset( $value['url'] ) && isset( $value['alt'] ) && count( $value ) <= 3 ) {
		return 'image';
	}

	// Repeater: sequential array of objects
	if ( array_is_list( $value ) ) {
		return 'repeater';
	}

	// Group: associative array
	return 'group';
}

/**
 * Create a human-readable summary of a value for logging.
 */
function summarize( $value ): string {
	if ( is_string( $value ) ) {
		return strlen( $value ) > 60 ? '"' . substr( $value, 0, 57 ) . '..."' : '"' . $value . '"';
	}
	if ( is_numeric( $value ) ) {
		return (string) $value;
	}
	if ( is_bool( $value ) ) {
		return $value ? 'true' : 'false';
	}
	if ( is_array( $value ) ) {
		if ( array_is_list( $value ) ) {
			return '[' . count( $value ) . ' items]';
		}
		return '{' . implode( ', ', array_keys( $value ) ) . '}';
	}
	return gettype( $value );
}

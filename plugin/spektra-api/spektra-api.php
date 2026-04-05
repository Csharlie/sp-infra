<?php
/**
 * Plugin Name: Spektra API
 * Description: Generic WP REST endpoint that serves SiteData-compatible JSON.
 * Version:     0.1.0
 * Author:      Spektra
 * Text Domain: spektra-api
 * Requires PHP: 8.0
 *
 * @package Spektra\API
 */

defined( 'ABSPATH' ) || exit;

// === Constants ===

define( 'SPEKTRA_API_VERSION', '0.1.0' );
define( 'SPEKTRA_API_PATH', plugin_dir_path( __FILE__ ) );
define( 'SPEKTRA_API_URL', plugin_dir_url( __FILE__ ) );

// === Autoload includes ===

require_once SPEKTRA_API_PATH . 'includes/class-cors.php';
require_once SPEKTRA_API_PATH . 'includes/class-rest-controller.php';
require_once SPEKTRA_API_PATH . 'includes/class-response-builder.php';

// === Client config loading ===
// Strategy B: ENV var with symlink fallback.
// The client config.php is loaded from an external path — never hardcoded.
// Real config comes from sp-benettcar/infra/config.php (symlinked at runtime).
//
// NOTE: We use WP_PLUGIN_DIR instead of __DIR__ because __DIR__ resolves through
// Junctions to the real source path (e.g. sp-infra/plugin/spektra-api/), while the
// spektra-config Junction lives as a sibling in wp-content/plugins/.

$spektra_config_path = getenv( 'SPEKTRA_CLIENT_CONFIG' ) ?: WP_PLUGIN_DIR . '/spektra-config/config.php';
$spektra_config      = [];

if ( file_exists( $spektra_config_path ) ) {
	$spektra_config = require $spektra_config_path;
	if ( ! is_array( $spektra_config ) ) {
		$spektra_config = [];
	}
}

define( 'SPEKTRA_CLIENT_CONFIG', $spektra_config );

// === ACF field group loading ===
// Load client ACF field groups from overlay (if present).
// The field-groups.php file registers its own acf/init hook internally,
// so a plain require_once is correct here — no timing issue.
$spektra_acf_path = dirname( $spektra_config_path ) . '/acf/field-groups.php';
if ( file_exists( $spektra_acf_path ) ) {
	require_once $spektra_acf_path;
}

// === Hook registration ===

add_action( 'rest_api_init', [ Spektra\API\Rest_Controller::class, 'register_routes' ] );
add_action( 'rest_api_init', [ Spektra\API\CORS::class, 'register_hooks' ] );

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

// === Autoload includes ===

require_once SPEKTRA_API_PATH . 'includes/class-cors.php';
require_once SPEKTRA_API_PATH . 'includes/class-rest-controller.php';
require_once SPEKTRA_API_PATH . 'includes/class-response-builder.php';

// === Client config loading ===
// Strategy B: ENV var with symlink fallback.
// The client config.php is loaded from an external path — never hardcoded.
// Real config comes from sp-benettcar/infra/config.php (symlinked at runtime).

$spektra_config_path = getenv( 'SPEKTRA_CLIENT_CONFIG' ) ?: __DIR__ . '/../spektra-config/config.php';

if ( file_exists( $spektra_config_path ) ) {
	require_once $spektra_config_path;
}

// === Hook registration ===
// REST route + CORS hooks registered on rest_api_init.
// Implementation: Phase 5.

add_action( 'rest_api_init', [ Spektra\API\Rest_Controller::class, 'register_routes' ] );
add_action( 'rest_api_init', [ Spektra\API\CORS::class, 'register_hooks' ] );

<?php
/**
 * CORS handler — adds cross-origin headers and handles preflight.
 *
 * @package Spektra\API
 */

namespace Spektra\API;

defined( 'ABSPATH' ) || exit;

class CORS {

	/**
	 * Register CORS-related hooks.
	 * Called via rest_api_init hook.
	 */
	public static function register_hooks(): void {
		// Phase 5.3: real CORS implementation.
		// - Allow-Origin from client config (allowed_origins)
		// - OPTIONS preflight → 204
		// - Allow-Headers: Authorization, Content-Type
		add_filter( 'rest_pre_serve_request', [ self::class, 'add_cors_headers' ], 10, 4 );
	}

	/**
	 * Add CORS headers to REST responses.
	 *
	 * @param bool              $served
	 * @param \WP_REST_Response $result
	 * @param \WP_REST_Request  $request
	 * @param \WP_REST_Server   $server
	 * @return bool
	 */
	public static function add_cors_headers( bool $served, \WP_REST_Response $result, \WP_REST_Request $request, \WP_REST_Server $server ): bool {
		// Phase 5.3: real implementation.
		// Allowed origins will come from client config (SPEKTRA_CORS_ORIGINS).
		return $served;
	}
}

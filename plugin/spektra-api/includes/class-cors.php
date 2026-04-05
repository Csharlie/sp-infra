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
		add_filter( 'rest_pre_serve_request', [ self::class, 'add_cors_headers' ], 100, 4 );
	}

	/**
	 * Add CORS headers to REST responses.
	 *
	 * Only applies to the spektra/v1 namespace.
	 * Allowed origins come from SPEKTRA_CLIENT_CONFIG['allowed_origins'].
	 *
	 * @param bool              $served
	 * @param \WP_REST_Response $result
	 * @param \WP_REST_Request  $request
	 * @param \WP_REST_Server   $server
	 * @return bool
	 */
	public static function add_cors_headers( bool $served, \WP_REST_Response $result, \WP_REST_Request $request, \WP_REST_Server $server ): bool {
		// Only handle spektra routes.
		$route = $request->get_route();
		if ( strpos( $route, '/spektra/' ) !== 0 ) {
			return $served;
		}

		$origin = $request->get_header( 'origin' );
		if ( ! $origin ) {
			return $served;
		}

		$allowed = defined( 'SPEKTRA_CLIENT_CONFIG' ) && isset( SPEKTRA_CLIENT_CONFIG['allowed_origins'] )
			? SPEKTRA_CLIENT_CONFIG['allowed_origins']
			: [];

		if ( ! in_array( $origin, $allowed, true ) ) {
			// Override WP default CORS — remove Allow-Origin for disallowed origins.
			header_remove( 'Access-Control-Allow-Origin' );
			header_remove( 'Access-Control-Allow-Methods' );
			header_remove( 'Access-Control-Allow-Credentials' );
			header( 'Vary: Origin' );
			return $served;
		}

		// Origin is allowed — set CORS headers.
		header( 'Access-Control-Allow-Origin: ' . $origin );
		header( 'Vary: Origin' );

		// Handle preflight.
		if ( $request->get_method() === 'OPTIONS' ) {
			header( 'Access-Control-Allow-Methods: GET, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Content-Type, Authorization' );
			header( 'Access-Control-Max-Age: 86400' );
			status_header( 204 );
			$served = true;
		}

		return $served;
	}
}

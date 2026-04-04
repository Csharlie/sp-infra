<?php
/**
 * REST Controller — registers the /spektra/v1/site route.
 *
 * @package Spektra\API
 */

namespace Spektra\API;

defined( 'ABSPATH' ) || exit;

class Rest_Controller {

	const NAMESPACE = 'spektra/v1';
	const ROUTE     = '/site';

	/**
	 * Register REST routes.
	 * Called via rest_api_init hook.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'handle_request' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Handle GET /wp-json/spektra/v1/site
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public static function handle_request( \WP_REST_Request $request ): \WP_REST_Response {
		// Phase 5.2: real implementation.
		// Phase 7: response builder integration.
		$is_preview = $request->get_param( 'preview' ) === 'true';

		$builder  = new Response_Builder();
		$response = $builder->build( $is_preview );

		return new \WP_REST_Response( $response, 200 );
	}
}

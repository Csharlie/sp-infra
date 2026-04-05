<?php
/**
 * REST Controller — registers the /spektra/v1/site route.
 *
 * @package Spektra\API
 */

namespace Spektra\API;

defined( 'ABSPATH' ) || exit;

class Rest_Controller {

	const API_NAMESPACE = 'spektra/v1';
	const ROUTE         = '/site';

	/**
	 * Register REST routes.
	 * Called via rest_api_init hook.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::API_NAMESPACE,
			self::ROUTE,
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'handle_request' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'preview' => [
						'type'              => 'string',
						'required'          => false,
						'validate_callback' => [ self::class, 'validate_preview_param' ],
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Validate the preview parameter.
	 *
	 * Accepted: missing (not sent) or 'true'.
	 * Rejected: anything else.
	 *
	 * @param string $value
	 * @return true|\WP_Error
	 */
	public static function validate_preview_param( string $value ): true|\WP_Error {
		if ( $value === 'true' ) {
			return true;
		}

		return new \WP_Error(
			'invalid_preview',
			'The preview parameter only accepts the value "true".',
			[ 'status' => 400 ]
		);
	}

	/**
	 * Handle GET /wp-json/spektra/v1/site
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public static function handle_request( \WP_REST_Request $request ): \WP_REST_Response {
		$is_preview = $request->get_param( 'preview' ) === 'true';

		$builder  = new Response_Builder();
		$data     = $builder->build( $is_preview );
		$response = new \WP_REST_Response( $data, 200 );

		$response->header( 'X-Spektra-Version', SPEKTRA_API_VERSION );

		if ( $is_preview ) {
			$response->header( 'Cache-Control', 'no-cache' );
		}

		return $response;
	}
}

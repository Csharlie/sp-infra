<?php
/**
 * Response Builder — assembles SiteData-compatible JSON from ACF fields.
 *
 * @package Spektra\API
 */

namespace Spektra\API;

defined( 'ABSPATH' ) || exit;

class Response_Builder {

	/**
	 * Build the full SiteData response.
	 *
	 * @param bool $is_preview Whether to include draft/preview content.
	 * @return array SiteData-compatible associative array.
	 */
	public function build( bool $is_preview = false ): array {
		// Phase 7: real assembly.
		// - Site meta + navigation (P7.2)
		// - Section assembly from ACF fields (P7.3)
		// - Media normalization (P7.4)
		return [
			'meta'       => [],
			'navigation' => [],
			'pages'      => [],
		];
	}
}

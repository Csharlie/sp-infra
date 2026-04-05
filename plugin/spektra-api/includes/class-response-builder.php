<?php
/**
 * Response Builder — assembles SiteData-compatible JSON from ACF fields.
 *
 * Platform contract (SiteData):
 *   { site: SiteMeta, navigation: Navigation, pages: Page[] }
 *
 * P7.1: skeleton only — no ACF reads, no config, no helpers.
 * P7.2: site meta + navigation from config + ACF.
 * P7.3: section assembly via spektra_get_section_data().
 * P7.4: media normalization integration.
 *
 * @package Spektra\API
 */

namespace Spektra\API;

defined( 'ABSPATH' ) || exit;

class Response_Builder {

	/**
	 * @var bool Whether this is a preview request.
	 */
	private bool $is_preview;

	/**
	 * Build the full SiteData response.
	 *
	 * @param bool $is_preview Whether to include draft/preview content.
	 * @return array SiteData-compatible associative array.
	 */
	public function build( bool $is_preview = false ): array {
		$this->is_preview = $is_preview;

		return [
			'site'       => $this->build_site_meta(),
			'navigation' => $this->build_navigation(),
			'pages'      => $this->build_pages(),
		];
	}

	/**
	 * Build SiteMeta shape.
	 *
	 * Platform contract: { name: string, description?, url?, locale? }
	 * P7.2: populated from config site_defaults + WP options.
	 *
	 * @return array SiteMeta
	 */
	private function build_site_meta(): array {
		return [
			'name' => '',
		];
	}

	/**
	 * Build Navigation shape.
	 *
	 * Platform contract: { primary: NavItem[], footer?: NavItem[] }
	 * P7.2: populated from config or WP menus.
	 *
	 * @return array Navigation
	 */
	private function build_navigation(): array {
		return [
			'primary' => [],
		];
	}

	/**
	 * Build the pages array.
	 *
	 * P7.3: config-driven section loop per page.
	 *
	 * @return array Page[]
	 */
	private function build_pages(): array {
		return [
			$this->build_page( 'home' ),
		];
	}

	/**
	 * Build a single Page shape.
	 *
	 * Platform contract: { slug: string, title?, meta?, sections: Section[] }
	 * P7.3: sections populated via config iteration + spektra_get_section_data().
	 *
	 * @param string $slug Page slug.
	 * @return array Page
	 */
	private function build_page( string $slug ): array {
		return [
			'slug'     => $slug,
			'sections' => [],
		];
	}

	/**
	 * Get the front page post ID.
	 *
	 * @return int Post ID, or 0 if not set.
	 */
	private function get_front_page_id(): int {
		return (int) get_option( 'page_on_front', 0 );
	}
}

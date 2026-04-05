<?php
/**
 * Response Builder — assembles SiteData-compatible JSON from config + WP runtime.
 *
 * Platform contract (SiteData):
 *   { site: SiteMeta, navigation: Navigation, pages: Page[] }
 *
 * Phase history:
 *   P7.1: skeleton — method structure, contract-safe defaults.
 *   P7.2: site meta + navigation from config + WP core.
 *   P7.3: section assembly via spektra_get_section_data().
 *   P7.4: media normalization integration.
 *
 * Future direction: native WordPress menu integration (Phase 11.5).
 * Phase 7.2 remains config-driven on purpose.
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
	 * Config is loaded here (not in constructor) to keep the builder stateless.
	 *
	 * @param bool $is_preview Whether to include draft/preview content.
	 * @return array SiteData-compatible associative array.
	 */
	public function build( bool $is_preview = false ): array {
		$this->is_preview = $is_preview;

		$config = $this->load_config();

		return [
			'site'       => $this->build_site_meta( $config ),
			'navigation' => $this->build_navigation( $config ),
			'pages'      => $this->build_pages(),
		];
	}

	/**
	 * Load client overlay config.
	 *
	 * @return array Config array, or empty array if missing.
	 */
	private function load_config(): array {
		$path = WP_PLUGIN_DIR . '/spektra-config/config.php';

		if ( ! file_exists( $path ) ) {
			return [];
		}

		$config = require $path;

		return is_array( $config ) ? $config : [];
	}

	/**
	 * Build SiteMeta shape.
	 *
	 * Platform contract: { name: string, description?, url?, locale? }
	 *
	 * Precedence: config override → WP runtime → fallback.
	 *
	 * @param array $config Client config.
	 * @return array SiteMeta
	 */
	private function build_site_meta( array $config ): array {
		$defaults = $config['site_defaults'] ?? [];

		return [
			'name'        => $defaults['title'] ?? get_bloginfo( 'name' ) ?: '',
			'description' => get_bloginfo( 'description' ) ?: '',
			'url'         => home_url( '/' ),
			'locale'      => $this->normalize_locale( get_locale() ),
		];
	}

	/**
	 * Normalize a WP locale string to BCP 47 format.
	 *
	 * WP uses underscores (hu_HU), BCP 47 uses hyphens (hu-HU).
	 *
	 * @param string $locale WP locale string.
	 * @return string BCP 47 locale.
	 */
	private function normalize_locale( string $locale ): string {
		return str_replace( '_', '-', $locale );
	}

	/**
	 * Build Navigation shape.
	 *
	 * Platform contract: { primary: NavItem[], footer?: NavItem[] }
	 *
	 * Source: config['navigation']['primary'] — curated list.
	 * Future: native WordPress menu integration (Phase 11.5).
	 *
	 * @param array $config Client config.
	 * @return array Navigation
	 */
	private function build_navigation( array $config ): array {
		$nav_config = $config['navigation'] ?? [];
		$primary    = $nav_config['primary'] ?? [];

		$items = array_map( [ $this, 'normalize_nav_item' ], $primary );

		return [
			'primary' => array_values( $items ),
		];
	}

	/**
	 * Normalize a raw config nav item to the canonical NavItem shape.
	 *
	 * Platform contract: { label: string, href: string, children?, external? }
	 *
	 * @param array $item Raw config nav item.
	 * @return array NavItem
	 */
	private function normalize_nav_item( array $item ): array {
		$normalized = [
			'label' => $item['label'] ?? '',
			'href'  => $item['href'] ?? '',
		];

		if ( ! empty( $item['external'] ) ) {
			$normalized['external'] = true;
		}

		if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
			$normalized['children'] = array_map( [ $this, 'normalize_nav_item' ], $item['children'] );
		}

		return $normalized;
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

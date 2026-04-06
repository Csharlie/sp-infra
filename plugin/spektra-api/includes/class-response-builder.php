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
 *   P7.3: section assembly — config-driven loop + spektra_get_section_data().
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
			'pages'      => $this->build_pages( $config ),
		];
	}

	/**
	 * Load client overlay config.
	 *
	 * Uses the canonical config loaded at bootstrap time (SPEKTRA_CLIENT_CONFIG
	 * constant, set in spektra-api.php via ENV var / symlink fallback).
	 * This ensures CORS, Response Builder, and all other consumers share the
	 * same config source within a single request.
	 *
	 * @return array Config array, or empty array if not bootstrapped.
	 */
	private function load_config(): array {
		return defined( 'SPEKTRA_CLIENT_CONFIG' ) && is_array( SPEKTRA_CLIENT_CONFIG )
			? SPEKTRA_CLIENT_CONFIG
			: [];
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
	 * Source: config['navigation']['primary'] and config['navigation']['footer'] — curated lists.
	 * Future: native WordPress menu integration (Phase 11.5).
	 *
	 * @param array $config Client config.
	 * @return array Navigation
	 */
	private function build_navigation( array $config ): array {
		$nav_config = $config['navigation'] ?? [];
		$primary    = $nav_config['primary'] ?? [];
		$footer     = $nav_config['footer'] ?? [];

		$nav = [
			'primary' => array_values( array_map( [ $this, 'normalize_nav_item' ], $primary ) ),
		];

		if ( ! empty( $footer ) ) {
			$nav['footer'] = array_values( array_map( [ $this, 'normalize_nav_item' ], $footer ) );
		}

		return $nav;
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
	 * @param array $config Client config.
	 * @return array Page[]
	 */
	private function build_pages( array $config ): array {
		return [
			$this->build_page( 'home', $config ),
		];
	}

	/**
	 * Build a single Page shape.
	 *
	 * Platform contract: { slug: string, title?, meta?, sections: Section[] }
	 *
	 * @param string $slug   Page slug.
	 * @param array  $config Client config.
	 * @return array Page
	 */
	private function build_page( string $slug, array $config ): array {
		return [
			'slug'     => $slug,
			'sections' => $this->build_sections( $config ),
		];
	}

	/**
	 * Build sections from config-driven slug list.
	 *
	 * Iterates config['sections'], calls spektra_get_section_data() for each,
	 * and wraps non-null results in the Section shape { id, type, data }.
	 *
	 * @param array $config Client config.
	 * @return array Section[]
	 */
	private function build_sections( array $config ): array {
		$post_id  = $this->get_front_page_id();
		$slugs    = $config['sections'] ?? [];
		$sections = [];

		if ( $post_id === 0 ) {
			return $sections;
		}

		foreach ( $slugs as $slug ) {
			if ( ! is_string( $slug ) || $slug === '' ) {
				continue;
			}

			$data = \spektra_get_section_data( $slug, $post_id );

			if ( ! is_array( $data ) ) {
				continue;
			}

			$sections[] = [
				'id'   => $slug,
				'type' => $slug,
				'data' => $data,
			];
		}

		return $sections;
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

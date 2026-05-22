<?php
/**
 * ACF Pro options page — "Site Content".
 *
 * Surfaces sitewide repeater data (offices, hours, trust pillars, why-us
 * pillars, process steps, team, credentials, homepage reviews, homepage
 * FAQ) in WP admin under a single "Site Content" menu so Steve can edit
 * any of it without touching code.
 *
 * Templates read via:
 *     $rows = function_exists( 'get_field' ) ? get_field( 'offices', 'option' ) : null;
 *     if ( empty( $rows ) ) { $rows = $php_default_array; }
 *
 * That progressive-enhancement pattern means the site keeps working if
 * ACF is ever deactivated — PHP defaults are the safety net.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class OptionsPage {

	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_options_page' ) );
	}

	/**
	 * Register the top-level "Site Content" menu + sub-pages, one per
	 * editable surface, so Steve sees a focused screen for each chunk.
	 */
	public function register_options_page(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		// Parent menu — hub.
		acf_add_options_page(
			array(
				'page_title'  => __( 'Site Content', 'showtime-pools' ),
				'menu_title'  => __( 'Site Content', 'showtime-pools' ),
				'menu_slug'   => 'showtime-site-content',
				'capability'  => 'manage_options',
				'redirect'    => true, // forward to first sub-page
				'icon_url'    => 'dashicons-admin-customizer',
				'position'    => 25,
			)
		);

		$subpages = array(
			array(
				'slug'  => 'showtime-content-images',
				'title' => __( 'Images', 'showtime-pools' ),
			),
			array(
				'slug'  => 'showtime-content-page-copy',
				'title' => __( 'Page Copy', 'showtime-pools' ),
			),
			array(
				'slug'  => 'showtime-content-business',
				'title' => __( 'Offices & hours', 'showtime-pools' ),
			),
			array(
				'slug'  => 'showtime-content-homepage',
				'title' => __( 'Homepage sections', 'showtime-pools' ),
			),
			array(
				'slug'  => 'showtime-content-team',
				'title' => __( 'Team & credentials', 'showtime-pools' ),
			),
			array(
				'slug'  => 'showtime-content-reviews-faq',
				'title' => __( 'Reviews & FAQ', 'showtime-pools' ),
			),
		);

		foreach ( $subpages as $sub ) {
			acf_add_options_sub_page(
				array(
					'page_title'  => $sub['title'],
					'menu_title'  => $sub['title'],
					'parent_slug' => 'showtime-site-content',
					'menu_slug'   => $sub['slug'],
					'capability'  => 'manage_options',
				)
			);
		}
	}
}

<?php
/**
 * Theme support, image sizes, menus.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'showtime-pools', SHOWTIME_CHILD_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );

		// Custom logo: enable the WP Customizer "Site Identity → Logo" upload.
		// height/width are the natural canvas; CSS handles the rendered size.
		add_theme_support(
			'custom-logo',
			array(
				'height'               => 96,
				'width'                => 320,
				'flex-height'          => true,
				'flex-width'           => true,
				'unlink-homepage-logo' => false,
			)
		);

		// Project image sizes. Squarish hero crops perform best in card grids.
		add_image_size( 'showtime-card', 720, 540, true );
		add_image_size( 'showtime-card-2x', 1440, 1080, true );
		add_image_size( 'showtime-hero', 1920, 1080, true );
		add_image_size( 'showtime-thumb', 360, 270, true );

		register_nav_menus(
			array(
				'primary'   => __( 'Primary Navigation', 'showtime-pools' ),
				'footer'    => __( 'Footer Navigation', 'showtime-pools' ),
				'mobile'    => __( 'Mobile Drawer', 'showtime-pools' ),
				'utility'   => __( 'Utility (top bar)', 'showtime-pools' ),
				'inspections' => __( 'Inspections Sub-brand', 'showtime-pools' ),
			)
		);
	}
);

// Editor stylesheet so Gutenberg matches the frontend.
add_action(
	'after_setup_theme',
	function () {
		add_editor_style( 'assets/css/editor.css' );
	}
);

// Astra parent removed — its scroll-to-top filters are no longer needed.

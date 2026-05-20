<?php
/**
 * Frontend + admin asset enqueue.
 *
 * Tokens load first (CSS variables) so every later sheet can resolve them.
 * All CSS is versioned by file mtime so cache busts on edit. Production
 * caching is handled by WP Rocket + Cloudflare; we expose the raw files.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper: file-mtime-versioned asset URL.
 */
function showtime_asset( string $rel ): array {
	$path = SHOWTIME_CHILD_DIR . '/' . ltrim( $rel, '/' );
	$uri  = SHOWTIME_CHILD_URI . '/' . ltrim( $rel, '/' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : SHOWTIME_CHILD_VERSION;
	return array( $uri, $ver );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// We run as a parent-agnostic child theme. We do not enqueue the
		// parent (Astra) stylesheet because we own every visual layer
		// through tokens.css → base.css → components.css. Astra acts as
		// the activation host only.

		// Tokens must load before any component CSS. Header/footer CSS depend
		// on tokens + components and are sitewide, so load them globally.
		$first_handle = '';
		foreach ( array( 'tokens', 'base', 'utilities', 'components', 'blocks', 'header', 'footer' ) as $sheet ) {
			[ $uri, $ver ] = showtime_asset( "assets/css/{$sheet}.css" );
			$deps = $first_handle ? array( $first_handle ) : array();
			wp_enqueue_style( "showtime-{$sheet}", $uri, $deps, $ver );
			if ( ! $first_handle ) {
				$first_handle = "showtime-{$sheet}";
			}
		}

		// Page-scoped CSS + JS, only on relevant templates.
		if ( is_front_page() ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/home.css' );
			wp_enqueue_style( 'showtime-home', $uri, array( 'showtime-components' ), $ver );

			[ $uri, $ver ] = showtime_asset( 'assets/js/home.js' );
			wp_enqueue_script( 'showtime-home', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		if ( is_page_template( 'page-service.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/service.css' );
			wp_enqueue_style( 'showtime-service', $uri, array( 'showtime-components' ), $ver );
		}

		if ( is_page_template( 'page-contact.php' ) || is_page_template( 'page-iframe.php' ) || is_page_template( 'page-shop.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/contact.css' );
			wp_enqueue_style( 'showtime-contact', $uri, array( 'showtime-components' ), $ver );
		}

		if ( is_page_template( 'page-contact.php' ) || is_page_template( 'page-shop.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/contact.js' );
			wp_enqueue_script( 'showtime-contact', $uri, array( 'showtime-main' ), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		// Interior pages (about, areas, inspections, projects, reviews, legal, 404).
		$interior_templates = array(
			'page-about.php', 'page-founder.php',
			'page-areas.php', 'page-area.php',
			'page-inspections.php', 'page-inspection.php',
			'page-projects.php', 'page-reviews.php', 'page-legal.php',
			'page-services-hub.php', 'page-shop.php', 'page-blog.php',
		);
		$is_interior = false;
		foreach ( $interior_templates as $tpl ) {
			if ( is_page_template( $tpl ) ) { $is_interior = true; break; }
		}
		if ( $is_interior || is_404() || is_singular( 'post' ) || is_archive() || is_home() ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/interior.css' );
			wp_enqueue_style( 'showtime-interior', $uri, array( 'showtime-components' ), $ver );
		}

		// Blog hub + archives + single posts get the dedicated blog stylesheet.
		if ( is_page_template( 'page-blog.php' ) || is_singular( 'post' ) || is_archive() || is_home() ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/blog.css' );
			wp_enqueue_style( 'showtime-blog', $uri, array( 'showtime-components', 'showtime-interior' ), $ver );
		}

		// TOC + scroll-spy only on single posts (article body required).
		if ( is_singular( 'post' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/blog.js' );
			wp_enqueue_script( 'showtime-blog', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		// Global JS, deferred (no render-block).
		[ $uri, $ver ] = showtime_asset( 'assets/js/main.js' );
		wp_enqueue_script( 'showtime-main', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		[ $uri, $ver ] = showtime_asset( 'assets/js/header.js' );
		wp_enqueue_script( 'showtime-header', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		// Expose minimal config to JS (REST URL, nonce). Never tokens or keys.
		wp_localize_script(
			'showtime-main',
			'ShowtimeConfig',
			array(
				'restUrl' => esc_url_raw( rest_url( 'showtime/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'isHome'  => is_front_page(),
			)
		);
	}
);

// Google Fonts: DM Sans (display + body, 400/500/700) — matches the Brikly
// reference exactly. Single family keeps requests minimal. On Cloudways the
// Local Google Fonts plugin will fetch and self-host these for perf; in local
// preview we ship them via the Google CDN with display=swap.
add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://images.unsplash.com" crossorigin>' . "\n";
		echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400;1,9..40,500&display=swap">' . "\n";
	},
	1
);

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

		// Fonts first (self-hosted @font-face), then tokens before any
		// component CSS. Header/footer CSS depend on tokens + components
		// and are sitewide, so load them globally.
		$first_handle = '';
		foreach ( array( 'fonts', 'tokens', 'base', 'utilities', 'components', 'blocks', 'header', 'footer' ) as $sheet ) {
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

			// Reusable carousel controller (services slider). Deferred; inert
			// until interaction. Native scroll-snap works without it.
			[ $uri, $ver ] = showtime_asset( 'assets/js/carousel.js' );
			wp_enqueue_script( 'showtime-carousel', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		}

		if ( is_page_template( 'page-service.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/service.css' );
			wp_enqueue_style( 'showtime-service', $uri, array( 'showtime-components' ), $ver );
		}

		if ( is_page_template( 'page-contact.php' ) || is_page_template( 'page-iframe.php' ) || is_page_template( 'page-shop.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/contact.css' );
			wp_enqueue_style( 'showtime-contact', $uri, array( 'showtime-components' ), $ver );
		}

		// GHL resize helper for the embedded booking/quote widgets. Sizes the
		// iframe to its content so the calendar never scrolls inside a box.
		if ( is_page_template( 'page-iframe.php' ) ) {
			wp_enqueue_script(
				'ghl-form-embed',
				'https://link.msgsndr.com/js/form_embed.js',
				array(),
				null,
				array( 'in_footer' => true, 'strategy' => 'defer' )
			);
		}

		if ( is_page_template( 'page-contact.php' ) || is_page_template( 'page-shop.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/contact.js' );
			wp_enqueue_script( 'showtime-contact', $uri, array( 'showtime-main' ), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		// Affiliate / Partner Program — reuses interior.css (loaded below via the
			// interior_templates list) plus its own scoped sheet + form JS.
			if ( is_page_template( 'page-affiliate.php' ) ) {
				[ $uri, $ver ] = showtime_asset( 'assets/css/affiliate.css' );
				wp_enqueue_style( 'showtime-affiliate', $uri, array( 'showtime-components', 'showtime-interior' ), $ver );

				[ $uri, $ver ] = showtime_asset( 'assets/js/affiliate.js' );
				wp_enqueue_script( 'showtime-affiliate', $uri, array( 'showtime-main' ), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}

			// Cloudflare Turnstile API — loaded only on form pages, and only when
			// keys are configured (Turnstile::is_configured()). Renders the widget
			// implicitly from the .cf-turnstile div in each form.
			$has_form = is_page_template( 'page-affiliate.php' )
				|| is_page_template( 'page-contact.php' )
				|| is_page_template( 'page-shop.php' );
			if ( $has_form
				&& class_exists( '\\Showtime\\Security\\Turnstile' )
				&& \Showtime\Security\Turnstile::is_configured() ) {
				wp_enqueue_script(
					'cf-turnstile',
					'https://challenges.cloudflare.com/turnstile/v0/api.js',
					array(),
					null,
					array( 'in_footer' => false, 'strategy' => 'async' )
				);
			}

			// Interior pages (about, areas, inspections, projects, reviews, legal, 404).
		$interior_templates = array(
			'page-about.php', 'page-founder.php',
			'page-areas.php', 'page-area.php',
			'page-inspections.php', 'page-inspection.php',
			'page-projects.php', 'page-reviews.php', 'page-legal.php',
			'page-services-hub.php', 'page-shop.php', 'page-blog.php',
				'page-affiliate.php',
		);
		$is_interior = false;
		foreach ( $interior_templates as $tpl ) {
			if ( is_page_template( $tpl ) ) { $is_interior = true; break; }
		}
		if ( $is_interior || is_404() || is_singular( 'post' ) || is_archive() || is_home() ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/interior.css' );
			wp_enqueue_style( 'showtime-interior', $uri, array( 'showtime-components' ), $ver );
		}

		// Blog hub + archives + single posts + single projects get the
		// dedicated blog/project stylesheet (single-project styles live
		// inside blog.css alongside blog single styles — same token system).
		if ( is_page_template( 'page-blog.php' )
			|| is_singular( 'post' )
			|| is_singular( 'project' )
			|| is_archive()
			|| is_home() ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/blog.css' );
			wp_enqueue_style( 'showtime-blog', $uri, array( 'showtime-components', 'showtime-interior' ), $ver );
		}

		// Single projects piggyback on interior.css for the .featured-projects__grid
		// + .proj-card styles used in the Related block.
		if ( is_singular( 'project' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/css/interior.css' );
			wp_enqueue_style( 'showtime-interior', $uri, array( 'showtime-components' ), $ver );
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

// DM Sans is self-hosted (assets/fonts/ + assets/css/fonts.css), so no
// Google Fonts requests remain. Preload the latin normal variable file,
// the one every first paint needs; font preloads require crossorigin
// even on same-origin requests. Unsplash preconnect stays for imagery.
add_action(
	'wp_head',
	function () {
		[ $font_uri ] = showtime_asset( 'assets/fonts/dm-sans-latin.woff2' );
		echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( $font_uri ) . '" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://images.unsplash.com" crossorigin>' . "\n";
	},
	1
);

// Make footer.css non-render-blocking: it styles only the (below-the-fold)
// footer, so loading it as media="print" and swapping to "all" on load removes
// one render-blocking request with no above-the-fold FOUC risk. A <noscript>
// fallback keeps it working without JS. blocks.css is intentionally NOT deferred
// here — Gutenberg block content can be above the fold on content pages.
// (In production, WP Rocket "Optimize CSS delivery" supersedes this for all CSS.)
add_filter(
	'style_loader_tag',
	function ( $tag, $handle ) {
		if ( is_admin() || 'showtime-footer' !== $handle ) {
			return $tag;
		}
		$noscript = '<noscript>' . $tag . '</noscript>';
		$deferred = preg_replace(
			'/media=([\'"])all\1/',
			'media="print" onload="this.media=\'all\';this.onload=null"',
			$tag,
			1,
			$count
		);
		if ( ! $count ) {
			$deferred = str_replace( ' />', ' media="print" onload="this.media=\'all\';this.onload=null" />', $tag );
		}
		return $deferred . $noscript;
	},
	10,
	2
);

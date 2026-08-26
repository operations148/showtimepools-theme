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

/**
 * Whether this request is a surface that carries the Google
 * "Add to Preferred Sources" button.
 *
 * Exactly two: the /blog/ hub (page-blog.php, including its /blog/page/N/
 * pagination) and an individual published post. Deliberately NOT the category
 * or tag archives, and not project singles — blog.css is shared with those, but
 * the button is not.
 *
 * ONE predicate answers for both the script and the markup: inc/enqueue.php
 * gates the publisher.js enqueue on it, and the callout partial refuses to
 * render anywhere it returns false. That makes the two impossible to
 * desynchronise — there can be no button without its script, and no script
 * without a button.
 */
function showtime_is_preferred_source_surface(): bool {
	$is_surface = is_page_template( 'page-blog.php' ) || is_singular( 'post' );

	return (bool) apply_filters( 'showtime/preferred_source/is_surface', $is_surface );
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

		// GHL resize helper for the embedded booking/quote/contact widgets. Sizes
		// the iframe to its content so the calendar/form never scrolls inside a box.
		if ( is_page_template( 'page-iframe.php' ) || is_page_template( 'page-contact.php' ) ) {
			wp_enqueue_script(
				'ghl-form-embed',
				'https://link.msgsndr.com/js/form_embed.js',
				array(),
				null,
				array( 'in_footer' => true, 'strategy' => 'defer' )
			);
		}

		// /contact/ now embeds the GHL form (no native form JS). /shop/ still uses it.
		if ( is_page_template( 'page-shop.php' ) ) {
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
			// The HTML sitemap uses the interior hero + .sitemap-group styles, and
			// interior.css is where --stp-hero-pad-top is declared. Without it the
			// has-hero offset in header-hero.css resolves against an undefined
			// custom property and the breadcrumb/H1 slide under the fixed header.
			'page-sitemap.php',
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

		// Projects archive slider — pure progressive enhancement. Every card is
		// server-rendered; this only paginates them. Without it the CSS leaves
		// all slides stacked as a readable grid.
		if ( is_page_template( 'page-projects.php' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/project-slider.js' );
			wp_enqueue_script( 'showtime-project-slider', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		// Before/After slider — single projects only. Pure progressive
		// enhancement: the pair renders side by side without it.
		if ( is_singular( 'project' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/project-compare.js' );
			wp_enqueue_script( 'showtime-project-compare', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

			// The additional-gallery carousel reuses the archive slider's markup
			// contract, so it reuses the same script. Loaded only on a project
			// whose registry entry actually configures a gallery — every other
			// project page ships neither the markup nor this request.
			$proj_for_gallery = function_exists( 'showtime_project_data' )
				? showtime_project_data( get_queried_object_id() )
				: null;
			if ( function_exists( 'showtime_project_gallery_pages' )
				&& ! empty( showtime_project_gallery_pages( $proj_for_gallery ) ) ) {
				[ $uri, $ver ] = showtime_asset( 'assets/js/project-slider.js' );
				wp_enqueue_script( 'showtime-project-slider', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}
		}

		// TOC + scroll-spy only on single posts (article body required).
		if ( is_singular( 'post' ) ) {
			[ $uri, $ver ] = showtime_asset( 'assets/js/blog.js' );
			wp_enqueue_script( 'showtime-blog', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}

		// Google "Add to Preferred Sources" (publisher.js). Loaded ONLY on the
		// two surfaces that render the button — the /blog/ hub and individual
		// posts — never globally, and never on service, project, service-area,
		// home, sitemap, shop or utility pages.
		//
		// WordPress dedupes by handle, so this registers exactly one <script>
		// per response even though two templates can include the callout
		// partial. Version is null: Google's endpoint is unversioned and a
		// ?ver= query would only bust their cache.
		//
		// Async, in the head, matching the Cloudflare Turnstile pattern above:
		// publisher.js hydrates the empty [google-add-preferred-source-btn]
		// element itself, so it must not be deferred behind DOMContentLoaded.
		//
		// Not consent-gated, consistent with Turnstile: this is a functional
		// widget the visitor chooses to act on, not an advertising or analytics
		// pixel. Every tracking pixel on this site lives in GTM behind Consent
		// Mode v2 (see inc/consent.php) and none is hard-coded here.
		if ( showtime_is_preferred_source_surface() ) {
			wp_enqueue_script(
				'google-swg-publisher',
				'https://news.google.com/swg/js/v1/publisher.js',
				array(),
				null,
				array( 'in_footer' => false, 'strategy' => 'async' )
			);
		}

		// Hero ↔ header geometry. Enqueued LAST of all stylesheets on purpose:
		// it adjusts hero rules that live in home/interior/service/contact/blog
		// CSS, all of which are queued above, and same-specificity selectors are
		// resolved by source order. See assets/css/header-hero.css.
		[ $uri, $ver ] = showtime_asset( 'assets/css/header-hero.css' );
		wp_enqueue_style( 'showtime-header-hero', $uri, array( 'showtime-header' ), $ver );

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

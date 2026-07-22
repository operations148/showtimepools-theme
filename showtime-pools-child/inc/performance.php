<?php
/**
 * Frontend perf cleanup + cache advertising.
 *
 * There is NO page-cache plugin on this site (verified 2026-07-22: no WP
 * Rocket / LiteSpeed / W3TC footprint in the live HTML). Caching is expected
 * to happen at the Cloudflare edge — see
 * .claude/audits/cloudflare-performance-runbook.md.
 *
 * This file handles the default WP bloat that has no business loading on a
 * service-business site, and emits the Cache-Control header that lets an edge
 * cache hold anonymous HTML.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Kill emoji JS + CSS (saves ~15KB and a request, we ship UTF-8 natively).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter(
	'tiny_mce_plugins',
	function ( $plugins ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
);

// Remove unused REST/oEmbed surfaces from <head>. We use the REST API but don't need link discovery.
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

// Drop the shortlink meta tag. Pretty permalinks are the canonical URL.
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

// Don't load the block-library CSS sitewide. We restyle the blocks we use; the rest are unused.
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' ); // We define our own tokens.
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);

// Lazy-load all images by default (WP core supports it; this just ensures it).
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// Preload the LCP hero image so the fetch starts before CSS/layout. URLs come
// from the same helpers the templates render with (inc/imagery.php), so the
// preloaded resource always matches the displayed one. The media attributes
// mirror the <picture> breakpoint in template-parts/home/section-01-hero.php.
add_action(
	'wp_head',
	function () {
		if ( is_front_page() && function_exists( 'showtime_front_hero_image' ) ) {
			// When a hero video plays, the LCP is the small poster, not the
			// responsive hero image — skip the image preload so we don't fetch the
			// wrong asset. Code-first mode always plays the bundled video; in
			// WordPress mode the video is on only when the setting is filled.
			$hero_video_on = ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST )
				? true
				: ( '' !== (string) get_option( 'showtime_hero_video_url', '' ) );
			if ( ! $hero_video_on ) {
				$h = showtime_front_hero_image();
				if ( '' !== $h['srcset'] ) {
					// Preload the exact responsive candidate the <img> will pick,
					// so mobile preloads the ~720w crop, not the full original.
					echo '<link rel="preload" as="image" imagesrcset="' . esc_attr( $h['srcset'] ) . '" imagesizes="' . esc_attr( $h['sizes'] ) . '" fetchpriority="high">' . "\n";
				} elseif ( '' !== $h['src'] ) {
					echo '<link rel="preload" as="image" href="' . esc_url( $h['src'] ) . '" fetchpriority="high">' . "\n";
				}
			}
			return;
		}

		if ( is_singular( 'post' ) && function_exists( 'showtime_post_hero_url' ) ) {
			$url = showtime_post_hero_url( get_queried_object_id() );
			if ( '' !== $url ) {
				echo '<link rel="preload" as="image" href="' . esc_url( $url ) . '" fetchpriority="high">' . "\n";
			}
		}
	},
	2
);

// Add fetchpriority=high to the LCP image when we mark one explicitly.
// Templates set the post meta `_showtime_lcp_image_id` to flag the LCP candidate.
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr, $attachment ) {
		if ( ! is_singular() ) {
			return $attr;
		}
		$lcp_id = (int) get_post_meta( get_queried_object_id(), '_showtime_lcp_image_id', true );
		if ( $lcp_id && (int) $attachment->ID === $lcp_id ) {
			$attr['fetchpriority'] = 'high';
			$attr['loading']       = 'eager';
		}
		return $attr;
	},
	10,
	2
);

/**
 * Advertise anonymous HTML as edge-cacheable.
 *
 * WordPress ships no Cache-Control on front-end HTML, so a shared cache has
 * nothing to act on and every visitor pays a full PHP+MySQL render (measured
 * ~1.2s origin think-time on 2026-07-22, cf-cache-status: DYNAMIC).
 *
 * `max-age=0` keeps BROWSERS from holding stale HTML, while `s-maxage` lets a
 * SHARED cache (the Cloudflare edge) serve it. This header is inert until
 * HTML caching is actually switched on at Cloudflare — either APO or a
 * "Cache Everything" rule set to respect origin TTL. See the runbook.
 *
 * Deliberately skipped: logged-in users, admin/AJAX/cron/REST, non-GET
 * requests, previews, and search/404 — anything that is personalised or
 * must never be served from a shared cache.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || is_user_logged_in() || is_preview() ) {
			return;
		}
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		// GET and HEAD are the cacheable methods; CDNs issue both.
		$method = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) );
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return;
		}
		// Never hand a shared cache a search result or a 404.
		if ( is_search() || is_404() ) {
			return;
		}
		// Logged-out visitors who still carry a WP auth/comment cookie are
		// treated as personalised to be safe.
		foreach ( array_keys( (array) $_COOKIE ) as $cookie ) {
			if ( 0 === strpos( (string) $cookie, 'wordpress_logged_in' )
				|| 0 === strpos( (string) $cookie, 'comment_author' ) ) {
				return;
			}
		}

		$s_maxage = (int) apply_filters( 'showtime/cache/s_maxage', 600 );
		if ( $s_maxage < 1 ) {
			return;
		}
		header( sprintf(
			'Cache-Control: public, max-age=0, s-maxage=%d, stale-while-revalidate=60',
			$s_maxage
		) );
	},
	20
);

<?php
/**
 * Frontend perf cleanup. WP Rocket handles caching, this file handles the
 * default WP bloat that has no business loading on a service-business site.
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
			// With a hero video the LCP is the small poster, not a single large
			// image — skip the image preload so we don't fetch the wrong asset.
			if ( '' === (string) get_option( 'showtime_hero_video_url', '' ) ) {
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

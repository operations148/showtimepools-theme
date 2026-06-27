<?php
/**
 * Google Reviews widget renderer.
 *
 * Renders the configured third-party review widget shortcode (Trustindex by
 * default) so both the Reviews page and the homepage section pull from one
 * live source — never from hardcoded testimonials.
 *
 * The shortcode is configurable via wp_options so a swap of widget plugin
 * doesn't require a code change. Fall-through order:
 *   1. wp_options[showtime_reviews_shortcode_compact]  (only if variant=compact)
 *   2. wp_options[showtime_reviews_shortcode]
 *   3. Hardcoded Trustindex default
 *
 * If the resolved shortcode renders nothing (e.g. plugin inactive), the helper
 * returns a "View Google reviews →" button pointing at the configured GBP URL,
 * falling back to a Google-search URL for the business name. It never returns
 * silence and never returns a fabricated review.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'showtime_render_reviews_widget' ) ) {

	/**
	 * Render the live Google Reviews widget.
	 *
	 * @param string $variant 'default' (Reviews page) or 'compact' (homepage section).
	 * @return string Sanitized widget HTML, or fallback CTA when the shortcode is dormant.
	 */
	function showtime_render_reviews_widget( string $variant = 'default' ): string {

		$shortcode = '';

		if ( 'compact' === $variant ) {
			$shortcode = (string) get_option( 'showtime_reviews_shortcode_compact', '' );
		}
		if ( '' === $shortcode ) {
			$shortcode = (string) get_option( 'showtime_reviews_shortcode', '' );
		}
		if ( '' === $shortcode ) {
			$shortcode = '[trustindex no-registration=google]';
		}

		$rendered = trim( (string) do_shortcode( $shortcode ) );

		// If do_shortcode returns the literal shortcode unchanged, the
		// shortcode isn't registered (plugin inactive). Treat as empty.
		if ( $rendered === $shortcode ) {
			$rendered = '';
		}

		if ( '' !== $rendered ) {
			// Lazy-mount the third-party widget. Trustindex's embed pulls
			// 30+ JS files via its loader <script>; it sits below the fold,
			// so we keep that markup inert inside a <template> and let
			// assets/js/main.js inject it (re-executing its scripts) only
			// when the reviews section scrolls into view — same defer-until-
			// needed pattern as the popup/quote iframes. The markup is the
			// exact shortcode output, so the live review pull is unchanged,
			// just delayed. No-IntersectionObserver browsers mount on load.
			return sprintf(
				'<div class="reviews-widget__lazy" data-trustindex-lazy><template data-trustindex-markup>%s</template></div>',
				$rendered
			);
		}

		// Fallback CTA — never silence, never fakes.
		$gbp_url = (string) apply_filters(
			'showtime/business/gbp_url',
			(string) get_option( 'showtime_gbp_public_url', '' )
		);
		if ( '' === $gbp_url ) {
			$gbp_url = 'https://www.google.com/search?q=' . rawurlencode( get_bloginfo( 'name' ) . ' Sherman Oaks pool service' );
		}

		return sprintf(
			'<div class="reviews-widget-fallback"><p>%s</p><a class="btn btn--primary" href="%s" target="_blank" rel="noopener noreferrer">%s</a></div>',
			esc_html__( 'Read every review on our Google Business Profile.', 'showtime-pools' ),
			esc_url( $gbp_url ),
			esc_html__( 'View Google reviews →', 'showtime-pools' )
		);
	}
}

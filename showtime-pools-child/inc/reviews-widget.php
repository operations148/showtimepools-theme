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

if ( ! function_exists( 'showtime_get_cached_reviews' ) ) {

	/**
	 * Read the curated review cache (P0-3).
	 *
	 * Read-only, and deliberately independent of the core plugin: the option is
	 * stored under a bare `showtime_*` name (same convention as
	 * showtime_reviews_shortcode) so review sections keep rendering even if the
	 * plugin is deactivated. Writing and validating is the plugin's job — see
	 * \Showtime\Reviews.
	 *
	 * Rows missing an author or text are dropped rather than rendered empty.
	 *
	 * @param int $limit Maximum rows to return. 0 = the stored maximum.
	 * @return array<int,array<string,mixed>>
	 */
	function showtime_get_cached_reviews( int $limit = 0 ): array {
		$raw = get_option( 'showtime_reviews_cached', array() );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$author = isset( $row['author'] ) ? trim( (string) $row['author'] ) : '';
			$text   = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
			if ( '' === $author || '' === $text ) {
				continue;
			}

			// Only a genuine integer 1-5 survives; anything else is omitted so
			// the renderer never prints "0 stars" or an empty rating element.
			$rating = null;
			if ( isset( $row['rating'] ) && is_numeric( $row['rating'] ) ) {
				$candidate = (int) $row['rating'];
				if ( (string) $candidate === (string) $row['rating'] && $candidate >= 1 && $candidate <= 5 ) {
					$rating = $candidate;
				}
			}

			// Same for the date — a malformed value is dropped, never guessed.
			$date = null;
			if ( isset( $row['date'] ) && is_string( $row['date'] ) && '' !== $row['date'] ) {
				if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $row['date'], $m )
					&& checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
					$date = $row['date'];
				}
			}

			$source_url = null;
			if ( isset( $row['source_url'] ) && is_string( $row['source_url'] ) && 0 === stripos( $row['source_url'], 'https://' ) ) {
				$source_url = $row['source_url'];
			}

			$out[] = array(
				'author'     => $author,
				'text'       => $text,
				'rating'     => $rating,
				'date'       => $date,
				'source'     => isset( $row['source'] ) ? (string) $row['source'] : '',
				'source_url' => $source_url,
			);
		}

		$max = $limit > 0 ? $limit : 12;
		return array_slice( $out, 0, $max );
	}
}

if ( ! function_exists( 'showtime_render_cached_reviews' ) ) {

	/**
	 * Render the curated reviews as ordinary server-side HTML.
	 *
	 * This is the whole point of P0-3: real review text present in the initial
	 * response, outside any <template>, <noscript>, script, or hidden wrapper,
	 * so a crawler that executes no JavaScript still reads it.
	 *
	 * Emits no Review or AggregateRating JSON-LD — the goal is visible text for
	 * readers and crawlers, not self-serving star rich-results.
	 *
	 * @param string $variant 'default' (all, max 12) or 'compact' (first 3).
	 * @return string Empty string when nothing is cached.
	 */
	function showtime_render_cached_reviews( string $variant = 'default' ): string {
		$limit   = ( 'compact' === $variant ) ? 3 : 12;
		$reviews = showtime_get_cached_reviews( $limit );
		if ( empty( $reviews ) ) {
			return '';
		}

		$items = '';
		foreach ( $reviews as $r ) {
			$meta = '';

			// Rating: only when a verified integer exists. The stars are
			// decorative; the accessible name carries the real value.
			if ( null !== $r['rating'] ) {
				$stars = str_repeat( '★', $r['rating'] ) . str_repeat( '☆', 5 - $r['rating'] );
				$meta .= sprintf(
					'<span class="review-card__rating"><span class="review-card__stars" aria-hidden="true">%s</span><span class="visually-hidden">%s</span></span>',
					esc_html( $stars ),
					esc_html(
						sprintf(
							/* translators: %d: rating out of five */
							__( 'Rated %d out of 5', 'showtime-pools' ),
							$r['rating']
						)
					)
				);
			}

			// Date: only when verified. datetime attribute is machine-readable.
			if ( null !== $r['date'] ) {
				$ts    = strtotime( $r['date'] . ' 00:00:00 UTC' );
				$label = $ts ? date_i18n( 'F j, Y', $ts ) : $r['date'];
				$meta .= sprintf(
					'<time class="review-card__date" datetime="%s">%s</time>',
					esc_attr( $r['date'] ),
					esc_html( $label )
				);
			}

			$author = sprintf( '<cite class="review-card__author">%s</cite>', esc_html( $r['author'] ) );
			if ( null !== $r['source_url'] ) {
				$author = sprintf(
					'<cite class="review-card__author"><a href="%s" target="_blank" rel="noopener noreferrer nofollow">%s</a></cite>',
					esc_url( $r['source_url'] ),
					esc_html( $r['author'] )
				);
			}

			$items .= sprintf(
				'<article class="review-card"><blockquote class="review-card__quote"><p>%s</p></blockquote><footer class="review-card__meta">%s%s</footer></article>',
				esc_html( $r['text'] ),
				$author,
				$meta
			);
		}

		return sprintf(
			'<div class="reviews-static%s" data-reviews-static>%s</div>',
			'compact' === $variant ? ' reviews-static--compact' : '',
			$items
		);
	}
}

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

		// P0-3: the curated cache, rendered as plain HTML in the initial
		// response. The Trustindex markup below it is inert until JS runs
		// (our <template>, then Trustindex's own hidden <pre><template>), so
		// without this block a no-JS crawler sees only the heading and CTA.
		$static = showtime_render_cached_reviews( $variant );

		if ( '' !== $rendered ) {
			// Lazy-mount the third-party widget. Trustindex's embed pulls
			// 30+ JS files via its loader <script>; it sits below the fold,
			// so we keep that markup inert inside a <template> and let
			// assets/js/main.js inject it (re-executing its scripts) only
			// when the reviews section scrolls into view — same defer-until-
			// needed pattern as the popup/quote iframes. The markup is the
			// exact shortcode output, so the live review pull is unchanged,
			// just delayed. No-IntersectionObserver browsers mount on load.
			$widget = sprintf(
				'<div class="reviews-widget__lazy" data-trustindex-lazy><template data-trustindex-markup>%s</template></div>',
				$rendered
			);

			// No cache yet — behave exactly as before.
			if ( '' === $static ) {
				return $widget;
			}

			// Both layers. main.js hides the static block only once the live
			// widget has actually hydrated with visible review items; if
			// Trustindex fails or is blocked, the static block simply stays.
			return sprintf(
				'<div class="reviews-widget__stack" data-reviews-stack>%s%s</div>',
				$static,
				$widget
			);
		}

		// Widget dormant but a curated cache exists — that cache IS the
		// content, so render it rather than a "go to Google" CTA.
		if ( '' !== $static ) {
			return $static;
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

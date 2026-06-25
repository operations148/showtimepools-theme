<?php
/**
 * Homepage quick-quote form section.
 *
 * A short "get a fast quote" GHL form embedded after the Reviews section on the
 * homepage — distinct from the full Contact page and from the footer booking
 * CTA. Everything is CMS-driven (Showtime → Settings → "Homepage Quote Form"):
 * toggle, embed URL, heading, subtext, and UTM defaults.
 *
 * The section is injected via the `showtime/home_sections` filter (no
 * front-page.php edit) and the GHL <iframe> is lazy-loaded by quote-form.js only
 * when it scrolls near the viewport, so it never affects LCP.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/** Built-in default GHL form (decision: form tH1eoDpRA4hMEb04GgzX). */
const SHOWTIME_QUOTE_DEFAULT_URL = 'https://app.showtimepoolmechanics.com/widget/form/tH1eoDpRA4hMEb04GgzX';

/**
 * Resolved quote-form config from wp_options, with display + UTM defaults.
 *
 * @return array{enabled:bool,heading:string,subtext:string,embed_url:string}
 */
function showtime_quote_config(): array {
	$base = trim( (string) get_option( 'showtime_quote_form_url', '' ) );
	if ( '' === $base ) {
		$base = SHOWTIME_QUOTE_DEFAULT_URL;
	}

	$utm = array(
		'utm_source'  => showtime_quote_text( 'showtime_quote_utm_source', 'website' ),
		'utm_medium'  => showtime_quote_text( 'showtime_quote_utm_medium', 'organic' ),
		'utm_content' => showtime_quote_text( 'showtime_quote_utm_content', 'homepage_quote_form' ),
	);

	return array(
		'enabled'   => '1' === (string) get_option( 'showtime_quote_enabled', '1' ),
		'heading'   => showtime_quote_text( 'showtime_quote_heading', __( 'Get a fast pool quote', 'showtime-pools' ) ),
		'subtext'   => showtime_quote_text( 'showtime_quote_subtext', __( 'Tell us about your pool and Steve sends an itemized written quote within one business day. No pressure, no spam.', 'showtime-pools' ) ),
		'embed_url' => (string) apply_filters( 'showtime/quote/form_url', add_query_arg( $utm, $base ) ),
	);
}

/**
 * Option value with a fallback when the stored value is empty.
 */
function showtime_quote_text( string $option, string $default ): string {
	$v = trim( (string) get_option( $option, '' ) );
	return '' === $v ? $default : $v;
}

/**
 * Whether the quote section should render on this request: front page, toggle
 * on, and an embed URL resolved (always true via the default).
 */
function showtime_quote_active(): bool {
	if ( is_admin() || ! is_front_page() ) {
		return false;
	}
	$cfg = showtime_quote_config();
	return $cfg['enabled'] && '' !== $cfg['embed_url'];
}

/**
 * Inject the section after Reviews (08-reviews) without editing front-page.php.
 */
add_filter(
	'showtime/home_sections',
	function ( array $sections ): array {
		if ( ! showtime_quote_active() ) {
			return $sections;
		}
		$pos = array_search( '08-reviews', $sections, true );
		if ( false === $pos ) {
			$sections[] = 'quote-form';
			return $sections;
		}
		array_splice( $sections, $pos + 1, 0, 'quote-form' );
		return $sections;
	}
);

/**
 * Deferred lazy-loader for the iframe — front page only, when active.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! showtime_quote_active() ) {
			return;
		}
		[ $uri, $ver ] = showtime_asset( 'assets/js/quote-form.js' );
		wp_enqueue_script( 'showtime-quote-form', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

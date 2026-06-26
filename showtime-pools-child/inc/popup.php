<?php
/**
 * Sitewide "Weekly Maintenance" lead popup wiring.
 *
 * Renders the popup markup in wp_footer and enqueues its deferred JS, gated by
 * a CMS toggle (Showtime Pools → Site Content → Homepage) and excluded on the
 * active-conversion templates (booking/quote iframe + contact) where a second
 * lead form would only compete. The /book/ booking calendar is never touched.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * GHL popup form URL with popup UTM. Filterable so the form/UTM can change in
 * one place without editing the template part.
 */
function showtime_popup_form_url(): string {
	// URL is a CMS field (Showtime → Settings → "GHL Popup Form URL"); falls back
	// to the bundled default form so the popup always has a form to show.
	$base = trim( (string) get_option( 'showtime_popup_form_url', '' ) );
	if ( '' === $base ) {
		$base = 'https://app.showtimepoolmechanics.com/widget/form/pZm1SEhLB9YMX21EvIV5';
	}
	$url  = add_query_arg(
		array(
			'utm_source'  => 'website',
			'utm_medium'  => 'popup',
			'utm_content' => 'weekly_maintenance',
		),
		$base
	);
	return (string) apply_filters( 'showtime/popup/form_url', $url );
}

/**
 * Whether the popup should load on the current request. Off when the CMS toggle
 * is disabled, on the conversion templates, or in the admin/login context.
 */
function showtime_popup_active(): bool {
	if ( is_admin() ) {
		return false;
	}
	// CMS toggle — default ON. Stored standalone so the theme reads it directly.
	if ( '0' === (string) get_option( 'showtime_popup_enabled', '1' ) ) {
		return false;
	}
	// Don't compete with the primary form on conversion pages.
	if ( is_page_template( array( 'page-iframe.php', 'page-contact.php' ) ) ) {
		return false;
	}
	return (bool) apply_filters( 'showtime/popup/active', true );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! showtime_popup_active() ) {
			return;
		}
		[ $uri, $ver ] = showtime_asset( 'assets/js/popup.js' );
		wp_enqueue_script( 'showtime-popup', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

add_action(
	'wp_footer',
	function () {
		if ( ! showtime_popup_active() ) {
			return;
		}
		get_template_part( 'template-parts/global/popup-weekly' );
	}
);

<?php
/**
 * Sitewide delayed "Get a Free Estimate" popup wiring.
 *
 * Replaces the former wp-admin-toggled "Weekly Maintenance" popup, which was
 * driven by a CMS option and an exit-intent/30s-dwell trigger and embedded a
 * GHL form <iframe> inside the modal. Nothing of that popup remains: its
 * template part, its option reads and its stylesheet block are gone, so no old
 * modal can render above or beneath this one.
 *
 * This popup is theme-controlled only — no option, no admin screen, no shortcode.
 * It renders once in wp_footer, opens 15s after load, is dismissed for the rest
 * of the browser session via sessionStorage, and never embeds the GHL calendar:
 * the primary CTA is a same-tab link to the booking widget, so nothing from
 * app.showtimepoolmechanics.com is requested until the visitor clicks.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * The booking destination for the primary CTA.
 *
 * A same-tab navigation target, never an embed source. Filterable so the
 * destination can move in one place without touching the template part.
 */
function showtime_popup_booking_url(): string {
	return (string) apply_filters(
		'showtime/popup/booking_url',
		'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb'
	);
}

/**
 * The tel: destination for the secondary CTA.
 */
function showtime_popup_tel_url(): string {
	return (string) apply_filters( 'showtime/popup/tel_url', 'tel:+13238252099' );
}

/**
 * Whether the popup should load on the current request.
 *
 * Fails closed. Every non-page context — admin, login, AJAX, REST, cron,
 * WP-CLI, XML-RPC, feeds, embeds and the XML sitemap — resolves to false, so
 * the markup and its assets can only ever reach a public frontend page.
 */
function showtime_popup_active(): bool {
	if ( is_admin()
		|| wp_doing_ajax()
		|| wp_doing_cron()
		|| ( defined( 'WP_CLI' ) && WP_CLI )
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		|| ( function_exists( 'is_login' ) && is_login() )
		|| is_feed()
		|| is_embed()
		|| '' !== (string) get_query_var( 'sitemap', '' )
		|| '' !== (string) get_query_var( 'sitemap-stylesheet', '' )
	) {
		return false;
	}

	// Don't compete with the primary form on the active-conversion templates.
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

		// Dedicated sheet + deferred script, each enqueued exactly once. No
		// inline style or listener is printed per template.
		[ $css_uri, $css_ver ] = showtime_asset( 'assets/css/popup.css' );
		wp_enqueue_style( 'showtime-popup', $css_uri, array( 'showtime-components' ), $css_ver );

		[ $js_uri, $js_ver ] = showtime_asset( 'assets/js/popup.js' );
		wp_enqueue_script( 'showtime-popup', $js_uri, array(), $js_ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

add_action(
	'wp_footer',
	function () {
		if ( ! showtime_popup_active() ) {
			return;
		}
		get_template_part( 'template-parts/global/popup-estimate' );
	}
);

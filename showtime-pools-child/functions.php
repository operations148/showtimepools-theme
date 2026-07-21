<?php

/**
 * Showtime Pools Child Theme — bootstrap.
 *
 * @package ShowtimePools
 */

defined('ABSPATH') || exit;

define('SHOWTIME_CHILD_VERSION', '0.1.0');

/**
 * Code-first edit mode. When true, display content + images render from the
 * theme code (template PHP defaults, bundled assets/img files, Unsplash slot
 * map) instead of the WordPress database (ACF option fields + Site Images /
 * Site Content wp_options). Lets us design by editing code and see it live.
 *
 * Set to false to "plug back into WordPress" — all ACF/option content and
 * CMS-uploaded images return with no other changes. Config (booking/form URLs,
 * consent, popup, reviews shortcodes, UTM) is read via get_option() and is
 * never affected by this switch. Per-post CPT content (individual project /
 * service detail pages) is also untouched — it has no code source.
 */
if ( ! defined( 'SHOWTIME_CODE_FIRST' ) ) {
	define( 'SHOWTIME_CODE_FIRST', false );
}

/** GHL booking widget URL — embedded on /book/ (page-iframe.php). */
define('SHOWTIME_BOOKING_URL', 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb');

/** GHL contact form URL — embedded on /contact/ (page-contact.php). Default
 *  used when the "GHL Contact Form URL" CMS field is left blank. */
define('SHOWTIME_CONTACT_FORM_URL', 'https://app.showtimepoolmechanics.com/widget/form/tH1eoDpRA4hMEb04GgzX');

/**
 * Filterable booking destination for every CTA. Points at the on-domain
 * /book/ page, which hosts the GHL widget above, so visitors book without
 * leaving the site. Override via the `showtime/booking_url` filter.
 */
function showtime_booking_url(): string {
	return (string) apply_filters( 'showtime/booking_url', home_url( '/book/' ) );
}

/**
 * EstimatorPro tool URL — used by the homepage estimator strip CTA.
 * Update in one place via the `showtime/estimator_url` filter (or swap the
 * default string below) rather than editing the template.
 */
function showtime_estimator_url(): string {
	return (string) apply_filters( 'showtime/estimator_url', 'https://estimatorpro-widget.vercel.app/embed-example.html?key=d7ad907f6911837b9be9383fa36bf7ff' );
}

/**
 * "Last reviewed" freshness date shown on the service AEO "at a glance" block.
 * One filterable source (`showtime/aeo/reviewed`) so it can be bumped in a
 * single place when service content is reviewed, rather than dated per page.
 */
function showtime_aeo_reviewed_date(): string {
	return (string) apply_filters( 'showtime/aeo/reviewed', 'July 2026' );
}

/**
 * One centralized telephone value for the whole theme.
 *
 * Display form: (323) 825-2099  — human-readable, used in copy + tel: labels.
 * Machine form: +13238252099    — strict E.164, used in every schema node.
 *
 * The E.164 value is DERIVED from the display value (single source of truth),
 * so the two can never drift and no generator hardcodes its own phone string.
 * This is what keeps Service, LocalBusiness, ContactPoint, and branch schema
 * on the same valid number, and guards against the malformed, double-dashed
 * E.164 variant seen in the production (Search Atlas OTTO) schema layer.
 */
function showtime_phone_display(): string {
	return (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
}

function showtime_phone_e164(): string {
	$digits  = preg_replace( '/\D/', '', preg_replace( '/^\+?1/', '', showtime_phone_display() ) );
	$default = '+1' . $digits;
	return (string) apply_filters( 'showtime/business/phone_e164', $default );
}

/**
 * Global timeline helper text shown once beneath the "Typical timeframe" on
 * every individual service page. Defined in one filterable place
 * (`showtime/timeline_helper`) so the caveat wording is never duplicated per
 * service. The per-service timeline value + detail live in the service
 * registry (default_turnaround / default_turnaround_detail); this is the
 * shared disclaimer that applies to all of them.
 */
function showtime_timeline_helper(): string {
	return (string) apply_filters(
		'showtime/timeline_helper',
		__( 'Typical timeframes are planning estimates, not guaranteed completion dates. Final timing is confirmed after site conditions, scope, materials, scheduling, and permit requirements are reviewed. Weather, inspection availability, access, change orders, special orders, and concealed conditions may extend completion.', 'showtime-pools' )
	);
}

/**
 * Resolve which top-level nav item should be active for the current request.
 *
 * Returns one of: 'home', 'about', 'services', 'projects', 'service-areas',
 * 'contact', 'shop', or '' (none). Children map to their parent section by
 * page ancestry, so /services/* highlights Services, /service-areas/*
 * highlights Service Areas, project singles highlight Projects, and blog
 * content highlights About (the Blog link lives in the About menu). Used by
 * the fallback primary nav and mobile drawer; WordPress menus keep their own
 * current-menu-* classes.
 */
function showtime_nav_active_section(): string {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( 'project' ) || is_post_type_archive( 'project' ) ) {
		return 'projects';
	}
	if ( is_singular( 'post' ) || is_home() || is_category() || is_tag() || is_author() || is_date() ) {
		return 'about';
	}
	if ( is_page() ) {
		$id    = get_queried_object_id();
		$chain = array( (string) get_post_field( 'post_name', $id ) );
		foreach ( get_post_ancestors( $id ) as $ancestor ) {
			$chain[] = (string) get_post_field( 'post_name', $ancestor );
		}
		if ( in_array( 'services', $chain, true ) )                                  { return 'services'; }
		if ( in_array( 'service-areas', $chain, true ) )                             { return 'service-areas'; }
		if ( in_array( 'projects', $chain, true ) )                                  { return 'projects'; }
		if ( array_intersect( array( 'about', 'the-founder', 'blog' ), $chain ) )    { return 'about'; }
		if ( array_intersect( array( 'contact', 'quote', 'book' ), $chain ) )        { return 'contact'; }
		if ( in_array( 'shop', $chain, true ) )                                      { return 'shop'; }
	}
	return '';
}

define('SHOWTIME_CHILD_DIR', get_stylesheet_directory());
define('SHOWTIME_CHILD_URI', get_stylesheet_directory_uri());

require_once SHOWTIME_CHILD_DIR . '/inc/code-first.php';
require_once SHOWTIME_CHILD_DIR . '/inc/theme-setup.php';
require_once SHOWTIME_CHILD_DIR . '/inc/enqueue.php';
require_once SHOWTIME_CHILD_DIR . '/inc/blocksy-overrides.php';
require_once SHOWTIME_CHILD_DIR . '/inc/security.php';
require_once SHOWTIME_CHILD_DIR . '/inc/performance.php';
require_once SHOWTIME_CHILD_DIR . '/inc/imagery.php';
require_once SHOWTIME_CHILD_DIR . '/inc/acf-readers.php';
require_once SHOWTIME_CHILD_DIR . '/inc/seo.php';
require_once SHOWTIME_CHILD_DIR . '/inc/seo-defaults.php';
require_once SHOWTIME_CHILD_DIR . '/inc/crawl.php';
require_once SHOWTIME_CHILD_DIR . '/inc/site-icon.php';
require_once SHOWTIME_CHILD_DIR . '/inc/meta-fields.php';
require_once SHOWTIME_CHILD_DIR . '/inc/reviews-widget.php';
require_once SHOWTIME_CHILD_DIR . '/inc/popup.php';
require_once SHOWTIME_CHILD_DIR . '/inc/consent.php';

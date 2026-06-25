<?php

/**
 * Showtime Pools Child Theme — bootstrap.
 *
 * @package ShowtimePools
 */

defined('ABSPATH') || exit;

define('SHOWTIME_CHILD_VERSION', '0.1.0');

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

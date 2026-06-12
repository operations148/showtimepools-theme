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

/**
 * Filterable booking destination for every CTA. Points at the on-domain
 * /book/ page, which hosts the GHL widget above, so visitors book without
 * leaving the site. Override via the `showtime/booking_url` filter.
 */
function showtime_booking_url(): string {
	return (string) apply_filters( 'showtime/booking_url', home_url( '/book/' ) );
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

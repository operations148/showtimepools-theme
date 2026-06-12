<?php

/**
 * Showtime Pools Child Theme — bootstrap.
 *
 * @package ShowtimePools
 */

defined('ABSPATH') || exit;

define('SHOWTIME_CHILD_VERSION', '0.1.0');

/** GHL booking URL — single source of truth. Replaces the old /quote/ page. */
define('SHOWTIME_BOOKING_URL', 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb');

/**
 * Filterable booking URL. Templates call this instead of the raw constant so
 * the destination can be overridden via the `showtime/booking_url` filter
 * without editing every CTA.
 */
function showtime_booking_url(): string {
	return (string) apply_filters( 'showtime/booking_url', SHOWTIME_BOOKING_URL );
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

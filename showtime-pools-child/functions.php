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
define('SHOWTIME_CHILD_DIR', get_stylesheet_directory());
define('SHOWTIME_CHILD_URI', set_url_scheme(get_stylesheet_directory_uri(), 'https'));

require_once SHOWTIME_CHILD_DIR . '/inc/theme-setup.php';
require_once SHOWTIME_CHILD_DIR . '/inc/enqueue.php';
require_once SHOWTIME_CHILD_DIR . '/inc/blocksy-overrides.php';
require_once SHOWTIME_CHILD_DIR . '/inc/security.php';
require_once SHOWTIME_CHILD_DIR . '/inc/performance.php';
require_once SHOWTIME_CHILD_DIR . '/inc/imagery.php';
require_once SHOWTIME_CHILD_DIR . '/inc/acf-readers.php';
require_once SHOWTIME_CHILD_DIR . '/inc/seo.php';
require_once SHOWTIME_CHILD_DIR . '/inc/seo-defaults.php';
require_once SHOWTIME_CHILD_DIR . '/inc/site-icon.php';

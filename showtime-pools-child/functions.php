<?php
/**
 * Showtime Pools Child Theme — bootstrap.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

define( 'SHOWTIME_CHILD_VERSION', '0.1.0' );
define( 'SHOWTIME_CHILD_DIR', get_stylesheet_directory() );
define( 'SHOWTIME_CHILD_URI', get_stylesheet_directory_uri() );

require_once SHOWTIME_CHILD_DIR . '/inc/theme-setup.php';
require_once SHOWTIME_CHILD_DIR . '/inc/enqueue.php';
require_once SHOWTIME_CHILD_DIR . '/inc/blocksy-overrides.php';
require_once SHOWTIME_CHILD_DIR . '/inc/security.php';
require_once SHOWTIME_CHILD_DIR . '/inc/performance.php';
require_once SHOWTIME_CHILD_DIR . '/inc/imagery.php';
require_once SHOWTIME_CHILD_DIR . '/inc/acf-readers.php';
require_once SHOWTIME_CHILD_DIR . '/inc/seo.php';

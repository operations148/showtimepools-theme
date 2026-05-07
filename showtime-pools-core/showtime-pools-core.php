<?php
/**
 * Plugin Name:       Showtime Pools — Core
 * Plugin URI:        https://showtimepools.com
 * Description:       CPTs, REST endpoints, GHL/GBP/OpenAI/Mapbox integrations, and admin settings for showtimepools.com. Theme-agnostic business logic — survives a theme swap.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            Showtime Pools Dev
 * Author URI:        https://showtimepools.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       showtime-pools-core
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

define( 'SHOWTIME_CORE_VERSION', '0.1.0' );
define( 'SHOWTIME_CORE_FILE', __FILE__ );
define( 'SHOWTIME_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHOWTIME_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Minimal PSR-4-style autoloader. We don't ship Composer in the bundle to keep
 * dependencies zero. Class names map: Showtime\Foo\Bar → includes/foo/class-bar.php
 */
spl_autoload_register(
	function ( string $class ) {
		if ( strpos( $class, 'Showtime\\' ) !== 0 ) {
			return;
		}
		$relative = substr( $class, strlen( 'Showtime\\' ) );
		$parts    = explode( '\\', $relative );
		$file     = array_pop( $parts );
		$file     = 'class-' . strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $file ) ) . '.php';
		$path     = SHOWTIME_CORE_DIR . 'includes/' . strtolower( implode( '/', $parts ) ) . ( $parts ? '/' : '' ) . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

require_once SHOWTIME_CORE_DIR . 'includes/class-plugin.php';

register_activation_hook(
	__FILE__,
	function () {
		// Flush rewrites so CPT permalinks resolve immediately on activation.
		// CPT registration runs on `init` so we trigger the bootstrap once first.
		\Showtime\Plugin::instance()->register();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		flush_rewrite_rules();
	}
);

add_action(
	'plugins_loaded',
	function () {
		\Showtime\Plugin::instance()->register();
	}
);

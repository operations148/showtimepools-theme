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
		// Boot subsystems so CPTs / nav / customizer hooks register before
		// the first-run setup runs (the seeder reads from \Showtime\Services).
		\Showtime\Plugin::instance()->register();

		// One-time first-run setup. Gated by a wp_options flag so the seed
		// never runs twice — reactivating the plugin won't re-create pages.
		// The 'showtime_first_run_complete' flag is the source of truth; to
		// re-run after manual cleanup, delete that option from wp_options.
		if ( ! get_option( 'showtime_first_run_complete' ) ) {

			// Permalinks → /post-name/. WP defaults to plain ?p=123 on a fresh
			// install; we need pretty permalinks for our slug-based templates
			// to resolve (page-service.php, page-area.php, etc.).
			if ( '' === (string) get_option( 'permalink_structure' ) ) {
				update_option( 'permalink_structure', '/%postname%/' );
			}

			// Brand the WP install. We only set these on first run — never
			// overwrite a value the site owner has already customized.
			if ( in_array( get_option( 'blogname' ), array( '', 'My Site', 'Just another WordPress site' ), true ) ) {
				update_option( 'blogname', 'Showtime Pools' );
			}
			if ( '' === (string) get_option( 'timezone_string' ) ) {
				update_option( 'timezone_string', 'America/Los_Angeles' );
			}

			// Seed every structural page idempotently. Existing pages by slug
			// are skipped — safe to re-run after deleting the flag.
			$seeder = new \Showtime\Admin\PageSeeder();
			$result = $seeder->run_all_idempotent();

			update_option( 'showtime_first_run_complete', '1' );
			update_option( 'showtime_first_run_result', $result );
			update_option( 'showtime_first_run_at', gmdate( 'c' ) );
		}

		// Always flush on activation so newly registered CPT / page slugs
		// resolve immediately without a manual Settings → Permalinks → Save.
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

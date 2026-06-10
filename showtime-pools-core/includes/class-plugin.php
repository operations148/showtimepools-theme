<?php
/**
 * Main plugin bootstrap. Singleton so activation + plugins_loaded hit the
 * same instance. Each subsystem (CPTs, REST, integrations, admin) owns its
 * own class and registers its own hooks; this class just wires them up.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static ?Plugin $instance = null;

	private bool $registered = false;

	public static function instance(): Plugin {
		return self::$instance ??= new self();
	}

	private function __construct() {}

	/**
	 * Wire all subsystems. Idempotent — safe to call from both activation
	 * hook and plugins_loaded.
	 */
	public function register(): void {
		if ( $this->registered ) {
			return;
		}

		// Subsystems get added here as we build them in later phases.
		// Phase 1B ships the bootstrap only — concrete subsystems land in
		// Phase 1H (GHL webhook handler), 1I (chat), 2A (Project CPT + Mapbox),
		// 2B (Review CPT + GBP), 2C (Gallery CPT).

		if ( is_admin() ) {
			( new Admin\SettingsPage() )->register();
			( new Admin\ContentPage() )->register();
			( new Admin\PageSeeder() )->register();
			( new Admin\ToolsPage() )->register();
		}

		// Seeder hooks (WP-CLI + admin-post) need to register on every request,
		// not only inside is_admin() — admin-post.php fires before is_admin()
		// resolves to true for non-page requests.
		( new Admin\Seeder() )->register();

		// Customizer is registered on EVERY request (not just admin) — its
		// `customize_register` hook only fires inside the Customizer, but
		// the filter bridges (showtime/business/*) need to hook on the
		// frontend so live values render in templates.
		( new Admin\Customizer() )->register();

		// ACF options page — "Site Content" menu in WP admin. Hooks into
		// `acf/init` so it self-skips if ACF Pro isn't active.
		( new Admin\OptionsPage() )->register();

		// REST endpoints.
		( new Rest\ContactController() )->register();
		( new Rest\AffiliateController() )->register();

		// Integrations.
		( new Integrations\FluentForms() )->register();

		// CPTs (Phase C — Project CPT for /projects/ + future Mapbox).
		// The CPT registers `public => true`, so WP core's /wp-sitemap.xml
		// includes it automatically; no sitemap filter needed.
		( new Cpt\Project() )->register();

		$this->registered = true;
	}
}

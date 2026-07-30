<?php
/**
 * Projects accessor — memoized read of the seed registry.
 *
 * Templates query the live `project` CPT directly; the seed registry is
 * used by the page seeder to populate the CPT on first run.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Projects {

	/** @var array<int, array<string, mixed>>|null */
	private static ?array $cache = null;

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array {
		if ( null === self::$cache ) {
			$path = SHOWTIME_CORE_DIR . '/includes/data/projects.php';
			self::$cache = file_exists( $path ) ? (array) include $path : array();
		}
		return self::$cache;
	}

	/**
	 * Look a seed entry up by slug. Mirrors Services::get() / Areas::get().
	 *
	 * The seeder copies scalar fields into post meta on first run, so the
	 * registry stays the fallback for anything added to the data file
	 * *after* seeding (the comparison block, for one) — that content would
	 * otherwise be unreachable without re-running a one-time seeder.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get( string $slug ): ?array {
		foreach ( self::all() as $project ) {
			if ( ( $project['slug'] ?? '' ) === $slug ) {
				return $project;
			}
		}
		return null;
	}
}

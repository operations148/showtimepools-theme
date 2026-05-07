<?php
/**
 * Services registry helper. Wraps the data file in `data/services.php` with
 * memoization so we never re-parse it within a single request, and exposes
 * convenience getters used by the page seeder, the service template, and
 * any future block/REST controller.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Services {

	/**
	 * @var array<int, array<string, mixed>>|null
	 */
	private static ?array $cache = null;

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}

		$path = SHOWTIME_CORE_DIR . 'includes/data/services.php';
		$data = file_exists( $path ) ? require $path : array();

		/**
		 * Filter the full services registry.
		 *
		 * @param array<int, array<string, mixed>> $data
		 */
		self::$cache = (array) apply_filters( 'showtime/services', $data );
		return self::$cache;
	}

	/**
	 * Resolve a service entry by slug.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get( string $slug ): ?array {
		foreach ( self::all() as $svc ) {
			if ( ( $svc['slug'] ?? '' ) === $slug ) {
				return $svc;
			}
		}
		return null;
	}

	/**
	 * Slugs only (used by the seeder + cross-link generators).
	 *
	 * @return string[]
	 */
	public static function slugs(): array {
		return array_values( array_filter( array_map( static fn( $s ) => $s['slug'] ?? null, self::all() ) ) );
	}

	/**
	 * Related services (everything except the given slug).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function related( string $exclude_slug, int $limit = 3 ): array {
		$rest = array_values( array_filter( self::all(), static fn( $s ) => ( $s['slug'] ?? '' ) !== $exclude_slug ) );
		return array_slice( $rest, 0, max( 0, $limit ) );
	}
}

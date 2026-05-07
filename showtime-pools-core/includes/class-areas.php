<?php
/**
 * Service-area registry helper. Memoized accessor over data/areas.php.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Areas {

	/** @var array<int, array<string, mixed>>|null */
	private static ?array $cache = null;

	/** @return array<int, array<string, mixed>> */
	public static function all(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}
		$path        = SHOWTIME_CORE_DIR . 'includes/data/areas.php';
		$data        = file_exists( $path ) ? require $path : array();
		self::$cache = (array) apply_filters( 'showtime/areas', $data );
		return self::$cache;
	}

	/** @return array<string, mixed>|null */
	public static function get( string $slug ): ?array {
		foreach ( self::all() as $area ) {
			if ( ( $area['slug'] ?? '' ) === $slug ) {
				return $area;
			}
		}
		return null;
	}

	/** @return string[] */
	public static function slugs(): array {
		return array_values( array_filter( array_map( static fn( $a ) => $a['slug'] ?? null, self::all() ) ) );
	}
}

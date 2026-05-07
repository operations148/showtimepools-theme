<?php
/**
 * Inspections registry helper. Memoized accessor over data/inspections.php.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Inspections {

	/** @var array<int, array<string, mixed>>|null */
	private static ?array $cache = null;

	/** @return array<int, array<string, mixed>> */
	public static function all(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}
		$path        = SHOWTIME_CORE_DIR . 'includes/data/inspections.php';
		$data        = file_exists( $path ) ? require $path : array();
		self::$cache = (array) apply_filters( 'showtime/inspections', $data );
		return self::$cache;
	}

	/** @return array<string, mixed>|null */
	public static function get( string $slug ): ?array {
		foreach ( self::all() as $i ) {
			if ( ( $i['slug'] ?? '' ) === $slug ) {
				return $i;
			}
		}
		return null;
	}

	/** @return string[] */
	public static function slugs(): array {
		return array_values( array_filter( array_map( static fn( $i ) => $i['slug'] ?? null, self::all() ) ) );
	}
}

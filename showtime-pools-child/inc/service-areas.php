<?php
/**
 * Service-area CARDS — the one list both service-area surfaces render.
 *
 * The /service-areas/ hub and the homepage marquee used to loop the Areas
 * registry directly, so both showed only the 9 neighborhoods that have a
 * published area landing page. The canonical location set is the 14-project
 * registry, so this resolver joins the two:
 *
 *   - A location WITH a published area page (`area_url` set on its project
 *     record, and a matching Areas record) keeps exactly what it had: the same
 *     area image, the approved `tag` copy, its pool-count pill, its gradient,
 *     and its /service-areas/<slug>/ destination.
 *
 *   - A location WITHOUT one links to its project page instead, using the SAME
 *     cover photograph the Projects archive already shows for it. No route is
 *     invented: `/service-areas/<slug>/` is not published for these, so it is
 *     never linked. These cards carry NO pool count, schedule or service
 *     frequency — none is recorded for them, and inventing one would be a
 *     claim — only the neutral, non-numeric sub-line below.
 *
 * Order is stable and derived: the 9 existing cards keep their Areas-registry
 * order, then the remaining locations follow in project-registry order. Adding
 * an area record for one of the five promotes its card automatically, with no
 * template edit.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sub-line used by a location that has no published service-area page. Neutral
 * and non-numeric on purpose: it describes where the card goes, and asserts no
 * schedule, frequency or volume.
 */
const SHOWTIME_AREA_CARD_PROJECT_SUB = 'Recent project';

/**
 * The canonical service-area card set — one entry per managed project location.
 *
 * @return array<int,array{
 *     slug:string, name:string, url:string, image:string, alt:string,
 *     sub:string, pool_count:string, gradient:string, has_area_page:bool
 * }>
 */
function showtime_service_area_cards(): array {
	static $cards = null;
	if ( null !== $cards ) {
		return $cards;
	}

	$default_gradient = 'linear-gradient(135deg,#1F2F3A,#5C8A9E)';

	// Areas registry, keyed by slug, for the locations that DO have a page.
	$areas = array();
	if ( class_exists( '\\Showtime\\Areas' ) ) {
		foreach ( \Showtime\Areas::all() as $a ) {
			$areas[ (string) ( $a['slug'] ?? '' ) ] = $a;
		}
	}

	// Project registry — the canonical location set, and the source of the
	// cover photograph each location shows on the Projects archive.
	$projects = function_exists( 'showtime_project_cards' ) ? showtime_project_cards() : array();

	$with_page = array();
	$without   = array();

	foreach ( $projects as $card ) {
		$name = trim( (string) ( $card['neighborhood'] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}

		// A location has a published area page only when its project record
		// carries an area_url AND the Areas registry actually holds that slug.
		$data      = function_exists( 'showtime_project_data' ) ? showtime_project_data( (string) ( $card['slug'] ?? '' ) ) : null;
		$area_url  = trim( (string) ( $data['area_url'] ?? '' ) );
		$area_slug = '' !== $area_url ? trim( (string) $area_url, '/' ) : '';
		$area_slug = '' !== $area_slug ? (string) substr( $area_slug, (int) strrpos( $area_slug, '/' ) + 1 ) : '';

		if ( '' !== $area_slug && isset( $areas[ $area_slug ] ) ) {
			$area = $areas[ $area_slug ];
			$img  = function_exists( 'showtime_image' ) ? (string) showtime_image( 'area_' . $area_slug, 800 ) : '';

			$with_page[ $area_slug ] = array(
				'slug'          => $area_slug,
				'name'          => (string) ( $area['name'] ?? $name ),
				'url'           => home_url( '/service-areas/' . $area_slug . '/' ),
				'image'         => $img,
				'alt'           => sprintf( /* translators: %s: neighborhood */ __( 'Pool service in %s', 'showtime-pools' ), (string) ( $area['name'] ?? $name ) ),
				'sub'           => (string) ( $area['tag'] ?? '' ),
				'pool_count'    => (string) ( $area['pool_count'] ?? '' ),
				'gradient'      => (string) ( $area['gradient'] ?? $default_gradient ),
				'has_area_page' => true,
			);
			continue;
		}

		$without[] = array(
			'slug'          => (string) ( $card['slug'] ?? '' ),
			'name'          => $name,
			'url'           => (string) ( $card['href'] ?? '' ),
			'image'         => (string) ( $card['image'] ?? '' ),
			'alt'           => sprintf( /* translators: %s: neighborhood */ __( 'Pool project in %s', 'showtime-pools' ), $name ),
			'sub'           => SHOWTIME_AREA_CARD_PROJECT_SUB,
			'pool_count'    => '',
			'gradient'      => $default_gradient,
			'has_area_page' => false,
		);
	}

	// Existing cards first, in Areas-registry order, so the published nine keep
	// the order they already had; the rest follow in project-registry order.
	$ordered = array();
	foreach ( array_keys( $areas ) as $slug ) {
		if ( isset( $with_page[ $slug ] ) ) {
			$ordered[] = $with_page[ $slug ];
		}
	}
	foreach ( $without as $card ) {
		if ( '' !== $card['url'] ) {
			$ordered[] = $card;
		}
	}

	$cards = $ordered;
	return $cards;
}

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
 * Slugs whose /service-areas/<slug>/ child page actually exists and is PUBLISHED.
 *
 * Theme code deploys before the WordPress page records are created, so a
 * registry entry is never proof that its URL resolves. Every surface that
 * advertises an area URL — the cards, the HTML sitemap, llms.txt and
 * llms-full.txt — asks this first, so a link is published only once the page
 * behind it is live, and a 404 is never exposed. Nothing here depends on
 * WordPress' fuzzy 404 guessing.
 *
 * One query for all of them, memoized per request.
 *
 * @return array<string,true> Set of published area slugs, keyed by slug.
 */
function showtime_published_area_slugs(): array {
	static $published = null;
	if ( null !== $published ) {
		return $published;
	}

	$published = array();

	$parent = get_page_by_path( 'service-areas' );
	if ( ! $parent instanceof WP_Post ) {
		return $published;
	}

	$children = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'post_parent'      => $parent->ID,
			'posts_per_page'   => -1,
			'no_found_rows'    => true,
			'suppress_filters' => false,
			'fields'           => 'ids',
		)
	);

	foreach ( $children as $child_id ) {
		$name = (string) get_post_field( 'post_name', $child_id );
		if ( '' !== $name ) {
			$published[ $name ] = true;
		}
	}

	return $published;
}

/**
 * Whether an area's landing page is published and therefore safe to link.
 */
function showtime_area_page_is_published( string $slug ): bool {
	if ( '' === $slug ) {
		return false;
	}
	return isset( showtime_published_area_slugs()[ $slug ] );
}

/**
 * The canonical area URL when its page is live, or '' when it is not yet.
 */
function showtime_area_url( string $slug ): string {
	return showtime_area_page_is_published( $slug )
		? home_url( '/service-areas/' . $slug . '/' )
		: '';
}

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

	// Project registry — the source of each location's cover photograph and of
	// the fallback destination used until its area page is live. Indexed by the
	// project slug so an area can name its own.
	$projects = array();
	if ( function_exists( 'showtime_project_cards' ) ) {
		foreach ( showtime_project_cards() as $card ) {
			$projects[ (string) ( $card['slug'] ?? '' ) ] = $card;
		}
	}

	// Resolve an area slug to its project. New records name it outright; the
	// original nine are matched through their project's area_url, which is how
	// that relationship has always been recorded.
	$project_for_area = array();
	foreach ( $projects as $p_slug => $card ) {
		$data     = function_exists( 'showtime_project_data' ) ? showtime_project_data( (string) $p_slug ) : null;
		$area_url = trim( (string) ( $data['area_url'] ?? '' ) );
		if ( '' === $area_url ) {
			continue;
		}
		$a_slug = trim( $area_url, '/' );
		$a_slug = (string) substr( $a_slug, (int) strrpos( $a_slug, '/' ) + 1 );
		if ( '' !== $a_slug ) {
			$project_for_area[ $a_slug ] = (string) $p_slug;
		}
	}

	// Order comes from the Areas registry and NOTHING else, so publishing a page
	// changes that card's destination without ever reshuffling the grid.
	$ordered = array();
	$areas   = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();

	foreach ( $areas as $area ) {
		$slug = (string) ( $area['slug'] ?? '' );
		$name = (string) ( $area['name'] ?? '' );
		if ( '' === $slug || '' === $name ) {
			continue;
		}

		// `related_project` is declared only by the records added alongside the
		// five new pages. The original nine resolve theirs through area_url, and
		// that lookup is used ONLY for the fallback destination — their card
		// imagery stays on the area_<slug> slot it has always used.
		$declared_project = (string) ( $area['related_project'] ?? '' );
		$p_slug           = '' !== $declared_project ? $declared_project : (string) ( $project_for_area[ $slug ] ?? '' );
		$project          = $projects[ $p_slug ] ?? null;

		// Live page → canonical area URL. Not yet → the project page, so the
		// card always points at something that actually resolves.
		$area_url  = showtime_area_url( $slug );
		$is_live   = '' !== $area_url;
		$url       = $is_live ? $area_url : (string) ( $project['href'] ?? '' );
		if ( '' === $url ) {
			continue;
		}

		// A record that declares a related project shows that project's cover —
		// the same image the Projects archive uses — in both states. Every other
		// area keeps resolving its own area_<slug> image slot, untouched.
		if ( $project && '' !== $declared_project ) {
			$image = (string) $project['image'];
			$alt   = $is_live
				? sprintf( /* translators: %s: neighborhood */ __( 'Pool service in %s', 'showtime-pools' ), $name )
				: sprintf( /* translators: %s: neighborhood */ __( 'Pool project in %s', 'showtime-pools' ), $name );
		} else {
			$image = function_exists( 'showtime_image' ) ? (string) showtime_image( 'area_' . $slug, 800 ) : '';
			$alt   = sprintf( /* translators: %s: neighborhood */ __( 'Pool service in %s', 'showtime-pools' ), $name );
		}

		$ordered[] = array(
			'slug'          => $slug,
			'name'          => $name,
			'url'           => $url,
			'image'         => $image,
			'alt'           => $alt,
			'sub'           => $is_live ? (string) ( $area['tag'] ?? '' ) : SHOWTIME_AREA_CARD_PROJECT_SUB,
			'pool_count'    => $is_live ? (string) ( $area['pool_count'] ?? '' ) : '',
			'gradient'      => (string) ( $area['gradient'] ?? $default_gradient ),
			'has_area_page' => $is_live,
		);
	}

	$cards = $ordered;
	return $cards;
}

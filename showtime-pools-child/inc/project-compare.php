<?php
/**
 * Project resolver + before/after comparison data for the `project` CPT.
 *
 * showtime_project_data() is THE shared resolver. For a project with a
 * `managed => true` entry in showtime-pools-core/includes/data/projects.php,
 * it returns the registry data and WordPress post meta is never consulted —
 * every surface (archive, single page, related cards, homepage strip,
 * before/after, OG/Twitter, CreativeWork) reads through this one function.
 *
 * Unmanaged/legacy posts (no matching managed entry) return null here. A
 * couple of call sites keep a historical post-meta fallback so those old seed
 * rows still render something if hit directly, but showtime_unmanaged_project_ids()
 * below keeps them out of every discovery surface, both sitemaps, and search
 * indexing — their copy was written once by the one-time seeder and never
 * passed the truth-safety review the managed registry enforces.
 *
 * Fails closed: showtime_project_compare() returns null unless BOTH images
 * resolve to a usable URL with real pixel dimensions. Callers render nothing
 * on null — no partial markup, no empty figure, no warning.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalize one image input into the shape the template renders.
 *
 * Accepts an ACF image array, an attachment ID, or a same-origin URL/path.
 * Width and height must be real — an image whose dimensions cannot be
 * determined is rejected rather than guessed, because wrong width/height
 * attributes cause layout shift instead of preventing it.
 *
 * @param mixed  $input Raw image value.
 * @param string $alt   Factual alt text supplied by the caller.
 * @return array{url:string,width:int,height:int,alt:string,srcset:string,sizes:string}|null
 */
function showtime_project_compare_image( $input, string $alt = '' ): ?array {
	$url = '';
	$w   = 0;
	$h   = 0;
	$srcset = '';

	// ACF image array.
	if ( is_array( $input ) && ! empty( $input['url'] ) ) {
		$large = $input['sizes']['large'] ?? '';
		$url   = (string) ( $large ?: $input['url'] );
		if ( $large ) {
			$w = (int) ( $input['sizes']['large-width'] ?? 0 );
			$h = (int) ( $input['sizes']['large-height'] ?? 0 );
		}
		if ( ! $w || ! $h ) {
			$w = (int) ( $input['width'] ?? 0 );
			$h = (int) ( $input['height'] ?? 0 );
		}
		if ( '' === $alt ) {
			$alt = (string) ( $input['alt'] ?? '' );
		}
		if ( ! empty( $input['ID'] ) ) {
			$srcset = (string) ( wp_get_attachment_image_srcset( (int) $input['ID'], 'large' ) ?: '' );
		}
	} elseif ( is_numeric( $input ) && (int) $input > 0 ) {
		// Attachment ID.
		$id  = (int) $input;
		$src = wp_get_attachment_image_src( $id, 'large' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			$url    = (string) $src[0];
			$w      = (int) $src[1];
			$h      = (int) $src[2];
			$srcset = (string) ( wp_get_attachment_image_srcset( $id, 'large' ) ?: '' );
			if ( '' === $alt ) {
				$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			}
		}
	} elseif ( is_string( $input ) && '' !== trim( $input ) ) {
		// Same-origin URL or absolute path (used by the local preview harness).
		$raw = trim( $input );
		if ( 0 === strpos( $raw, 'http' ) ) {
			$url  = $raw;
			$path = showtime_project_compare_local_path( $raw );
		} else {
			$path = $raw;
			$url  = showtime_project_compare_local_url( $raw );
		}
		if ( $path && is_readable( $path ) ) {
			$size = @getimagesize( $path );
			if ( is_array( $size ) ) {
				$w = (int) $size[0];
				$h = (int) $size[1];
			}
		}
	}

	if ( '' === $url || $w < 1 || $h < 1 ) {
		return null;
	}

	return array(
		'url'    => $url,
		'width'  => $w,
		'height' => $h,
		'alt'    => $alt,
		'srcset' => $srcset,
		'sizes'  => $srcset ? '(min-width: 768px) 50vw, 100vw' : '',
	);
}

/**
 * Map a child-theme asset URL back to its filesystem path (for getimagesize).
 */
function showtime_project_compare_local_path( string $url ): string {
	if ( ! defined( 'SHOWTIME_CHILD_URI' ) || ! defined( 'SHOWTIME_CHILD_DIR' ) ) {
		return '';
	}
	$base = rtrim( SHOWTIME_CHILD_URI, '/' );
	$url  = preg_replace( '#^https?://#', '//', $url );
	$base = preg_replace( '#^https?://#', '//', $base );
	if ( 0 !== strpos( $url, $base ) ) {
		return '';
	}
	return SHOWTIME_CHILD_DIR . substr( $url, strlen( $base ) );
}

/**
 * Map a child-theme filesystem path to its public URL.
 */
function showtime_project_compare_local_url( string $path ): string {
	if ( ! defined( 'SHOWTIME_CHILD_URI' ) || ! defined( 'SHOWTIME_CHILD_DIR' ) ) {
		return '';
	}
	$dir  = wp_normalize_path( SHOWTIME_CHILD_DIR );
	$path = wp_normalize_path( $path );
	if ( 0 !== strpos( $path, $dir ) ) {
		return '';
	}
	return rtrim( SHOWTIME_CHILD_URI, '/' ) . substr( $path, strlen( $dir ) );
}

/**
 * Format a registry `completion_date` (YYYY-MM) as "September 2025".
 * Returns '' for anything that is not a valid year-month, so the caller can
 * drop the clause rather than print a malformed date.
 */
function showtime_project_compare_date( string $ym ): string {
	$ym = trim( $ym );
	if ( ! preg_match( '/^(\d{4})-(\d{2})$/', $ym, $m ) ) {
		return '';
	}
	$month = (int) $m[2];
	if ( $month < 1 || $month > 12 ) {
		return '';
	}
	$ts = mktime( 0, 0, 0, $month, 1, (int) $m[1] );
	return false === $ts ? '' : date_i18n( 'F Y', $ts );
}

/**
 * THE shared project resolver.
 *
 * Accepts a project post ID **or** slug and returns the normalized registry
 * entry, or null when the project is not code-managed.
 *
 * Registry data wins UNCONDITIONALLY for managed projects: WordPress post meta
 * is never consulted, so a stale seeded value can no longer leak onto any
 * surface. The `project` post exists purely as a routing shell (permalink,
 * published status, sitemap inclusion, WP_Query compatibility).
 *
 * Every project surface reads through this one function: archive cards, the
 * single page, related cards, the homepage strip, the before/after comparison,
 * Open Graph / Twitter metadata and CreativeWork.
 *
 * @param int|string $post_id_or_slug Project post ID or slug.
 * @return array<string,mixed>|null   Normalized entry, or null if unmanaged.
 */
function showtime_project_data( $post_id_or_slug ): ?array {
	static $cache = array();

	if ( is_numeric( $post_id_or_slug ) ) {
		$pid = (int) $post_id_or_slug;
		if ( $pid < 1 || 'project' !== get_post_type( $pid ) ) {
			return null;
		}
		$slug = (string) get_post_field( 'post_name', $pid );
	} else {
		$slug = sanitize_title( (string) $post_id_or_slug );
		$pid  = 0;
	}
	if ( '' === $slug ) {
		return null;
	}
	if ( isset( $cache[ $slug ] ) ) {
		$hit = $cache[ $slug ];
		if ( is_array( $hit ) && $pid > 0 ) {
			$hit['post_id'] = $pid;
		}
		return $hit;
	}

	$entry = class_exists( 'Showtime\Projects' ) ? \Showtime\Projects::get( $slug ) : null;
	if ( ! is_array( $entry ) || empty( $entry['managed'] ) ) {
		$cache[ $slug ] = null;
		return null; // Legacy/unmanaged row — the old post-meta path still applies.
	}

	$str = static function ( $k ) use ( $entry ) {
		return isset( $entry[ $k ] ) ? trim( (string) $entry[ $k ] ) : '';
	};

	$asset = static function ( string $file ): string {
		if ( '' === $file || ! defined( 'SHOWTIME_CHILD_DIR' ) ) {
			return '';
		}
		$path = SHOWTIME_CHILD_DIR . '/assets/img/projects/comparisons/' . basename( $file );
		return is_readable( $path ) ? showtime_project_compare_local_url( $path ) : '';
	};

	$data = array(
		'slug'               => $slug,
		'post_id'            => $pid,
		'managed'            => true,
		// Placeholder ("Coming soon") projects are code-managed and DO render on
		// the archive, but they assert no verified work: no scope, finish,
		// timeline, investment, date, testimonial or photograph. Consumers use
		// these two keys to keep them out of discovery surfaces and schema.
		'is_coming_soon'     => ! empty( $entry['is_coming_soon'] ),
		'status'             => '' !== $str( 'status' ) ? $str( 'status' ) : 'verified',
		'title'              => $str( 'title' ),
		'excerpt'            => $str( 'excerpt' ),
		'neighborhood'       => $str( 'neighborhood' ),
		'finish'             => $str( 'finish' ),
		'scope'              => $str( 'scope' ),
		'timeline'           => $str( 'timeline' ),
		'investment'         => $str( 'investment' ),
		'completion_date'    => $str( 'completion_date' ),
		'client_quote'       => $str( 'client_quote' ),
		'hero_image'         => $asset( $str( 'hero_image' ) ),
		'hero_alt'           => $str( 'hero_alt' ),
		'before_image'       => $asset( $str( 'before_image' ) ),
		'before_alt'         => $str( 'before_alt' ),
		'after_image'        => $asset( $str( 'after_image' ) ),
		'after_alt'          => $str( 'after_alt' ),
		'comparison_heading' => $str( 'comparison_heading' ),
		'comparison_summary' => $str( 'comparison_summary' ),
		'before_condition'   => $str( 'before_condition' ),
		'work_completed'     => $str( 'work_completed' ),
		'completed_result'   => $str( 'completed_result' ),
		'service_url'        => $str( 'service_url' ),
		'area_url'           => $str( 'area_url' ),
		'seo_title'          => $str( 'seo_title' ),
		'meta_description'   => $str( 'meta_description' ),
		'og_image'           => $asset( $str( 'og_image' ) ),
	);

	$cache[ $slug ] = $data;
	return $data;
}

/**
 * Post IDs of published `project` posts that have NO managed registry entry.
 *
 * These are historical seed rows (the "Legacy seed rows" block at the bottom
 * of projects.php) whose prices, durations, material names and client quotes
 * were written once by the one-time seeder and never passed the truth-safety
 * review the six managed projects now go through. They are never deleted,
 * unpublished or renamed — the standing rule for existing project posts — but
 * every discovery surface, both sitemaps, and the robots-noindex check consult
 * this list so that content stays unadvertised rather than repeat unverified
 * claims. A project only needs a `managed => true` registry entry to leave
 * this list; no other action required.
 *
 * @return int[]
 */
function showtime_unmanaged_project_ids(): array {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	if ( ! post_type_exists( 'project' ) ) {
		return $ids;
	}
	$posts = get_posts(
		array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $post_id ) {
		if ( null === showtime_project_data( (int) $post_id ) ) {
			$ids[] = (int) $post_id;
		}
	}
	return $ids;
}

/**
 * Whether a resolved project entry is an unverified "Coming soon" placeholder.
 *
 * @param array<string,mixed>|null $p Result of showtime_project_data().
 */
function showtime_project_is_placeholder( ?array $p ): bool {
	return is_array( $p ) && ! empty( $p['is_coming_soon'] );
}

/**
 * Post IDs of published `project` posts backing a placeholder registry entry.
 *
 * These are code-managed and intentionally visible on the /projects/ archive,
 * but they must never be advertised as completed work — see
 * showtime_project_ids_hidden_from_discovery().
 *
 * @return int[]
 */
function showtime_placeholder_project_ids(): array {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	if ( ! post_type_exists( 'project' ) ) {
		return $ids;
	}
	$posts = get_posts(
		array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $post_id ) {
		if ( showtime_project_is_placeholder( showtime_project_data( (int) $post_id ) ) ) {
			$ids[] = (int) $post_id;
		}
	}
	return $ids;
}

/**
 * Every project post that must stay out of discovery surfaces: the XML and HTML
 * sitemaps, the homepage strip, related-project cards, and search indexing.
 *
 * Two distinct populations, same treatment, different reasons:
 *   - Legacy seed rows (showtime_unmanaged_project_ids()) carry unverified
 *     seeder-era prices and testimonials.
 *   - Placeholders (showtime_placeholder_project_ids()) carry no project
 *     content at all yet.
 *
 * Neither is ever deleted, unpublished or renamed. The /projects/ archive is
 * deliberately NOT driven off this list: it renders placeholders (so the
 * service-area footprint is visible) while still excluding legacy rows.
 *
 * @return int[]
 */
function showtime_project_ids_hidden_from_discovery(): array {
	return array_values(
		array_unique(
			array_merge(
				showtime_unmanaged_project_ids(),
				showtime_placeholder_project_ids()
			)
		)
	);
}

/**
 * Permalink for a managed project, whether or not its routing post exists yet.
 *
 * Falls back to the canonical /projects/{slug}/ path so the archive can render
 * a complete, linked card set before `wp showtime projects sync --apply` has
 * created the routing shells.
 */
function showtime_project_permalink( string $slug ): string {
	$post = get_page_by_path( $slug, OBJECT, 'project' );
	if ( $post instanceof \WP_Post ) {
		$url = get_permalink( $post );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	return home_url( '/projects/' . $slug . '/' );
}

/**
 * Every managed project as a render-ready card, in registry order.
 *
 * THE source for the archive grid. Reads the registry through the shared
 * resolver, so a card can never disagree with its own project page. Legacy
 * unmanaged rows are excluded by construction (the resolver returns null).
 *
 * @return array<int,array<string,mixed>>
 */
function showtime_project_cards(): array {
	if ( ! class_exists( 'Showtime\Projects' ) ) {
		return array();
	}
	$cards = array();
	foreach ( \Showtime\Projects::all() as $entry ) {
		if ( empty( $entry['managed'] ) ) {
			continue;
		}
		$d = showtime_project_data( (string) $entry['slug'] );
		if ( null === $d ) {
			continue;
		}
		$cards[] = array(
			'slug'           => $d['slug'],
			'title'          => $d['title'],
			'href'           => showtime_project_permalink( $d['slug'] ),
			'neighborhood'   => $d['neighborhood'],
			'scope'          => $d['scope'],
			'finish'         => $d['finish'],
			'duration'       => $d['timeline'],
			'value'          => $d['investment'],
			'image'          => $d['hero_image'],
			'image_alt'      => $d['hero_alt'],
			'is_coming_soon' => ! empty( $d['is_coming_soon'] ),
			'status'         => $d['status'],
		);
	}
	return $cards;
}

/**
 * The archive card set grouped into slides of six.
 *
 * 14 managed projects chunk to 6 / 6 / 2. The grouping is derived, not
 * hardcoded: adding or promoting a project re-chunks automatically, and a
 * trailing partial slide is centred by the template rather than padded with
 * empty cards.
 *
 * @return array<int,array<int,array<string,mixed>>>
 */
function showtime_project_slides( int $per_slide = 6 ): array {
	$cards = showtime_project_cards();
	if ( empty( $cards ) ) {
		return array();
	}
	return array_chunk( $cards, max( 1, $per_slide ) );
}

/**
 * Fixed display labels for the meta strip.
 *
 * Legacy `duration_label` / `value_label` are retired as headings. An estimate
 * always publishes under "Typical investment" so it can never read as a
 * confirmed contract price. A blank optional value omits the entire row —
 * there is deliberately no fallback to a seeded database value.
 *
 * @return array<int,array{k:string,v:string}>
 */
function showtime_project_meta_rows( array $p ): array {
	$rows = array(
		array( 'k' => __( 'Neighborhood', 'showtime-pools' ),       'v' => $p['neighborhood'] ?? '' ),
		array( 'k' => __( 'Finish', 'showtime-pools' ),             'v' => $p['finish'] ?? '' ),
		array( 'k' => __( 'Scope', 'showtime-pools' ),              'v' => $p['scope'] ?? '' ),
		array( 'k' => __( 'Typical timeline', 'showtime-pools' ),   'v' => $p['timeline'] ?? '' ),
		array( 'k' => __( 'Typical investment', 'showtime-pools' ), 'v' => $p['investment'] ?? '' ),
		array( 'k' => __( 'Completed', 'showtime-pools' ),          'v' => $p['completion_date'] ?? '' ),
	);
	return array_values(
		array_filter(
			$rows,
			static function ( $r ) {
				return '' !== trim( (string) $r['v'] );
			}
		)
	);
}

/**
 * Public URL of a managed project's bundled photograph.
 *
 * @param string $side 'after' (default), 'before' or 'hero'.
 */
function showtime_project_compare_asset_url( string $slug, string $side = 'after' ): string {
	$p = showtime_project_data( $slug );
	if ( null === $p ) {
		return '';
	}
	$key = 'before' === $side ? 'before_image' : ( 'hero' === $side ? 'hero_image' : 'after_image' );
	return (string) ( $p[ $key ] ?? '' );
}

/**
 * Assemble everything the comparison section needs, or null if it cannot
 * render truthfully. Reads exclusively through showtime_project_data().
 *
 * @return array<string, mixed>|null
 */
function showtime_project_compare( int $pid ): ?array {
	$p = showtime_project_data( $pid );
	if ( null === $p ) {
		return null;
	}

	// A "Coming soon" project may now carry its own verified photographs. The
	// comparison is allowed only when BOTH resolve to readable images with real
	// pixel dimensions — the checks below do exactly that and return null
	// otherwise, so a project with one image, no images, or an unreadable file
	// still renders no comparison at all.
	$before = showtime_project_compare_image( $p['before_image'], $p['before_alt'] );
	$after  = showtime_project_compare_image( $p['after_image'], $p['after_alt'] );

	// Fail closed — a one-sided pair is not a before/after.
	if ( null === $before || null === $after ) {
		return null;
	}
	if ( '' === $p['comparison_heading'] || '' === $p['comparison_summary'] ) {
		return null;
	}

	$links    = array();
	$svc_slug = trim( (string) preg_replace( '#^/services/#', '', $p['service_url'] ), '/' );
	if ( '' !== $svc_slug && class_exists( 'Showtime\Services' ) ) {
		$svc = \Showtime\Services::get( $svc_slug );
		if ( $svc ) {
			$links['primary'] = array(
				'url'   => home_url( $p['service_url'] ),
				'label' => strtolower( (string) $svc['title'] ) . ' services',
			);
		}
	}
	$area_slug = trim( (string) preg_replace( '#^/service-areas/#', '', $p['area_url'] ), '/' );
	if ( '' !== $area_slug && class_exists( 'Showtime\Areas' ) ) {
		$area = \Showtime\Areas::get( $area_slug );
		if ( $area ) {
			$links['area'] = array(
				'url'   => home_url( $p['area_url'] ),
				'label' => 'pool services in ' . (string) $area['name'],
			);
		}
	}

	$sliderable = abs( ( $before['width'] / $before['height'] ) - ( $after['width'] / $after['height'] ) ) <= 0.02;

	return array(
		'heading'          => $p['comparison_heading'],
		'summary'          => $p['comparison_summary'],
		'eyebrow'          => '' !== $p['neighborhood']
			? sprintf( 'Real project · %s', $p['neighborhood'] )
			: 'Real project',
		'before'           => $before,
		'after'            => $after,
		'before_condition' => $p['before_condition'],
		'work_completed'   => $p['work_completed'],
		'completed_result' => $p['completed_result'],
		'links'            => $links,
		'sliderable'       => $sliderable,
	);
}

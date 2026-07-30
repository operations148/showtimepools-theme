<?php
/**
 * Before / After comparison data for the `project` CPT.
 *
 * Resolution order, per field family:
 *   images — ACF `before_image` / `after_image` on the post (CONTENT, set by
 *            the owner in wp-admin). Nothing else. There is deliberately no
 *            code-side image fallback: substituting a photo from another
 *            project or a service page would publish a false claim about the
 *            work shown. The `showtime/project/compare_images` filter exists
 *            so a LOCAL preview harness can inject stand-ins without that
 *            substitution ever shipping in theme code.
 *   copy   — post meta first (owner-editable), then the seed registry entry
 *            in showtime-pools-core/includes/data/projects.php. The registry
 *            is the fallback because the one-time seeder has already run:
 *            anything added to the data file afterwards never reached post
 *            meta and would otherwise be unreachable.
 *   links  — resolved through \Showtime\Services and \Showtime\Areas. Never
 *            hardcoded URLs, so a slug rename cannot orphan a link.
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
 * Public URL of a project's bundled comparison photograph, or '' if none.
 *
 * Lets card listings show the real finished project instead of a generic
 * stock slot, reading the same registry keys the comparison section uses so
 * there is only one place to declare a project's photography.
 *
 * @param string $slug Project slug.
 * @param string $side 'after' (default) or 'before'.
 */
function showtime_project_compare_asset_url( string $slug, string $side = 'after' ): string {
	if ( ! class_exists( '\Showtime\Projects' ) || ! defined( 'SHOWTIME_CHILD_DIR' ) ) {
		return '';
	}
	$entry = \Showtime\Projects::get( $slug );
	$key   = ( 'before' === $side ? 'before_asset' : 'after_asset' );
	$file  = $entry['compare'][ $key ] ?? '';
	if ( ! is_string( $file ) || '' === $file ) {
		return '';
	}
	$path = SHOWTIME_CHILD_DIR . '/assets/img/projects/comparisons/' . basename( $file );
	return is_readable( $path ) ? showtime_project_compare_local_url( $path ) : '';
}

/**
 * Assemble everything the comparison section needs, or null if it cannot
 * render truthfully.
 *
 * @return array<string, mixed>|null
 */
function showtime_project_compare( int $pid ): ?array {
	if ( $pid < 1 || 'project' !== get_post_type( $pid ) ) {
		return null;
	}

	$slug     = (string) get_post_field( 'post_name', $pid );
	$registry = class_exists( '\Showtime\Projects' ) ? \Showtime\Projects::get( $slug ) : null;
	$cmp      = is_array( $registry ) && isset( $registry['compare'] ) && is_array( $registry['compare'] )
		? $registry['compare']
		: array();

	// Copy: post meta wins, registry fills in.
	$field = static function ( string $key ) use ( $pid, $cmp ) {
		$v = get_post_meta( $pid, $key, true );
		if ( is_string( $v ) && '' !== trim( $v ) ) {
			return trim( $v );
		}
		return isset( $cmp[ $key ] ) ? (string) $cmp[ $key ] : '';
	};

	$before_alt = $field( 'before_alt' );
	$after_alt  = $field( 'after_alt' );

	// Images: ACF only. The filter is the sole injection point and is used by
	// the local preview harness; nothing in the theme hooks it.
	$raw = array(
		'before' => function_exists( 'get_field' ) ? get_field( 'before_image', $pid ) : null,
		'after'  => function_exists( 'get_field' ) ? get_field( 'after_image', $pid ) : null,
	);

	/**
	 * Filter the raw before/after image values for a project.
	 *
	 * @param array  $raw  array{before:mixed, after:mixed}
	 * @param int    $pid  Project post ID.
	 * @param string $slug Project slug.
	 */
	$raw = (array) apply_filters( 'showtime/project/compare_images', $raw, $pid, $slug );

	$before = showtime_project_compare_image( $raw['before'] ?? null, $before_alt );
	$after  = showtime_project_compare_image( $raw['after'] ?? null, $after_alt );

	// Code-first fallback: bundled comparison photographs declared in the
	// project registry. Applied ONLY when neither WordPress image resolved, so
	// an uploaded pair always wins and a pair is never half CMS / half bundled.
	// Both files must exist and be readable, or nothing is used — a one-sided
	// pair still fails closed below.
	if ( null === $before && null === $after ) {
		$b_file = $field( 'before_asset' );
		$a_file = $field( 'after_asset' );
		if ( '' !== $b_file && '' !== $a_file && defined( 'SHOWTIME_CHILD_DIR' ) ) {
			$dir = SHOWTIME_CHILD_DIR . '/assets/img/projects/comparisons/';
			// basename() keeps a registry typo from walking outside the folder.
			$b_path = $dir . basename( $b_file );
			$a_path = $dir . basename( $a_file );
			if ( is_readable( $b_path ) && is_readable( $a_path ) ) {
				$before = showtime_project_compare_image( $b_path, $before_alt );
				$after  = showtime_project_compare_image( $a_path, $after_alt );
			}
		}
	}

	// Fail closed — a one-sided pair is not a before/after.
	if ( null === $before || null === $after ) {
		return null;
	}

	$heading = $field( 'comparison_heading' ) ?: $field( 'heading' );
	$summary = $field( 'comparison_summary' ) ?: $field( 'summary' );
	if ( '' === $heading || '' === $summary ) {
		return null;
	}

	// Links resolved through the registries — never hardcoded URLs.
	$links = array();
	$svc_slug = $field( 'primary_service' );
	if ( '' !== $svc_slug && class_exists( '\Showtime\Services' ) ) {
		$svc = \Showtime\Services::get( $svc_slug );
		if ( $svc ) {
			$links['primary'] = array(
				'url'   => home_url( '/services/' . $svc_slug . '/' ),
				'label' => strtolower( (string) $svc['title'] ) . ' services',
			);
		}
	}
	$sec_slug = $field( 'secondary_service' );
	if ( '' !== $sec_slug && class_exists( '\Showtime\Services' ) ) {
		$sec = \Showtime\Services::get( $sec_slug );
		if ( $sec ) {
			$links['secondary'] = array(
				'url'   => home_url( '/services/' . $sec_slug . '/' ),
				'label' => strtolower( (string) $sec['title'] ),
			);
		}
	}
	$area_slug = $field( 'area' );
	if ( '' !== $area_slug && class_exists( '\Showtime\Areas' ) ) {
		$area = \Showtime\Areas::get( $area_slug );
		if ( $area ) {
			$links['area'] = array(
				'url'   => home_url( '/service-areas/' . $area_slug . '/' ),
				'label' => 'pool services in ' . (string) $area['name'],
			);
		}
	}

	// Neighborhood for the eyebrow: post meta, then registry.
	$neighborhood = (string) get_post_meta( $pid, 'neighborhood', true );
	if ( '' === $neighborhood && is_array( $registry ) ) {
		$neighborhood = (string) ( $registry['neighborhood'] ?? '' );
	}

	// A slider overlay only makes sense when both frames share a shape.
	$ar_before  = $before['width'] / $before['height'];
	$ar_after   = $after['width'] / $after['height'];
	$sliderable = abs( $ar_before - $ar_after ) <= 0.02;

	return array(
		'heading'          => $heading,
		'summary'          => $summary,
		'eyebrow'          => '' !== $neighborhood
			? sprintf( 'Real project · %s', $neighborhood )
			: 'Real project',
		'before'           => $before,
		'after'            => $after,
		'before_condition' => $field( 'before_condition' ),
		'work_completed'   => $field( 'work_completed' ),
		'completed_result' => $field( 'completed_result' ),
		'links'            => $links,
		'sliderable'       => $sliderable,
	);
}

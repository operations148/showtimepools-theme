<?php
/**
 * SEO defaults — Rank Math filters for title + description on registry-driven
 * pages (services, areas, home, about, contact). Also a no-Rank-Math fallback
 * via pre_get_document_title so local/staging titles match production.
 *
 * Precedence: a manual Rank Math title/description typed on a specific page
 * ALWAYS wins. We only supply the DEFAULT when the admin left it blank.
 *
 * Sources, in order: hand-crafted registry `seo_title` / `seo_meta`, then the
 * keyword `seo_h1` / `seo_intro`, then a sane fallback.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the current page's registry SEO context.
 *
 * @return array{type:string,h1:string,keyword:string,intro:string,title:string,meta:string}|null
 */
function showtime_seo_context() {
	if ( ! is_singular( array( 'page', 'post', 'project' ) ) && ! is_front_page() ) {
		return null;
	}

	$id = get_queried_object_id();

	// Service single.
	$service_slug = (string) get_post_meta( $id, '_showtime_service_slug', true );
	if ( '' !== $service_slug && class_exists( '\\Showtime\\Services' ) ) {
		$svc = \Showtime\Services::get( $service_slug );
		if ( $svc ) {
			return array(
				'type'    => 'service',
				'h1'      => (string) ( $svc['seo_h1']      ?? $svc['title']   ?? '' ),
				'keyword' => (string) ( $svc['seo_keyword'] ?? '' ),
				'intro'   => (string) ( $svc['seo_intro']   ?? $svc['summary'] ?? '' ),
				'title'   => (string) ( $svc['seo_title']    ?? '' ),
				'meta'    => (string) ( $svc['seo_meta']     ?? '' ),
			);
		}
	}

	// Area single.
	$area_slug = (string) get_post_meta( $id, '_showtime_area_slug', true );
	if ( '' !== $area_slug && class_exists( '\\Showtime\\Areas' ) ) {
		$area = \Showtime\Areas::get( $area_slug );
		if ( $area ) {
			return array(
				'type'    => 'area',
				'h1'      => (string) ( $area['seo_h1'] ?? '' ) ?: sprintf( 'Pool Service in %s', (string) $area['name'] ),
				'keyword' => 'pool service near me',
				'intro'   => (string) ( $area['seo_intro'] ?? $area['lead'] ?? '' ),
				'title'   => (string) ( $area['seo_title'] ?? '' ),
				'meta'    => (string) ( $area['seo_meta']  ?? '' ),
			);
		}
	}

	// Homepage.
	if ( is_front_page() ) {
		return array(
			'type'    => 'home',
			'h1'      => 'Pool Service in Los Angeles',
			'keyword' => 'pool service near me',
			'intro'   => 'Los Angeles pool companies near me — pool service, pool cleaning service, pool repair near me, remodeling, and new pool construction. One supervised crew. Steve on every quote.',
			'title'   => 'Pool Service & Repair in Los Angeles | Showtime Pools',
			'meta'    => 'Stop juggling contractors. One LA crew for pool repair, weekly service, remodels, and equipment since 2003. Free quote, call (323) 825-2099.',
		);
	}

	// About + Contact (slug-driven, not in a registry).
	$slug = (string) get_post_field( 'post_name', $id );
	$static = array(
		'about' => array(
			'title' => 'About Showtime Pools | LA Pool Company Since 2003',
			'meta'  => 'Showtime Pools is an owner-operated LA pool company since 2003. Founder Steve Adams on every quote. Repairs, service, remodels. Call (323) 825-2099.',
		),
		'contact' => array(
			'title' => 'Contact Showtime Pools | Sherman Oaks Pool Company',
			'meta'  => 'Contact Showtime Pools in Sherman Oaks. Repairs, weekly service, remodels, and inspections across LA. Steve replies within a business day. (323) 825-2099.',
		),
	);
	if ( isset( $static[ $slug ] ) ) {
		return array(
			'type'    => $slug,
			'h1'      => '',
			'keyword' => '',
			'intro'   => '',
			'title'   => $static[ $slug ]['title'],
			'meta'    => $static[ $slug ]['meta'],
		);
	}

	return null;
}

/**
 * Build the final title from context. Prefers the hand-crafted seo_title
 * (already length-checked). Otherwise "<H1> · Brand", dropping the brand
 * suffix when that would exceed the 60-char SERP budget (the H1 carries geo).
 */
function showtime_seo_resolved_title( array $ctx ): string {
	if ( '' !== ( $ctx['title'] ?? '' ) ) {
		return $ctx['title'];
	}
	if ( '' === ( $ctx['h1'] ?? '' ) ) {
		return '';
	}
	$full = $ctx['h1'] . ' · ' . get_bloginfo( 'name' );
	return ( strlen( $full ) <= 60 ) ? $full : $ctx['h1'];
}

/**
 * Build the final meta description. Prefers hand-crafted seo_meta. Otherwise
 * trims the intro and appends the phone CTA, staying within the SERP budget.
 */
function showtime_seo_resolved_desc( array $ctx ): string {
	if ( '' !== ( $ctx['meta'] ?? '' ) ) {
		return $ctx['meta'];
	}
	$intro = (string) ( $ctx['intro'] ?? '' );
	if ( '' === $intro ) {
		return '';
	}
	$trimmed = wp_trim_words( wp_strip_all_tags( $intro ), 20, '' );
	return rtrim( $trimmed, " .\u{2026}" ) . '. Call (323) 825-2099.';
}

/**
 * True when the page has a manual Rank Math title/description so we never
 * override what Steve typed in the Rank Math meta box.
 *
 * @param string $key 'rank_math_title' or 'rank_math_description'.
 */
function showtime_has_rank_math_override( string $key ): bool {
	$id = get_queried_object_id();
	return $id > 0 && '' !== (string) get_post_meta( $id, $key, true );
}

// Rank Math title default.
add_filter(
	'rank_math/frontend/title',
	function ( $title ) {
		if ( showtime_has_rank_math_override( 'rank_math_title' ) ) {
			return $title; // Admin override wins.
		}
		$ctx = showtime_seo_context();
		if ( ! $ctx ) {
			return $title;
		}
		$resolved = showtime_seo_resolved_title( $ctx );
		return '' !== $resolved ? $resolved : $title;
	},
	5
);

// Rank Math description default.
add_filter(
	'rank_math/frontend/description',
	function ( $desc ) {
		if ( showtime_has_rank_math_override( 'rank_math_description' ) ) {
			return $desc; // Admin override wins.
		}
		$ctx = showtime_seo_context();
		if ( ! $ctx ) {
			return $desc;
		}
		$resolved = showtime_seo_resolved_desc( $ctx );
		return '' !== $resolved ? $resolved : $desc;
	},
	5
);

/**
 * No-Rank-Math fallback: drive the <title> through the same logic so local and
 * staging match production. When Rank Math is active it owns the title.
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return $title;
		}
		$ctx = showtime_seo_context();
		if ( ! $ctx ) {
			return $title;
		}
		$resolved = showtime_seo_resolved_title( $ctx );
		return '' !== $resolved ? $resolved : $title;
	},
	20
);


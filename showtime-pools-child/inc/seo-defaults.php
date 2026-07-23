<?php
/**
 * SEO defaults: registry-driven <title> + meta description for services,
 * areas, home, about, contact. The theme owns the server-rendered head
 * everywhere (local and production); Search Atlas OTTO layers its edits
 * client-side and is not depended on here.
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
			'intro'   => 'Los Angeles pool companies near me: pool service, pool cleaning service, pool repair near me, remodeling, and new pool construction. One supervised crew. Steve on every quote.',
			'title'   => 'Pool Service & Repairs in Los Angeles | Showtime Pools',
			'meta'    => 'Stop juggling contractors. One LA crew for pool repair, weekly service, remodels, and equipment since 2003. Free quote, call (323) 825-2099.',
		);
	}

	// Slug-driven pages that live outside the service/area registries: hubs,
	// about, contact, and utility pages. Without an entry here they fall
	// back to WP's "{Page} - {blogname}" pattern, which double-brands the
	// SERP whenever the blogname carries an environment or sub-brand suffix.
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
		'services' => array(
			'title' => 'Pool Services in Los Angeles | Showtime Pools',
			'meta'  => 'Every pool service under one roof: repairs, weekly cleaning, remodeling, equipment, inspections, and outdoor living across LA. Call (323) 825-2099.',
		),
		'service-areas' => array(
			'title' => 'Pool Service Areas in Los Angeles | Showtime Pools',
			'meta'  => 'Showtime Pools serves Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills from our Ventura Blvd. headquarters. (323) 825-2099.',
		),
		'blog' => array(
			'title' => 'Pool Care Tips & Guides | Showtime Pools Blog',
			'meta'  => 'Practical pool care advice from the crew that does the work: maintenance checklists, equipment comparisons, and remodeling trends for LA homeowners.',
		),
		'projects' => array(
			'title' => 'Our Pool Projects in Los Angeles | Showtime Pools',
			'meta'  => 'Browse completed pool remodels, new builds, and equipment overhauls across Sherman Oaks, Encino, Beverly Hills, and the Valley. See the work up close.',
		),
		'pool-inspections' => array(
			'title' => 'Independent Pool Inspections in LA | Showtime Pools',
			'meta'  => 'Pre-purchase, seasonal, and equipment-diagnostic pool inspections across Los Angeles. Written report in 24 to 48 hours. Call (323) 825-2099.',
		),
		'the-founder' => array(
			'title' => 'Steve Adams, Founder & CEO | Showtime Pools',
			'meta'  => 'Meet Steve Adams, the owner-operator behind Showtime Pools. On every quote, walks every site, pulls every permit personally. Call (323) 825-2099.',
		),
		'reviews' => array(
			'title' => 'Showtime Pools Reviews | LA Pool Company',
			'meta'  => 'What Los Angeles homeowners say about Showtime Pools: repairs, weekly service, remodels, and inspections. Read verified customer reviews.',
		),
		'quote' => array(
			'title' => 'Get a Free Pool Service Quote | Showtime Pools',
			'meta'  => 'Free itemized pool service quote in one business day. Repairs, weekly cleaning, remodels, and equipment across Los Angeles. Call (323) 825-2099.',
		),
		'book' => array(
			'title' => 'Book an Appointment | Showtime Pools',
			'meta'  => 'Book a pool service appointment in Los Angeles: repairs, weekly service, remodel quotes, and inspections. Pick a time online or call (323) 825-2099.',
		),
		'shop' => array(
			'title' => 'Pool Equipment & Supplies | Showtime Pools',
			'meta'  => 'Shop pool equipment and supplies from Showtime Pools: pumps, heaters, salt systems, and automation from Pentair and Jandy. LA-local support.',
		),
		'affiliate' => array(
			'title' => 'Partner Program | Showtime Pools',
			'meta'  => 'Refer pool work in Los Angeles and earn. The Showtime Pools partner program for realtors, inspectors, and trades. Apply in two minutes.',
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
 * The single, authoritative <title> generator for the whole site.
 *
 * `pre_get_document_title` short-circuits wp_get_document_title(), so this is
 * the one place titles are decided — no document_title_parts branch competes
 * with it. Order:
 *   1. Hand-crafted registry seo_title (home, services, areas, hub/utility) —
 *      these already carry the brand exactly once.
 *   2. Fall-through (posts, projects, archives, unmapped singles): build
 *      "{page title} | Showtime Pools". The brand is HARDCODED, never
 *      get_bloginfo('name'): if the WP Site Title ever carries a sub-brand or
 *      environment suffix, letting it into the title is what produces a
 *      double/'wrong-brand' SERP title. We also never append the brand twice
 *      when the base already contains it.
 *
 * This controls the SERVER-rendered title only. On production, Search Atlas
 * OTTO can still rewrite the title in its own layer — that is a dashboard
 * setting, documented in .claude/audits/critical-schema-metadata-fix.md, not
 * something the theme can override from here.
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		$brand = (string) apply_filters( 'showtime/business/name', 'Showtime Pools' );

		// 1. Registry-owned titles (already brand-correct).
		$ctx = showtime_seo_context();
		if ( $ctx ) {
			$resolved = showtime_seo_resolved_title( $ctx );
			if ( '' !== $resolved ) {
				return $resolved;
			}
		}

		// 2. Fall-through — force a single-brand "{base} | {brand}" title and
		// keep the WP Site Title (blogname) out of the tag entirely.
		if ( is_404() ) {
			return 'Page not found | ' . $brand;
		}

		$base = '';
		if ( is_singular() ) {
			$base = wp_strip_all_tags( get_the_title( get_queried_object_id() ) );
		} elseif ( is_archive() ) {
			$base = wp_strip_all_tags( get_the_archive_title() );
		} elseif ( is_search() ) {
			$base = 'Search results';
		}

		$base = trim( (string) $base );
		if ( '' === $base ) {
			return $title; // Leave genuinely unknown edge cases to WP.
		}

		// Don't double-brand when the base already names the brand.
		if ( false !== stripos( $base, $brand ) ) {
			return $base;
		}
		return $base . ' | ' . $brand;
	},
	20
);


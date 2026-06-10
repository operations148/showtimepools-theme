<?php
/**
 * SEO + schema injector. Sitewide.
 *
 * The theme is the single owner of the server-rendered head. Search Atlas
 * OTTO layers its edits client-side via JS and is not depended on here.
 *
 * Injects into wp_head:
 *   - Canonical URL.
 *   - Robots meta (index/follow + max-* directives + noimageindex off).
 *   - Open Graph + Twitter Card.
 *   - Theme color + viewport.
 *   - JSON-LD: WebSite (with sitelinks SearchAction), Organization (light),
 *     and BreadcrumbList for any non-home page.
 *   - Hreflang (en-US only for v1).
 *
 * The LocalBusiness + Service + FAQ + Person schemas live in their own
 * template-part / page templates so they can carry per-page detail.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// WordPress core emits its own <link rel="canonical"> for singular views.
// Remove it so there is exactly one canonical: ours.
remove_action( 'wp_head', 'rel_canonical' );

/**
 * Resolve the current canonical URL.
 */
function showtime_canonical_url(): string {
	if ( is_singular() ) { return get_permalink(); }
	if ( is_home() || is_front_page() ) { return home_url( '/' ); }
	if ( is_archive() ) {
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ) ) . '/';
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}

/**
 * Resolve a per-page SEO title. Falls back to wp_get_document_title().
 */
function showtime_seo_title(): string {
	if ( is_front_page() ) {
		return 'Showtime Pools — Stop juggling contractors. One team for repairs, weekly service, remodels, and equipment in LA.';
	}
	if ( is_404() ) {
		return 'Page not found — Showtime Pools';
	}
	$title = wp_get_document_title();
	return $title ? $title : get_bloginfo( 'name' );
}

/**
 * Resolve per-page meta description. Pulls from post excerpt if present,
 * otherwise from the registry summary for service / area / inspection
 * pages, otherwise a sane default.
 */
function showtime_seo_description(): string {
	$default = 'Showtime Pools is one supervised crew for pool repairs, weekly service, remodels, equipment, inspections, and outdoor living across Sherman Oaks, Encino, Beverly Hills, and Los Angeles. (323) 825-2099.';

	if ( is_front_page() ) {
		return 'Stop juggling contractors. Showtime Pools handles repairs, weekly service, remodels, equipment, inspections, and outdoor living end-to-end across Los Angeles. Direct from the people who do the work.';
	}

	if ( is_singular() ) {
		$excerpt = get_post_field( 'post_excerpt', get_the_ID() );
		if ( $excerpt ) { return wp_trim_words( $excerpt, 36, '…' ); }

		// Service registry fallback
		$svc_slug = (string) get_post_meta( get_the_ID(), '_showtime_service_slug', true );
		if ( $svc_slug && class_exists( '\\Showtime\\Services' ) ) {
			$svc = \Showtime\Services::get( $svc_slug );
			if ( $svc && ! empty( $svc['summary'] ) ) { return wp_trim_words( (string) $svc['summary'], 36, '…' ); }
		}

		$area_slug = (string) get_post_meta( get_the_ID(), '_showtime_area_slug', true );
		if ( $area_slug && class_exists( '\\Showtime\\Areas' ) ) {
			$area = \Showtime\Areas::get( $area_slug );
			if ( $area && ! empty( $area['lead'] ) ) { return wp_trim_words( (string) $area['lead'], 36, '…' ); }
		}

		$insp_slug = (string) get_post_meta( get_the_ID(), '_showtime_inspection_slug', true );
		if ( $insp_slug && class_exists( '\\Showtime\\Inspections' ) ) {
			$insp = \Showtime\Inspections::get( $insp_slug );
			if ( $insp && ! empty( $insp['lead'] ) ) { return wp_trim_words( (string) $insp['lead'], 36, '…' ); }
		}

		$content = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ), 36, '…' );
		if ( $content ) { return $content; }
	}

	return $default;
}

/**
 * Open Graph image. Hero image for the homepage; per-page image when
 * available, otherwise the brand default.
 */
function showtime_og_image(): string {
	if ( function_exists( 'showtime_image' ) ) {
		if ( is_front_page() ) { return showtime_image( 'hero', 1200 ); }
		if ( is_singular() ) {
			$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
			if ( $thumb ) { return $thumb; }
			return showtime_image( 'lifestyle_main', 1200 );
		}
	}
	return home_url( '/wp-content/themes/showtime-pools-child/assets/images/og-default.jpg' );
}

/**
 * Build the BreadcrumbList JSON-LD for the current request.
 *
 * @return array<string,mixed>|null
 */
function showtime_breadcrumb_schema(): ?array {
	if ( is_front_page() || is_404() ) { return null; }

	$items = array();
	$pos   = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => 'Home',
		'item'     => home_url( '/' ),
	);

	if ( is_singular() ) {
		$post     = get_post( get_the_ID() );
		$ancestors = array_reverse( get_post_ancestors( $post ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title( $ancestor_id ),
				'item'     => get_permalink( $ancestor_id ),
			);
		}
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title( $post ),
			'item'     => get_permalink( $post ),
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_archive_title(),
			'item'     => showtime_canonical_url(),
		);
	}

	if ( count( $items ) < 2 ) { return null; }

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * WebSite schema with Sitelinks SearchAction. Helps Google render the
 * sitelinks search box under the brand-name SERP.
 */
function showtime_website_schema(): array {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => 'Showtime Pools',
		'description'     => 'Pool repairs, weekly service, remodels, equipment, inspections, and outdoor living in Los Angeles.',
		'inLanguage'      => 'en-US',
		'publisher'       => array( '@id' => home_url( '/#localbusiness' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

// ─── wp_head injectors ─────────────────────────────────────────────

// Remove WP's auto title-tag injection so we can place ours with intent.
// (Astra registers `title-tag`; theme-setup keeps it; we override via the
// document_title_parts filter rather than removing support.)

// Drive title tag through a clean filter so Rank Math + WP play nice.
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( is_front_page() ) {
			$parts['title']   = 'Showtime Pools';
			$parts['tagline'] = 'Stop juggling contractors. One team handles it all.';
			$parts['site']    = '';
		}
		return $parts;
	},
	5
);

// Open Graph + Twitter + canonical + theme color + extra schema
add_action(
	'wp_head',
	function () {
		$canonical = showtime_canonical_url();

		// Theme color + geo signals, always emitted.
		echo '<meta name="theme-color" content="#0A0A0A">' . "
";
		echo '<meta name="geo.region" content="US-CA">' . "
";
		echo '<meta name="geo.placename" content="Sherman Oaks, Los Angeles">' . "
";
		echo '<meta name="geo.position" content="34.1511;-118.4490">' . "
";
		echo '<meta name="ICBM" content="34.1511, -118.4490">' . "
";

		// The theme owns the server-rendered head: canonical, description,
		// robots, Open Graph, Twitter, hreflang, WebSite + Breadcrumb JSON-LD.
		// Search Atlas OTTO applies its edits client-side via JS, so this
		// output is what crawlers that skip JS (GPTBot, ClaudeBot,
		// PerplexityBot) actually read.
		$title = showtime_seo_title();
		$desc  = showtime_seo_description();
		$image = showtime_og_image();

		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "
";
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "
";
		echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "
";

		// Open Graph
		echo '<meta property="og:type" content="' . ( is_singular() && ! is_front_page() ? 'article' : 'website' ) . '">' . "
";
		echo '<meta property="og:locale" content="en_US">' . "
";
		echo '<meta property="og:site_name" content="Showtime Pools">' . "
";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "
";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "
";
		echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "
";
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "
";
		echo '<meta property="og:image:width" content="1200">' . "
";
		echo '<meta property="og:image:height" content="675">' . "
";

		// Twitter Card
		echo '<meta name="twitter:card" content="summary_large_image">' . "
";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "
";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "
";
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "
";
		echo '<meta name="twitter:site" content="@showtime_pools">' . "
";

		// Hreflang (en-US only for v1)
		echo '<link rel="alternate" hreflang="en-US" href="' . esc_url( $canonical ) . '">' . "
";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $canonical ) . '">' . "
";

		// JSON-LD: WebSite + BreadcrumbList
		$website = showtime_website_schema();
		echo '<script type="application/ld+json">' . wp_json_encode( $website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "
";

		$crumbs = showtime_breadcrumb_schema();
		if ( $crumbs ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $crumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "
";
		}
	},
	2
);

// Strip the WP-default `<meta name="generator" content="WordPress X.Y">`
// (already done in security.php) and add a tighter, branded meta line.

/**
 * On the /services/ hub, inject ItemList schema so Google can render an
 * expanded service listing. Uses the registry as truth.
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_page( 'services' ) ) { return; }
		if ( ! class_exists( '\\Showtime\\Services' ) ) { return; }

		$services = \Showtime\Services::all();
		$items    = array();
		$pos      = 1;
		foreach ( $services as $svc ) {
			$slug = (string) ( $svc['slug'] ?? '' );
			if ( '' === $slug ) { continue; }
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'item'     => array(
					'@type'       => 'Service',
					'@id'         => home_url( '/services/' . $slug . '/#service' ),
					'name'        => (string) $svc['title'],
					'description' => (string) ( $svc['summary'] ?? '' ),
					'url'         => home_url( '/services/' . $slug . '/' ),
					'provider'    => array( '@id' => home_url( '/#localbusiness' ) ),
				),
			);
		}

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'@id'             => home_url( '/services/#itemlist' ),
			'name'            => 'Pool Services Offered by Showtime Pools',
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	},
	5
);

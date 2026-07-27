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
 * Page templates whose pages are noindexed AND kept out of the XML sitemap.
 * Thin GHL-iframe utility pages (/quote/, /book/, via page-iframe.php), the
 * /affiliate/ partner form (page-affiliate.php), and the /shop/ coming-soon
 * placeholder (page-shop.php). One list, so the robots meta
 * (showtime_seo_should_noindex) and the sitemap exclusion (inc/crawl.php) can
 * never disagree about what is indexable.
 *
 * /shop/ indexing is a single documented switch: flip `showtime/seo/shop_indexable`
 * to true once /shop/ is a real store with genuine Product/Offer data, and it
 * returns to the index + sitemap with no other change.
 */
function showtime_noindex_page_templates(): array {
	$templates = array( 'page-iframe.php', 'page-affiliate.php' );
	if ( ! apply_filters( 'showtime/seo/shop_indexable', false ) ) {
		$templates[] = 'page-shop.php';
	}
	return (array) apply_filters( 'showtime/seo/noindex_templates', $templates );
}

/**
 * Thin taxonomy archives (blog category groupings) that carry no unique
 * indexable value. noindex,follow keeps link equity flowing while dropping them
 * from the index and the taxonomy sitemap. Filterable.
 */
function showtime_noindex_term_slugs(): array {
	return (array) apply_filters(
		'showtime/seo/noindex_terms',
		array( 'pool-trends', 'maintenance-tips', 'equipment-guides' )
	);
}

/**
 * Page slugs to noindex + keep out of the sitemap even though they use a normal
 * template — e.g. a legacy duplicate (/terms-2/) that 301s to the canonical.
 */
function showtime_noindex_page_slugs(): array {
	return (array) apply_filters( 'showtime/seo/noindex_slugs', array( 'terms-2' ) );
}

/**
 * Whether the current request should be noindexed. Everything not matched here
 * stays indexable. Always noindex,follow (links stay followable).
 */
function showtime_seo_should_noindex(): bool {
	if ( is_page_template( showtime_noindex_page_templates() ) ) {
		return (bool) apply_filters( 'showtime/seo/noindex', true );
	}
	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		if ( in_array( $slug, showtime_noindex_page_slugs(), true ) ) {
			return (bool) apply_filters( 'showtime/seo/noindex', true );
		}
	}
	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && in_array( $term->slug, showtime_noindex_term_slugs(), true ) ) {
			return (bool) apply_filters( 'showtime/seo/noindex', true );
		}
	}
	return false;
}

/**
 * Single robots directive. WordPress core prints exactly one robots meta from
 * this filtered array (wp_robots), so the theme sets directives HERE rather
 * than hand-printing a second tag. noindex,follow for the noindex set;
 * index + max-* previews everywhere else.
 */
add_filter(
	'wp_robots',
	function ( array $robots ): array {
		if ( showtime_seo_should_noindex() ) {
			unset( $robots['index'], $robots['max-snippet'], $robots['max-image-preview'], $robots['max-video-preview'] );
			$robots['noindex'] = true;
			$robots['follow']  = true;
			return $robots;
		}
		unset( $robots['noindex'], $robots['nofollow'] );
		$robots['index']  = true;
		$robots['follow'] = true;
		// String values so WP core's wp_robots() renders "key:value"
		// (an int renders as a bare directive with no value).
		$robots['max-snippet']       = '-1';
		$robots['max-image-preview'] = 'large';
		$robots['max-video-preview'] = '-1';
		return $robots;
	},
	20
);

/**
 * Permanent redirects for retired/duplicate URLs, keyed by source page slug →
 * destination path on this domain. /terms-2/ is a duplicate of /terms/. Fires
 * early on template_redirect. wp_safe_redirect keeps the target on-site; the
 * destination slug is never itself a key, so no loop is possible. The original
 * page content is not touched — set it to draft in wp-admin after verifying the
 * 301 (see the deployment report).
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_page() ) {
			return;
		}
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		$map  = (array) apply_filters(
			'showtime/seo/page_redirects',
			array( 'terms-2' => '/terms/' )
		);
		if ( '' === $slug || ! isset( $map[ $slug ] ) ) {
			return;
		}
		$dest = home_url( $map[ $slug ] );
		$qs   = (string) ( $_SERVER['QUERY_STRING'] ?? '' );
		if ( '' !== $qs ) {
			$dest = add_query_arg( wp_parse_args( wp_unslash( $qs ) ), $dest );
		}
		wp_safe_redirect( $dest, 301 );
		exit;
	},
	0
);

/**
 * Resolve the per-page SEO title for Open Graph + Twitter.
 *
 * Deliberately delegates to wp_get_document_title(), which is driven by the
 * single title authority in inc/seo-defaults.php (pre_get_document_title).
 * This guarantees og:title and twitter:title are ALWAYS identical to the
 * <title> tag — no separate, drifting front-page/404 strings.
 */
function showtime_seo_title(): string {
	$title = wp_get_document_title();
	return $title ? $title : (string) apply_filters( 'showtime/business/name', 'Showtime Pools' );
}

/**
 * Resolve per-page meta description. The hand-crafted registry seo_meta
 * wins everywhere it exists (services, areas, home, about, contact, via
 * showtime_seo_context()). Then post excerpt, registry summary for
 * service / area / inspection pages, trimmed content, a sane default.
 */
function showtime_seo_description(): string {
	$default = 'Showtime Pools is one supervised crew for pool repairs, weekly service, remodels, equipment, inspections, and outdoor living across Sherman Oaks, Encino, Beverly Hills, and Los Angeles. (323) 825-2099.';

	// Copy written for the SERP beats anything derived from page content.
	$ctx = function_exists( 'showtime_seo_context' ) ? showtime_seo_context() : null;
	if ( $ctx ) {
		$resolved = showtime_seo_resolved_desc( $ctx );
		if ( '' !== $resolved ) {
			return $resolved;
		}
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
		// Archives, search, 404: fall back to the hero image (a real, resolvable
		// asset) rather than a hardcoded path that may not exist.
		return showtime_image( 'hero', 1200 );
	}
	return SHOWTIME_CHILD_URI . '/assets/img/logo.png';
}

/**
 * Build the BreadcrumbList JSON-LD for the current request.
 *
 * @return array<string,mixed>|null
 */
function showtime_breadcrumb_schema(): ?array {
	if ( is_front_page() || is_404() ) { return null; }

	// single.php and single-project.php emit their own richer trails
	// (Home > Blog > Category > Post); emitting this generic one too would
	// duplicate the BreadcrumbList node on those URLs.
	if ( is_singular( array( 'post', 'project' ) ) ) { return null; }

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
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
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

// The <title> tag has ONE authority: the pre_get_document_title filter in
// inc/seo-defaults.php. A former document_title_parts front-page branch lived
// here but was dead code (pre_get_document_title short-circuits it) and gave
// the impression of a second title generator — removed so there is exactly one.

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
		// Robots is emitted once by WordPress core's wp_robots(); the theme
		// drives its directives via the `wp_robots` filter below (not a second
		// hand-printed tag), so there is exactly one robots meta.

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
					'provider'    => array( '@id' => home_url( '/#organization' ) ),
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

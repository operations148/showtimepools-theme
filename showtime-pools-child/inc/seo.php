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
	if ( is_singular( 'project' ) && function_exists( 'showtime_unmanaged_project_ids' ) ) {
		// Legacy seed rows with no managed registry entry: unverified
		// seeder-era prices/durations/testimonials, so the direct URL stays
		// live (never deleted or unpublished) but out of the index.
		if ( in_array( get_queried_object_id(), showtime_unmanaged_project_ids(), true ) ) {
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

		// Service registry fallback. Uses the shared resolver so this path has
		// exactly the same meta-first / template-gated-post_name priority as
		// showtime_seo_context() — the two can no longer drift.
		$svc_slug = function_exists( 'showtime_registry_slug' )
			? showtime_registry_slug( (int) get_the_ID(), '_showtime_service_slug', 'page-service.php' )
			: (string) get_post_meta( get_the_ID(), '_showtime_service_slug', true );
		if ( $svc_slug && class_exists( '\\Showtime\\Services' ) ) {
			$svc = \Showtime\Services::get( $svc_slug );
			if ( $svc && ! empty( $svc['summary'] ) ) { return wp_trim_words( (string) $svc['summary'], 36, '…' ); }
		}

		$area_slug = function_exists( 'showtime_registry_slug' )
			? showtime_registry_slug( (int) get_the_ID(), '_showtime_area_slug', 'page-area.php' )
			: (string) get_post_meta( get_the_ID(), '_showtime_area_slug', true );
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
 * Resolve a bundled image slot for SOCIAL sharing.
 *
 * The visible hero (showtime_image()'s own pick, usually WebP) is always the
 * baseline identity. A same-named JPEG/PNG sibling is substituted ONLY when it
 * is VERIFIED to be the same photo: same basename (i.e. the same slot) AND
 * identical pixel dimensions to the visible hero. A same-named file at
 * different dimensions is a different crop — proven live on 4 of 9 area
 * heroes, where the ".jpg" is a portrait 960x1280 crop and the on-page ".webp"
 * is a landscape 1200x896 crop of what is visually a different framing. Never
 * substitute on filename existence alone; when unverified, the exact visible
 * hero URL is returned even though it is WebP.
 *
 * Media Library / ACF overrides and remote (Unsplash) URLs are returned exactly
 * as showtime_image() resolved them; only bundled files are ever re-pointed.
 *
 * @param string $slot Image slot, e.g. 'service_pool-repairs-plumbing'.
 * @return array{url:string,width:int,height:int}
 */
function showtime_og_slot_image( string $slot ): array {
	if ( ! function_exists( 'showtime_image' ) ) {
		return array( 'url' => '', 'width' => 0, 'height' => 0 );
	}

	$url = (string) showtime_image( $slot, 1200 );
	if ( '' === $url ) {
		return array( 'url' => '', 'width' => 0, 'height' => 0 );
	}
	$dims = showtime_og_image_dimensions( $url );

	$bundled_prefix = SHOWTIME_CHILD_URI . '/assets/img/';
	if ( 0 === strpos( $url, $bundled_prefix ) && $dims['width'] > 0 ) {
		$order = (array) apply_filters( 'showtime/og/format_priority', array( 'jpg', 'jpeg', 'png' ), $slot );
		foreach ( $order as $ext ) {
			$candidate = "{$slot}.{$ext}";
			if ( ! file_exists( SHOWTIME_CHILD_DIR . "/assets/img/{$candidate}" ) ) {
				continue;
			}
			$cand_dims = showtime_og_image_dimensions( $bundled_prefix . $candidate );
			if ( $cand_dims['width'] === $dims['width'] && $cand_dims['height'] === $dims['height'] ) {
				return array_merge( array( 'url' => $bundled_prefix . $candidate ), $cand_dims );
			}
		}
	}

	return array_merge( array( 'url' => $url ), $dims );
}

/**
 * Exact pixel dimensions for an image URL, or zeros when they cannot be read.
 *
 * Only local, theme-bundled files are measured (a header-only getimagesize()
 * read, memoized per request). Remote URLs are never fetched, so callers get
 * 0/0 and must omit the dimension tags rather than guess — emitting wrong or
 * partial dimensions is worse than emitting none.
 *
 * @return array{width:int,height:int}
 */
function showtime_og_image_dimensions( string $url ): array {
	static $cache = array();

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$dims           = array( 'width' => 0, 'height' => 0 );
	$bundled_prefix = SHOWTIME_CHILD_URI . '/assets/img/';

	if ( 0 === strpos( $url, $bundled_prefix ) ) {
		$path = SHOWTIME_CHILD_DIR . '/assets/img/' . basename( wp_parse_url( $url, PHP_URL_PATH ) ?: '' );
		if ( is_readable( $path ) ) {
			$size = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- non-image/corrupt file must degrade to "no dimensions", not warn.
			if ( is_array( $size ) && ! empty( $size[0] ) && ! empty( $size[1] ) ) {
				$dims = array( 'width' => (int) $size[0], 'height' => (int) $size[1] );
			}
		}
	}

	$cache[ $url ] = $dims;
	return $dims;
}

/**
 * Open Graph image for the current request, with dimensions and alt text.
 *
 * Priority for singular views:
 *   1. A real featured image (unchanged from the previous behaviour).
 *   2. The page's own hero slot — service_{slug} on page-service.php,
 *      area_{slug} on page-area.php — resolved through the shared,
 *      template-gated showtime_registry_slug() introduced in P0-1. The template
 *      gate means an unrelated page cannot inherit a service/area image just by
 *      sharing a slug.
 *   3. The lifestyle_main brand default.
 *
 * @return array{url:string,width:int,height:int,alt:string}
 */
function showtime_og_image_data(): array {
	$fallback = array(
		'url'    => function_exists( 'showtime_image' ) ? '' : SHOWTIME_CHILD_URI . '/assets/img/logo.png',
		'width'  => 0,
		'height' => 0,
		'alt'    => (string) apply_filters( 'showtime/business/name', 'Showtime Pools' ),
	);

	if ( ! function_exists( 'showtime_image' ) ) {
		return $fallback;
	}

	if ( is_front_page() ) {
		return array_merge( showtime_og_slot_image( 'hero' ), array( 'alt' => $fallback['alt'] ) );
	}

	if ( is_singular() ) {
		$id = (int) get_the_ID();

		// 0. Code-managed project: the registry owns the social image, so every
		// project shares one unique og_image across og:image and twitter:image.
		if ( is_singular( 'project' ) && function_exists( 'showtime_project_data' ) ) {
			$proj = showtime_project_data( $id );
			if ( null !== $proj && '' !== $proj['og_image'] ) {
				$dims = function_exists( 'showtime_project_compare_local_path' )
					? @getimagesize( showtime_project_compare_local_path( $proj['og_image'] ) )
					: false;
				return array(
					'url'    => $proj['og_image'],
					'width'  => is_array( $dims ) ? (int) $dims[0] : 0,
					'height' => is_array( $dims ) ? (int) $dims[1] : 0,
					'alt'    => '' !== $proj['hero_alt'] ? $proj['hero_alt'] : $proj['title'],
				);
			}
		}

		// 1. Featured image wins, exactly as before.
		$thumb_id = get_post_thumbnail_id( $id );
		if ( $thumb_id ) {
			$src = wp_get_attachment_image_src( $thumb_id, 'large' );
			if ( is_array( $src ) && ! empty( $src[0] ) ) {
				$alt = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				return array(
					'url'    => (string) $src[0],
					'width'  => (int) ( $src[1] ?? 0 ),
					'height' => (int) ( $src[2] ?? 0 ),
					'alt'    => '' !== $alt ? $alt : wp_strip_all_tags( get_the_title( $id ) ),
				);
			}
		}

		// 2. Per-page hero slot, via the shared template-gated resolver.
		if ( function_exists( 'showtime_registry_slug' ) ) {
			$svc_slug = showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' );
			if ( '' !== $svc_slug && class_exists( '\\Showtime\\Services' ) ) {
				$svc = \Showtime\Services::get( $svc_slug );
				if ( $svc ) {
					$img = showtime_og_slot_image( 'service_' . $svc_slug );
					if ( '' !== $img['url'] ) {
						// Mirrors the visible hero alt in
						// template-parts/service/section-hero.php.
						$img['alt'] = sprintf(
							/* translators: %s: service name */
							__( '%s by Showtime Pools in Los Angeles', 'showtime-pools' ),
							(string) ( $svc['title'] ?? get_the_title( $id ) )
						);
						return $img;
					}
				}
			}

			$area_slug = showtime_registry_slug( $id, '_showtime_area_slug', 'page-area.php' );
			if ( '' !== $area_slug && class_exists( '\\Showtime\\Areas' ) ) {
				$area = \Showtime\Areas::get( $area_slug );
				if ( $area ) {
					$img = showtime_og_slot_image( 'area_' . $area_slug );
					if ( '' !== $img['url'] ) {
						// Mirrors the visible hero alt in page-area.php.
						$img['alt'] = sprintf(
							/* translators: %s: neighborhood */
							__( 'Pool service in %s, Los Angeles', 'showtime-pools' ),
							(string) ( $area['name'] ?? get_the_title( $id ) )
						);
						return $img;
					}
				}
			}
		}

		// 3. Brand default.
		return array_merge(
			showtime_og_slot_image( 'lifestyle_main' ),
			array( 'alt' => wp_strip_all_tags( get_the_title( $id ) ) ?: $fallback['alt'] )
		);
	}

	// Archives, search, 404: a real, resolvable asset.
	return array_merge( showtime_og_slot_image( 'hero' ), array( 'alt' => $fallback['alt'] ) );
}

/**
 * Open Graph image URL. Thin wrapper kept for backward compatibility — callers
 * that only need the URL are unaffected by the structured resolver above.
 */
function showtime_og_image(): string {
	$data = showtime_og_image_data();
	return (string) $data['url'];
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
		$title  = showtime_seo_title();
		$desc   = showtime_seo_description();
		$og_img = showtime_og_image_data();
		$image  = (string) $og_img['url'];

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
		if ( '' !== $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "
";
			if ( '' !== (string) $og_img['alt'] ) {
				echo '<meta property="og:image:alt" content="' . esc_attr( (string) $og_img['alt'] ) . '">' . "
";
			}
			// Exact dimensions or none at all — never a guess, never a half
			// pair, and never the previous hardcoded 1200x675 (which matched
			// none of the real assets).
			if ( $og_img['width'] > 0 && $og_img['height'] > 0 ) {
				echo '<meta property="og:image:width" content="' . (int) $og_img['width'] . '">' . "
";
				echo '<meta property="og:image:height" content="' . (int) $og_img['height'] . '">' . "
";
			}
		}

		// Twitter Card
		echo '<meta name="twitter:card" content="summary_large_image">' . "
";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "
";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "
";
		if ( '' !== $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "
";
			if ( '' !== (string) $og_img['alt'] ) {
				echo '<meta name="twitter:image:alt" content="' . esc_attr( (string) $og_img['alt'] ) . '">' . "
";
			}
		}
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

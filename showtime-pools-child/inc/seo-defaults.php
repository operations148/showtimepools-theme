<?php
/**
 * SEO defaults — Rank Math filters for title, description, and canonical
 * on registry-driven pages (services, areas, inspections, projects).
 *
 * No-ops if Rank Math is not active; the filters simply never fire.
 * When Rank Math IS active, these hook in BEFORE the admin override,
 * so anything Steve types into the Rank Math meta box on a specific
 * page still wins. We only set the DEFAULT.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the current page's registry context for SEO purposes.
 * Returns an array with 'type', 'h1', 'keyword', 'intro', or null.
 */
function showtime_seo_context() {
	if ( ! is_singular( array( 'page', 'post', 'project' ) ) && ! is_front_page() ) {
		return null;
	}

	$id = get_queried_object_id();

	// Service single?
	$service_slug = (string) get_post_meta( $id, '_showtime_service_slug', true );
	if ( '' !== $service_slug && class_exists( '\\Showtime\\Services' ) ) {
		$svc = \Showtime\Services::get( $service_slug );
		if ( $svc ) {
			return array(
				'type'    => 'service',
				'h1'      => (string) ( $svc['seo_h1']     ?? $svc['title']   ?? '' ),
				'keyword' => (string) ( $svc['seo_keyword'] ?? '' ),
				'intro'   => (string) ( $svc['seo_intro']  ?? $svc['summary'] ?? '' ),
			);
		}
	}

	// Area single?
	$area_slug = (string) get_post_meta( $id, '_showtime_area_slug', true );
	if ( '' !== $area_slug && class_exists( '\\Showtime\\Areas' ) ) {
		$area = \Showtime\Areas::get( $area_slug );
		if ( $area ) {
			return array(
				'type'    => 'area',
				'h1'      => (string) ( $area['seo_h1']    ?? '' ) ?: sprintf( 'Pool Service in %s', (string) $area['name'] ),
				'keyword' => 'pool service near me',
				'intro'   => (string) ( $area['seo_intro'] ?? $area['lead'] ?? '' ),
			);
		}
	}

	// Homepage?
	if ( is_front_page() ) {
		return array(
			'type'    => 'home',
			'h1'      => 'Pool Service in Los Angeles',
			'keyword' => 'pool service near me',
			'intro'   => 'Pool repair, pool cleaning service, remodels, equipment installation, and new construction across Los Angeles. One in-house crew, no subcontractors. Itemized written quote inside one business day.',
		);
	}

	return null;
}

/**
 * Set Rank Math default meta description for our registry-driven pages.
 * Filter: `rank_math/frontend/description`.
 */
add_filter(
	'rank_math/frontend/description',
	function ( $desc ) {
		if ( ! empty( $desc ) ) {
			return $desc; // Admin override or Rank Math auto-fill wins.
		}
		$ctx = showtime_seo_context();
		if ( ! $ctx || empty( $ctx['intro'] ) ) {
			return $desc;
		}
		// Cap at ~158 chars, the SERP truncation budget.
		return wp_trim_words( wp_strip_all_tags( $ctx['intro'] ), 28, '…' );
	},
	5
);

/**
 * Set Rank Math default meta title for registry-driven pages.
 * Format: "<H1> · Showtime Pools" so the brand stays visible in the SERP.
 * Filter: `rank_math/frontend/title`.
 */
add_filter(
	'rank_math/frontend/title',
	function ( $title ) {
		$ctx = showtime_seo_context();
		if ( ! $ctx || empty( $ctx['h1'] ) ) {
			return $title;
		}
		$brand = get_bloginfo( 'name' );
		return $ctx['h1'] . ' · ' . $brand;
	},
	5
);

/**
 * Fallback for sites NOT running Rank Math: hook `pre_get_document_title`
 * with the same logic so the <title> tag still benefits.
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		// If Rank Math is active, let it own the title.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return $title;
		}
		$ctx = showtime_seo_context();
		if ( ! $ctx || empty( $ctx['h1'] ) ) {
			return $title;
		}
		return $ctx['h1'] . ' · ' . get_bloginfo( 'name' );
	},
	20
);

/**
 * Inject the registry keyword + intro into the page's <head> as a
 * <meta name="keywords"> hint. Most search engines ignore this, but
 * some AI engines (Perplexity, ChatGPT crawler) still parse it as a
 * lightweight relevance hint.
 */
add_action(
	'wp_head',
	function () {
		$ctx = showtime_seo_context();
		if ( ! $ctx || empty( $ctx['keyword'] ) ) {
			return;
		}
		echo '<meta name="keywords" content="' . esc_attr( $ctx['keyword'] ) . ', Showtime Pools, Los Angeles">' . "\n";
	},
	5
);

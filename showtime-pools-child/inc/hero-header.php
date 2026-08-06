<?php
/**
 * Header ↔ hero integration — one shared sitewide treatment.
 *
 * The site header is viewport-fixed on every public page. On templates that
 * render a genuine full-bleed hero it overlays that hero (transparent at the
 * top of the page, frosted translucent white once scrolled). On templates
 * without a hero it starts in the frosted state and the body reserves the
 * header's height so nothing hides underneath it.
 *
 * Which of the two it is, is decided ONCE here and published as a single body
 * class (`has-hero` / `no-hero`) plus a matching `data-hero` attribute on the
 * header. No template ever styles itself, and no page-specific CSS selector
 * exists for this behavior.
 *
 * The decision is derived from the template file WordPress actually resolved
 * for the request — not from a URL, slug or page ID — so routing changes can
 * never desynchronise the class from what is really rendered.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template files that render a genuine hero as their first section.
 *
 * A "genuine hero" means a full-bleed banner that owns the top of the page and
 * carries its own dark background (image, video, or brand gradient) — i.e. a
 * surface the header can legibly sit on top of. Everything not listed here
 * (index.php, 404.php, and any template added later) falls back to the
 * readable frosted header, which is the safe default.
 *
 * @return string[] Template basenames.
 */
function showtime_hero_templates(): array {
	return (array) apply_filters(
		'showtime/hero_templates',
		array(
			'front-page.php',        // .home-hero          — video / photo
			'archive.php',           // .int-hero           — photo
			'search.php',            // .int-hero           — brand gradient
			'single.php',            // .post-hero          — photo
			'single-project.php',    // .proj-single__hero  — photo
			'page-about.php',        // .int-hero           — photo
			'page-affiliate.php',    // .int-hero           — brand gradient
			'page-area.php',         // .area-hero          — photo
			'page-areas.php',        // .int-hero           — photo
			'page-blog.php',         // .int-hero           — photo
			'page-contact.php',      // .contact-hero       — photo
			'page-founder.php',      // .int-hero           — photo
			'page-iframe.php',       // .iframe-hero        — photo
			'page-inspection.php',   // .int-hero           — mechanics gradient
			'page-inspections.php',  // .int-hero           — photo
			'page-legal.php',        // .int-hero--compact  — brand gradient
			'page-projects.php',     // .int-hero           — photo
			'page-reviews.php',      // .int-hero           — photo
			'page-service.php',      // .svc-hero           — brand gradient
			'page-services-hub.php', // .int-hero           — photo
			'page-shop.php',         // .int-hero           — photo
			'page-sitemap.php',      // .int-hero           — brand gradient
		)
	);
}

/**
 * Record the template WordPress resolved for this request.
 *
 * Runs inside `template_include`, which fires immediately before the template
 * is included — and therefore before that template calls get_header() and
 * body_class(). Pass-through filter: the template itself is never altered.
 */
add_filter(
	'template_include',
	static function ( $template ) {
		$GLOBALS['showtime_resolved_template'] = is_string( $template ) ? basename( $template ) : '';
		return $template;
	},
	PHP_INT_MAX
);

/**
 * Does the current request render a genuine hero the header can overlay?
 *
 * Fails closed. Every non-page context (admin, login, AJAX, REST, cron,
 * WP-CLI, feeds, embeds, XML-RPC, sitemaps, 404) resolves to false, which is
 * the readable frosted state — the transparent treatment is never applied
 * anywhere it could reduce readability.
 */
function showtime_page_has_hero(): bool {
	if ( is_admin()
		|| wp_doing_ajax()
		|| wp_doing_cron()
		|| ( defined( 'WP_CLI' ) && WP_CLI )
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		|| ( function_exists( 'is_login' ) && is_login() )
		|| is_feed()
		|| is_embed()
		|| is_404()
		|| '' !== (string) get_query_var( 'sitemap', '' )
	) {
		return false;
	}

	$template = (string) ( $GLOBALS['showtime_resolved_template'] ?? '' );

	return '' !== $template && in_array( $template, showtime_hero_templates(), true );
}

/**
 * Publish the decision as a body class.
 *
 * `has-hero` → header overlays the hero, transparent at the top.
 * `no-hero`  → header starts frosted; body reserves its height.
 */
add_filter(
	'body_class',
	static function ( $classes ): array {
		$classes   = (array) $classes;
		$classes[] = showtime_page_has_hero() ? 'has-hero' : 'no-hero';
		return $classes;
	}
);

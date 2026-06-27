<?php
/**
 * Crawl surface: robots.txt directives and core sitemap hygiene.
 *
 * The theme owns the server-rendered crawl signals. WP core already appends
 * the "Sitemap:" line to its virtual robots.txt (WP_Sitemaps::add_robots),
 * so this file only adds the AI-engine allowlist and trims sitemap noise.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Explicitly allow the AI search and answer-engine crawlers. Default WP
 * robots.txt already permits them implicitly, but explicit groups survive
 * stricter edge rules (Cloudflare bot toggles) being layered on later and
 * document intent for anyone editing robots rules.
 */
add_filter(
	'robots_txt',
	function ( string $output, $public ): string {
		if ( ! $public ) {
			return $output;
		}

		$bots = array(
			'GPTBot',
			'OAI-SearchBot',
			'ChatGPT-User',
			'ClaudeBot',
			'Claude-SearchBot',
			'PerplexityBot',
			'Perplexity-User',
			'Google-Extended',
			'CCBot',
			'bingbot',
		);

		$extra = "\n";
		foreach ( $bots as $bot ) {
			$extra .= "User-agent: {$bot}\nAllow: /\n\n";
		}

		return $output . $extra;
	},
	10,
	2
);

/**
 * Serve /llms.txt — the llms.txt standard (https://llmstxt.org/) Markdown
 * file that helps AI engines (ChatGPT, Perplexity, Claude, Gemini, Copilot)
 * understand the site entity and find the canonical pages. Supports the
 * AEO/GEO goals.
 *
 * We answer the request directly off the raw REQUEST_URI basename rather than
 * via a rewrite/query-var, for two reasons:
 *   1. It must resolve at the SITE ROOT (/llms.txt) on live, which is a root
 *      install, AND at /showtimepools/llms.txt on the local subdirectory
 *      install — basename matching handles both with no rewrite flush.
 *   2. WP's own virtual robots.txt rewrite does not resolve on the local
 *      subdirectory install (the request still reaches WP, it just 404s), so
 *      relying on a rewrite would be unverifiable locally. parse_request fires
 *      on the same booted request before the 404 is decided, so this works
 *      in both environments.
 *
 * The body is built from the live registries (Services / Areas) so it never
 * drifts from the actual site.
 */
add_action(
	'parse_request',
	function () {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared as a path basename only, never stored or echoed.
		$path = (string) wp_parse_url( (string) $uri, PHP_URL_PATH );
		if ( 'llms.txt' !== basename( untrailingslashit( $path ) ) ) {
			return;
		}

		if ( ! headers_sent() ) {
			status_header( 200 );
			header( 'Content-Type: text/markdown; charset=utf-8' );
			header( 'X-Robots-Tag: noindex' ); // The file is for ingestion, not indexing.
			header( 'Cache-Control: max-age=86400' );
		}

		echo showtime_llms_txt_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- builder returns pre-escaped plain-text Markdown.
		exit;
	},
	0
);

/**
 * Build the llms.txt Markdown body from the live site registries.
 *
 * @return string Markdown beginning with a single H1, per the llms.txt spec.
 */
function showtime_llms_txt_body(): string {
	$base    = home_url( '/' );
	$phone   = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
	$email   = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );
	$book    = function_exists( 'showtime_booking_url' ) ? showtime_booking_url() : home_url( '/book/' );

	$lines   = array();
	$lines[] = '# Showtime Pools';
	$lines[] = '';
	$lines[] = '> Showtime Pools is a Los Angeles pool service company offering pool repair, '
		. 'weekly pool cleaning and maintenance, pool remodeling and resurfacing, equipment '
		. 'installation and upgrades, and new pool construction across Los Angeles and the San '
		. 'Fernando Valley. Family-run, licensed and insured, and supervised by founder Steve '
		. 'Adams on every job. Call ' . $phone . '.';
	$lines[] = '';

	// Services — enumerated from the registry so the list never drifts.
	$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
	if ( $services ) {
		$lines[] = '## Services';
		$lines[] = '';
		foreach ( $services as $svc ) {
			$slug  = (string) ( $svc['slug'] ?? '' );
			$title = (string) ( $svc['title'] ?? '' );
			if ( '' === $slug || '' === $title ) {
				continue;
			}
			$summary = trim( wp_strip_all_tags( (string) ( $svc['summary'] ?? '' ) ) );
			$url     = home_url( '/services/' . $slug . '/' );
			$lines[] = '- [' . $title . '](' . $url . ')' . ( '' !== $summary ? ': ' . $summary : '' );
		}
		$lines[] = '';
	}

	// Service areas — hub plus each city from the registry.
	$areas = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();
	$lines[] = '## Service Areas';
	$lines[] = '';
	$lines[] = '- [Service Areas](' . home_url( '/service-areas/' ) . '): Pool service across Los Angeles and the San Fernando Valley.';
	foreach ( $areas as $area ) {
		$slug = (string) ( $area['slug'] ?? '' );
		$name = (string) ( $area['name'] ?? '' );
		if ( '' === $slug || '' === $name ) {
			continue;
		}
		$lines[] = '- [' . $name . '](' . home_url( '/service-areas/' . $slug . '/' ) . ')';
	}
	$lines[] = '';

	// Company.
	$lines[] = '## Company';
	$lines[] = '';
	$lines[] = '- [About Showtime Pools](' . home_url( '/about/' ) . ')';
	$lines[] = '- [The Founder — Steve Adams](' . home_url( '/the-founder/' ) . ')';
	$lines[] = '- [Projects](' . home_url( '/projects/' ) . ')';
	$lines[] = '- [Blog](' . home_url( '/blog/' ) . ')';
	$lines[] = '';

	// Contact & booking.
	$lines[] = '## Contact & Booking';
	$lines[] = '';
	$lines[] = '- [Contact](' . home_url( '/contact/' ) . '): Request a quote or reach the team.';
	$lines[] = '- [Book an Appointment](' . $book . '): Schedule pool service or a free quote.';
	$lines[] = '- Phone: ' . $phone;
	$lines[] = '- Email: ' . $email;
	$lines[] = '';

	$lines[] = 'Canonical site: ' . $base;
	$lines[] = '';

	return implode( "\n", $lines );
}

// Drop the users sitemap (author archives invite user enumeration and
// carry zero search value for a single-author service business).
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, string $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

// Keep the default "Uncategorized" term out of the category sitemap. It
// only ever holds placeholder content and dilutes the canonical set.
add_filter(
	'wp_sitemaps_taxonomies_query_args',
	function ( array $args, string $taxonomy ): array {
		if ( 'category' !== $taxonomy ) {
			return $args;
		}
		$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $uncategorized instanceof WP_Term ) {
			$args['exclude'] = array_merge( (array) ( $args['exclude'] ?? array() ), array( $uncategorized->term_id ) );
		}
		return $args;
	},
	10,
	2
);

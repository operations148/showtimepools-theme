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
 * Serve /llms.txt and /llms-full.txt — the llms.txt standard
 * (https://llmstxt.org/) Markdown files that help AI engines (ChatGPT,
 * Perplexity, Claude, Gemini, Copilot) understand the site entity and find
 * the canonical pages. llms.txt is a curated index (links + one-line
 * summaries); llms-full.txt is the full-text companion (complete per-service
 * and per-area content) for single-request ingestion. Supports the AEO/GEO
 * goals.
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
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared as a path basename only, never stored or echoed.
		$path = (string) wp_parse_url( (string) $uri, PHP_URL_PATH );
		$base = basename( untrailingslashit( $path ) );

		if ( 'llms.txt' !== $base && 'llms-full.txt' !== $base ) {
			return;
		}

		if ( ! headers_sent() ) {
			status_header( 200 );
			header( 'Content-Type: text/markdown; charset=utf-8' );
			header( 'X-Robots-Tag: noindex' ); // The file is for ingestion, not indexing.
			header( 'Cache-Control: max-age=86400' );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- builders return pre-escaped plain-text Markdown.
		echo 'llms-full.txt' === $base ? showtime_llms_full_txt_body() : showtime_llms_txt_body();
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

	$lines[] = 'Full-text version (per-service and per-area detail): ' . home_url( '/llms-full.txt' );
	$lines[] = '';
	$lines[] = 'Canonical site: ' . $base;
	$lines[] = '';

	return implode( "\n", $lines );
}

/**
 * Build the llms-full.txt Markdown body — the full-text companion to
 * llms.txt. Where llms.txt is a curated index of links + one-line summaries,
 * this concatenates the actual per-service and per-area content (intro,
 * price, turnaround, what's included, FAQs, local characteristics) so an AI
 * system can ingest the substance in one request instead of crawling every
 * page. Sourced from the same live registries as llms.txt, so it never
 * drifts from the site.
 *
 * @return string Markdown beginning with a single H1, per the llms.txt spec.
 */
function showtime_llms_full_txt_body(): string {
	$phone = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
	$email = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );

	$lines   = array();
	$lines[] = '# Showtime Pools — Full Content';
	$lines[] = '';
	$lines[] = '> Full-text companion to /llms.txt. One supervised crew for pool repairs, weekly '
		. 'service, remodels, equipment, inspections, and outdoor living across Los Angeles and the '
		. 'San Fernando Valley. Call ' . $phone . '.';
	$lines[] = '';

	// Services — full detail per service.
	$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
	if ( $services ) {
		$lines[] = '## Services';
		$lines[] = '';
		foreach ( $services as $svc ) {
			$slug = (string) ( $svc['slug'] ?? '' );
			$title = (string) ( $svc['title'] ?? '' );
			if ( '' === $slug || '' === $title ) {
				continue;
			}
			$url = home_url( '/services/' . $slug . '/' );
			$lines[] = '### ' . $title;
			$lines[] = '';
			$lines[] = 'URL: ' . $url;
			$intro = trim( wp_strip_all_tags( (string) ( $svc['seo_intro'] ?? $svc['summary'] ?? '' ) ) );
			if ( '' !== $intro ) {
				$lines[] = '';
				$lines[] = $intro;
			}
			$price      = trim( wp_strip_all_tags( (string) ( $svc['default_price'] ?? '' ) ) );
			$turnaround = trim( wp_strip_all_tags( (string) ( $svc['default_turnaround'] ?? '' ) ) );
			if ( '' !== $price || '' !== $turnaround ) {
				$lines[] = '';
				if ( '' !== $price ) { $lines[] = '- Price: ' . $price; }
				if ( '' !== $turnaround ) { $lines[] = '- Turnaround: ' . $turnaround; }
			}
			$includes = (array) ( $svc['default_includes'] ?? array() );
			if ( $includes ) {
				$lines[] = '';
				$lines[] = 'What is included:';
				foreach ( $includes as $item ) {
					$lines[] = '- ' . trim( wp_strip_all_tags( (string) $item ) );
				}
			}
			$faqs = (array) ( $svc['default_faqs'] ?? array() );
			if ( $faqs ) {
				$lines[] = '';
				$lines[] = 'FAQs:';
				foreach ( $faqs as $faq ) {
					$q = trim( wp_strip_all_tags( (string) ( $faq['q'] ?? '' ) ) );
					$a = trim( wp_strip_all_tags( (string) ( $faq['a'] ?? '' ) ) );
					if ( '' === $q || '' === $a ) {
						continue;
					}
					$lines[] = '- Q: ' . $q;
					$lines[] = '  A: ' . $a;
				}
			}
			$lines[] = '';
		}
	}

	// Service areas — full detail per city.
	$areas = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();
	if ( $areas ) {
		$lines[] = '## Service Areas';
		$lines[] = '';
		foreach ( $areas as $area ) {
			$slug = (string) ( $area['slug'] ?? '' );
			$name = (string) ( $area['name'] ?? '' );
			if ( '' === $slug || '' === $name ) {
				continue;
			}
			$url = home_url( '/service-areas/' . $slug . '/' );
			$lines[] = '### ' . $name;
			$lines[] = '';
			$lines[] = 'URL: ' . $url;
			$intro = trim( wp_strip_all_tags( (string) ( $area['seo_intro'] ?? $area['lead'] ?? '' ) ) );
			if ( '' !== $intro ) {
				$lines[] = '';
				$lines[] = $intro;
			}
			$characteristics = (array) ( $area['characteristics'] ?? array() );
			if ( $characteristics ) {
				$lines[] = '';
				$lines[] = 'Local conditions:';
				foreach ( $characteristics as $c ) {
					$lines[] = '- ' . trim( wp_strip_all_tags( (string) $c ) );
				}
			}
			$jobs = (array) ( $area['common_jobs'] ?? array() );
			if ( $jobs ) {
				$lines[] = '';
				$lines[] = 'Common jobs in this area:';
				foreach ( $jobs as $j ) {
					$lines[] = '- ' . trim( wp_strip_all_tags( (string) $j ) );
				}
			}
			$lines[] = '';
		}
	}

	// Company & contact.
	$book    = function_exists( 'showtime_booking_url' ) ? showtime_booking_url() : home_url( '/book/' );
	$lines[] = '## Contact & Booking';
	$lines[] = '';
	$lines[] = '- Contact: ' . home_url( '/contact/' );
	$lines[] = '- Book an Appointment: ' . $book;
	$lines[] = '- Phone: ' . $phone;
	$lines[] = '- Email: ' . $email;
	$lines[] = '';
	$lines[] = 'Canonical site: ' . home_url( '/' );
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

// Keep the noindex utility pages (/book/, /quote/, /shop/) OUT of the pages
// sitemap. WordPress core does not honour the robots noindex meta for
// sitemaps, so a page can be noindexed yet still listed. We drive the
// exclusion off the SAME template list as the robots meta
// (showtime_noindex_page_templates) so the two never disagree. The OR with
// NOT EXISTS keeps default-template pages (no _wp_page_template meta) in.
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( array $args, string $post_type ): array {
		if ( 'page' !== $post_type || ! function_exists( 'showtime_noindex_page_templates' ) ) {
			return $args;
		}
		$templates = showtime_noindex_page_templates();
		if ( empty( $templates ) ) {
			return $args;
		}
		$meta_query   = (array) ( $args['meta_query'] ?? array() );
		$meta_query[] = array(
			'relation' => 'OR',
			array( 'key' => '_wp_page_template', 'value' => $templates, 'compare' => 'NOT IN' ),
			array( 'key' => '_wp_page_template', 'compare' => 'NOT EXISTS' ),
		);
		$args['meta_query'] = $meta_query;
		return $args;
	},
	10,
	2
);

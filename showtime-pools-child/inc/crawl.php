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

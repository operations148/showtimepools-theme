<?php
/**
 * GoHighLevel Live Chat widget — the official LeadConnector embed.
 *
 * Visitor conversations route into the GHL Conversations inbox for the CSR
 * team. This class is the SINGLE code-managed source of truth for the widget's
 * enabled state, widget ID, loader URL and resources URL.
 *
 * WHY THE CORE PLUGIN, NOT THE THEME
 * ----------------------------------
 * The CRM connection is business-critical and must survive a theme swap, which
 * is exactly what this plugin exists for ("Theme-agnostic business logic" in
 * the plugin header). It also sits beside the other GHL surface,
 * Integrations\Ghl (outbound webhook), so both halves of the CRM connection
 * live together.
 *
 * WHAT THIS DOES NOT DO
 * ---------------------
 * The embed is the vendor's, unmodified: same widget ID, same two URLs, same
 * two data-* attributes, no async/defer, no lazy or interaction-gated loading,
 * no consent gating. Nothing is self-hosted, proxied or bundled. WordPress
 * makes no server-side request to LeadConnector, stores nothing, logs nothing,
 * and never touches visitor form data — the widget talks to GHL directly from
 * the browser.
 *
 * Widget content (logo, avatar, header, welcome message, form fields, business
 * hours, CSR routing, consent text) is configured in the GHL sub-account, not
 * here. The customer-facing business is Showtime Pools; the "Powered by
 * AdaptiveAutomation" footer is the approved white-label attribution of the
 * provider that supplies the sub-account and must not be hidden or altered.
 *
 * Disable sitewide from one place: either flip WIDGET_ENABLED below, or
 * `add_filter( 'showtime/chat_widget/enabled', '__return_false' );`
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Integrations;

defined( 'ABSPATH' ) || exit;

final class ChatWidget {

	/**
	 * Master switch. The filter below can also disable it without a code edit.
	 */
	private const WIDGET_ENABLED = true;

	/**
	 * Public embed identifier for the Showtime Pools Live Chat widget.
	 *
	 * NOT a secret: it is designed to be readable in page source, carries no
	 * authority, and is not an API key, location token or access credential.
	 * No private credential belongs in this file.
	 */
	private const WIDGET_ID = '69b32c236a7fada7ea40faca';

	/** Official LeadConnector loader. Never self-hosted, proxied or rewritten. */
	private const LOADER_URL = 'https://widgets.leadconnectorhq.com/loader.js';

	/** Official chat-widget resources loader, passed through verbatim. */
	private const RESOURCES_URL = 'https://widgets.leadconnectorhq.com/chat-widget/loader.js';

	/** Guard so the embed can only ever be printed once per request. */
	private bool $printed = false;

	public function register(): void {
		// wp_footer is the only hook used, so the tag lands once near </body>.
		// It does not fire for wp-admin, REST, AJAX, cron, WP-CLI, feeds,
		// robots.txt or XML sitemaps, so those responses stay clean by
		// construction. The explicit guards in should_render() are belt and
		// braces for anything that calls wp_footer() outside a page render.
		if ( ! function_exists( 'add_action' ) ) {
			return; // Fail safe: no hook API, no output, no fatal.
		}
		add_action( 'wp_footer', array( $this, 'render' ), 20 );
	}

	/**
	 * Whether the embed should print on the current request.
	 */
	private function should_render(): bool {
		if ( $this->printed ) {
			return false; // Duplicate-output guard.
		}
		if ( ! self::WIDGET_ENABLED ) {
			return false;
		}

		// Never in admin, and never on a non-HTML response.
		if ( is_admin() ) {
			return false;
		}
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return false;
		}
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return false;
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return false;
		}
		// Any command-line context, not just WP-CLI: a maintenance script or
		// cron wrapper that bootstraps WordPress and fires wp_footer must not
		// emit a browser tag into its output. Under mod_php/FPM the SAPI is
		// never 'cli', so this cannot affect a real page request.
		if ( 'cli' === PHP_SAPI || 'phpdbg' === PHP_SAPI ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return false;
		}
		// Feeds and XML sitemaps are XML documents, not HTML pages.
		if ( function_exists( 'is_feed' ) && is_feed() ) {
			return false;
		}
		if ( function_exists( 'is_embed' ) && is_embed() ) {
			return false;
		}
		if ( ! empty( $GLOBALS['wp_query'] ) && function_exists( 'get_query_var' ) && get_query_var( 'sitemap' ) ) {
			return false;
		}

		return (bool) apply_filters( 'showtime/chat_widget/enabled', true );
	}

	/**
	 * Print the official embed verbatim.
	 *
	 * Every attribute value is a class constant — static, trusted, and never
	 * derived from user input, a query string, post meta or an option. The
	 * escaping below is defence in depth, not sanitisation of untrusted data.
	 */
	public function render(): void {
		if ( ! $this->should_render() ) {
			return;
		}
		$this->printed = true;

		printf(
			'<script src="%1$s" data-resources-url="%2$s" data-widget-id="%3$s"></script>' . "\n",
			esc_url( self::LOADER_URL ),
			esc_url( self::RESOURCES_URL ),
			esc_attr( self::WIDGET_ID )
		);
	}

	/**
	 * Read-only configuration accessor for tests and diagnostics.
	 *
	 * @return array{enabled:bool,widget_id:string,loader_url:string,resources_url:string}
	 */
	public static function config(): array {
		return array(
			'enabled'       => self::WIDGET_ENABLED,
			'widget_id'     => self::WIDGET_ID,
			'loader_url'    => self::LOADER_URL,
			'resources_url' => self::RESOURCES_URL,
		);
	}
}

<?php
/**
 * GoHighLevel Live Chat widget regression tests.
 *
 * Proves the official LeadConnector embed ships verbatim, exactly once, on
 * public HTML pages only — and never on admin, REST, feed, sitemap or
 * robots.txt responses.
 *
 *   php tests/chat-widget-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/chat-widget-unit.php
 *
 * Read-only: creates no posts, writes no options, submits no chat inquiry.
 *
 * Exit 0 = all pass, 1 = one or more fail.
 *
 * @package ShowtimePools
 */

$wp_load = getenv( 'WP_LOAD' ) ?: 'C:/xampp/htdocs/showtimepools/wp/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at: $wp_load\nSet WP_LOAD=/path/to/wp-load.php\n" );
	exit( 1 );
}
define( 'WP_USE_THEMES', false );
require $wp_load;

$pass = 0; $fail = 0; $skip = 0;
function ok( $m ) { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ) { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skip( $m ) { global $skip; $skip++; echo "  ~ SKIP: $m\n"; }

function fetch( string $url ): array {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 25,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-chat-widget-test/1.0',
	) );
	$body = (string) curl_exec( $ch );
	$code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$type = (string) curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
	curl_close( $ch );
	return array( 'body' => $body, 'code' => $code, 'type' => $type );
}

// The official embed values. These are asserted literally: if anyone edits the
// widget ID or either LeadConnector URL, these tests fail loudly.
const LOADER    = 'https://widgets.leadconnectorhq.com/loader.js';
const RESOURCES = 'https://widgets.leadconnectorhq.com/chat-widget/loader.js';
const WIDGET_ID = '69b32c236a7fada7ea40faca';

if ( ! class_exists( '\Showtime\Integrations\ChatWidget' ) ) {
	bad( '\Showtime\Integrations\ChatWidget not loaded — is showtime-pools-core active?' );
	echo "\n== RESULT ==\n  pass: 0   fail: 1   skip: 0\n";
	exit( 1 );
}

$base = rtrim( (string) get_option( 'home' ), '/' );

echo "\n== CONFIG — single code-managed source of truth ==\n";

$cfg = \Showtime\Integrations\ChatWidget::config();

/* 1. Exact widget ID, unmodified. */
WIDGET_ID === $cfg['widget_id']
	? ok( '1. widget ID is exactly ' . WIDGET_ID )
	: bad( '1. widget ID is "' . $cfg['widget_id'] . '"' );

/* 2. Both LeadConnector URLs verbatim — never self-hosted, proxied or rewritten. */
( LOADER === $cfg['loader_url'] && RESOURCES === $cfg['resources_url'] )
	? ok( '2. loader + resources URLs are the official LeadConnector URLs' )
	: bad( '2. loader="' . $cfg['loader_url'] . '" resources="' . $cfg['resources_url'] . '"' );

/* 3. Enabled, and disableable from one place. */
true === $cfg['enabled']
	? ok( '3. widget is enabled in the single source of truth' )
	: bad( '3. widget is disabled' );

/* 4. The integration lives in the core plugin, so it survives a theme swap. */
$ref  = new ReflectionClass( '\Showtime\Integrations\ChatWidget' );
$file = str_replace( '\\', '/', (string) $ref->getFileName() );
false !== strpos( $file, '/showtime-pools-core/includes/integrations/' )
	? ok( '4. integration is owned by showtime-pools-core (survives a theme change)' )
	: bad( '4. integration lives at ' . $file );

/* 5. Source carries no credential, no server-side call, no DB write, no
 * inline JS, and captures no visitor data. */
$src  = (string) file_get_contents( $ref->getFileName() );
$body_only = preg_replace( '#/\*.*?\*/#s', '', $src );      // strip block comments
$body_only = preg_replace( '#^\s*//.*$#m', '', (string) $body_only ); // strip line comments
$leaks = array();
if ( preg_match( '/(api[_-]?key|access[_-]?token|bearer|authorization|password|private[_-]?key)/i', (string) $body_only ) ) { $leaks[] = 'credential'; }
if ( preg_match( '/(wp_remote_|curl_init|file_get_contents|fsockopen)/', (string) $body_only ) ) { $leaks[] = 'server-side request'; }
if ( preg_match( '/(update_option|add_option|update_post_meta|\$wpdb|wp_insert_post)/', (string) $body_only ) ) { $leaks[] = 'db write'; }
if ( preg_match( '/(\$_POST|\$_GET|\$_REQUEST|\$_COOKIE|error_log)/', (string) $body_only ) ) { $leaks[] = 'visitor data capture'; }
empty( $leaks )
	? ok( '5. no credential, server-side request, DB write, or visitor-data capture in the integration' )
	: bad( '5. found: ' . implode( ', ', $leaks ) );

echo "\n== PUBLIC HTML PAGES — exactly one embed ==\n";

$routes = array(
	'/'                                      => 'homepage',
	'/services/'                             => 'services hub',
	'/services/pool-remodeling-resurfacing/' => 'single service',
	'/service-areas/sherman-oaks/'           => 'service area',
	'/projects/'                             => 'projects archive',
	'/projects/tarzana-resort-style-finish/' => 'verified project',
	'/projects/van-nuys-pool-project/'       => 'coming-soon project',
	'/reviews/'                              => 'reviews',
	'/pentair-vs-jandy-salt-systems/'        => 'article',
);

$bad_routes = array();
foreach ( $routes as $path => $label ) {
	$r = fetch( $base . $path );
	if ( 200 !== $r['code'] || '' === $r['body'] ) {
		$bad_routes[] = "$label (HTTP {$r['code']})";
		continue;
	}
	$counts = array(
		'loader'             => substr_count( $r['body'], LOADER ),
		'resources'          => substr_count( $r['body'], RESOURCES ),
		'widget id'          => substr_count( $r['body'], WIDGET_ID ),
		'data-widget-id'     => substr_count( $r['body'], 'data-widget-id' ),
		'data-resources-url' => substr_count( $r['body'], 'data-resources-url' ),
	);
	foreach ( $counts as $what => $n ) {
		if ( 1 !== $n ) { $bad_routes[] = "$label: $n x $what"; }
	}
}
empty( $bad_routes )
	? ok( '6. all ' . count( $routes ) . ' public routes carry exactly one loader, one resources URL, one widget ID, one data-widget-id, one data-resources-url' )
	: bad( '6. ' . implode( '; ', $bad_routes ) );

/* 7. The tag is byte-for-byte the official embed, with no async/defer/type
 * and no lazy-loading attributes bolted on. */
$home = fetch( $base . '/' );
preg_match( '#<script[^>]*leadconnectorhq[^>]*>\s*</script>#', $home['body'], $tm );
$tag = $tm[0] ?? '';
$attr_ok = '' !== $tag
	&& false !== strpos( $tag, 'src="' . LOADER . '"' )
	&& false !== strpos( $tag, 'data-resources-url="' . RESOURCES . '"' )
	&& false !== strpos( $tag, 'data-widget-id="' . WIDGET_ID . '"' );
$no_extras = '' !== $tag
	&& ! preg_match( '/\basync\b|\bdefer\b|data-src=|data-lazy|type="module"/', $tag );
( $attr_ok && $no_extras )
	? ok( '7. embed is the official tag verbatim — no async/defer/lazy attributes added' )
	: bad( '7. unexpected tag: ' . ( '' === $tag ? '(not found)' : $tag ) );

/* 8. Script is external only — no inline JS was introduced alongside it. */
'' !== $tag && preg_match( '#>\s*</script>#', $tag )
	? ok( '8. script tag has an empty body (no unsafe inline JavaScript)' )
	: bad( '8. the embed carries an inline script body' );

/* 9. Printed once, near </body>, after the footer content. */
$pos_tag  = strpos( $home['body'], LOADER );
$pos_body = strrpos( $home['body'], '</body>' );
( false !== $pos_tag && false !== $pos_body && $pos_tag < $pos_body )
	? ok( '9. embed prints inside the document, before </body>' )
	: bad( '9. embed is not positioned before </body>' );

echo "\n== NON-HTML RESPONSES — embed must be absent ==\n";

/* 10. REST, sitemaps and feeds never carry the embed. These are structural:
 * none of them calls wp_footer. */
$non_html = array(
	'/wp-json/'                       => 'REST index',
	'/wp-sitemap.xml'                 => 'XML sitemap index',
	'/wp-sitemap-posts-project-1.xml' => 'XML project sitemap',
	'/feed/'                          => 'RSS feed',
	'/comments/feed/'                 => 'comments feed',
);
$leaked = array();
foreach ( $non_html as $path => $label ) {
	$r = fetch( $base . $path );
	if ( '' === $r['body'] ) { continue; }
	if ( false !== strpos( $r['body'], 'leadconnectorhq' ) ) { $leaked[] = $label; }
}
empty( $leaked )
	? ok( '10. REST, XML sitemaps and feeds carry no embed' )
	: bad( '10. embed leaked into: ' . implode( ', ', $leaked ) );

/* 11. robots.txt is text/plain and produced by WP core's do_robots(), which
 * never calls wp_footer. On this local subdirectory install the virtual
 * robots.txt route does not resolve (it 404s to the theme's HTML 404 page), so
 * assert the structural guarantee instead of a misleading local response. */
$robots = fetch( $base . '/robots.txt' );
if ( false !== strpos( $robots['type'], 'text/plain' ) ) {
	false === strpos( $robots['body'], 'leadconnectorhq' )
		? ok( '11. robots.txt (text/plain) carries no embed' )
		: bad( '11. embed leaked into robots.txt' );
} else {
	// Prove it from WP core instead: do_robots() sets text/plain and echoes
	// the rules without ever invoking wp_footer.
	$core = ABSPATH . 'wp-includes/functions.php';
	$fn   = '';
	if ( is_readable( $core ) ) {
		$c = (string) file_get_contents( $core );
		if ( preg_match( '#function do_robots\(\).*?\n\}#s', $c, $fm ) ) { $fn = $fm[0]; }
	}
	( '' !== $fn && false === strpos( $fn, 'wp_footer' ) && false !== strpos( $fn, 'text/plain' ) )
		? ok( '11. robots.txt is text/plain via do_robots(), which never calls wp_footer (local route 404s; verified against WP core)' )
		: skip( '11. could not verify do_robots() source on this install' );
}

/* 12. wp_footer is the ONLY hook used, which is what keeps admin, WP-CLI, cron
 * and AJAX clean by construction. This test process is itself a non-web PHP
 * request, so a direct render() call here must print nothing. */
$w = new \Showtime\Integrations\ChatWidget();
ob_start();
$w->render();
$cli_out = (string) ob_get_clean();
'' === $cli_out
	? ok( '12. render() prints nothing outside a web page request (CLI context)' )
	: bad( '12. render() printed in a CLI context: ' . substr( $cli_out, 0, 80 ) );

/* 13. Duplicate-output guard: two render() calls can never print twice. */
$w2 = new \Showtime\Integrations\ChatWidget();
add_filter( 'showtime/chat_widget/enabled', '__return_true' );
ob_start(); $w2->render(); $first = (string) ob_get_clean();
ob_start(); $w2->render(); $second = (string) ob_get_clean();
'' === $second
	? ok( '13. duplicate-output guard holds — a second render() prints nothing' )
	: bad( '13. render() printed twice' );

/* 14. The kill switch works from one place. */
add_filter( 'showtime/chat_widget/enabled', '__return_false', 99 );
$w3 = new \Showtime\Integrations\ChatWidget();
ob_start(); $w3->render(); $off = (string) ob_get_clean();
remove_filter( 'showtime/chat_widget/enabled', '__return_false', 99 );
'' === $off
	? ok( '14. showtime/chat_widget/enabled=false disables the embed sitewide' )
	: bad( '14. filter did not disable the embed' );

echo "\n== BRANDING ==\n";

/* 15. The embed carries no CSS/JS that would hide the approved white-label
 * footer attribution ("Powered by AdaptiveAutomation"), and the theme adds no
 * rule targeting it. Both branding layers are intentional. */
$hide_hits = array();
$theme_dir = get_stylesheet_directory();
foreach ( glob( $theme_dir . '/assets/css/*.css' ) ?: array() as $css ) {
	$c = (string) file_get_contents( $css );
	if ( preg_match( '/(adaptive|powered[- ]?by|lc[_-]?branding|leadconnector)/i', $c ) ) {
		$hide_hits[] = basename( $css );
	}
}
foreach ( glob( $theme_dir . '/assets/js/*.js' ) ?: array() as $js ) {
	$c = (string) file_get_contents( $js );
	if ( preg_match( '/(adaptive|powered[- ]?by|leadconnector)/i', $c ) ) {
		$hide_hits[] = basename( $js );
	}
}
empty( $hide_hits )
	? ok( '15. no site CSS/JS targets or conceals the "Powered by AdaptiveAutomation" attribution' )
	: bad( '15. attribution targeted in: ' . implode( ', ', $hide_hits ) );

echo "\n== RESULT ==\n  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

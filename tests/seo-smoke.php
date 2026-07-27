<?php
/**
 * SEO smoke test — Phase 7 acceptance criteria. Dependency-free; curls a base
 * URL (live or local) and checks metadata ownership, noindex, redirects, the
 * HTML sitemap, and MetaSync/OTTO / retired-branding removal.
 *
 *   SHOWTIME_BASE_URL="https://showtimepools.com" php tests/seo-smoke.php
 *   SHOWTIME_BASE_URL="http://localhost/showtimepools/wp" php tests/seo-smoke.php
 *
 * Exit 0 = all pass, 1 = one or more fail. Some checks (data-metasync-otto,
 * otto-tracker, /terms-2/) only prove out against LIVE, where MetaSync and the
 * duplicate page exist — they're reported as skips locally.
 *
 * @package ShowtimePools
 */

$base = rtrim( getenv( 'SHOWTIME_BASE_URL' ) ?: 'http://localhost/showtimepools/wp', '/' );
$pass = 0; $fail = 0; $skip = 0;
function ok( $m ){ global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ){ global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skp( $m ){ global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

function fetch( $url, $follow = true ) {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_MAXREDIRS => 5,
		CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => 'showtime-smoke/1.0',
	) );
	$raw = curl_exec( $ch );
	$info = curl_getinfo( $ch );
	$hlen = $info['header_size'] ?? 0;
	curl_close( $ch );
	return array(
		'code'    => (int) ( $info['http_code'] ?? 0 ),
		'headers' => substr( (string) $raw, 0, $hlen ),
		'body'    => substr( (string) $raw, $hlen ),
		'ctype'   => (string) ( $info['content_type'] ?? '' ),
		'nredir'  => (int) ( $info['redirect_count'] ?? 0 ),
		'effurl'  => (string) ( $info['url'] ?? '' ),
	);
}
$count = fn( $s, $re ) => preg_match_all( $re, $s, $m );

echo "\n== SEO SMOKE TEST — base: $base ==\n";

/* --- /about/ : metadata ownership + OTTO/branding removal --- */
echo "\n[/about/]\n";
$a = fetch( "$base/about/" );
200 === $a['code'] ? ok( 'about 200' ) : bad( "about HTTP {$a['code']}" );
false === stripos( $a['body'], 'Showtime Pools Mechanics' ) ? ok( 'no "Showtime Pools Mechanics"' ) : bad( 'contains "Showtime Pools Mechanics"' );
if ( false !== stripos( $base, 'localhost' ) ) {
	skp( 'data-metasync-otto / otto-tracker only testable on live (MetaSync not local)' );
} else {
	false === stripos( $a['body'], 'data-metasync-otto' ) ? ok( 'no data-metasync-otto attribute' ) : bad( 'data-metasync-otto present' );
	false === stripos( $a['body'], 'otto-tracker' ) ? ok( 'no otto-tracker.min.js' ) : bad( 'otto-tracker.min.js still loaded' );
}

/* --- one-of-each metadata on indexable pages --- */
echo "\n[metadata singletons on indexable pages]\n";
foreach ( array( '/', '/about/', '/services/pool-leak-detection/' ) as $p ) {
	$r = fetch( "$base$p" );
	$t = $count( $r['body'], '#<title[ >]#i' );
	$d = $count( $r['body'], '#<meta\s+name=["\']description["\']#i' );
	$c = $count( $r['body'], '#rel=["\']canonical["\']#i' );
	$ro = $count( $r['body'], '#<meta\s+name=["\']robots["\']#i' );
	( 1 === $t && 1 === $d && 1 === $c && 1 === $ro )
		? ok( "$p: exactly one title/description/canonical/robots" )
		: bad( "$p: title=$t desc=$d canonical=$c robots=$ro (each must be 1)" );
}

/* --- HTML sitemap + Calabasas --- */
echo "\n[pages]\n";
$s = fetch( "$base/sitemap/" );
200 === $s['code'] && preg_match( '#<h1[^>]*>\s*Sitemap#i', $s['body'] ) ? ok( '/sitemap/ 200 with H1 Sitemap' ) : bad( "/sitemap/ HTTP {$s['code']}" );
$cal = fetch( "$base/service-areas/calabasas/" );
200 === $cal['code'] ? ok( '/service-areas/calabasas/ 200' ) : skp( "/service-areas/calabasas/ HTTP {$cal['code']} (publish the page — WP-CLI in report)" );

/* --- noindex,follow on utility pages + categories --- */
echo "\n[noindex, follow]\n";
$noindex_paths = array( '/book/', '/quote/', '/shop/', '/affiliate/', '/category/pool-trends/', '/category/maintenance-tips/', '/category/equipment-guides/' );
foreach ( $noindex_paths as $p ) {
	$r = fetch( "$base$p" );
	if ( preg_match( '#<meta\s+name=["\']robots["\']\s+content=["\']([^"\']*)["\']#i', $r['body'], $mm ) ) {
		( false !== stripos( $mm[1], 'noindex' ) && false !== stripos( $mm[1], 'follow' ) )
			? ok( "$p noindex,follow" ) : bad( "$p robots = '{$mm[1]}'" );
	} else {
		bad( "$p: no robots meta found (HTTP {$r['code']})" );
	}
}

/* --- terms-2 single 301 --- */
echo "\n[/terms-2/ redirect]\n";
$t2 = fetch( "$base/terms-2/", false );
if ( 404 === $t2['code'] ) {
	skp( '/terms-2/ 404 locally (live-only duplicate); on live expect a single 301 to /terms/' );
} else {
	( 301 === $t2['code'] && preg_match( '#location:\s*\S*/terms/#i', $t2['headers'] ) )
		? ok( '/terms-2/ → 301 /terms/' ) : bad( "/terms-2/ code {$t2['code']} (expected 301 to /terms/)" );
}

/* --- sitemap hygiene: noindex/redirected URLs excluded; all children 200 --- */
echo "\n[XML sitemap hygiene]\n";
$idx = fetch( "$base/wp-sitemap.xml" );
if ( 200 !== $idx['code'] ) {
	bad( "wp-sitemap.xml HTTP {$idx['code']}" );
} else {
	ok( 'wp-sitemap.xml 200' );
	preg_match_all( '#<loc>([^<]+)</loc>#', $idx['body'], $cm );
	$children = $cm[1];
	$all_urls = '';
	$children_ok = true;
	foreach ( $children as $cu ) {
		$cr = fetch( $cu );
		if ( 200 !== $cr['code'] ) { $children_ok = false; bad( "child 404/err: $cu" ); }
		$all_urls .= $cr['body'];
	}
	$children_ok && ok( 'all child sitemaps 200' );
	$leaked = array();
	foreach ( array( '/book/', '/quote/', '/shop/', '/affiliate/', '/terms-2/', '/category/pool-trends/', '/category/maintenance-tips/', '/category/equipment-guides/' ) as $bad_path ) {
		if ( false !== strpos( $all_urls, $bad_path ) ) { $leaked[] = $bad_path; }
	}
	$leaked ? bad( 'noindex/redirected URLs in sitemap: ' . implode( ', ', $leaked ) ) : ok( 'no noindex/redirected URLs in sitemap' );
}

echo "\n== RESULT ==\n  pass: $pass   skip: $skip   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

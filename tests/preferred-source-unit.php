<?php
/**
 * Google "Add to Preferred Sources" integration.
 *
 * Proves the button and its script appear on exactly the two blog surfaces and
 * nowhere else, that Google's own markup is emitted unaltered, and that none of
 * the PR #8 blog-discovery behaviour regressed.
 *
 * STRUCTURAL ONLY. publisher.js renders its real button only for an origin
 * Google recognises, which localhost is not. These assertions therefore verify
 * the CONTAINER, the SCRIPT and the COPY — never that a live interactive Google
 * button painted. Live-domain eligibility is a separate manual check.
 *
 * Run:  php tests/preferred-source-unit.php
 *
 * @package ShowtimePools
 */

define( 'WP_USE_THEMES', false );
$wp_load = getenv( 'WP_LOAD' ) ?: 'C:/xampp/htdocs/showtimepools/wp/wp-load.php';
require $wp_load;

$pass = 0;
$fail = 0;
$skip = 0;
function ok( string $m ): void { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( string $m ): void { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skipped( string $m ): void { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

function ps_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
function ps_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 40, CURLOPT_SSL_VERIFYPEER => false ) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}

const PS_SCRIPT_SRC = 'https://news.google.com/swg/js/v1/publisher.js';
const PS_BTN_ATTR   = 'google-add-preferred-source-btn';
const PS_HEADING    = 'Helpful pool advice from Showtime Pools';
const PS_BODY       = 'Add Showtime Pools as a preferred source on Google.';
const PS_FALLBACK   = 'https://www.google.com/preferences/source?q=showtimepools.com';

$home      = untrailingslashit( home_url() );
$blog_page = get_page_by_path( 'blog' );
$blog_url  = $blog_page ? (string) get_permalink( $blog_page ) : '';

$a_post   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1 ) );
$post_url = $a_post ? (string) get_permalink( $a_post[0] ) : '';

/* ══════════════════════════════════════════════════════════════════
 * PRESENCE — exactly once, on exactly two surfaces
 * ═══════════════════════════════════════════════════════════════ */
echo "== BUTTON PRESENCE ==\n";

$blog_html = '' !== $blog_url ? ps_fetch( $blog_url ) : '';
$post_html = '' !== $post_url ? ps_fetch( $post_url ) : '';

'' !== $blog_html
	? ok( '1. the /blog/ archive renders' )
	: bad( '1. could not fetch the blog archive' );

1 === substr_count( $blog_html, PS_BTN_ATTR )
	? ok( '2. the Google container appears exactly once on /blog/' )
	: bad( '2. /blog/ has ' . substr_count( $blog_html, PS_BTN_ATTR ) . ' containers, expected 1' );

if ( '' === $post_html ) {
	bad( '3. no published post available to test' );
} else {
	1 === substr_count( $post_html, PS_BTN_ATTR )
		? ok( '3. the Google container appears exactly once on an individual post' )
		: bad( '3. the post has ' . substr_count( $post_html, PS_BTN_ATTR ) . ' containers, expected 1' );
}

// Google's element must be emitted verbatim and left EMPTY for publisher.js.
preg_match( '#<div ' . PS_BTN_ATTR . '([^>]*)>(\s*)</div>#', $blog_html, $bm )
	? ok( '4. the container is Google\'s own empty <div ' . PS_BTN_ATTR . '> — nothing injected inside it' )
	: bad( '4. the container is not an empty div carrying Google\'s attribute' );

$attrs = $bm[1] ?? '';
( false !== strpos( $attrs, 'data-theme="light"' ) && false !== strpos( $attrs, 'data-lang="en"' ) )
	? ok( '5. the container carries the official data-theme="light" data-lang="en" attributes' )
	: bad( '5. container attributes are "' . trim( $attrs ) . '"' );

/* ── Absent everywhere else ─────────────────────────────────────── */
echo "\n== ABSENT FROM UNRELATED SURFACES ==\n";

$others = array(
	'homepage'        => $home . '/',
	'/service-areas/' => $home . '/service-areas/',
	'/services/'      => $home . '/services/',
	'/projects/'      => $home . '/projects/',
	'/sitemap/'       => $home . '/sitemap/',
	'/contact/'       => $home . '/contact/',
	'/about/'         => $home . '/about/',
);
$leaked_btn = array();
$leaked_scr = array();
foreach ( $others as $label => $url ) {
	if ( 200 !== ps_status( $url ) ) { continue; }
	$h = ps_fetch( $url );
	if ( false !== strpos( $h, PS_BTN_ATTR ) )   { $leaked_btn[] = $label; }
	if ( false !== strpos( $h, PS_SCRIPT_SRC ) ) { $leaked_scr[] = $label; }
}
empty( $leaked_btn )
	? ok( '6. the container is absent from every unrelated page type' )
	: bad( '6. container leaked onto: ' . implode( ', ', $leaked_btn ) );

empty( $leaked_scr )
	? ok( '7. publisher.js is NOT globally enqueued — absent from every unrelated page type' )
	: bad( '7. script leaked onto: ' . implode( ', ', $leaked_scr ) );

// A service-area single and a project single specifically: they share blog.css.
$extra = array();
foreach ( array( '/service-areas/sherman-oaks/', '/projects/brentwood-pool-project/' ) as $path ) {
	$u = $home . $path;
	if ( 200 !== ps_status( $u ) ) { continue; }
	$h = ps_fetch( $u );
	if ( false !== strpos( $h, PS_BTN_ATTR ) || false !== strpos( $h, PS_SCRIPT_SRC ) ) { $extra[] = $path; }
}
empty( $extra )
	? ok( '8. singles that share blog.css (project, service-area) carry neither the button nor the script' )
	: bad( '8. leaked onto: ' . implode( ', ', $extra ) );

/* ══════════════════════════════════════════════════════════════════
 * SCRIPT LOADING
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== SCRIPT LOADING ==\n";

foreach ( array( '/blog/' => $blog_html, 'post' => $post_html ) as $label => $html ) {
	if ( '' === $html ) { continue; }
	$n = substr_count( $html, PS_SCRIPT_SRC );
	1 === $n
		? ok( "9. publisher.js is loaded exactly once on $label" )
		: bad( "9. publisher.js appears $n times on $label" );
}

preg_match( '#<script([^>]*)src="' . preg_quote( PS_SCRIPT_SRC, '#' ) . '"([^>]*)>#', $blog_html, $sm );
$tag = ( $sm[1] ?? '' ) . ( $sm[2] ?? '' );
'' !== $tag
	? ok( '10. the publisher.js <script> tag is present in the markup' )
	: bad( '10. no publisher.js script tag found' );

false !== strpos( $tag, 'async' )
	? ok( '11. the script is asynchronous' )
	: bad( '11. the script tag is not async: "' . trim( $tag ) . '"' );

false === strpos( $tag, 'defer' )
	? ok( '12. the script is not deferred — publisher.js hydrates the element itself' )
	: bad( '12. the script is deferred, which would delay hydration' );

false === strpos( $blog_html, PS_SCRIPT_SRC . '?ver=' )
	? ok( '13. no ?ver= cache-buster is appended to Google\'s endpoint' )
	: bad( '13. a ?ver= query was appended to Google\'s script URL' );

// The surface predicate and the markup must agree by construction.
function_exists( 'showtime_is_preferred_source_surface' )
	? ok( '14. showtime_is_preferred_source_surface() exists as the single shared predicate' )
	: bad( '14. the shared surface predicate is missing' );

/* ══════════════════════════════════════════════════════════════════
 * COPY
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== APPROVED COPY ==\n";

$copy_bad = array();
foreach ( array( '/blog/' => $blog_html, 'post' => $post_html ) as $label => $html ) {
	if ( '' === $html ) { continue; }
	if ( 1 !== substr_count( $html, PS_HEADING ) ) { $copy_bad[] = "$label heading x" . substr_count( $html, PS_HEADING ); }
	if ( 1 !== substr_count( $html, PS_BODY ) )    { $copy_bad[] = "$label body x" . substr_count( $html, PS_BODY ); }
}
empty( $copy_bad )
	? ok( '15. both surfaces carry the approved heading and supporting text verbatim, exactly once' )
	: bad( '15. ' . implode( '; ', $copy_bad ) );

false !== strpos( $blog_html, PS_FALLBACK )
	? ok( '16. the no-JavaScript fallback link to Google\'s preferences page is present' )
	: bad( '16. the no-JS fallback link is missing' );

// The fallback must live inside <noscript> so it never competes with the real
// button, and must not be dressed as one.
// The page carries several <noscript> blocks (consent/GTM, a CSS fallback),
// so every one is scanned rather than only the first.
preg_match_all( '#<noscript>(.*?)</noscript>#s', $blog_html, $nm );
$fallback_in_noscript = false;
foreach ( $nm[1] ?? array() as $block ) {
	if ( false !== strpos( $block, PS_FALLBACK ) ) { $fallback_in_noscript = true; }
}
$fallback_in_noscript
	? ok( '17. the fallback is inside <noscript>, so it cannot appear beside the real button' )
	: bad( '17. the fallback link is not wrapped in <noscript>' );

preg_match( '#<a class="([^"]*)"[^>]*href="' . preg_quote( PS_FALLBACK, '#' ) . '"#', $blog_html, $fm );
( isset( $fm[1] ) && false === strpos( $fm[1], 'btn' ) )
	? ok( '18. the fallback is a text link, not disguised as a button (class="' . ( $fm[1] ?? '' ) . '")' )
	: bad( '18. the fallback carries button styling: "' . ( $fm[1] ?? 'not found' ) . '"' );

/* ══════════════════════════════════════════════════════════════════
 * PR #8 BEHAVIOUR MUST NOT REGRESS
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== PR #8 BEHAVIOUR INTACT ==\n";

$published = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
$n_pub     = count( $published );

preg_match_all( '#<a class="blog-card__link" href="([^"]+)"#', $blog_html, $cm );
$cards = $cm[1] ?? array();
$expect = min( 12, $n_pub );
count( $cards ) === $expect
	? ok( "19. the archive still renders $expect cards (page size 12, $n_pub published)" )
	: bad( '19. archive renders ' . count( $cards ) . ", expected $expect" );

count( array_unique( $cards ) ) === count( $cards )
	? ok( '20. no duplicate cards on the archive' )
	: bad( '20. duplicate cards present' );

// Pagination contract (only observable when the DB exceeds one page).
if ( $n_pub > 12 ) {
	$p2  = ps_fetch( trailingslashit( $blog_url ) . 'page/2/' );
	$has = false !== strpos( $blog_html, 'class="blog-pagination"' );
	preg_match( '#<link rel="canonical" href="([^"]+)"#', $p2, $c2 );
	( $has && isset( $c2[1] ) && false !== strpos( $c2[1], '/page/2' ) )
		? ok( '21. pagination renders and page two still self-canonicalises' )
		: bad( '21. pagination or page-two canonical regressed' );
	1 === substr_count( $p2, PS_BTN_ATTR )
		? ok( '22. paginated archive pages carry the button exactly once' )
		: bad( '22. page two has ' . substr_count( $p2, PS_BTN_ATTR ) . ' containers' );
} else {
	skipped( '21/22. pagination assertions — this database holds ' . $n_pub . ' published posts, fewer than the page size of 12, so no second page exists. Covered by tests/blog-archive-unit.php, which creates fixtures.' );
}

// The three formerly missing posts must still be reachable from the archive.
$three   = array( 'weekly-pool-care-checklist-la-homeowners', '2026-pool-design-trends-los-angeles', 'why-pebble-finishes-replacing-plaster' );
$missing = array();
$absent  = array();
foreach ( $three as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $p || 'publish' !== $p->post_status ) { $absent[] = $slug; continue; }
	if ( false === strpos( $blog_html, '/' . $slug . '/' ) ) { $missing[] = $slug; }
}
if ( count( $absent ) === count( $three ) ) {
	skipped( '23. formerly-missing-post visibility — none of the three exists in this database.' );
} else {
	empty( $missing )
		? ok( '23. every formerly missing post present in this database is still visible in the archive' )
		: bad( '23. no longer visible: ' . implode( ', ', $missing ) );
}

// Article author schema must still honour the PR #8 invariant. The correct
// assertion is INTERNAL CONSISTENCY, not a fixed type: a byline naming the
// business resolves to Organization@#organization, any other byline resolves
// to Person@#person, and a Person named after the company — the exact
// contradiction PR #8 removed — must never reappear.
if ( '' !== $post_html ) {
	$brand      = (string) apply_filters( 'showtime/business/name', 'Showtime Pools' );
	$author_ok  = false;
	$author_why = 'no BlogPosting author node found';
	if ( preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $post_html, $lm ) ) {
		foreach ( $lm[1] as $json ) {
			$d = json_decode( $json, true );
			if ( ! is_array( $d ) ) { continue; }
			foreach ( ( isset( $d[0] ) ? $d : array( $d ) ) as $node ) {
				if ( ! is_array( $node ) || 'BlogPosting' !== ( $node['@type'] ?? '' ) ) { continue; }
				$au   = (array) ( $node['author'] ?? array() );
				$type = (string) ( $au['@type'] ?? '' );
				$id   = (string) ( $au['@id'] ?? '' );
				$name = (string) ( $au['name'] ?? '' );

				if ( 'Person' === $type && 0 === strcasecmp( trim( $name ), trim( $brand ) ) ) {
					$author_why = 'a Person node is named after the company again';
					$author_ok  = false;
					break 2;
				}
				if ( 'Organization' === $type && false !== strpos( $id, '#organization' ) ) {
					$author_ok  = true;
					$author_why = 'Organization -> #organization';
				} elseif ( 'Person' === $type && false !== strpos( $id, '#person' ) ) {
					$author_ok  = true;
					$author_why = 'Person "' . $name . '" -> #person';
				} else {
					$author_why = "author is $type -> $id";
					$author_ok  = false;
				}
			}
		}
	}
	$author_ok
		? ok( '24. the PR #8 author invariant holds (' . $author_why . '); no company-named Person node' )
		: bad( '24. author schema regressed — ' . $author_why );
}

// Sitemaps must be untouched: same posts, same formats.
$xml_index = ps_fetch( $home . '/wp-sitemap.xml' );
$xml_all   = $xml_index;
if ( preg_match_all( '#<loc>([^<]+)</loc>#', $xml_index, $xm ) ) {
	foreach ( $xm[1] as $child ) {
		if ( false !== strpos( $child, 'wp-sitemap' ) ) { $xml_all .= ps_fetch( html_entity_decode( $child ) ); }
	}
}
$sm_missing = array();
foreach ( $published as $id ) {
	$slug = (string) get_post_field( 'post_name', $id );
	if ( 'hello-world' === $slug ) { continue; }
	if ( false === strpos( $xml_all, '/' . $slug . '/' ) ) { $sm_missing[] = $slug; }
}
empty( $sm_missing )
	? ok( '25. every published post is still in the XML sitemap' )
	: bad( '25. absent from the XML sitemap: ' . implode( ', ', $sm_missing ) );

0 === strpos( ltrim( $xml_index ), '<?xml' )
	? ok( '26. /wp-sitemap.xml is still served as XML' )
	: bad( '26. the XML sitemap format changed' );

false === strpos( $xml_all, PS_BTN_ATTR ) && false === strpos( $xml_all, PS_SCRIPT_SRC )
	? ok( '27. neither the button nor the script leaked into any sitemap response' )
	: bad( '27. preferred-source markup leaked into a sitemap' );

false !== stripos( ps_fetch( $home . '/sitemap/' ), '<html' )
	? ok( '28. /sitemap/ is still the human HTML sitemap' )
	: bad( '28. the HTML sitemap changed' );

echo "\n== NOTE ==\n";
skipped( 'Live-button rendering is NOT asserted. publisher.js only paints its real control for an origin Google recognises, which this test origin is not. Everything above verifies the container, the script and the copy — the structural integration — not that an interactive Google button appeared.' );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

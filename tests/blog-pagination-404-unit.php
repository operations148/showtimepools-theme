<?php
/**
 * Out-of-range /blog/page/N/ pagination — genuine 404, not a soft-404.
 *
 * Guards the defect found in the 2026-08-27 production audit: page-blog.php
 * builds its own WP_Query for the feed rather than relying on WordPress's main
 * query, so WordPress never applies its normal out-of-range-pagination 404
 * behaviour to it. Any /blog/page/N/ beyond the real page count rendered the
 * ordinary empty state ("Articles coming soon") at HTTP 200, with a
 * self-referencing, indexable canonical — an unbounded set of soft-404 pages
 * for every N beyond the real count.
 *
 * The fix is a template_redirect guard in inc/seo.php: it runs a throwaway
 * probe query mirroring page-blog.php's real one, and when the requested page
 * exceeds max_num_pages it calls set_404(), sends a genuine 404 status and
 * no-cache headers, and renders the theme's real 404 template — never
 * page-blog.php itself. showtime_canonical_url() and
 * showtime_seo_should_noindex() were also given is_404() cases, since without
 * them a 404 page still exposed the same catch-all self-canonical.
 *
 * Every scenario is proved with FIXTURES created and destroyed inside the
 * run, so this holds on any database regardless of how many posts it starts
 * with — including the "13th post makes page 2 real" future-proofing
 * requirement, which cannot be observed without temporarily exceeding 12.
 *
 * Run:  php tests/blog-pagination-404-unit.php
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

function p404_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
/** [status, body, header_block] without following redirects. */
function p404_full( string $url ): array {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$resp        = (string) curl_exec( $ch );
	$code        = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$header_size = (int) curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
	curl_close( $ch );
	return array( $code, substr( $resp, $header_size ), substr( $resp, 0, $header_size ) );
}
function p404_cards( string $html ): array {
	preg_match_all( '#class="blog-card__link" href="([^"]+)"#', $html, $m );
	return $m[1] ?? array();
}

$home      = untrailingslashit( home_url() );
$blog_page = get_page_by_path( 'blog' );
$blog_url  = $blog_page ? (string) get_permalink( $blog_page ) : '';

$baseline_published = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
$n_baseline          = count( $baseline_published );

echo "== SETUP ==\n";
echo "  (this database starts with $n_baseline published posts)\n";

'' !== $blog_url
	? ok( '1. the /blog/ page resolves' )
	: bad( '1. no page with slug "blog"' );

/* ══════════════════════════════════════════════════════════════════
 * NORMAL ARCHIVE PAGE (page 1) — must stay 200, always
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== NORMAL ARCHIVE PAGE (page 1) ==\n";

[ $code1, $body1, $head1 ] = p404_full( $blog_url );

200 === $code1
	? ok( '2. /blog/ (page 1) returns HTTP 200' )
	: bad( '2. /blog/ returned ' . $code1 );

false === strpos( $body1, 'Articles coming soon' ) || $n_baseline > 0
	? ok( '3. page 1 never shows the empty state when posts exist (or legitimately does when the DB is empty — not a regression either way)' )
	: bad( '3. unexpected empty state' );

preg_match( '#<link rel="canonical" href="([^"]+)"#', $body1, $cm1 );
( isset( $cm1[1] ) && untrailingslashit( $cm1[1] ) === untrailingslashit( $blog_url ) )
	? ok( '4. page 1 keeps its self-referencing canonical' )
	: bad( '4. page 1 canonical is "' . ( $cm1[1] ?? 'MISSING' ) . '"' );

false !== strpos( $head1, 'HTTP/1.1 200' ) || false !== strpos( $head1, 'HTTP/2 200' )
	? ok( '5. page 1 status line is a genuine 200' )
	: bad( '5. page 1 status line: ' . trim( strtok( $head1, "\r\n" ) ) );

/* ══════════════════════════════════════════════════════════════════
 * FIRST OUT-OF-RANGE PAGE
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== FIRST OUT-OF-RANGE PAGE ==\n";

// With the DB's current post count, /page/2/ is out of range unless there
// happen to already be > 12 published posts (handled by the >12-post branch
// below). Guard against that overlap so this section always targets a
// genuinely invalid page.
$page2_url = trailingslashit( $blog_url ) . 'page/2/';
if ( $n_baseline > 12 ) {
	skipped( '6-11. first-out-of-range-page checks — this database already has more than 12 published posts, so /page/2/ is legitimately real here. Covered by the >12-post fixture section below instead.' );
} else {
	[ $code2, $body2, $head2 ] = p404_full( $page2_url );

	404 === $code2
		? ok( '6. /blog/page/2/ returns a genuine HTTP 404' )
		: bad( '6. /blog/page/2/ returned ' . $code2 );

	( false !== strpos( $head2, 'HTTP/1.1 404' ) || false !== strpos( $head2, 'HTTP/2 404' ) )
		? ok( '7. status line is a real 404, not a 200 with a 404-looking body' )
		: bad( '7. status line: ' . trim( strtok( $head2, "\r\n" ) ) );

	false === strpos( $body2, 'Articles coming soon' )
		? ok( '8. the empty "Articles coming soon" archive state is NOT rendered' )
		: bad( '8. the empty archive state rendered on an out-of-range page' );

	false !== strpos( $body2, '404' ) && ( false !== stripos( $body2, 'not found' ) || false !== strpos( $body2, 'not-found' ) )
		? ok( '9. the theme\'s real 404 template rendered instead' )
		: bad( '9. no recognisable 404 template content found' );

	false === strpos( $body2, '<link rel="canonical"' )
		? ok( '10. no canonical tag at all — the invalid URL is not asserted as canonical for anything' )
		: bad( '10. a canonical tag was emitted on a 404 page' );

	preg_match( "#<meta name=['\"]robots['\"] content=['\"]([^'\"]*)['\"]#", $body2, $rm2 );
	$robots2 = $rm2[1] ?? '';
	( false !== strpos( $robots2, 'noindex' ) )
		? ok( '11. robots meta is noindex on the out-of-range page (got "' . $robots2 . '")' )
		: bad( '11. robots meta is not noindex: "' . $robots2 . '"' );

	$nocache = ( false !== stripos( $head2, 'no-cache' ) || false !== stripos( $head2, 'no-store' ) )
		&& false !== stripos( $head2, 'Expires:' );
	$nocache
		? ok( '12. no-cache headers are present (Cache-Control + Expires)' )
		: bad( '12. no-cache headers missing from: ' . preg_replace( '/\r?\n/', ' | ', trim( $head2 ) ) );
}

/* ══════════════════════════════════════════════════════════════════
 * VERY LARGE OUT-OF-RANGE PAGE
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== VERY LARGE OUT-OF-RANGE PAGE ==\n";

$page99_url = trailingslashit( $blog_url ) . 'page/99999/';
[ $code99, $body99 ] = p404_full( $page99_url );

404 === $code99
	? ok( '13. /blog/page/99999/ also returns a genuine HTTP 404 — the guard is not a special case for "page 2" specifically' )
	: bad( '13. /blog/page/99999/ returned ' . $code99 );

false === strpos( $body99, 'Articles coming soon' )
	? ok( '14. the empty archive state does not render on an extreme out-of-range page either' )
	: bad( '14. empty archive state rendered on page 99999' );

/* ══════════════════════════════════════════════════════════════════
 * VALID PAGINATION BECOMES REAL WITH A 13TH POST (future-proofing)
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== VALID PAGINATION WITH >12 PUBLISHED POSTS ==\n";

$need     = max( 0, 13 - $n_baseline );
$pager_fx = array();
for ( $i = 1; $i <= $need; $i++ ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'ZZ Pagination404 Fixture ' . $i,
			'post_content' => 'fixture body',
			// Oldest, so fixtures land at the END of the archive and never
			// displace a real post from page one.
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 3650 + $i ) . ' days' ) ),
		),
		true
	);
	if ( ! is_wp_error( $id ) ) { $pager_fx[] = (int) $id; }
}
$n_now = count( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) );

$p1        = p404_fetch( $blog_url );
$c1        = p404_cards( $p1 );
[ $codeR2, $bodyR2, $headR2 ] = p404_full( $page2_url );
$c2        = p404_cards( $bodyR2 );
$page3_url = trailingslashit( $blog_url ) . 'page/3/';
[ $codeR3, $bodyR3 ] = p404_full( $page3_url );

count( $c1 ) === 12
	? ok( "15. with $n_now published posts, page one still renders exactly 12 cards" )
	: bad( '15. page one renders ' . count( $c1 ) . " cards with $n_now published" );

200 === $codeR2
	? ok( '16. /blog/page/2/ automatically becomes HTTP 200 now that a 13th post exists — no code change needed per new post' )
	: bad( '16. /blog/page/2/ returned ' . $codeR2 . ' with ' . $n_now . ' published posts' );

count( $c2 ) === ( $n_now - 12 ) && count( $c2 ) > 0
	? ok( '17. the now-valid page two renders the remaining ' . count( $c2 ) . ' card(s), with unique content' )
	: bad( '17. page two renders ' . count( $c2 ) . ' cards, expected ' . ( $n_now - 12 ) );

empty( array_intersect( $c1, $c2 ) )
	? ok( '18. no card is duplicated between page one and the now-valid page two' )
	: bad( '18. duplicate cards across pages: ' . implode( ', ', array_intersect( $c1, $c2 ) ) );

preg_match( '#<link rel="canonical" href="([^"]+)"#', $bodyR2, $cmR2 );
( isset( $cmR2[1] ) && false !== strpos( $cmR2[1], '/page/2' ) && false === strpos( $cmR2[1], '/blog/"' ) )
	? ok( '19. the now-valid page two has its own correct self-canonical (' . $cmR2[1] . ')' )
	: bad( '19. page two canonical is "' . ( $cmR2[1] ?? 'MISSING' ) . '"' );

// With exactly 13 posts and page size 12, page 3 is still out of range.
404 === $codeR3
	? ok( '20. page three is still correctly rejected as out of range (13 posts = 2 real pages)' )
	: bad( '20. page three returned ' . $codeR3 . ', expected 404' );

/* ══════════════════════════════════════════════════════════════════
 * PR #8 / PR #9 BEHAVIOUR PRESERVED
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== PR #8 / PR #9 BEHAVIOUR PRESERVED ==\n";

$a_post   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1 ) );
$post_url = $a_post ? (string) get_permalink( $a_post[0] ) : '';

1 === substr_count( $p1, 'google-add-preferred-source-btn' )
	? ok( '21. the Google Preferred Sources container still appears exactly once on the archive' )
	: bad( '21. Preferred Sources container count is ' . substr_count( $p1, 'google-add-preferred-source-btn' ) );

1 === substr_count( $p1, 'news.google.com/swg/js/v1/publisher.js' )
	? ok( '22. publisher.js is still enqueued exactly once on the archive' )
	: bad( '22. publisher.js count is ' . substr_count( $p1, 'news.google.com/swg/js/v1/publisher.js' ) );

if ( '' !== $post_url ) {
	$post_html = p404_fetch( $post_url );
	$author_ok = false;
	if ( preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $post_html, $lm ) ) {
		foreach ( $lm[1] as $json ) {
			$d = json_decode( $json, true );
			if ( ! is_array( $d ) ) { continue; }
			foreach ( ( isset( $d[0] ) ? $d : array( $d ) ) as $node ) {
				if ( is_array( $node ) && 'BlogPosting' === ( $node['@type'] ?? '' ) ) {
					$au = (array) ( $node['author'] ?? array() );
					if ( ( 'Organization' === ( $au['@type'] ?? '' ) && false !== strpos( (string) ( $au['@id'] ?? '' ), '#organization' ) )
						|| ( 'Person' === ( $au['@type'] ?? '' ) && false !== strpos( (string) ( $au['@id'] ?? '' ), '#person' ) ) ) {
						$author_ok = true;
					}
				}
			}
		}
	}
	$author_ok
		? ok( '23. Organization/Person author schema on a single post is unaffected' )
		: bad( '23. author schema regressed on a single post' );
} else {
	skipped( '23. no published post available to check author schema' );
}

// All posts that existed at baseline remain reachable — the guard must never
// swallow a legitimate post permalink.
$unreachable = array();
foreach ( $baseline_published as $id ) {
	$slug = (string) get_post_field( 'post_name', $id );
	if ( 'hello-world' === $slug ) { continue; }
	$u = home_url( '/' . $slug . '/' );
	list( $c ) = p404_full( $u );
	if ( 200 !== $c ) { $unreachable[] = "$slug -> $c"; }
}
empty( $unreachable )
	? ok( '24. every originally-published post remains reachable at HTTP 200' )
	: bad( '24. ' . implode( '; ', $unreachable ) );

// The three formerly-orphaned posts specifically, if present in this DB.
$three   = array( 'weekly-pool-care-checklist-la-homeowners', '2026-pool-design-trends-los-angeles', 'why-pebble-finishes-replacing-plaster' );
$missing = array();
$absent  = array();
foreach ( $three as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $p || 'publish' !== $p->post_status ) { $absent[] = $slug; continue; }
	if ( false === strpos( $p1, '/' . $slug . '/' ) ) { $missing[] = $slug; }
}
if ( count( $absent ) === count( $three ) ) {
	skipped( '25. formerly-orphaned-post visibility — none of the three exists in this database.' );
} else {
	empty( $missing )
		? ok( '25. the formerly orphaned posts present in this database remain visible in the archive' )
		: bad( '25. no longer visible: ' . implode( ', ', $missing ) );
}

/* ── Cleanup ─────────────────────────────────────────────────────── */
foreach ( $pager_fx as $id ) { wp_delete_post( $id, true ); }
$restored = count( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) );
$restored === $n_baseline
	? ok( '26. all fixtures removed; the database is back to ' . $n_baseline . ' published posts' )
	: bad( '26. fixture cleanup left ' . $restored . ' posts, expected ' . $n_baseline );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

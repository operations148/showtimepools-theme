<?php
/**
 * Blog archive discovery, pagination, and sitemap coverage.
 *
 * Guards the defect this branch fixes: page-blog.php capped its feed at
 * `posts_per_page => 9` with `no_found_rows => true` and no `paged`, so the
 * 10th post onward was unreachable from anywhere on the site, no pager could
 * render (max_num_pages was always 0), and /blog/page/2/ answered 200 with the
 * same nine cards under a /blog/ canonical.
 *
 * Archive and exclusion assertions run against whatever WordPress this suite
 * points at. Pagination is proved with FIXTURES created and destroyed inside
 * the run, so it holds on any database regardless of how many posts it has.
 *
 * Article-content assertions (sibling links, tables, singular metadata) need
 * the finalized article bodies. Those live in the production database only —
 * per CLAUDE.md, blog posts are CONTENT edited in live wp-admin and never
 * travel local -> live — so they run only against a database that actually
 * holds them, and report a documented skip otherwise. They are never silently
 * passed.
 *
 * Run:  php tests/blog-archive-unit.php
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

function ba_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
function ba_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 40, CURLOPT_SSL_VERIFYPEER => false ) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}
/** Post slugs whose card renders on a given archive page. */
function ba_cards( string $html ): array {
	preg_match_all( '#<a class="blog-card__link" href="([^"]+)"#', $html, $m );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	return array_map(
		static function ( $u ) use ( $base ) {
			$path = trim( (string) wp_parse_url( $u, PHP_URL_PATH ), '/' );
			if ( '' !== $base && 0 === strpos( $path, $base . '/' ) ) {
				$path = substr( $path, strlen( $base ) + 1 );
			}
			return $path;
		},
		$m[1] ?? array()
	);
}

$blog_page = get_page_by_path( 'blog' );
$blog_url  = $blog_page ? (string) get_permalink( $blog_page ) : '';
$home      = untrailingslashit( home_url() );

/** The three articles that were unreachable from site navigation. */
$CONFIRMED_ORPHANS = array(
	'weekly-pool-care-checklist-la-homeowners',
	'2026-pool-design-trends-los-angeles',
	'why-pebble-finishes-replacing-plaster',
);

$published   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
$n_published = count( $published );

echo "== ARCHIVE PAGE SIZE ==\n";
echo "  (this database holds $n_published published posts)\n";

'' !== $blog_url
	? ok( '1. the /blog/ page resolves' )
	: bad( '1. no page with slug "blog"' );

$page1  = ba_fetch( $blog_url );
$cards1 = ba_cards( $page1 );

$expect_page1 = min( 12, $n_published );
count( $cards1 ) === $expect_page1
	? ok( "2. page one renders $expect_page1 cards (page size 12, $n_published published)" )
	: bad( '2. page one renders ' . count( $cards1 ) . " cards, expected $expect_page1" );

if ( $n_published > 9 ) {
	count( $cards1 ) > 9
		? ok( '3. the 9-post cap is gone — page one exceeds nine cards' )
		: bad( '3. page one is still capped at ' . count( $cards1 ) );
} else {
	skipped( '3. nine-post cap check — this database has only ' . $n_published . ' published posts, so the old cap cannot be observed here. The fixture pagination below proves the page-size and paging logic independently.' );
}

count( array_unique( $cards1 ) ) === count( $cards1 )
	? ok( '4. no post appears twice on page one' )
	: bad( '4. duplicate cards on page one' );

preg_match_all( '#<article class="blog-card[^"]*">\s*<a class="blog-card__link" href="(https?://[^"]+)"#', $page1, $am );
count( $am[1] ?? array() ) === count( $cards1 )
	? ok( '5. every card wraps a genuine server-rendered <a href>' )
	: bad( '5. ' . count( $am[1] ?? array() ) . ' of ' . count( $cards1 ) . ' cards have a real anchor' );

echo "\n== EXCLUSIONS ==\n";

$leak = array();
foreach ( $cards1 as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $p ) { $leak[] = "$slug is not a standard post"; continue; }
	if ( 'publish' !== $p->post_status ) { $leak[] = "$slug is {$p->post_status}"; }
}
empty( $leak )
	? ok( '6. every card on page one is a published standard post' )
	: bad( '6. ' . implode( '; ', $leak ) );

$mk = static function ( string $status, string $type, string $title ) {
	$id = wp_insert_post( array( 'post_type' => $type, 'post_status' => $status, 'post_title' => $title, 'post_content' => 'fixture' ), true );
	return is_wp_error( $id ) ? 0 : (int) $id;
};
$fx = array(
	'draft'   => $mk( 'draft', 'post', 'ZZ Fixture Draft' ),
	'private' => $mk( 'private', 'post', 'ZZ Fixture Private' ),
	'pending' => $mk( 'pending', 'post', 'ZZ Fixture Pending' ),
	'page'    => $mk( 'publish', 'page', 'ZZ Fixture Page' ),
);
$future_id       = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'future', 'post_title' => 'ZZ Fixture Scheduled', 'post_date' => gmdate( 'Y-m-d H:i:s', time() + 604800 ), 'post_content' => 'fixture' ), true );
$fx['scheduled'] = is_wp_error( $future_id ) ? 0 : (int) $future_id;
if ( post_type_exists( 'project' ) ) { $fx['project'] = $mk( 'publish', 'project', 'ZZ Fixture Project' ); }

$after    = ba_cards( ba_fetch( $blog_url ) );
$excluded = array();
foreach ( $fx as $label => $id ) {
	if ( ! $id ) { continue; }
	$slug = (string) get_post_field( 'post_name', $id );
	if ( '' !== $slug && in_array( $slug, $after, true ) ) { $excluded[] = "$label leaked into the archive"; }
}
empty( $excluded )
	? ok( '7. draft, private, pending, scheduled, page and project records all stay out of the archive' )
	: bad( '7. ' . implode( '; ', $excluded ) );

echo "\n== PAGINATION (fixtures) ==\n";

$need     = max( 0, 13 - $n_published );
$pager_fx = array();
for ( $i = 1; $i <= $need; $i++ ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'ZZ Pager Fixture ' . $i,
			'post_content' => 'fixture body',
			// Dated oldest so fixtures land at the END of the archive and never
			// displace a real post from page one.
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 3650 + $i ) . ' days' ) ),
		),
		true
	);
	if ( ! is_wp_error( $id ) ) { $pager_fx[] = (int) $id; }
}
$total_now = count( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) );

$p1    = ba_fetch( $blog_url );
$c1    = ba_cards( $p1 );
$p2url = trailingslashit( $blog_url ) . 'page/2/';
$p2    = ba_fetch( $p2url );
$c2    = ba_cards( $p2 );

count( $c1 ) === 12
	? ok( "8. with $total_now published posts, page one renders exactly 12 cards" )
	: bad( '8. page one renders ' . count( $c1 ) . " cards with $total_now published" );

200 === ba_status( $p2url )
	? ok( '9. /blog/page/2/ returns HTTP 200' )
	: bad( '9. /blog/page/2/ returns HTTP ' . ba_status( $p2url ) );

( count( $c2 ) === ( $total_now - 12 ) && count( $c2 ) > 0 )
	? ok( '10. page two renders the remaining ' . count( $c2 ) . ' card(s)' )
	: bad( '10. page two renders ' . count( $c2 ) . ', expected ' . ( $total_now - 12 ) );

empty( array_intersect( $c1, $c2 ) )
	? ok( '11. no post appears on both page one and page two' )
	: bad( '11. duplicated across pages: ' . implode( ', ', array_intersect( $c1, $c2 ) ) );

$has_nav = false !== strpos( $p1, 'class="blog-pagination"' );
preg_match_all( '#<a[^>]+class="[^"]*page-numbers[^"]*"[^>]+href="([^"]+)"#', $p1, $pl );
$pager_links = $pl[1] ?? array();
( $has_nav && count( $pager_links ) > 0 )
	? ok( '12. pagination is server-rendered: <nav class="blog-pagination"> with ' . count( $pager_links ) . ' real <a href> link(s)' )
	: bad( '12. no server-rendered pager found on page one' );

$bad_href = array_filter( $pager_links, static fn( $h ) => ! preg_match( '#/blog/page/\d+/#', $h ) );
empty( $bad_href )
	? ok( '13. every pager link is a semantic /blog/page/N/ URL' )
	: bad( '13. non-semantic pager links: ' . implode( ', ', $bad_href ) );

$dead = array();
foreach ( array_unique( $pager_links ) as $h ) {
	if ( 200 !== ba_status( $h ) ) { $dead[] = $h; }
}
empty( $dead )
	? ok( '14. every pager link resolves with HTTP 200' )
	: bad( '14. dead pager links: ' . implode( ', ', $dead ) );

preg_match( '#<link rel="canonical" href="([^"]+)"#', $p2, $cm2 );
$canon2 = $cm2[1] ?? '';
( '' !== $canon2 && false !== strpos( $canon2, '/page/2' ) )
	? ok( '15. page two carries its own canonical (' . $canon2 . ')' )
	: bad( '15. page two canonical is "' . $canon2 . '" — it must not point at page one' );

foreach ( array_merge( array_values( $fx ), $pager_fx ) as $id ) {
	if ( $id ) { wp_delete_post( $id, true ); }
}
$restored = count( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) ) );
$restored === $n_published
	? ok( '16. all fixtures removed; the database is back to ' . $n_published . ' published posts' )
	: bad( '16. fixture cleanup left ' . $restored . ' posts, expected ' . $n_published );

echo "\n== SITEMAP COVERAGE ==\n";

$html_sitemap = ba_fetch( $home . '/sitemap/' );
$missing_html = array();
foreach ( $published as $id ) {
	$slug = (string) get_post_field( 'post_name', $id );
	if ( 'hello-world' === $slug ) { continue; }
	if ( false === strpos( $html_sitemap, '/' . $slug . '/' ) ) { $missing_html[] = $slug; }
}
empty( $missing_html )
	? ok( '17. every published post appears in the HTML sitemap at /sitemap/' )
	: bad( '17. absent from the HTML sitemap: ' . implode( ', ', $missing_html ) );

$xml_index = ba_fetch( $home . '/wp-sitemap.xml' );
$xml_all   = $xml_index;
if ( preg_match_all( '#<loc>([^<]+)</loc>#', $xml_index, $xm ) ) {
	foreach ( $xm[1] as $child ) {
		if ( false !== strpos( $child, 'wp-sitemap' ) ) { $xml_all .= ba_fetch( html_entity_decode( $child ) ); }
	}
}
$missing_xml = array();
foreach ( $published as $id ) {
	$slug = (string) get_post_field( 'post_name', $id );
	if ( 'hello-world' === $slug ) { continue; }
	if ( false === strpos( $xml_all, '/' . $slug . '/' ) ) { $missing_xml[] = $slug; }
}
empty( $missing_xml )
	? ok( '18. every published post appears in the XML sitemap' )
	: bad( '18. absent from the XML sitemap: ' . implode( ', ', $missing_xml ) );

0 === strpos( ltrim( $xml_index ), '<?xml' )
	? ok( '19. /wp-sitemap.xml is still served as XML' )
	: bad( '19. /wp-sitemap.xml is no longer XML' );

false !== stripos( $html_sitemap, '<html' )
	? ok( '20. /sitemap/ is still the human HTML sitemap' )
	: bad( '20. /sitemap/ is no longer HTML' );

if ( function_exists( 'showtime_project_ids_hidden_from_discovery' ) ) {
	$hidden = showtime_project_ids_hidden_from_discovery();
	$leaked = array();
	foreach ( $hidden as $hid ) {
		$hslug = (string) get_post_field( 'post_name', $hid );
		if ( '' !== $hslug && false !== strpos( $xml_all, '/' . $hslug . '/' ) ) { $leaked[] = $hslug; }
	}
	empty( $leaked )
		? ok( '21. Coming Soon / unmanaged project exclusions are unchanged (' . count( $hidden ) . ' hidden)' )
		: bad( '21. previously-hidden project leaked into the sitemap: ' . implode( ', ', $leaked ) );
} else {
	skipped( '21. project discovery-exclusion helper not present in this build' );
}

echo "\n== CONFIRMED ORPHANS ==\n";

$archive_all = $cards1;
if ( $n_published > 12 ) {
	$archive_all = array_merge( $archive_all, ba_cards( ba_fetch( trailingslashit( $blog_url ) . 'page/2/' ) ) );
}
$still_orphan = array();
$not_here     = array();
foreach ( $CONFIRMED_ORPHANS as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $p || 'publish' !== $p->post_status ) { $not_here[] = $slug; continue; }
	if ( ! in_array( $slug, $archive_all, true ) ) { $still_orphan[] = $slug; }
}
if ( count( $not_here ) === count( $CONFIRMED_ORPHANS ) ) {
	skipped( '22. confirmed-orphan archive check — none of the three posts exists in this database, so there is nothing to place here. They are verified against production in the audit report.' );
} else {
	empty( $still_orphan )
		? ok( '22. every confirmed orphan present in this database now renders in the blog archive' )
		: bad( '22. still absent from the archive: ' . implode( ', ', $still_orphan ) );
	if ( ! empty( $not_here ) ) {
		skipped( '22b. not present in this database: ' . implode( ', ', $not_here ) );
	}
}

echo "\n== ARTICLE CONTENT ==\n";

$has_real_bodies = false;
foreach ( $published as $id ) {
	if ( str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ) ) > 1200 ) {
		$has_real_bodies = true;
		break;
	}
}

if ( ! $has_real_bodies ) {
	skipped( '23. sibling-link, table-rendering and singular-metadata assertions — this database holds stub bodies, not the finalized articles. Per CLAUDE.md blog posts are CONTENT owned by live wp-admin and never travel local -> live, so these are measured against production in the audit report rather than asserted here.' );
} else {
	$link_bad  = array();
	$table_bad = array();
	$meta_bad  = array();
	foreach ( $published as $id ) {
		$slug = (string) get_post_field( 'post_name', $id );
		if ( 'hello-world' === $slug ) { continue; }
		$body = ba_fetch( (string) get_permalink( $id ) );
		$article = preg_match( '#<div class="post-body">([\s\S]*?)</div>\s*</article>#', $body, $bm ) ? $bm[1] : $body;

		preg_match_all( '#href="' . preg_quote( $home, '#' ) . '/([a-z0-9-]+)/"#', $article, $lm );
		$siblings = array();
		foreach ( array_unique( $lm[1] ?? array() ) as $cand ) {
			if ( $cand === $slug ) { $link_bad[] = "$slug links to itself"; continue; }
			if ( get_page_by_path( $cand, OBJECT, 'post' ) ) { $siblings[] = $cand; }
		}
		if ( empty( $siblings ) ) { $link_bad[] = "$slug has no in-body sibling article link"; }
		foreach ( $siblings as $sib ) {
			if ( 200 !== ba_status( $home . '/' . $sib . '/' ) ) { $link_bad[] = "$slug -> $sib does not resolve"; }
		}
		if ( preg_match( '/[a-z][A-Z][a-z]+(Price|Range|Details|Function|Cost|Type)/', wp_strip_all_tags( $article ) ) ) {
			$table_bad[] = "$slug has run-together table text";
		}
		foreach ( array( 'og:title', 'og:description', 'twitter:title', 'twitter:description' ) as $tag ) {
			$n = substr_count( $body, '"' . $tag . '"' );
			if ( $n > 1 ) { $meta_bad[] = "$slug has $n $tag tags"; }
		}
		if ( 1 !== preg_match_all( '#rel="canonical"#', $body ) ) { $meta_bad[] = "$slug canonical count wrong"; }
	}
	empty( $link_bad )
		? ok( '23. every article carries at least one resolving in-body sibling link, and none links to itself' )
		: bad( '23. ' . implode( '; ', $link_bad ) );
	empty( $table_bad )
		? ok( '24. no article shows run-together table text' )
		: bad( '24. ' . implode( '; ', $table_bad ) );
	empty( $meta_bad )
		? ok( '25. article metadata is singular and canonical' )
		: bad( '25. ' . implode( '; ', $meta_bad ) );
}

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

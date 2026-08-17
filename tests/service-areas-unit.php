<?php
/**
 * Service areas + Brentwood highlights + popup wiring — regression suite.
 *
 * Covers the three changes shipped together on this branch:
 *   1. Brentwood publishes six real highlight photographs alongside its
 *      original before/after pair, and no placeholder state remains.
 *   2. /service-areas/ renders the canonical 14 locations, and the homepage
 *      marquee renders the same 14 as 7 + 7 semantic cards, with the animation
 *      clones excluded from a11y and keyboard navigation.
 *   3. The delayed estimate popup is wired once, with exact CTA destinations,
 *      and no trace of the retired weekly-maintenance popup survives.
 *
 * Counts are derived from the registry, never hardcoded, so promoting another
 * location cannot silently leave an assertion behind.
 *
 * Run:  php tests/service-areas-unit.php
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

function sa_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

/** Status WITHOUT following redirects — a 301 is not a resolved internal URL. */
function sa_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}

/** Magic-number image sniff — never trusts the file extension. */
function sa_image_mime( string $path ): string {
	if ( ! is_readable( $path ) ) { return ''; }
	$h = (string) file_get_contents( $path, false, null, 0, 12 );
	if ( 12 === strlen( $h ) && 'RIFF' === substr( $h, 0, 4 ) && 'WEBP' === substr( $h, 8, 4 ) ) { return 'image/webp'; }
	if ( "\xFF\xD8\xFF" === substr( $h, 0, 3 ) ) { return 'image/jpeg'; }
	if ( "\x89PNG\r\n\x1a\n" === substr( $h, 0, 8 ) ) { return 'image/png'; }
	return '';
}

$child    = get_stylesheet_directory();
$home     = untrailingslashit( home_url() );
$gal_root = "$child/assets/img/projects/galleries";
$cmp_root = "$child/assets/img/projects/comparisons";

/* ══════════════════════════════════════════════════════════════════════
 * REGISTRY SHAPE
 * ═══════════════════════════════════════════════════════════════════ */
echo "== PROJECT REGISTRY ==\n";

$all      = \Showtime\Projects::all();
$managed  = array_values( array_filter( $all, static fn( $p ) => ! empty( $p['managed'] ) ) );
$slugs    = array_map( static fn( $p ) => (string) $p['slug'], $managed );

count( $managed ) === 14
	? ok( '1. the registry contains exactly 14 managed projects' )
	: bad( '1. managed project count is ' . count( $managed ) . ', expected 14' );

count( array_unique( $slugs ) ) === 14
	? ok( '2. all 14 managed project slugs are unique' )
	: bad( '2. duplicate slugs: ' . implode( ', ', array_diff_assoc( $slugs, array_unique( $slugs ) ) ) );

/* ══════════════════════════════════════════════════════════════════════
 * BRENTWOOD
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== BRENTWOOD IMAGERY ==\n";

$bw = showtime_project_data( 'brentwood-pool-project' );

if ( null === $bw ) {
	bad( '3. brentwood-pool-project does not resolve from the registry' );
} else {
	$bw_gallery = showtime_project_gallery( $bw );
	$ready      = array_values( array_filter( $bw_gallery, static fn( $s ) => 'ready' === $s['status'] ) );
	$pending    = array_values( array_filter( $bw_gallery, static fn( $s ) => 'ready' !== $s['status'] ) );

	count( $ready ) === 6
		? ok( '3. Brentwood publishes exactly six highlight photographs' )
		: bad( '3. Brentwood publishes ' . count( $ready ) . ' highlights, expected 6' );

	empty( $pending )
		? ok( '4. no "Coming Soon" placeholder slot remains for Brentwood' )
		: bad( '4. Brentwood still carries ' . count( $pending ) . ' placeholder slot(s)' );

	// Six unique files, all genuine WebP, all under Brentwood's own directory.
	$bw_files = array();
	$bad_mime = array();
	$bw_hash  = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$n    = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
		$file = "$gal_root/brentwood-pool-project/brentwood-pool-project-highlight-$n.webp";
		$bw_files[] = $file;
		$mime = sa_image_mime( $file );
		if ( 'image/webp' !== $mime ) { $bad_mime[] = basename( $file ) . " ($mime)"; }
		if ( is_readable( $file ) ) { $bw_hash[] = hash_file( 'sha256', $file ); }
	}
	empty( $bad_mime )
		? ok( '5. all six Brentwood highlight files exist and carry the RIFF/WEBP magic number' )
		: bad( '5. non-WebP or missing highlight(s): ' . implode( ', ', $bad_mime ) );

	count( array_unique( $bw_hash ) ) === 6
		? ok( '6. all six Brentwood highlights are unique images (distinct SHA-256)' )
		: bad( '6. Brentwood highlights contain duplicates' );

	// The original before/after pair is untouched and still a genuine WebP pair.
	$pair     = array( "$cmp_root/brentwood-pool-project-before.webp", "$cmp_root/brentwood-pool-project-after.webp" );
	$pair_bad = array();
	foreach ( $pair as $p ) {
		if ( 'image/webp' !== sa_image_mime( $p ) ) { $pair_bad[] = basename( $p ); }
	}
	empty( $pair_bad )
		? ok( '7. Brentwood keeps its original before/after pair, both genuine WebP' )
		: bad( '7. before/after pair broken: ' . implode( ', ', $pair_bad ) );

	// Eight images total, all distinct: the pair must not reappear as a highlight.
	$pair_hash = array_map( static fn( $p ) => is_readable( $p ) ? hash_file( 'sha256', $p ) : '', $pair );
	count( array_unique( array_merge( $bw_hash, $pair_hash ) ) ) === 8
		? ok( '8. Brentwood carries exactly 8 distinct images: 2 before/after + 6 highlights' )
		: bad( '8. Brentwood image set is not 8 distinct files' );

	// Alt text: present, unique, and free of invented specifics.
	$alts = array_map( static fn( $s ) => trim( (string) $s['alt'] ), $ready );
	$empty_alt = array_filter( $alts, static fn( $a ) => '' === $a );
	empty( $empty_alt ) && count( array_unique( $alts ) ) === count( $alts )
		? ok( '9. every Brentwood highlight carries unique, non-empty alt text' )
		: bad( '9. Brentwood alt text is missing or duplicated' );

	$forbidden = array( '$', 'warranty', 'guarantee', 'permit', 'star', 'rating', 'best ', 'award', '20 2', 'before and after' );
	$claims    = array();
	foreach ( $alts as $a ) {
		foreach ( $forbidden as $f ) {
			if ( false !== stripos( $a, $f ) ) { $claims[] = "\"$f\" in: $a"; }
		}
	}
	empty( $claims )
		? ok( '10. no Brentwood alt text asserts a price, warranty, permit, rating or before/after claim' )
		: bad( '10. unsupported claim in alt text — ' . implode( ' | ', $claims ) );

	// The rendered page serves all eight and references no other project.
	$bw_body = sa_fetch( "$home/projects/brentwood-pool-project/" );
	$leaks   = array();
	foreach ( $slugs as $s ) {
		if ( 'brentwood-pool-project' === $s ) { continue; }
		if ( false !== strpos( $bw_body, "galleries/$s/" ) ) { $leaks[] = $s; }
	}
	empty( $leaks )
		? ok( '11. the Brentwood page references no other project\'s gallery directory' )
		: bad( '11. foreign gallery referenced on the Brentwood page: ' . implode( ', ', $leaks ) );

	$http_bad = array();
	foreach ( array_merge( $bw_files, $pair ) as $f ) {
		$url = str_replace( $child, get_stylesheet_directory_uri(), $f );
		if ( 200 !== sa_status( $url ) ) { $http_bad[] = basename( $f ); }
	}
	empty( $http_bad )
		? ok( '12. all 8 Brentwood image paths resolve over HTTP with status 200' )
		: bad( '12. unreachable Brentwood image(s): ' . implode( ', ', $http_bad ) );

	// Identity fields must be exactly what they were before this batch.
	$identity_ok = 'Pool Cleaning in Brentwood, CA' === $bw['title']
		&& 'brentwood-pool-project-before.webp' === basename( (string) $bw['before_image'] )
		&& 'brentwood-pool-project-after.webp' === basename( (string) $bw['after_image'] )
		&& 'Pool cleaning and water clearing' === $bw['scope']
		&& '1–2 weeks' === $bw['timeline']
		&& '$300–$800' === $bw['investment']
		&& 'Existing pool finish retained' === $bw['finish']
		&& 'Before and After: Pool Cleaning in Brentwood, CA' === $bw['comparison_heading'];
	$identity_ok
		? ok( '13. Brentwood keeps its title, before/after pair, scope, finish, timeline, investment and comparison heading' )
		: bad( '13. a preserved Brentwood field changed' );

	200 === sa_status( "$home/projects/brentwood-pool-project/" )
		? ok( '14. the Brentwood canonical project URL still resolves' )
		: bad( '14. the Brentwood project URL no longer resolves' );
}

/* ══════════════════════════════════════════════════════════════════════
 * SERVICE-AREA CARD SET
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== SERVICE-AREA CARD SET ==\n";

$cards = showtime_service_area_cards();

count( $cards ) === 14
	? ok( '15. the resolver returns exactly 14 service-area cards' )
	: bad( '15. resolver returned ' . count( $cards ) . ' cards, expected 14' );

$names = array_map( static fn( $c ) => $c['name'], $cards );
$urls  = array_map( static fn( $c ) => $c['url'], $cards );

count( array_unique( $names ) ) === count( $cards )
	? ok( '16. all card locations are unique' )
	: bad( '16. duplicate locations: ' . implode( ', ', array_diff_assoc( $names, array_unique( $names ) ) ) );

count( array_unique( $urls ) ) === count( $cards )
	? ok( '17. all card destinations are unique' )
	: bad( '17. duplicate destinations present' );

// Every location in the project registry appears exactly once.
$hoods   = array_values( array_unique( array_map( static fn( $p ) => (string) $p['neighborhood'], $managed ) ) );
$missing = array_diff( $hoods, $names );
empty( $missing )
	? ok( '18. every location in the 14-project registry has a card: ' . implode( ', ', $names ) )
	: bad( '18. locations missing a card: ' . implode( ', ', $missing ) );

// The five previously-absent locations are present.
$expected_new = array( 'Burbank', 'Brentwood', 'Toluca Lake', 'North Hollywood', 'Van Nuys' );
$still_absent = array_diff( $expected_new, $names );
empty( $still_absent )
	? ok( '19. the five added locations are present: ' . implode( ', ', $expected_new ) )
	: bad( '19. still absent: ' . implode( ', ', $still_absent ) );

// The original nine keep their order, their area destination and their copy.
$expected_first_nine = array(
	'Sherman Oaks', 'Encino', 'Beverly Hills', 'Studio City', 'Tarzana',
	'Woodland Hills', 'West Hollywood', 'Bel Air', 'Calabasas',
);
array_slice( $names, 0, 9 ) === $expected_first_nine
	? ok( '20. the original nine cards keep their exact position and order' )
	: bad( '20. the first nine cards are now: ' . implode( ', ', array_slice( $names, 0, 9 ) ) );

$area_page_bad = array();
foreach ( array_slice( $cards, 0, 9 ) as $c ) {
	if ( ! $c['has_area_page'] ) { $area_page_bad[] = $c['name'] . ' lost its area page'; }
	if ( 0 !== strpos( $c['url'], $home . '/service-areas/' ) ) { $area_page_bad[] = $c['name'] . ' no longer links to its area page'; }
}
empty( $area_page_bad )
	? ok( '21. all nine original cards still link to their /service-areas/ landing page' )
	: bad( '21. ' . implode( '; ', $area_page_bad ) );

// Every destination is a real, internal, non-redirecting URL.
$link_bad = array();
foreach ( $cards as $c ) {
	if ( 0 !== strpos( (string) $c['url'], $home ) ) { $link_bad[] = $c['name'] . ' links off-site'; continue; }
	$code = sa_status( (string) $c['url'] );
	if ( 200 !== $code ) { $link_bad[] = $c['name'] . " -> HTTP $code"; }
}
empty( $link_bad )
	? ok( '22. all 14 card destinations are internal URLs returning HTTP 200 without a redirect' )
	: bad( '22. bad destinations: ' . implode( ', ', $link_bad ) );

// A location with no published area page links to its project page instead,
// and never to an unpublished /service-areas/ route.
$fallback_bad = array();
foreach ( $cards as $c ) {
	if ( $c['has_area_page'] ) { continue; }
	if ( 0 !== strpos( (string) $c['url'], $home . '/projects/' ) ) {
		$fallback_bad[] = $c['name'] . ' does not fall back to its project page';
	}
	// Strengthened: pool counts were unverified, so the key was removed from the
	// card contract outright rather than merely left blank. Its ABSENCE is now
	// the assertion — a reintroduced key fails here.
	if ( array_key_exists( 'pool_count', $c ) ) {
		$fallback_bad[] = $c['name'] . ' still carries a pool_count key';
	}
	// The sub-line itself is gone now, so "is it neutral?" became "is it absent?"
	// — strictly stronger: no wording at all can assert a schedule or frequency.
	if ( array_key_exists( 'sub', $c ) ) {
		$fallback_bad[] = $c['name'] . ' still carries a sub key';
	}
}
empty( $fallback_bad )
	? ok( '23. locations without a published area page link to their project page and assert no count, schedule or frequency' )
	: bad( '23. ' . implode( '; ', $fallback_bad ) );

// Each new card reuses the exact cover image the Projects archive shows.
$archive_cover = array();
foreach ( showtime_project_cards() as $pc ) { $archive_cover[ (string) $pc['neighborhood'] ] = (string) $pc['image']; }
$cover_bad = array();
foreach ( $cards as $c ) {
	if ( $c['has_area_page'] ) { continue; }
	if ( ( $archive_cover[ $c['name'] ] ?? '' ) !== $c['image'] ) {
		$cover_bad[] = $c['name'];
	}
}
empty( $cover_bad )
	? ok( '24. every added card reuses the exact project cover image from the Projects archive' )
	: bad( '24. wrong cover image for: ' . implode( ', ', $cover_bad ) );

$img_bad = array();
foreach ( $cards as $c ) {
	if ( '' === (string) $c['image'] ) { $img_bad[] = $c['name'] . ' has no image'; continue; }
	if ( 0 !== strpos( (string) $c['image'], 'http' ) ) { $img_bad[] = $c['name'] . ' image is not a URL'; continue; }
	// Locally-bundled images must exist on disk with a real image MIME type.
	if ( 0 === strpos( (string) $c['image'], get_stylesheet_directory_uri() ) ) {
		$p = str_replace( get_stylesheet_directory_uri(), $child, (string) $c['image'] );
		$p = (string) strtok( $p, '?' );
		if ( '' === sa_image_mime( $p ) ) { $img_bad[] = $c['name'] . ' image is not a valid image file'; }
	}
	if ( 200 !== sa_status( (string) $c['image'] ) ) { $img_bad[] = $c['name'] . ' image does not serve 200'; }
}
empty( $img_bad )
	? ok( '25. every card image resolves and, when bundled, is a real image file on disk' )
	: bad( '25. ' . implode( '; ', $img_bad ) );

/* ══════════════════════════════════════════════════════════════════════
 * /service-areas/ PAGE
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== SERVICE AREAS PAGE ==\n";

$hub = sa_fetch( "$home/service-areas/" );

$hub_cards = preg_match_all( '/class="area-card area-card--lg"/', $hub );
14 === $hub_cards
	? ok( '26. /service-areas/ renders exactly 14 cards' )
	: bad( "26. /service-areas/ renders $hub_cards cards, expected 14" );

preg_match_all( '/<a class="area-card area-card--lg" href="([^"]+)"/', $hub, $hm );
$hub_hrefs = $hm[1] ?? array();
count( array_unique( $hub_hrefs ) ) === 14
	? ok( '27. the 14 rendered card links are unique' )
	: bad( '27. rendered card links are not unique (' . count( array_unique( $hub_hrefs ) ) . ' unique)' );

// The card component is otherwise unchanged: same grid, same wrapper, same
// overlay, same title. The .area-card__sub element was removed outright — the
// card is now the location name over its photograph — so its ABSENCE is part of
// the structural contract rather than its presence.
$structure_ok = false !== strpos( $hub, 'areas-hub__grid' )
	&& false !== strpos( $hub, 'area-card__overlay' )
	&& false !== strpos( $hub, 'area-card__content' )
	&& false !== strpos( $hub, 'area-card__title' )
	&& false === strpos( $hub, 'area-card__sub' );
$structure_ok
	? ok( '28. the existing card component and grid wrapper are unchanged, and carry no sub-line element' )
	: bad( '28. the card component structure changed' );

/* ══════════════════════════════════════════════════════════════════════
 * HOMEPAGE CAROUSEL
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== HOMEPAGE CAROUSEL ==\n";

$hp = sa_fetch( "$home/" );

preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>#s', $hp, $am );
$anchors   = $am[0] ?? array();
$clones    = array_values( array_filter( $anchors, static fn( $a ) => false !== strpos( $a, 'tabindex="-1"' ) ) );
$semantic  = array_values( array_filter( $anchors, static fn( $a ) => false === strpos( $a, 'tabindex="-1"' ) ) );

count( $semantic ) === 14
	? ok( '29. the homepage carousel renders exactly 14 semantic cards (clones excluded)' )
	: bad( '29. carousel has ' . count( $semantic ) . ' semantic cards, expected 14' );

count( $clones ) === 14
	? ok( '30. exactly one animation clone accompanies each semantic card' )
	: bad( '30. carousel has ' . count( $clones ) . ' clones, expected 14' );

$clone_bad = array_filter( $clones, static fn( $a ) => false === strpos( $a, 'aria-hidden="true"' ) );
empty( $clone_bad )
	? ok( '31. every clone is aria-hidden AND removed from the tab order' )
	: bad( '31. ' . count( $clone_bad ) . ' clone(s) are not aria-hidden' );

// Two rows, seven semantic cards each.
preg_match_all( '#<div class="service-areas__row service-areas__row--(ltr|rtl)">(.*?)</div>\s*</div>#s', $hp, $rm, PREG_SET_ORDER );
count( $rm ) === 2
	? ok( '32. the carousel keeps its existing two-row structure' )
	: bad( '32. found ' . count( $rm ) . ' carousel rows, expected 2' );

$row_counts = array();
$row_names  = array();
foreach ( $rm as $row ) {
	preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>.*?</a>#s', $row[2], $ra );
	$row_semantic = array_values( array_filter( $ra[0] ?? array(), static fn( $a ) => false === strpos( $a, 'tabindex="-1"' ) ) );
	$row_counts[] = count( $row_semantic );
	foreach ( $row_semantic as $a ) {
		if ( preg_match( '#area-card__title">([^<]+)#', $a, $t ) ) { $row_names[ $row[1] ][] = trim( $t[1] ); }
	}
}
$row_counts === array( 7, 7 )
	? ok( '33. the carousel splits into exactly seven unique cards per row' )
	: bad( '33. row split is ' . implode( ' / ', $row_counts ) . ', expected 7 / 7' );

$all_row_names = array_merge( $row_names['ltr'] ?? array(), $row_names['rtl'] ?? array() );
count( array_unique( $all_row_names ) ) === 14
	? ok( '34. the two rows together carry 14 distinct locations, none repeated across rows' )
	: bad( '34. rows carry ' . count( array_unique( $all_row_names ) ) . ' distinct locations' );

// Each location is discoverable exactly once by a crawler / assistive tech.
$dup_semantic = array();
foreach ( $semantic as $a ) {
	if ( false !== strpos( $a, 'aria-hidden' ) ) { $dup_semantic[] = $a; }
}
empty( $dup_semantic )
	? ok( '35. no semantic card is hidden from assistive technology' )
	: bad( '35. ' . count( $dup_semantic ) . ' semantic card(s) carry aria-hidden' );

// The loop math the seamless reset depends on must stay intact.
$home_css = (string) file_get_contents( "$child/assets/css/home.css" );
$loop_ok  = false !== strpos( $home_css, 'width: max-content' )
	&& preg_match( '/\.area-card--marquee\s*\{[^}]*margin-right:/s', $home_css )
	&& ! preg_match( '/\.service-areas__track\s*\{[^}]*\bgap:/s', $home_css )
	&& false !== strpos( $home_css, 'translateX(-50%)' );
$loop_ok
	? ok( '36. the seamless-loop contract holds: max-content track, per-card margin-right (no flex gap), 50% translation' )
	: bad( '36. the marquee loop math changed — a seam or jump is likely at the reset point' );

preg_match( '/@media \(prefers-reduced-motion: reduce\)\s*\{\s*\.service-areas__track\s*\{\s*animation: none/', $home_css )
	? ok( '37. the carousel still respects prefers-reduced-motion' )
	: bad( '37. the prefers-reduced-motion guard on the carousel is missing' );

/* ══════════════════════════════════════════════════════════════════════
 * ESTIMATE POPUP
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== ESTIMATE POPUP ==\n";

$GHL = 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb';
$TEL = 'tel:+13238252099';

1 === substr_count( $hp, 'id="stp-estimate-popup"' )
	? ok( '38. the popup is printed exactly once on the homepage' )
	: bad( '38. popup printed ' . substr_count( $hp, 'id="stp-estimate-popup"' ) . ' times' );

1 === substr_count( $hp, 'assets/css/popup.css' ) && 1 === substr_count( $hp, 'assets/js/popup.js' )
	? ok( '39. popup CSS and deferred JS are each enqueued exactly once' )
	: bad( '39. popup assets are not enqueued exactly once' );

// WP prints the strategy attribute before src, so match the whole tag.
preg_match( '#<script\b[^>]*\bsrc="[^"]*assets/js/popup\.js[^"]*"[^>]*>#', $hp, $tag )
	&& false !== strpos( $tag[0], ' defer' )
	? ok( '40. the popup script is deferred' )
	: bad( '40. the popup script is not deferred' );

1 === substr_count( $hp, 'href="' . $GHL . '"' )
	? ok( '41. the primary CTA points at the exact GHL booking URL' )
	: bad( '41. GHL booking URL missing or duplicated' );

1 === substr_count( $hp, 'href="' . $TEL . '"' )
	? ok( '42. the secondary CTA points at the exact tel: URL' )
	: bad( '42. tel: URL missing or duplicated' );

// The calendar must not be embedded — only linked.
! preg_match( '#<iframe[^>]+showtimepoolmechanics#', $hp ) && ! preg_match( '#stp-estimate[^>]*>.*?<iframe#s', $hp )
	? ok( '43. no GHL iframe is embedded in the popup markup' )
	: bad( '43. a GHL iframe is embedded before any click' );

$dialog_ok = preg_match( '#role="dialog"#', $hp )
	&& preg_match( '#aria-modal="true"#', $hp )
	&& preg_match( '#aria-labelledby="stp-estimate-title"#', $hp )
	&& preg_match( '#id="stp-estimate-title"#', $hp );
$dialog_ok
	? ok( '44. the popup uses semantic dialog markup with a resolving heading association' )
	: bad( '44. dialog semantics or heading association missing' );

preg_match( '#class="stp-estimate__close"[^>]*aria-label="[^"]{6,}"#s', $hp )
	? ok( '45. the close button carries a descriptive accessible label' )
	: bad( '45. the close button label is missing or too terse' );

// Exact copy.
$copy_missing = array();
foreach ( array(
	'LOS ANGELES POOL EXPERTS',
	'Get a Free Estimate',
	'Free, no-obligation estimate',
	'Response within 1 business day',
	'Upfront pricing before any work begins',
	'Serving 50+ Los Angeles communities',
	'Book your Service Appointment',
	'No spam',
	'No pressure',
	'Fast response',
) as $needle ) {
	if ( false === strpos( $hp, $needle ) ) { $copy_missing[] = $needle; }
}
empty( $copy_missing )
	? ok( '46. every required popup string is present verbatim' )
	: bad( '46. missing copy: ' . implode( ' | ', $copy_missing ) );

// The retired weekly-maintenance popup must be gone everywhere.
$old_traces = array();
if ( false !== strpos( $hp, 'stp-popup' ) )                                    { $old_traces[] = 'old markup on the homepage'; }
if ( file_exists( "$child/template-parts/global/popup-weekly.php" ) )          { $old_traces[] = 'popup-weekly.php still exists'; }
if ( false !== strpos( (string) file_get_contents( "$child/assets/css/components.css" ), '.stp-popup' ) ) { $old_traces[] = '.stp-popup rules remain in components.css'; }
if ( false !== strpos( (string) file_get_contents( "$child/assets/js/popup.js" ), 'data-open-weekly-popup' ) ) { $old_traces[] = 'old opener hook remains in popup.js'; }
empty( $old_traces )
	? ok( '47. no trace of the retired weekly-maintenance popup remains — markup, template part, CSS or listener' )
	: bad( '47. old popup remnants: ' . implode( '; ', $old_traces ) );

// It must never reach a non-page context.
$feed = sa_fetch( "$home/feed/" );
$xml  = sa_fetch( "$home/wp-sitemap.xml" );
false === strpos( $feed, 'stp-estimate' ) && false === strpos( $xml, 'stp-estimate' )
	? ok( '48. the popup is absent from the RSS feed and the XML sitemap' )
	: bad( '48. popup markup leaked into a feed or the sitemap' );

preg_match( '/@media \(prefers-reduced-motion: reduce\)/', (string) file_get_contents( "$child/assets/css/popup.css" ) )
	? ok( '49. the popup stylesheet respects prefers-reduced-motion' )
	: bad( '49. the popup has no prefers-reduced-motion guard' );

/* ══════════════════════════════════════════════════════════════════════
 * NOTHING ELSE MOVED
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== NOTHING ELSE MOVED ==\n";

$sitemap_html = sa_fetch( "$home/sitemap/" );
200 === sa_status( "$home/sitemap/" ) && false !== stripos( $sitemap_html, '<html' )
	? ok( '50. /sitemap/ is still served as the human HTML sitemap' )
	: bad( '50. /sitemap/ is no longer an HTML page' );

$xml_ok = 200 === sa_status( "$home/wp-sitemap.xml" ) && 0 === strpos( ltrim( $xml ), '<?xml' );
if ( $xml_ok ) {
	libxml_use_internal_errors( true );
	$xml_ok = false !== simplexml_load_string( $xml );
	libxml_clear_errors();
}
$xml_ok
	? ok( '51. /wp-sitemap.xml is still served and parses as valid XML' )
	: bad( '51. /wp-sitemap.xml is missing or no longer valid XML' );

// Every managed project page still resolves and keeps one canonical.
$proj_bad = array();
foreach ( $slugs as $s ) {
	$b = sa_fetch( "$home/projects/$s/" );
	if ( 1 !== substr_count( $b, 'rel="canonical"' ) ) { $proj_bad[] = "$s canonical"; }
	if ( false === strpos( $b, 'index' ) ) { $proj_bad[] = "$s robots"; }
}
empty( $proj_bad )
	? ok( '52. all 14 project pages still render exactly one canonical' )
	: bad( '52. ' . implode( ', ', $proj_bad ) );

// Header and footer are untouched on the two pages this branch changed.
$chrome_ok = true;
foreach ( array( $hp, $hub ) as $body ) {
	if ( false === strpos( $body, 'footer-wordmark' ) ) { $chrome_ok = false; }
	if ( false === strpos( $body, 'site-header' ) && false === strpos( $body, '<header' ) ) { $chrome_ok = false; }
}
$chrome_ok
	? ok( '53. the site header and footer still render on both changed pages' )
	: bad( '53. header or footer missing from a changed page' );

// The other 13 project records still publish six highlights each.
$other_bad = array();
foreach ( $slugs as $s ) {
	if ( 'brentwood-pool-project' === $s ) { continue; }
	$g = showtime_project_gallery( showtime_project_data( $s ) );
	$r = array_filter( $g, static fn( $x ) => 'ready' === $x['status'] );
	if ( 6 !== count( $r ) ) { $other_bad[] = "$s has " . count( $r ); }
}
empty( $other_bad )
	? ok( '54. the other 13 projects still publish exactly six highlights each' )
	: bad( '54. changed galleries: ' . implode( ', ', $other_bad ) );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

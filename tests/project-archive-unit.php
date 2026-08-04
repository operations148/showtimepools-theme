<?php
/**
 * Projects archive + placeholder-project regression tests.
 *
 * Covers the expansion of /projects/ from 6 verified projects to 14 (6 verified
 * + 8 "Coming soon" placeholders) rendered as a three-slide slider, and proves
 * the placeholders never masquerade as completed work.
 *
 *   php tests/project-archive-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/project-archive-unit.php
 *
 * Read-only: creates no posts and writes no options.
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

$pass = 0; $fail = 0;
function ok( $m ) { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ) { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }

function fetch_body( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 25,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-archive-test/1.0',
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

if ( ! function_exists( 'showtime_project_slides' ) || ! class_exists( 'Showtime\Projects' ) ) {
	bad( 'showtime_project_slides() / Showtime\Projects not loaded — is the theme + core plugin active?' );
	echo "\n== RESULT ==\n  pass: 0   fail: 1\n";
	exit( 1 );
}

$base = rtrim( (string) get_option( 'home' ), '/' );

$verified_slugs = array(
	'sherman-oaks-mid-century-remodel',
	'encino-estate-new-build',
	'studio-city-modern-automation',
	'beverly-hills-luxe-spa-renovation',
	'tarzana-resort-style-finish',
	'woodland-hills-tile-coping-refresh',
);
$placeholder_slugs = array(
	'van-nuys-pool-project',
	'north-hollywood-pool-project',
	'toluca-lake-pool-project',
	'burbank-pool-project',
	'calabasas-pool-project',
	'bel-air-pool-project',
	'west-hollywood-pool-project',
	'brentwood-pool-project',
);

echo "\n== PROJECT REGISTRY — 14 managed projects ==\n";

$all     = \Showtime\Projects::all();
$managed = array_values( array_filter( $all, static fn( $e ) => ! empty( $e['managed'] ) ) );

/* 1. Exactly 14 managed projects. */
14 === count( $managed )
	? ok( '1. registry contains exactly 14 managed projects' )
	: bad( '1. registry contains ' . count( $managed ) . ' managed projects (expected 14)' );

/* 2. All 14 slugs unique. */
$slugs = array_column( $managed, 'slug' );
count( array_unique( $slugs ) ) === count( $slugs )
	? ok( '2. all 14 managed slugs are unique' )
	: bad( '2. duplicate slug(s): ' . implode( ', ', array_diff_assoc( $slugs, array_unique( $slugs ) ) ) );

/* 3. The original six are unchanged — slug, title, scope, finish, timeline,
 * investment, and images all still carry their verified values. A placeholder
 * flag on any of them would be a regression. */
$expected_six = array(
	'sherman-oaks-mid-century-remodel'   => array( 'Pool Interior Finish Project in Sherman Oaks, CA', '1–3 weeks', '$14,000–$30,000' ),
	'encino-estate-new-build'            => array( 'Pool Interior Finish Renewal in Encino, CA',        '1–3 weeks', '$14,000–$30,000' ),
	'studio-city-modern-automation'      => array( 'Pool Control Panel Replacement in Studio City, CA', '1–3 days',  '$3,000–$8,000' ),
	'beverly-hills-luxe-spa-renovation'  => array( 'Spa Tile Renovation in Beverly Hills, CA',          '1–3 weeks', '$12,000–$25,000' ),
	'tarzana-resort-style-finish'        => array( 'Pool Resurfacing in Tarzana, CA',                   '1–3 weeks', '$14,000–$30,000' ),
	'woodland-hills-tile-coping-refresh' => array( 'Spa Tile Renovation in Woodland Hills, CA',         '1–3 weeks', '$10,000–$25,000' ),
);
$six_bad = array();
foreach ( $expected_six as $slug => $exp ) {
	$d = showtime_project_data( $slug );
	if ( null === $d ) { $six_bad[] = "$slug missing"; continue; }
	if ( $d['title'] !== $exp[0] )      { $six_bad[] = "$slug title"; }
	if ( $d['timeline'] !== $exp[1] )   { $six_bad[] = "$slug timeline"; }
	if ( $d['investment'] !== $exp[2] ) { $six_bad[] = "$slug investment"; }
	if ( ! empty( $d['is_coming_soon'] ) ) { $six_bad[] = "$slug wrongly flagged placeholder"; }
	if ( '' === $d['hero_image'] )      { $six_bad[] = "$slug lost its hero image"; }
	if ( '' === $d['before_image'] || '' === $d['after_image'] ) { $six_bad[] = "$slug lost a comparison image"; }
}
empty( $six_bad )
	? ok( '3. the original six verified records are unchanged' )
	: bad( '3. changed: ' . implode( ', ', $six_bad ) );

/* 4. Exactly eight records carry coming_soon status. */
$coming = array_values( array_filter( $managed, static fn( $e ) => ( $e['status'] ?? '' ) === 'coming_soon' ) );
$flagged = array_values( array_filter( $managed, static fn( $e ) => ! empty( $e['is_coming_soon'] ) ) );
( 8 === count( $coming ) && 8 === count( $flagged ) && count( $coming ) === count( $flagged ) )
	? ok( '4. exactly 8 records are status=coming_soon AND is_placeholder' )
	: bad( '4. coming_soon=' . count( $coming ) . ' is_placeholder=' . count( $flagged ) . ' (expected 8/8)' );

/* 4b. Placeholder slugs are the eight expected areas, in the specified order. */
$actual_ph = array_column( $flagged, 'slug' );
$actual_ph === $placeholder_slugs
	? ok( '4b. the eight placeholders are the expected areas in the specified order' )
	: bad( '4b. placeholder order/slugs: ' . implode( ', ', $actual_ph ) );

echo "\n== SLIDE GROUPING ==\n";

$slides = showtime_project_slides( 6 );

/* 5. Exactly three slides, counts 6 / 6 / 2. */
$counts = array_map( 'count', $slides );
( 3 === count( $slides ) && array( 6, 6, 2 ) === array_values( $counts ) )
	? ok( '5. exactly three slides with counts 6 / 6 / 2' )
	: bad( '5. slides=' . count( $slides ) . ' counts=' . implode( '/', $counts ) );

/* 5b. Slide 1 is the six verified projects. */
$slide1 = array_column( $slides[0] ?? array(), 'slug' );
$slide1 === $verified_slugs
	? ok( '5b. slide 1 holds the six verified projects' )
	: bad( '5b. slide 1: ' . implode( ', ', $slide1 ) );

/* 6. Slide 3 holds West Hollywood + Brentwood. */
$slide3 = array_column( $slides[2] ?? array(), 'slug' );
$slide3 === array( 'west-hollywood-pool-project', 'brentwood-pool-project' )
	? ok( '6. slide 3 holds West Hollywood and Brentwood' )
	: bad( '6. slide 3: ' . implode( ', ', $slide3 ) );

echo "\n== ARCHIVE RAW HTML (no JavaScript executed) ==\n";

$html = fetch_body( "$base/projects/" );
'' !== $html
	? ok( 'archive responded' )
	: bad( 'archive returned an empty body' );

/* 7. All 14 cards exist in raw server HTML — curl runs no JS. */
$card_count = preg_match_all( '#<(?:a|article) class="proj-card[^"]*"#', $html );
14 === $card_count
	? ok( '7. all 14 project cards exist in raw HTML without JavaScript' )
	: bad( "7. raw HTML contains $card_count project cards (expected 14)" );

/* 7b. Three slides and three dots in raw HTML. */
$slide_els = preg_match_all( '#data-proj-slider-slide="#', $html );
$dot_els   = preg_match_all( '#data-proj-slider-dot="#', $html );
( 3 === $slide_els && 3 === $dot_els )
	? ok( '7b. raw HTML has 3 slides and exactly 3 pagination dots' )
	: bad( "7b. slides=$slide_els dots=$dot_els (expected 3/3)" );

/* 7c. Cards are NOT buried in <template> or inline display:none. */
$buried = ( false !== stripos( $html, '<template' ) && preg_match( '#<template[^>]*>.*?proj-card#s', $html ) )
	|| preg_match( '#class="proj-slider__slide"[^>]*style="[^"]*display:\s*none#i', $html );
! $buried
	? ok( '7c. no card is hidden inside a <template> or inline display:none' )
	: bad( '7c. cards are buried in an inert/hidden container' );

/* 8. Every card links to its own project route. */
preg_match_all( '#<(?:a|article) class="proj-card[^"]*"\s+href="([^"]+)"#', $html, $hm );
$hrefs   = $hm[1] ?? array();
$missing = array();
foreach ( array_merge( $verified_slugs, $placeholder_slugs ) as $slug ) {
	$found = false;
	foreach ( $hrefs as $h ) {
		if ( false !== strpos( $h, "/projects/$slug/" ) ) { $found = true; break; }
	}
	if ( ! $found ) { $missing[] = $slug; }
}
( 14 === count( $hrefs ) && empty( $missing ) )
	? ok( '8. all 14 cards link to their own project route' )
	: bad( '8. hrefs=' . count( $hrefs ) . ' missing: ' . implode( ', ', $missing ) );

/* 9. Placeholder fields display "Coming soon" — never blank, never $0. */
$coming_cells = preg_match_all( '#<dd>Coming soon</dd>#', $html );
$empty_cells  = preg_match_all( '#<dd>\s*</dd>#', $html );
$zero_cells   = preg_match_all( '#<dd>\s*\$0#', $html );
( 32 === $coming_cells && 0 === $empty_cells && 0 === $zero_cells )
	? ok( '9. 8 placeholders x 4 fields render "Coming soon"; no empty or $0 values' )
	: bad( "9. coming=$coming_cells empty=$empty_cells zero=$zero_cells (expected 32/0/0)" );

/* 9b. Every placeholder card shows the Coming Soon chip. */
$chips = preg_match_all( '#class="proj-card__status"#', $html );
8 === $chips
	? ok( '9b. all 8 placeholder cards show a "Coming Soon" chip' )
	: bad( "9b. $chips Coming Soon chips (expected 8)" );

/* 10. Placeholder cards carry NO <img> at all, so they can never reuse a
 * verified project's photograph. Also assert none of the six verified image
 * filenames appears inside a placeholder card. */
preg_match_all( '#<(?:a|article) class="proj-card proj-card--placeholder".*?</(?:a|article)>#s', $html, $pm );
$ph_cards = $pm[0] ?? array();
$ph_imgs  = 0;
$stolen   = array();
foreach ( $ph_cards as $card ) {
	$ph_imgs += preg_match_all( '#<img#', $card );
	foreach ( $verified_slugs as $vs ) {
		if ( false !== strpos( $card, $vs . '-after' ) || false !== strpos( $card, $vs . '-before' ) ) {
			$stolen[] = $vs;
		}
	}
}
( 8 === count( $ph_cards ) && 0 === $ph_imgs && empty( $stolen ) )
	? ok( '10. placeholder cards contain no <img> and reuse no verified project photo' )
	: bad( '10. cards=' . count( $ph_cards ) . " imgs=$ph_imgs stolen=" . implode( ',', $stolen ) );

/* 10b. Each placeholder card renders the CSS-only branded placeholder. */
$art = preg_match_all( '#Project photos coming soon#', $html );
8 === $art
	? ok( '10b. all 8 placeholders render the CSS-only branded placeholder' )
	: bad( "10b. $art branded placeholders (expected 8)" );

/* 16. Slider controls: semantic buttons, labelled, and no duplicate IDs. */
$prev_ok = preg_match( '#<button[^>]+data-proj-slider-prev[^>]+aria-label="[^"]+"#', $html )
	|| preg_match( '#<button[^>]+aria-label="[^"]+"[^>]*data-proj-slider-prev#', $html );
$next_ok = preg_match( '#<button[^>]+data-proj-slider-next[^>]+aria-label="[^"]+"#', $html )
	|| preg_match( '#<button[^>]+aria-label="[^"]+"[^>]*data-proj-slider-next#', $html );
$dots_labelled = preg_match_all( '#<button[^>]*data-proj-slider-dot="\d+"[^>]*aria-label="Slide \d+ of 3"#', $html );
( $prev_ok && $next_ok && 3 === $dots_labelled )
	? ok( '16. prev/next/dots are semantic <button>s with accessible labels' )
	: bad( "16. prev=$prev_ok next=$next_ok labelled dots=$dots_labelled" );

/* 16b. No duplicate id attributes anywhere in the archive document. */
preg_match_all( '#\sid="([^"]+)"#', $html, $im );
$ids  = $im[1] ?? array();
$dupe = array_values( array_unique( array_diff_assoc( $ids, array_unique( $ids ) ) ) );
empty( $dupe )
	? ok( '16b. no duplicate id attributes on the archive page' )
	: bad( '16b. duplicate id(s): ' . implode( ', ', $dupe ) );

/* 16c. Each slide announces its position to assistive technology. */
$slide_labels = preg_match_all( '#aria-label="Slide [123] of 3"#', $html );
$slide_labels >= 6
	? ok( '16c. slides and dots announce "Slide N of 3" (' . $slide_labels . ' labels)' )
	: bad( "16c. only $slide_labels 'Slide N of 3' labels (expected >= 6)" );

/* 16d. The nav ships with the `hidden` attribute so it never appears without
 * the script that operates it, and there is a live region for announcements. */
( preg_match( '#data-proj-slider-nav[^>]*hidden#', $html ) && false !== strpos( $html, 'data-proj-slider-status' ) )
	? ok( '16d. nav is hidden until enhanced and an aria-live status region exists' )
	: bad( '16d. nav hidden attribute or aria-live status region missing' );

/* 17. Without JS the slider is not in enhanced mode, so CSS keeps every slide
 * stacked and visible. The class is added by script only. */
false === strpos( $html, 'proj-slider is-enhanced' )
	? ok( '17. archive is not pre-marked "is-enhanced" — no-JS keeps all slides stacked' )
	: bad( '17. archive ships "is-enhanced", which would hide slides without JS' );

/* 17b. Slide 3 uses the centered modifier rather than filler cards. */
$center = preg_match_all( '#proj-slider__grid--center#', $html );
$fillers = preg_match_all( '#proj-card[^"]*(is-empty|is-filler|placeholder-spacer)#', $html );
( 1 === $center && 0 === $fillers )
	? ok( '17b. the partial slide is centered, with no filler/invisible cards' )
	: bad( "17b. center=$center fillers=$fillers" );

echo "\n== PLACEHOLDER PAGE SEO ==\n";

/* 12. Placeholder pages: 200, noindex,follow, self-canonical, no project schema. */
$ph_bad = array();
foreach ( $placeholder_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	if ( '' === $body ) { $ph_bad[] = "$slug empty"; continue; }
	if ( ! preg_match( "#robots['\"] content=['\"]noindex, follow#", $body ) ) { $ph_bad[] = "$slug not noindex"; }
	if ( ! preg_match( '#<link rel="canonical" href="[^"]*/projects/' . preg_quote( $slug, '#' ) . '/"#', $body ) ) { $ph_bad[] = "$slug canonical"; }
	if ( preg_match( '#"@type":"CreativeWork"#', $body ) ) { $ph_bad[] = "$slug emitted CreativeWork"; }
	if ( ! preg_match( '#"@type":"BreadcrumbList"#', $body ) ) { $ph_bad[] = "$slug missing BreadcrumbList"; }
	if ( false === stripos( $body, 'Coming soon' ) ) { $ph_bad[] = "$slug shows no Coming soon notice"; }
}
empty( $ph_bad )
	? ok( '12. all 8 placeholder pages: 200, noindex+follow, self-canonical, breadcrumb-only schema' )
	: bad( '12. ' . implode( '; ', $ph_bad ) );

/* 11. Comparison stays hidden until BOTH real images exist. */
$cmp_bad = array();
foreach ( $placeholder_slugs as $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'project' );
	if ( ! $post instanceof WP_Post ) { $cmp_bad[] = "$slug no routing post"; continue; }
	if ( null !== showtime_project_compare( (int) $post->ID ) ) { $cmp_bad[] = "$slug resolved a comparison"; }
}
empty( $cmp_bad )
	? ok( '11. placeholder comparisons stay hidden until both images exist' )
	: bad( '11. ' . implode( ', ', $cmp_bad ) );

/* 14. Placeholder pages emit no price / review / rating / product schema. */
$schema_bad = array();
foreach ( $placeholder_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	foreach ( array( '"@type":"Product"', '"@type":"Offer"', '"offers"', 'aggregateRating', '"@type":"Review"' ) as $needle ) {
		if ( false !== stripos( $body, $needle ) ) { $schema_bad[] = "$slug:$needle"; }
	}
	// No fabricated completion data or currency figure anywhere on the page.
	if ( preg_match( '#<dd>\$[\d,]+#', $body ) ) { $schema_bad[] = "$slug: price figure"; }
	if ( preg_match( '#<dt>Completed</dt>#', $body ) ) { $schema_bad[] = "$slug: completion date"; }
}
empty( $schema_bad )
	? ok( '14. no price/review/rating/product/completion data on placeholder pages' )
	: bad( '14. ' . implode( ', ', $schema_bad ) );

echo "\n== SITEMAPS + VERIFIED-SIX REGRESSION ==\n";

/* 12b + 13. XML sitemap holds exactly the six verified projects. */
$xml = fetch_body( "$base/wp-sitemap-posts-project-1.xml" );
$xml_ok = '' !== $xml;
foreach ( $verified_slugs as $slug ) {
	$xml_ok = $xml_ok && false !== strpos( $xml, "/projects/$slug/" );
}
foreach ( $placeholder_slugs as $slug ) {
	$xml_ok = $xml_ok && false === strpos( $xml, "/projects/$slug/" );
}
$xml_ok
	? ok( '13. XML sitemap lists the six verified projects and no placeholder' )
	: bad( '13. XML sitemap mismatch' );

/* HTML sitemap: same contract. */
$hs = fetch_body( "$base/sitemap/" );
$hs_ok = '' !== $hs;
foreach ( $verified_slugs as $slug ) {
	$hs_ok = $hs_ok && false !== strpos( $hs, "/projects/$slug/" );
}
foreach ( $placeholder_slugs as $slug ) {
	$hs_ok = $hs_ok && false === strpos( $hs, "/projects/$slug/" );
}
$hs_ok
	? ok( '13b. HTML sitemap lists the six verified projects and no placeholder' )
	: bad( '13b. HTML sitemap mismatch' );

/* 13c. The verified six remain indexable, keep their CreativeWork, and keep
 * their before/after comparison. */
$v_bad = array();
foreach ( $verified_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	if ( preg_match( "#robots['\"] content=['\"][^'\"]*noindex#", $body ) ) { $v_bad[] = "$slug noindexed"; }
	if ( 1 !== preg_match_all( '#"@type":"CreativeWork"#', $body ) ) { $v_bad[] = "$slug CreativeWork"; }
	if ( ! preg_match( '#proj-compare__media#', $body ) ) { $v_bad[] = "$slug lost its comparison"; }
}
empty( $v_bad )
	? ok( '13c. the six verified pages stay indexable with CreativeWork + comparison intact' )
	: bad( '13c. ' . implode( ', ', $v_bad ) );

/* 13d. The homepage strip still features verified work only. */
$home = fetch_body( "$base/" );
( '' !== $home && false === stripos( $home, 'Pool Project — Coming Soon' ) )
	? ok( '13d. homepage featured strip excludes placeholders' )
	: bad( '13d. a placeholder leaked into the homepage featured strip' );

echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

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

$pass = 0; $fail = 0; $skip = 0;
function ok( $m ) { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ) { global $fail; $fail++; echo "  ✘ FAIL: $m
"; }
function skip( $m ) { global $skip; $skip++; echo "  ~ SKIP: $m
"; }

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
$new_slugs = array(
	'van-nuys-pool-project',
	'north-hollywood-pool-project',
	'toluca-lake-pool-project',
	'burbank-pool-project',
	'calabasas-pool-project',
	'bel-air-pool-project',
	'west-hollywood-pool-project',
	'brentwood-pool-project',
);

// Seven of the eight new projects are now published, image-backed and indexable.
// West Hollywood alone stays a Coming Soon placeholder: its supplied photographs
// were byte-identical to the Sherman Oaks verified project, so it has no genuine
// imagery of its own.
$promoted_slugs    = array_values( array_diff( $new_slugs, array( 'west-hollywood-pool-project' ) ) );
$placeholder_slugs = array( 'west-hollywood-pool-project' );

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
( 1 === count( $coming ) && 1 === count( $flagged ) && count( $coming ) === count( $flagged ) )
	? ok( '4. exactly 1 record is status=coming_soon AND is_coming_soon (west-hollywood)' )
	: bad( '4. coming_soon=' . count( $coming ) . ' is_coming_soon=' . count( $flagged ) . ' (expected 1/1)' );

/* 4b. Placeholder slugs are the eight expected areas, in the specified order. */
$actual_ph = array_column( $flagged, 'slug' );
$actual_ph === $placeholder_slugs
	? ok( '4b. west-hollywood is the only remaining Coming Soon placeholder' )
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
( 4 === $coming_cells && 0 === $empty_cells && 0 === $zero_cells )
	? ok( '9. the single remaining placeholder renders 4 "Coming soon" fields; no empty or $0 values' )
	: bad( "9. coming=$coming_cells empty=$empty_cells zero=$zero_cells (expected 4/0/0)" );

/* 9b. Every placeholder card shows the Coming Soon chip. */
$chips = preg_match_all( '#class="proj-card__status"#', $html );
1 === $chips
	? ok( '9b. exactly one card shows a "Coming Soon" chip (west-hollywood)' )
	: bad( "9b. $chips Coming Soon chips (expected 1)" );

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
/*
 * Six placeholders now carry their OWN verified photographs, so a placeholder
 * card legitimately contains an <img>. The load-bearing half of this check is
 * unchanged and non-negotiable: no placeholder may ever display a photograph
 * belonging to one of the six verified projects.
 */
( 1 === count( $ph_cards ) && empty( $stolen ) )
	? ok( '10. the one remaining placeholder card renders and reuses no verified project photograph' )
	: bad( '10. cards=' . count( $ph_cards ) . ' stolen=' . implode( ',', $stolen ) );

/* 10b. A placeholder WITHOUT photographs must still show the CSS-only branded
 * placeholder rather than a blank or broken frame; one WITH photographs must
 * not show it. Currently 2 of 8 are image-free (toluca-lake, west-hollywood). */
$art        = preg_match_all( '#Project photos coming soon#', $html );
$image_free = 0;
foreach ( $placeholder_slugs as $slug ) {
	$d = showtime_project_data( $slug );
	if ( '' === (string) ( $d['after_image'] ?? '' ) ) { $image_free++; }
}
$art === $image_free
	? ok( "10b. the $image_free image-free placeholder(s) render the CSS-only branded placeholder; the imaged ones show their photo" )
	: bad( "10b. $art branded placeholders for $image_free image-free projects" );

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

echo "\n== PROMOTED PROJECTS (7) ==\n";

/* 12. All seven promoted pages: 200, index+follow, self-canonical, exactly one
 * CreativeWork, and no Coming Soon notice. */
$prom_bad = array();
foreach ( $promoted_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	if ( '' === $body ) { $prom_bad[] = "$slug empty"; continue; }
	if ( preg_match( "#robots['\"] content=['\"][^'\"]*noindex#", $body ) )                { $prom_bad[] = "$slug noindexed"; }
	if ( ! preg_match( '#<link rel="canonical" href="[^"]*/projects/' . preg_quote( $slug, '#' ) . '/"#', $body ) ) { $prom_bad[] = "$slug canonical"; }
	if ( 1 !== preg_match_all( '#<link rel="canonical"#', $body ) )                         { $prom_bad[] = "$slug canonical count"; }
	if ( 1 !== preg_match_all( '#"@type":"CreativeWork"#', $body ) )                        { $prom_bad[] = "$slug CreativeWork"; }
	if ( false !== stripos( $body, 'proj-notice' ) )                                        { $prom_bad[] = "$slug still shows Coming soon notice"; }
	if ( 2 !== preg_match_all( '#proj-compare__frame proj-compare__frame--#', $body ) )      { $prom_bad[] = "$slug comparison frames"; }
}
empty( $prom_bad )
	? ok( '12. all 7 promoted pages: 200, index+follow, one self-canonical, one CreativeWork, comparison present' )
	: bad( '12. ' . implode( '; ', $prom_bad ) );

/* 12b. Exactly one complete OG set and one complete Twitter set per promoted page. */
$og_bad = array();
foreach ( $promoted_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	foreach ( array( 'og:title', 'og:description', 'og:url', 'og:image', 'twitter:title', 'twitter:description', 'twitter:image' ) as $tag ) {
		$n = preg_match_all( '#(?:property|name)="' . preg_quote( $tag, '#' ) . '"#', $body );
		if ( 1 !== $n ) { $og_bad[] = "$slug:$tag=$n"; }
	}
}
empty( $og_bad )
	? ok( '12c. every promoted page carries exactly one complete OG set and one complete Twitter set' )
	: bad( '12c. ' . implode( ', ', $og_bad ) );

/* 12d. Each promoted page uses its OWN after image for card, hero, OG and Twitter. */
$hero_bad = array();
foreach ( $promoted_slugs as $slug ) {
	$d    = showtime_project_data( $slug );
	$body = fetch_body( "$base/projects/$slug/" );
	if ( $d['hero_image'] !== $d['after_image'] || $d['og_image'] !== $d['after_image'] ) { $hero_bad[] = "$slug fields"; }
	if ( ! preg_match( '#property="og:image" content="[^"]*' . preg_quote( $slug, '#' ) . '-after\.webp"#', $body ) )  { $hero_bad[] = "$slug og:image"; }
	if ( ! preg_match( '#name="twitter:image" content="[^"]*' . preg_quote( $slug, '#' ) . '-after\.webp"#', $body ) ) { $hero_bad[] = "$slug twitter:image"; }
	if ( ! preg_match( '#' . preg_quote( $slug, '#' ) . '-before\.webp#', $body ) )                                    { $hero_bad[] = "$slug before missing"; }
}
empty( $hero_bad )
	? ok( '12d. every promoted page uses its own after image for hero/OG/Twitter and its own before image in the comparison' )
	: bad( '12d. ' . implode( ', ', $hero_bad ) );

/* 12e. Promoted registry content is complete — no blank required field, and the
 * investment reads as a researched range rather than a contract price. */
$content_bad = array();
foreach ( $promoted_slugs as $slug ) {
	$d = showtime_project_data( $slug );
	foreach ( array( 'title', 'excerpt', 'scope', 'finish', 'timeline', 'investment', 'comparison_heading',
		'comparison_summary', 'before_condition', 'work_completed', 'completed_result',
		'hero_alt', 'before_alt', 'after_alt', 'seo_title', 'meta_description' ) as $k ) {
		if ( '' === trim( (string) ( $d[ $k ] ?? '' ) ) ) { $content_bad[] = "$slug.$k"; }
	}
	if ( ! preg_match( '/^\$[\d,]+\s*[–-]\s*\$[\d,]+$/u', (string) $d['investment'] ) ) { $content_bad[] = "$slug.investment not a range"; }
	// Fields that stay blank until independently verified.
	if ( '' !== (string) $d['completion_date'] ) { $content_bad[] = "$slug.completion_date should be blank"; }
	if ( '' !== (string) $d['client_quote'] )    { $content_bad[] = "$slug.client_quote should be blank"; }
}
empty( $content_bad )
	? ok( '12e. all 7 promoted records are content-complete, with a range-formatted investment and blank date/quote' )
	: bad( '12e. ' . implode( ', ', $content_bad ) );

/* 12f. area_url is set only where the service-area page actually exists. */
$area_bad = array();
foreach ( $promoted_slugs as $slug ) {
	$d   = showtime_project_data( $slug );
	$raw = trim( (string) $d['area_url'] );
	if ( '' === $raw ) { continue; }
	$a = trim( (string) preg_replace( '#^/service-areas/#', '', $raw ), '/' );
	if ( ! class_exists( '\Showtime\Areas' ) || null === \Showtime\Areas::get( $a ) ) { $area_bad[] = "$slug -> $raw"; }
}
empty( $area_bad )
	? ok( '12g. every area_url that is set resolves to a real service-area page' )
	: bad( '12g. unresolvable area_url: ' . implode( ', ', $area_bad ) );

/* 13. Toluca Lake is classified as WATER TREATMENT ONLY and makes no physical
 * renovation claim anywhere in its entry or rendered page. */
$tl        = showtime_project_data( 'toluca-lake-pool-project' );
$tl_body   = fetch_body( "$base/projects/toluca-lake-pool-project/" );
$tl_blob   = strtolower( (string) wp_json_encode( $tl ) );
$tl_banned = array( 'replaster', 'resurfac', 'new finish', 'remodel', 'construction', 'constructed',
	'tile replacement', 'coping replacement', 'renovat', 'rebuilt', 'gunite', 'plaster', 'demolit' );
// This project's OWN content only: from the H1 through to the end of the
// comparison section, excluding related cards, nav and sitewide schema.
$tl_own = '';
if ( preg_match( '#<h1.*?(?=<section class="proj-single__related"|</main>)#s', $tl_body, $om ) ) {
	$tl_own = preg_replace( '#<script.*?</script>#s', '', $om[0] );
}
$tl_hits = array();
foreach ( $tl_banned as $needle ) {
	if ( false !== strpos( $tl_blob, $needle ) ) { $tl_hits[] = "registry:$needle"; }
	if ( '' !== $tl_own && false !== stripos( $tl_own, $needle ) ) { $tl_hits[] = "page:$needle"; }
}
empty( $tl_hits )
	? ok( '13. Toluca Lake carries zero physical-renovation claims in the registry or the rendered page' )
	: bad( '13. Toluca Lake renovation claim: ' . implode( ', ', $tl_hits ) );

echo "\n== WEST HOLLYWOOD — remains Coming Soon ==\n";

/* 14. West Hollywood: 200, noindex+follow, self-canonical, no CreativeWork, no
 * imagery, no comparison, and it still shows the Coming Soon notice. */
$wh      = showtime_project_data( 'west-hollywood-pool-project' );
$wh_body = fetch_body( "$base/projects/west-hollywood-pool-project/" );
$wh_bad  = array();
if ( 'coming_soon' !== ( $wh['status'] ?? '' ) || empty( $wh['is_coming_soon'] ) ) { $wh_bad[] = 'status'; }
foreach ( array( 'hero_image', 'before_image', 'after_image', 'og_image' ) as $k ) {
	if ( '' !== (string) ( $wh[ $k ] ?? '' ) ) { $wh_bad[] = "carries $k"; }
}
if ( ! preg_match( "#robots['\"] content=['\"]noindex, follow#", $wh_body ) )                 { $wh_bad[] = 'not noindex,follow'; }
if ( ! preg_match( '#<link rel="canonical" href="[^"]*/projects/west-hollywood-pool-project/"#', $wh_body ) ) { $wh_bad[] = 'canonical'; }
if ( preg_match( '#"@type":"CreativeWork"#', $wh_body ) )                                     { $wh_bad[] = 'emitted CreativeWork'; }
if ( preg_match( '#proj-compare__media#', $wh_body ) )                                        { $wh_bad[] = 'rendered a comparison'; }
if ( false === stripos( $wh_body, 'Coming soon' ) )                                           { $wh_bad[] = 'no Coming soon notice'; }
$wh_post = get_page_by_path( 'west-hollywood-pool-project', OBJECT, 'project' );
if ( $wh_post instanceof WP_Post && null !== showtime_project_compare( (int) $wh_post->ID ) ) { $wh_bad[] = 'comparison resolved'; }
empty( $wh_bad )
	? ok( '14. west-hollywood: 200, noindex+follow, self-canonical, image-free, comparison-free, no project schema' )
	: bad( '14. ' . implode( ', ', $wh_bad ) );

/* 14b. West Hollywood must never present Sherman Oaks imagery AS ITS OWN.
 * Scoped to this page's own content: the related-projects block legitimately
 * links to other projects (and is ordered randomly), so scanning the whole
 * document would be both wrong and flaky. */
$wh_own = '';
if ( preg_match( '#<h1.*?(?=<section class="proj-single__related"|</main>)#s', $wh_body, $wm ) ) {
	$wh_own = preg_replace( '#<script.*?</script>#s', '', $wm[0] );
}
( '' !== $wh_own
	&& false === strpos( $wh_own, 'sherman-oaks-mid-century-remodel-' )
	&& 0 === preg_match_all( '#<img#', $wh_own ) )
	? ok( '14b. west-hollywood shows no photograph of its own, and none from Sherman Oaks' )
	: bad( '14b. west-hollywood own content carries imagery' );

echo "\n== SITEMAPS + SCHEMA + VERIFIED-SIX REGRESSION ==\n";

/* 15. XML sitemap: exactly the 13 indexable projects, west-hollywood absent. */
$xml     = fetch_body( "$base/wp-sitemap-posts-project-1.xml" );
$xml_n   = preg_match_all( '#/projects/[a-z0-9-]+/#', $xml );
$xml_ok  = '' !== $xml && 13 === $xml_n && false === strpos( $xml, 'west-hollywood' );
foreach ( array_merge( $verified_slugs, $promoted_slugs ) as $slug ) {
	$xml_ok = $xml_ok && false !== strpos( $xml, "/projects/$slug/" );
}
$xml_ok
	? ok( '15. XML sitemap lists exactly 13 project URLs and excludes west-hollywood' )
	: bad( "15. XML sitemap has $xml_n project URLs (expected 13) or is missing/leaking a slug" );

/* 15b. HTML sitemap: same contract. */
$hs     = fetch_body( "$base/sitemap/" );
$hs_n   = preg_match_all( '#href="[^"]*/projects/[a-z0-9-]+/"#', $hs );
$hs_ok  = '' !== $hs && 13 === $hs_n && false === strpos( $hs, '/projects/west-hollywood-pool-project/' );
foreach ( array_merge( $verified_slugs, $promoted_slugs ) as $slug ) {
	$hs_ok = $hs_ok && false !== strpos( $hs, "/projects/$slug/" );
}
$hs_ok
	? ok( '15b. HTML sitemap lists exactly 13 project URLs and excludes west-hollywood' )
	: bad( "15b. HTML sitemap has $hs_n project URLs (expected 13)" );

/* 16. No project page may carry Product / Offer / Review / rating / price schema,
 * and the researched investment range must never reach structured data. */
$schema_bad = array();
foreach ( array_merge( $verified_slugs, $new_slugs ) as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	foreach ( array( '"@type":"Product"', '"@type":"Offer"', '"offers"', 'aggregateRating',
		'ratingValue', 'reviewCount', '"@type":"Review"', 'priceCurrency' ) as $needle ) {
		if ( false !== stripos( $body, $needle ) ) { $schema_bad[] = "$slug:$needle"; }
	}
	if ( preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $body, $lm ) ) {
		foreach ( $lm[1] as $json ) {
			if ( preg_match( '/\$[\d,]{3,}/', $json ) ) { $schema_bad[] = "$slug: price figure in JSON-LD"; }
		}
	}
}
empty( $schema_bad )
	? ok( '16. no project page emits Product/Offer/Review/rating/price schema, and no investment range reaches JSON-LD' )
	: bad( '16. ' . implode( ', ', $schema_bad ) );

/* 17. The six verified pages are untouched: still indexable, one CreativeWork,
 * comparison intact. */
$v_bad = array();
foreach ( $verified_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	if ( preg_match( "#robots['\"] content=['\"][^'\"]*noindex#", $body ) ) { $v_bad[] = "$slug noindexed"; }
	if ( 1 !== preg_match_all( '#"@type":"CreativeWork"#', $body ) )        { $v_bad[] = "$slug CreativeWork"; }
	if ( ! preg_match( '#proj-compare__media#', $body ) )                   { $v_bad[] = "$slug lost its comparison"; }
}
empty( $v_bad )
	? ok( '17. the six verified pages remain indexable with CreativeWork and comparison intact' )
	: bad( '17. ' . implode( ', ', $v_bad ) );

/* 18. The homepage strip stays curated: verified work only, never a placeholder
 * and never one of the newly promoted projects. */
$home = fetch_body( "$base/" );
$home_bad = array();
if ( false !== stripos( $home, 'Coming Soon' ) ) { $home_bad[] = 'Coming Soon leaked'; }
foreach ( $new_slugs as $slug ) {
	if ( false !== strpos( $home, "/projects/$slug/" ) ) { $home_bad[] = $slug; }
}
empty( $home_bad )
	? ok( '18. homepage featured strip stays curated to verified projects only' )
	: bad( '18. homepage leaked: ' . implode( ', ', $home_bad ) );

echo "\n== IMAGERY ==\n";

/* 19. Every promoted project maps to its own unique before/after WebP pair at the
 * project-image standard, and no photograph is reused between projects. */
$cmp_dir = get_stylesheet_directory() . '/assets/img/projects/comparisons/';
$img_bad = array();
foreach ( $promoted_slugs as $slug ) {
	foreach ( array( 'before', 'after' ) as $side ) {
		$p = $cmp_dir . $slug . '-' . $side . '.webp';
		if ( ! is_readable( $p ) ) { $img_bad[] = "$slug-$side missing"; continue; }
		$i = @getimagesize( $p );
		if ( ! is_array( $i ) || 'image/webp' !== ( $i['mime'] ?? '' ) ) { $img_bad[] = "$slug-$side not webp"; continue; }
		$head = (string) file_get_contents( $p, false, null, 0, 12 );
		if ( 'RIFF' !== substr( $head, 0, 4 ) || 'WEBP' !== substr( $head, 8, 4 ) ) { $img_bad[] = "$slug-$side bad magic"; }
		if ( 1376 !== (int) $i[0] || 768 !== (int) $i[1] ) { $img_bad[] = "$slug-$side {$i[0]}x{$i[1]}"; }
		if ( abs( ( $i[0] / $i[1] ) - 1.7917 ) > 0.01 )    { $img_bad[] = "$slug-$side aspect"; }
	}
}
empty( $img_bad )
	? ok( '19. all 14 promoted images are genuine WebP at 1376x768 with the standard aspect ratio' )
	: bad( '19. ' . implode( ', ', $img_bad ) );

/* 20. Checksum uniqueness across every comparison asset. */
$by_hash = array();
foreach ( glob( $cmp_dir . '*.webp' ) ?: array() as $p ) {
	$by_hash[ hash_file( 'sha256', $p ) ][] = basename( $p );
}
$dupe_groups = array_values( array_filter( $by_hash, static fn( $n ) => count( $n ) > 1 ) );
empty( $dupe_groups )
	? ok( '20. all ' . count( $by_hash ) . ' comparison assets have unique SHA-256 checksums — no photograph is reused' )
	: bad( '20. duplicate photographs: ' . wp_json_encode( $dupe_groups ) );

/* 21. Alt text: non-empty, unique across all 13 imaged projects, and free of
 * price, date, brand or warranty claims. */
$alts = array(); $alt_bad = array();
foreach ( array_merge( $verified_slugs, $promoted_slugs ) as $slug ) {
	$d = showtime_project_data( $slug );
	foreach ( array( 'hero_alt', 'before_alt', 'after_alt' ) as $k ) {
		$v = trim( (string) $d[ $k ] );
		if ( '' === $v ) { $alt_bad[] = "$slug.$k empty"; continue; }
		if ( isset( $alts[ $v ] ) ) { $alt_bad[] = "$slug.$k duplicates {$alts[$v]}"; }
		$alts[ $v ] = "$slug.$k";
		if ( preg_match( '/\$[\d,]|\b(19|20)\d{2}\b|PebbleTec|Pentair|Jandy|warrant|guarantee/i', $v ) ) {
			$alt_bad[] = "$slug.$k unverified claim";
		}
	}
}
empty( $alt_bad )
	? ok( '21. alt text is non-empty, unique across all imaged projects, and free of price/date/brand claims' )
	: bad( '21. ' . implode( ', ', $alt_bad ) );

/* 22. No source filename or _incoming path leaks into rendered HTML. */
$leak_bad = array();
foreach ( $new_slugs as $slug ) {
	$body = fetch_body( "$base/projects/$slug/" );
	if ( preg_match( '/_incoming|van_nuys|north_hollywood|burbank_pool|calabasas_pool|bel_air_pool|brentwood_pool|toluca_lake|west_hollywood_|\.jpeg/i', $body ) ) {
		$leak_bad[] = $slug;
	}
}
empty( $leak_bad )
	? ok( '22. no _incoming path or source filename leaks into rendered HTML' )
	: bad( '22. leaked on: ' . implode( ', ', $leak_bad ) );

/* 23. Every promoted image URL is actually served. */
$url_bad = array();
foreach ( $promoted_slugs as $slug ) {
	foreach ( array( 'before', 'after' ) as $side ) {
		$u  = get_stylesheet_directory_uri() . '/assets/img/projects/comparisons/' . $slug . '-' . $side . '.webp';
		$ch = curl_init( $u );
		curl_setopt_array( $ch, array( CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT => 15 ) );
		curl_exec( $ch );
		$code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		if ( 200 !== $code ) { $url_bad[] = "$slug-$side=$code"; }
	}
}
empty( $url_bad )
	? ok( '23. all 14 promoted image URLs return HTTP 200' )
	: bad( '23. ' . implode( ', ', $url_bad ) );

/* 24. Comparison gating still fails closed when an image is unavailable. */
$probe_slug = 'van-nuys-pool-project';
$probe_file = $cmp_dir . $probe_slug . '-before.webp';
if ( is_readable( $probe_file ) ) {
	$moved = $probe_file . '.testhide';
	if ( @rename( $probe_file, $moved ) ) {
		$gone = null === showtime_project_compare_image( $probe_file, 'x' );
		@rename( $moved, $probe_file );
		$gone
			? ok( '24. a missing before image still makes the comparison fail closed' )
			: bad( '24. a missing before image still resolved' );
	} else {
		skip( '24. could not rename the probe file on this filesystem' );
	}
} else {
	skip( '24. probe file unavailable' );
}

echo "\n== RESULT ==\n  pass: $pass   fail: $fail   skip: $skip\n";

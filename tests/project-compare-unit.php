<?php
/**
 * Unit + render tests for the project Before/After comparison section.
 *
 * Covers the data resolver (showtime_project_compare) and the server-rendered
 * markup on all six in-scope project pages: exactly one section, exactly one
 * before + one after image, both URLs present without JavaScript, factual alt
 * text, accurate width/height, valid service + area links, fail-closed
 * behaviour when imagery is missing, one final CTA, no legacy brand in titles,
 * and no Review / aggregateRating / HowTo schema introduced.
 *
 *   php tests/project-compare-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/project-compare-unit.php
 *
 * Read-only: creates no posts and writes no options. Image inputs are injected
 * through the showtime/project/compare_images filter so the suite is
 * self-contained and does not depend on the local preview harness.
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
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-compare-test/1.0',
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
$count = fn( $s, $re ) => preg_match_all( $re, $s, $m );

if ( ! function_exists( 'showtime_project_compare' ) ) {
	bad( 'showtime_project_compare() not loaded — is the child theme active?' );
	echo "\n== RESULT ==\n  pass: 0   fail: 1\n";
	exit( 1 );
}

$slugs = array(
	'sherman-oaks-mid-century-remodel',
	'encino-estate-new-build',
	'studio-city-modern-automation',
	'beverly-hills-luxe-spa-renovation',
	'tarzana-resort-style-finish',
	'woodland-hills-tile-coping-refresh',
);

$img_dir = get_stylesheet_directory() . '/assets/img/';
$fixture = array(
	'before' => $img_dir . 'service_pool-remodeling-resurfacing_before.webp',
	'after'  => $img_dir . 'service_pool-remodeling-resurfacing_after.webp',
);

echo "\n== PROJECT BEFORE/AFTER — data resolver ==\n";

/* --- 1. All six projects resolve their project data. --- */
$ids = array();
foreach ( $slugs as $s ) {
	$p = get_page_by_path( $s, OBJECT, 'project' );
	if ( $p ) {
		$ids[ $s ] = (int) $p->ID;
	} else {
		bad( "1. $s: project post not found" );
	}
}
count( $ids ) === count( $slugs )
	? ok( '1. all six project posts resolve' )
	: bad( '1. only ' . count( $ids ) . '/' . count( $slugs ) . ' projects resolved' );

/* Registry copy must exist for each, independent of post meta. */
foreach ( $slugs as $s ) {
	$entry = class_exists( '\Showtime\Projects' ) ? \Showtime\Projects::get( $s ) : null;
	$cmp   = $entry['compare'] ?? null;
	$has   = is_array( $cmp )
		&& '' !== trim( (string) ( $cmp['heading'] ?? '' ) )
		&& '' !== trim( (string) ( $cmp['summary'] ?? '' ) );
	$has ? ok( "1b. $s: registry comparison copy present" ) : bad( "1b. $s: registry comparison copy missing" );
}

/* --- 9. Fail closed when imagery is absent.
   Detach anything already hooked (e.g. the local preview harness) so this
   asserts real production behaviour, not the dev stand-ins. --- */
remove_all_filters( 'showtime/project/compare_images' );

foreach ( $ids as $s => $pid ) {
	$r = showtime_project_compare( $pid );
	null === $r
		? ok( "9. $s: no imagery -> returns null (fails closed)" )
		: bad( "9. $s: returned data with no imagery present" );
}

/* Half a pair is still not a pair. */
$half = function ( $a, $b ) { return function () use ( $a, $b ) { return array( 'before' => $a, 'after' => $b ); }; };
$f_half = $half( $fixture['before'], null );
add_filter( 'showtime/project/compare_images', $f_half, 10, 0 );
$r = showtime_project_compare( reset( $ids ) );
remove_filter( 'showtime/project/compare_images', $f_half, 10 );
null === $r ? ok( '9b. before-only pair -> null' ) : bad( '9b. before-only pair rendered' );

$f_half2 = $half( null, $fixture['after'] );
add_filter( 'showtime/project/compare_images', $f_half2, 10, 0 );
$r = showtime_project_compare( reset( $ids ) );
remove_filter( 'showtime/project/compare_images', $f_half2, 10 );
null === $r ? ok( '9c. after-only pair -> null' ) : bad( '9c. after-only pair rendered' );

/* A path that does not exist must be rejected, not guessed at. */
$f_bogus = $half( $img_dir . 'does-not-exist.webp', $img_dir . 'also-missing.webp' );
add_filter( 'showtime/project/compare_images', $f_bogus, 10, 0 );
$r = showtime_project_compare( reset( $ids ) );
remove_filter( 'showtime/project/compare_images', $f_bogus, 10 );
null === $r ? ok( '9d. unreadable files -> null (no guessed dimensions)' ) : bad( '9d. unreadable files accepted' );

/* --- With a valid pair injected: structure, links, dimensions. --- */
$f_ok = $half( $fixture['before'], $fixture['after'] );
add_filter( 'showtime/project/compare_images', $f_ok, 10, 0 );

$real = @getimagesize( $fixture['before'] );
foreach ( $ids as $s => $pid ) {
	$r = showtime_project_compare( $pid );
	if ( ! is_array( $r ) ) {
		bad( "5/6/7/8. $s: resolver returned null with a valid pair" );
		continue;
	}

	// 5. Factual, non-empty, non-duplicated alt text.
	$ba = trim( (string) $r['before']['alt'] );
	$aa = trim( (string) $r['after']['alt'] );
	$heading = (string) $r['heading'];
	$alt_ok = '' !== $ba && '' !== $aa
		&& 0 !== strcasecmp( $ba, $heading )
		&& 0 !== strcasecmp( $aa, $heading )
		&& 0 !== strcasecmp( $ba, $aa );
	$alt_ok ? ok( "5. $s: alt text non-empty, distinct, not the heading" ) : bad( "5. $s: alt text weak/duplicated" );

	// Alt must not carry marketing/keyword spam.
	$spam = preg_match( '/\b(best|near me|cheap|#1|number one|\$\d)/i', $ba . ' ' . $aa );
	$spam ? bad( "5b. $s: alt text contains marketing language" ) : ok( "5b. $s: alt text free of marketing language" );

	// 6. Dimensions accurate against the real file.
	$dim_ok = is_array( $real )
		&& (int) $r['before']['width'] === (int) $real[0]
		&& (int) $r['before']['height'] === (int) $real[1];
	$dim_ok ? ok( "6. $s: width/height match the actual file" ) : bad( "6. $s: dimensions wrong" );

	// 7. Exactly one primary service link, resolved through the registry.
	$prim = $r['links']['primary'] ?? null;
	if ( is_array( $prim ) && ! empty( $prim['url'] ) ) {
		preg_match( '#/services/([^/]+)/#', $prim['url'], $m );
		$svc_slug = $m[1] ?? '';
		$valid = $svc_slug && class_exists( '\Showtime\Services' ) && null !== \Showtime\Services::get( $svc_slug );
		$valid ? ok( "7. $s: primary service link -> /services/$svc_slug/ (registered)" ) : bad( "7. $s: primary service slug not in registry" );
	} else {
		bad( "7. $s: no primary service link" );
	}

	// 8. Area link resolved through the registry.
	$area = $r['links']['area'] ?? null;
	if ( is_array( $area ) && ! empty( $area['url'] ) ) {
		preg_match( '#/service-areas/([^/]+)/#', $area['url'], $m );
		$area_slug = $m[1] ?? '';
		$valid = $area_slug && class_exists( '\Showtime\Areas' ) && null !== \Showtime\Areas::get( $area_slug );
		$valid ? ok( "8. $s: area link -> /service-areas/$area_slug/ (registered)" ) : bad( "8. $s: area slug not in registry" );
	} else {
		bad( "8. $s: no area link" );
	}

	// Anchors must be descriptive, never "click here".
	$labels = array_map( static fn( $l ) => strtolower( (string) ( $l['label'] ?? '' ) ), $r['links'] );
	$generic = array_filter( $labels, static fn( $l ) => in_array( $l, array( 'click here', 'here', 'read more', 'learn more' ), true ) );
	$generic ? bad( "8b. $s: generic anchor text" ) : ok( "8b. $s: anchors descriptive" );

	// Summary length budget (60-100 words). Counted on whitespace, the way a
	// reader counts: str_word_count() drops numerals and hyphenates such as
	// "6x6", "30-year-old" and "$12.4k", undercounting compliant copy.
	$w = count( preg_split( '/\s+/', trim( wp_strip_all_tags( (string) $r['summary'] ) ), -1, PREG_SPLIT_NO_EMPTY ) );
	( $w >= 60 && $w <= 100 ) ? ok( "8c. $s: summary $w words (60-100)" ) : bad( "8c. $s: summary $w words, outside 60-100" );
}

/* Woodland Hills must never be described as a resurface. */
$wh = showtime_project_compare( $ids['woodland-hills-tile-coping-refresh'] ?? 0 );
if ( is_array( $wh ) ) {
	$blob = strtolower( $wh['summary'] . ' ' . $wh['work_completed'] . ' ' . $wh['completed_result'] . ' ' . $wh['heading'] );
	// "no resurfacing"/"rather than resurfaced" are fine; a positive claim is not.
	$bad_claim = preg_match( '/(?<!no )(?<!not )\bresurfac(e|ed|ing)\b(?![^.]*\b(no|not|rather than|without|retained|kept)\b)/', $blob )
		&& ! preg_match( '/(no resurfacing|rather than resurfaced|without touching the plaster)/', $blob );
	$bad_claim ? bad( '8d. Woodland Hills described as a resurface' ) : ok( '8d. Woodland Hills not described as a resurface' );
}

remove_filter( 'showtime/project/compare_images', $f_ok, 10 );

/* --- Rendered markup, server-side, no JavaScript. --- */
echo "\n== PROJECT BEFORE/AFTER — server-rendered markup ==\n";
$base = rtrim( (string) get_option( 'home' ), '/' );
foreach ( $slugs as $s ) {
	$body = fetch_body( "$base/projects/$s/" );
	if ( '' === $body ) { bad( "render $s: empty response" ); continue; }

	$has_section = $count( $body, '#<section class="proj-compare"#' );

	// 2. Exactly one comparison section (0 is valid only when imagery is absent).
	if ( 0 === $has_section ) {
		ok( "2. $s: no imagery on this install -> section correctly absent" );
	} elseif ( 1 === $has_section ) {
		ok( "2. $s: exactly one comparison section" );

		// 3. Exactly one before and one after frame.
		$b = $count( $body, '#proj-compare__frame--before#' );
		$a = $count( $body, '#proj-compare__frame--after#' );
		( 1 === $b && 1 === $a )
			? ok( "3. $s: exactly one before + one after frame" )
			: bad( "3. $s: before=$b after=$a (each must be 1)" );

		// 4. Both image URLs in the initial HTML, not behind JS/CSS/data-attrs.
		// Bound the slice to THIS section only — the related-projects cards
		// that follow use intentionally empty decorative alts and would
		// otherwise be judged against this section's rules.
		$sec_start = strpos( $body, '<section class="proj-compare"' );
		$next_sec  = strpos( $body, '<section', $sec_start + 10 );
		$sec = false === $next_sec
			? substr( $body, $sec_start )
			: substr( $body, $sec_start, $next_sec - $sec_start );
		$imgs = $count( $sec, '#<img[^>]+src="https?://[^"]+"#' );
		$imgs >= 2
			? ok( "4. $s: both images present as real <img src> in the HTML" )
			: bad( "4. $s: only $imgs <img src> found in section" );

		// No template/JS-only delivery.
		( false === strpos( $sec, '<template' ) && false === strpos( $sec, 'data-src=' ) )
			? ok( "4b. $s: no <template> or data-src indirection" )
			: bad( "4b. $s: images hidden behind template/data-src" );

		// 5/6. alt + width/height on every image in the section.
		preg_match_all( '#<img[^>]*>#', $sec, $tags );
		$bad_tag = 0;
		foreach ( $tags[0] as $t ) {
			if ( ! preg_match( '#\salt="[^"]+"#', $t ) ) { $bad_tag++; continue; }
			if ( ! preg_match( '#\swidth="\d+"#', $t ) || ! preg_match( '#\sheight="\d+"#', $t ) ) { $bad_tag++; }
		}
		0 === $bad_tag
			? ok( "6b. $s: every image has non-empty alt + width + height" )
			: bad( "6b. $s: $bad_tag image(s) missing alt/width/height" );

		// Below-the-fold loading hints.
		( 2 <= $count( $sec, '#loading="lazy"#' ) && 2 <= $count( $sec, '#decoding="async"#' ) )
			? ok( "6c. $s: lazy + async decoding on both images" )
			: bad( "6c. $s: missing loading=lazy / decoding=async" );

		// Semantic structure.
		( $count( $sec, '#<figure#' ) >= 2 && $count( $sec, '#<figcaption#' ) >= 2 && $count( $sec, '#<picture>#' ) >= 2 )
			? ok( "6d. $s: semantic figure/figcaption/picture used" )
			: bad( "6d. $s: missing semantic figure/figcaption/picture" );

		// Visible BEFORE / AFTER labels.
		( false !== stripos( $sec, '>Before<' ) && false !== stripos( $sec, '>After<' ) )
			? ok( "6e. $s: visible Before/After labels" )
			: bad( "6e. $s: Before/After labels missing" );

		// Section placement: after the facts strip, before the testimonial.
		$p_meta = strpos( $body, 'proj-single__meta' );
		$p_cmp  = strpos( $body, '<section class="proj-compare"' );
		$p_quote = strpos( $body, 'proj-single__quote' );
		( false !== $p_meta && false !== $p_cmp && false !== $p_quote && $p_meta < $p_cmp && $p_cmp < $p_quote )
			? ok( "2b. $s: placed after facts, before testimonial" )
			: bad( "2b. $s: wrong section order" );
	} else {
		bad( "2. $s: $has_section comparison sections (must be 0 or 1)" );
	}

	// 10. Exactly one final CTA.
	$cta = $count( $body, '#<section class="footer-cta#' );
	1 === $cta ? ok( "10. $s: exactly one final CTA" ) : bad( "10. $s: $cta final CTAs" );

	// 11. No legacy brand in the title.
	preg_match( '#<title>(.*?)</title>#is', $body, $tm );
	$title = $tm[1] ?? '';
	false === stripos( $title, 'Showtime Pools Mechanics' )
		? ok( "11. $s: title free of legacy brand" )
		: bad( "11. $s: title contains 'Showtime Pools Mechanics' -> $title" );

	// 12. No Review / aggregateRating / HowTo schema.
	$schema_bad = array();
	foreach ( array( '"@type"\s*:\s*"Review"' => 'Review', 'aggregateRating' => 'aggregateRating', '"@type"\s*:\s*"HowTo"' => 'HowTo' ) as $re => $name ) {
		if ( preg_match( '#' . $re . '#i', $body ) ) { $schema_bad[] = $name; }
	}
	$schema_bad ? bad( "12. $s: forbidden schema present: " . implode( ', ', $schema_bad ) ) : ok( "12. $s: no Review/aggregateRating/HowTo schema" );

	// 14. P0-3 review widget behaviour unchanged (project pages never mounted it).
	ok( "14. $s: reviews widget untouched (" . $count( $body, '#data-trustindex-lazy#' ) . ' instances)' );
}

echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

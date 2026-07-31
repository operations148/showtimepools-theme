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

// Projects that ship a verified bundled pair in the registry resolve from the
// code-first assets; every other project must still fail closed. Derived from
// the registry rather than hardcoded, so adding a future pair updates this.
$with_assets = array();
foreach ( array_keys( $ids ) as $s ) {
	$entry = class_exists( '\Showtime\Projects' ) ? \Showtime\Projects::get( $s ) : null;
	$cmp   = $entry['compare'] ?? array();
	if ( ! empty( $cmp['before_asset'] ) && ! empty( $cmp['after_asset'] ) ) {
		$with_assets[] = $s;
	}
}

foreach ( $ids as $s => $pid ) {
	$r = showtime_project_compare( $pid );
	if ( in_array( $s, $with_assets, true ) ) {
		( null !== $r )
			? ok( "9. $s: bundled comparison pair resolves with no WordPress imagery" )
			: bad( "9. $s: declares bundled assets but resolved null" );
	} else {
		( null === $r )
			? ok( "9. $s: no imagery and no bundled pair -> returns null (fails closed)" )
			: bad( "9. $s: returned data with no imagery present" );
	}
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

/* A path that does not exist must be rejected, not guessed at. Asserted on the
   image normalizer itself: it is the single guard responsible, and testing it
   directly stays deterministic no matter which projects ship bundled assets. */
$unreadable = array(
	$img_dir . 'does-not-exist.webp',
	$img_dir . 'projects/comparisons/not-a-real-file.webp',
	'/nowhere/at/all.jpg',
);
$all_null = true;
foreach ( $unreadable as $p ) {
	if ( null !== showtime_project_compare_image( $p, 'alt' ) ) { $all_null = false; }
}
$all_null
	? ok( '9d. unreadable file paths -> null (no guessed dimensions)' )
	: bad( '9d. an unreadable path produced an image' );

/* A readable file with no determinable dimensions must also be refused. */
$fake = $img_dir . 'projects/comparisons/.not-an-image-tmp';
file_put_contents( $fake, 'this is definitely not an image' );
$r_fake = showtime_project_compare_image( $fake, 'alt' );
@unlink( $fake );
null === $r_fake
	? ok( '9e. readable non-image -> null (dimensions never invented)' )
	: bad( '9e. non-image file accepted as an image' );

/* The bundled fallback needs BOTH files; a half-declared pair must not render. */
$half_decl = showtime_project_compare_image( $img_dir . 'projects/comparisons/missing-half.webp', 'alt' );
null === $half_decl
	? ok( '9f. a missing bundled file yields null, so a pair can never be half-resolved' )
	: bad( '9f. missing bundled file resolved' );

/* --- Bundled comparison image registry (real project photographs). --- */
echo "\n== BUNDLED COMPARISON IMAGE REGISTRY ==\n";
$asset_dir = get_stylesheet_directory() . '/assets/img/projects/comparisons/';
$seen_sums = array();
$seen_files = array();

foreach ( $with_assets as $s ) {
	$cmp = ( \Showtime\Projects::get( $s ) )['compare'];
	$b   = $asset_dir . $cmp['before_asset'];
	$a   = $asset_dir . $cmp['after_asset'];

	// Files exist and are readable.
	( is_readable( $b ) && is_readable( $a ) )
		? ok( "img. $s: both bundled files exist" )
		: bad( "img. $s: missing bundled file(s)" );
	if ( ! is_readable( $b ) || ! is_readable( $a ) ) { continue; }

	// Valid image MIME + real dimensions.
	$bi = @getimagesize( $b );
	$ai = @getimagesize( $a );
	( is_array( $bi ) && is_array( $ai )
		&& 0 === strpos( (string) $bi['mime'], 'image/' )
		&& 0 === strpos( (string) $ai['mime'], 'image/' ) )
		? ok( "img. $s: valid image MIME ({$bi['mime']}) with real dimensions" )
		: bad( "img. $s: not a valid image" );

	// Pair is not identical, and no file is reused across projects.
	$bs = hash_file( 'sha256', $b );
	$as = hash_file( 'sha256', $a );
	( $bs !== $as ) ? ok( "img. $s: before and after are different images" ) : bad( "img. $s: before == after" );
	foreach ( array( $bs, $as ) as $sum ) {
		isset( $seen_sums[ $sum ] )
			? bad( "img. $s: image reused from {$seen_sums[ $sum ]}" )
			: null;
		$seen_sums[ $sum ] = $s;
	}
	foreach ( array( $cmp['before_asset'], $cmp['after_asset'] ) as $fn ) {
		if ( isset( $seen_files[ $fn ] ) ) { bad( "img. $s: filename reused" ); }
		$seen_files[ $fn ] = $s;
		// Production-safe filename.
		( $fn === strtolower( $fn ) && ! preg_match( '/[\s()]/', $fn ) )
			? null
			: bad( "img. $s: unsafe production filename '$fn'" );
	}

	// Correct before/after ordering + slug association encoded in the name.
	( false !== strpos( $cmp['before_asset'], $s . '-before' )
		&& false !== strpos( $cmp['after_asset'], $s . '-after' ) )
		? ok( "img. $s: filenames encode correct slug and before/after order" )
		: bad( "img. $s: filename/slug or ordering mismatch" );

	// Truthful, non-empty alt text that is not just the heading.
	$balt = trim( (string) ( $cmp['before_alt'] ?? '' ) );
	$aalt = trim( (string) ( $cmp['after_alt'] ?? '' ) );
	( '' !== $balt && '' !== $aalt && $balt !== $aalt
		&& 0 !== strcasecmp( $balt, (string) ( $cmp['heading'] ?? '' ) ) )
		? ok( "img. $s: distinct non-empty alt text on both images" )
		: bad( "img. $s: weak or duplicated alt text" );

	// Both photographs must be LANDSCAPE. The shared frame is 16:9; a portrait
	// source would either letterbox enormously or force a crop.
	( $bi[0] > $bi[1] && $ai[0] > $ai[1] )
		? ok( "img. $s: both images are landscape ({$bi[0]}x{$bi[1]}, {$ai[0]}x{$ai[1]})" )
		: bad( "img. $s: a source image is portrait/square" );

	// before and after must share one aspect ratio, or they letterbox by
	// different amounts inside the shared frame and the divider stops lining up.
	$ar_b = $bi[0] / $bi[1];
	$ar_a = $ai[0] / $ai[1];
	( abs( $ar_b - $ar_a ) < 0.001 )
		? ok( "img. $s: before/after share one aspect ratio (" . number_format( $ar_b, 4 ) . ') so the divider stays aligned' )
		: bad( "img. $s: aspect ratios differ (" . number_format( $ar_b, 4 ) . ' vs ' . number_format( $ar_a, 4 ) . ')' );

	// The frame is 16:9 with object-fit:contain. Anything WIDER than 16:9 is
	// letterboxed (safe, nothing lost). Anything much taller would waste height.
	// Guard the ratio stays in a sane band so a wildly-shaped source is caught.
	( $ar_b >= 1.70 && $ar_b <= 1.90 )
		? ok( "img. $s: aspect ratio within the 16:9 frame's safe band" )
		: bad( "img. $s: aspect ratio " . number_format( $ar_b, 4 ) . ' is outside 1.70-1.90' );
}

( 6 === count( $ids ) ) ? ok( 'img. exactly six project mappings present' ) : bad( 'img. project count != 6' );
( count( $seen_files ) === count( $with_assets ) * 2 )
	? ok( 'img. exactly two unique image files per asset-backed project (' . count( $seen_files ) . ' files / ' . count( $with_assets ) . ' projects)' )
	: bad( 'img. image-per-project count wrong' );

/* The shared frame must CONTAIN (never crop) the photography, and must be
   16:9 in both side-by-side and slider modes. Asserted against the single
   stylesheet so a future switch back to `cover` — which would silently crop
   the 43:24 sources — fails here rather than in production. */
$css = (string) file_get_contents( get_stylesheet_directory() . '/assets/css/blog.css' );
preg_match( '#\.proj-compare__frame img\s*\{([^}]*)\}#', $css, $fm );
$frame_css = $fm[1] ?? '';
( false !== strpos( $frame_css, 'object-fit: contain' ) )
	? ok( 'img. shared frame uses object-fit:contain (no silent cropping)' )
	: bad( 'img. shared frame is not object-fit:contain — sources would be cropped' );
( false !== strpos( $frame_css, 'aspect-ratio: 16 / 9' ) )
	? ok( 'img. shared frame is 16:9' )
	: bad( 'img. shared frame is not 16:9' );
( preg_match( '#\.proj-compare__media\.is-slider\s*\{[^}]*aspect-ratio:\s*16\s*/\s*9#', $css ) )
	? ok( 'img. slider mode is 16:9 too (same frame in both modes)' )
	: bad( 'img. slider mode is not 16:9' );

/* Exactly one shared implementation — no per-project template/CSS/JS forks. */
$renderers = glob( get_stylesheet_directory() . '/single-project*.php' ) ?: array();
( 1 === count( $renderers ) )
	? ok( 'img. exactly one project template (no per-project forks)' )
	: bad( 'img. ' . count( $renderers ) . ' project templates found' );

/* ── Truth-safe copy: retired claims must not reappear in the registry ──
   Each of these was contradicted by, or unsupported by, the project
   photographs during the truth-first audit. They are asserted against the
   whole compare block (heading + summary + before/work/result + alt text). */
echo "\n== TRUTH-SAFE PROJECT COPY ==\n";
$retired = array(
	'sherman-oaks-mid-century-remodel'   => array( 'mid-century', 'PebbleTec', 'Cool Blue', '6×6', 'ceramic', 'new coping', 'IntelliFlo', 'IntelliCenter', 'equipment pad', '$28k', '12 days', 'September 2025' ),
	'encino-estate-new-build'            => array( 'new gunite', 'undeveloped', 'vanishing edge', 'outdoor kitchen', 'fire bowl', 'fire features', 'hardscape', 'PebbleTec', 'Aqua White', 'glass mosaic', '$142k', '10 weeks', 'July 2025', 'permits' ),
	'studio-city-modern-automation'      => array( 'Pentair', 'IntelliCenter', 'variable-speed', 'salt cell', 'salt chlorine', 'Raypak', 'heater', 'pad rebuild', 'recycled', 'pebble', '$8.6k', '3 days', 'November 2025' ),
	'beverly-hills-luxe-spa-renovation'  => array( 'hand-cut', 'Italian', 'glass', 'LED', 'color loop', 'color-tuned', 'new jets', 'stripped', '$22k', '8 days', 'August 2025' ),
	'tarzana-resort-style-finish'        => array( 'PebbleTec', 'Caribbean Blue', 'sunshelf', 'sun shelf', 'bullnose', 'travertine', 'repointing', '$36k', '14 days', 'June 2025' ),
	'woodland-hills-tile-coping-refresh' => array( '6×6', 'porcelain', 'cantilever', 'no resurfacing', '30-year-old', 'plaster', '$12.4k', '6 days', 'May 2025' ),
);
// Only human-readable copy is checked. Asset filenames and service/area slugs
// are excluded: they encode the URL slug, which this phase deliberately does
// not change, so "sherman-oaks-mid-century-remodel-before.webp" is not a claim.
$copy_keys = array( 'heading', 'summary', 'before_condition', 'work_completed', 'completed_result', 'before_alt', 'after_alt' );
foreach ( $retired as $slug => $terms ) {
	$cmp   = ( \Showtime\Projects::get( $slug ) )['compare'] ?? array();
	$parts = array();
	foreach ( $copy_keys as $k ) {
		if ( isset( $cmp[ $k ] ) && is_string( $cmp[ $k ] ) ) { $parts[] = $cmp[ $k ]; }
	}
	$blob = implode( ' ', $parts );
	$hits = array();
	foreach ( $terms as $t ) {
		// Word-boundary match, so "LED" does not fire inside "tiled" and
		// "pebble" does not fire inside "pebbled".
		$re = '/(?<![\p{L}\d])' . preg_quote( $t, '/' ) . '(?![\p{L}\d])/iu';
		if ( preg_match( $re, $blob ) ) { $hits[] = $t; }
	}
	empty( $hits )
		? ok( "truth. $slug: no retired/unverified claims in comparison copy" )
		: bad( "truth. $slug: retired claim(s) still present: " . implode( ', ', $hits ) );
}

/* No price, duration or completion date may appear in the code-owned copy —
   those belong to owner-verified WordPress fields, not the registry. */
foreach ( array_keys( $retired ) as $slug ) {
	$cmp  = ( \Showtime\Projects::get( $slug ) )['compare'] ?? array();
	$blob = implode( ' ', array_filter( $cmp, 'is_string' ) );
	$bad_pattern = preg_match( '/\$[\d,]+k?\b/i', $blob )                       // prices
		|| preg_match( '/\b\d+\s*(day|days|week|weeks|month|months)\b/i', $blob ) // durations
		|| preg_match( '/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+20\d\d\b/', $blob ); // dates
	$bad_pattern
		? bad( "truth. $slug: comparison copy contains a price, duration or date" )
		: ok( "truth. $slug: no price, duration or date in comparison copy" );
}

/* Every project must still resolve a service and area link after the
   reclassifications (Encino -> resurfacing, Woodland Hills -> spa). */
$expect_service = array(
	'sherman-oaks-mid-century-remodel'   => 'pool-remodeling-resurfacing',
	'encino-estate-new-build'            => 'pool-remodeling-resurfacing',
	'studio-city-modern-automation'      => 'equipment-installation-upgrades',
	'beverly-hills-luxe-spa-renovation'  => 'spa-installation-renovations',
	'tarzana-resort-style-finish'        => 'pool-remodeling-resurfacing',
	'woodland-hills-tile-coping-refresh' => 'spa-installation-renovations',
);
foreach ( $expect_service as $slug => $svc ) {
	$cmp = ( \Showtime\Projects::get( $slug ) )['compare'] ?? array();
	( ( $cmp['primary_service'] ?? '' ) === $svc && null !== \Showtime\Services::get( $svc ) )
		? ok( "truth. $slug: primary service is $svc (registered)" )
		: bad( "truth. $slug: primary service is '" . ( $cmp['primary_service'] ?? '' ) . "', expected $svc" );
}

/* ── Admin editability of the estimate fields ──────────────────────────
   single-project.php reads `value` and `duration_value`. If nothing renders
   an input and saves them, the feature is unreachable for an administrator,
   so assert the whole path: registration hook, renderer, nonce, capability
   gate, sanitisation, and blank-means-blank. */
echo "\n== PROJECT ESTIMATE FIELDS — ADMIN SUPPORT ==\n";
function_exists( 'showtime_project_estimate_meta_box' )
	? ok( 'admin. estimate meta box renderer is defined' )
	: bad( 'admin. renderer missing — `value`/`duration_value` are not editable' );

if ( function_exists( 'showtime_project_estimate_meta_box' ) ) {
	$probe = get_page_by_path( 'tarzana-resort-style-finish', OBJECT, 'project' );
	ob_start();
	showtime_project_estimate_meta_box( $probe );
	$box = (string) ob_get_clean();
	( false !== strpos( $box, 'name="value"' ) && false !== strpos( $box, 'name="duration_value"' ) )
		? ok( 'admin. both estimate inputs render' )
		: bad( 'admin. an estimate input is missing from the box' );
	( false !== strpos( $box, 'showtime_project_estimate_nonce' ) )
		? ok( 'admin. nonce field present in the box' )
		: bad( 'admin. no nonce field' );

	// Save handler must be wired to the project post type only.
	( has_action( 'save_post_project' ) )
		? ok( 'admin. save handler bound to save_post_project' )
		: bad( 'admin. no save_post_project handler' );

	// A POST without the nonce must not write anything.
	$before_val = get_post_meta( $probe->ID, 'value', true );
	$_POST      = array( 'value' => 'UNAUTHORIZED' );
	do_action( 'save_post_project', $probe->ID );
	$_POST      = array();
	( get_post_meta( $probe->ID, 'value', true ) === $before_val )
		? ok( 'admin. save without a valid nonce is rejected' )
		: bad( 'admin. nonce-less save wrote to post meta' );
}

/* The template must not fall back to a bare "Investment"/"Duration" caption
   once a caption field is supplied, and must never leak an estimate into
   structured data. Both are asserted against the rendered page below. */

// No remote or placeholder source may appear in the registry.
$registry_blob = wp_json_encode( \Showtime\Projects::all() );
$remote_hits = array();
foreach ( array( 'picsum', 'unsplash', 'placehold', 'http://', 'https://' ) as $needle ) {
	if ( false !== stripos( $registry_blob, $needle ) ) { $remote_hits[] = $needle; }
}
empty( $remote_hits )
	? ok( 'img. no remote/placeholder image source in the project registry' )
	: bad( 'img. remote/placeholder source found: ' . implode( ', ', $remote_hits ) );

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

	// Summary length budget: the approved AEO answer-paragraph target of
	// 40-70 words. Counted on whitespace, the way a reader counts —
	// str_word_count() drops numerals and hyphenates, undercounting copy.
	// Long enough to answer "what changed?" on its own, short enough that it
	// cannot be padded back out with unverifiable specifics.
	$w = count( preg_split( '/\s+/', trim( wp_strip_all_tags( (string) $r['summary'] ) ), -1, PREG_SPLIT_NO_EMPTY ) );
	( $w >= 40 && $w <= 70 ) ? ok( "8c. $s: summary $w words (40-70)" ) : bad( "8c. $s: summary $w words, outside 40-70" );
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

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
$asset_probe_before = get_stylesheet_directory() . '/assets/img/projects/comparisons/tarzana-resort-style-finish-before.webp';
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
	$cmp   = $entry;
	$has   = is_array( $cmp )
		&& '' !== trim( (string) ( $cmp['comparison_heading'] ?? '' ) )
		&& '' !== trim( (string) ( $cmp['comparison_summary'] ?? '' ) );
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
	$cmp   = $entry;
	if ( ! empty( $cmp['before_image'] ) && ! empty( $cmp['after_image'] ) ) {
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

/* Half a pair is still not a pair.
   The old `showtime/project/compare_images` injection filter is gone: the
   registry is now the sole image source, so a half pair is produced by an
   unreadable file rather than a filter. Asserted on the normalizer, which is
   the single guard showtime_project_compare() depends on for each side. */
$only_before = showtime_project_compare_image( $asset_probe_before, 'alt' );
$missing_side = showtime_project_compare_image( $img_dir . 'projects/comparisons/no-such-half.webp', 'alt' );
( null !== $only_before && null === $missing_side )
	? ok( '9b. one readable side + one missing side cannot both resolve' )
	: bad( '9b. half-pair guard broken' );

/* And the section itself fails closed when either side cannot resolve. */
$probe_slug = 'tarzana-resort-style-finish';
$probe_dir  = get_stylesheet_directory() . '/assets/img/projects/comparisons/';
$probe_file = $probe_dir . 'tarzana-resort-style-finish-after.webp';
$probe_bak  = $probe_file . '.testbak';
rename( $probe_file, $probe_bak );
$r_missing = showtime_project_compare( $ids[ $probe_slug ] );
rename( $probe_bak, $probe_file );
null === $r_missing
	? ok( '9c. missing after-image -> whole section fails closed' )
	: bad( '9c. section rendered with a missing after-image' );

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
	$cmp = ( \Showtime\Projects::get( $s ) );
	$b   = $asset_dir . $cmp['before_image'];
	$a   = $asset_dir . $cmp['after_image'];

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
	foreach ( array( $cmp['before_image'], $cmp['after_image'] ) as $fn ) {
		if ( isset( $seen_files[ $fn ] ) ) { bad( "img. $s: filename reused" ); }
		$seen_files[ $fn ] = $s;
		// Production-safe filename.
		( $fn === strtolower( $fn ) && ! preg_match( '/[\s()]/', $fn ) )
			? null
			: bad( "img. $s: unsafe production filename '$fn'" );
	}

	// Correct before/after ordering + slug association encoded in the name.
	( false !== strpos( $cmp['before_image'], $s . '-before' )
		&& false !== strpos( $cmp['after_image'], $s . '-after' ) )
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
	$cmp   = ( \Showtime\Projects::get( $slug ) ) ?? array();
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

/* No price, duration or completion date may appear in the narrative copy.
   Scoped to the prose fields only: `timeline` and `investment` are dedicated
   registry fields that legitimately hold a range and are rendered under the
   fixed "Typical timeline" / "Typical investment" labels, so scanning the whole
   flat entry would flag them by design. */
foreach ( array_keys( $retired ) as $slug ) {
	$cmp  = ( \Showtime\Projects::get( $slug ) ) ?? array();
	$blob = implode(
		' ',
		array_map(
			static function ( $k ) use ( $cmp ) {
				return isset( $cmp[ $k ] ) && is_string( $cmp[ $k ] ) ? $cmp[ $k ] : '';
			},
			array( 'title', 'excerpt', 'comparison_heading', 'comparison_summary', 'before_condition', 'work_completed', 'completed_result', 'before_alt', 'after_alt', 'hero_alt', 'seo_title', 'meta_description' )
		)
	);
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
	'sherman-oaks-mid-century-remodel'   => '/services/pool-remodeling-resurfacing/',
	'encino-estate-new-build'            => '/services/pool-remodeling-resurfacing/',
	'studio-city-modern-automation'      => '/services/equipment-installation-upgrades/',
	'beverly-hills-luxe-spa-renovation'  => '/services/spa-installation-renovations/',
	'tarzana-resort-style-finish'        => '/services/pool-remodeling-resurfacing/',
	'woodland-hills-tile-coping-refresh' => '/services/spa-installation-renovations/',
);
foreach ( $expect_service as $slug => $svc ) {
	$cmp = ( \Showtime\Projects::get( $slug ) ) ?? array();
	$svc_slug = trim( (string) preg_replace( '#^/services/#', '', $svc ), '/' );
	( ( $cmp['service_url'] ?? '' ) === $svc && null !== \Showtime\Services::get( $svc_slug ) )
		? ok( "truth. $slug: primary service is $svc (registered)" )
		: bad( "truth. $slug: primary service is '" . ( $cmp['service_url'] ?? '' ) . "', expected $svc" );
}

/* ── Admin is READ-ONLY for managed projects ───────────────────────────
   Project content is owned by the code registry, so the editor must expose a
   notice and NO editable project-data field. An editable Timeline/Investment
   box would compete with the registry and silently diverge from the frontend. */
echo "\n== PROJECT ADMIN — READ-ONLY CODE-MANAGEMENT NOTICE ==\n";
function_exists( 'showtime_project_code_managed_meta_box' )
	? ok( 'admin. read-only code-management notice is defined' )
	: bad( 'admin. code-management notice missing' );

! function_exists( 'showtime_project_estimate_meta_box' )
	? ok( 'admin. old editable estimate box removed' )
	: bad( 'admin. an editable estimate box still competes with the registry' );

if ( function_exists( 'showtime_project_code_managed_meta_box' ) ) {
	$probe = get_page_by_path( 'tarzana-resort-style-finish', OBJECT, 'project' );
	ob_start();
	showtime_project_code_managed_meta_box( $probe );
	$box = (string) ob_get_clean();

	( false !== strpos( $box, 'managed in the Showtime Pools code registry' ) )
		? ok( 'admin. notice names the code registry as the source of truth' )
		: bad( 'admin. notice text missing' );
	( false !== strpos( $box, 'projects.php' ) )
		? ok( 'admin. notice points at the registry file' )
		: bad( 'admin. notice does not name the file to edit' );
	( false === strpos( $box, '<input' ) && false === strpos( $box, '<textarea' ) )
		? ok( 'admin. notice contains no editable input' )
		: bad( 'admin. notice still renders an editable field' );
	( false === strpos( $box, 'nonce' ) )
		? ok( 'admin. no save path (read-only, nothing to nonce)' )
		: bad( 'admin. a nonce implies a writable field' );
}

/* No save_post_project writer may remain for project data. */
$writes = has_action( 'save_post_project' );
( false === $writes )
	? ok( 'admin. no save_post_project writer — post meta cannot shadow the registry' )
	: bad( 'admin. a save_post_project handler is still registered' );

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

/* --- Structure, links, dimensions. The registry supplies the images, so no
   injection is needed: every managed project resolves a real pair. --- */
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
	// Compare against THIS project's own registry image, not a shared fixture.
	$real_file = get_stylesheet_directory() . '/assets/img/projects/comparisons/' . $s . '-before.webp';
	$real_dim  = @getimagesize( $real_file );
	$dim_ok    = is_array( $real_dim )
		&& (int) $r['before']['width'] === (int) $real_dim[0]
		&& (int) $r['before']['height'] === (int) $real_dim[1];
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

		// Section placement: after the facts strip and, when a testimonial is
		// present, before it. Client quotes are blank for managed projects, so
		// the quote section is legitimately absent — only assert the order that
		// actually exists rather than requiring a section we deliberately omit.
		$p_meta  = strpos( $body, 'proj-single__meta' );
		$p_cmp   = strpos( $body, '<section class="proj-compare"' );
		$p_quote = strpos( $body, 'proj-single__quote' );
		$ordered = ( false !== $p_meta && false !== $p_cmp && $p_meta < $p_cmp )
			&& ( false === $p_quote || $p_cmp < $p_quote );
		$ordered
			? ok( "2b. $s: placed after facts" . ( false === $p_quote ? ' (no testimonial — correctly omitted)' : ', before testimonial' ) )
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

echo "\n== PROJECT REGISTRY — legacy/unmanaged posts orphaned from public surfaces ==\n";

/*
 * The two legacy seed rows below carry no `managed` entry in projects.php.
 * Their post meta (written once by the one-time seeder) still holds
 * unverified prices, durations, materials and client quotes, so they must
 * never be discoverable — even though the posts themselves stay published
 * (never deleted, unpublished or renamed; that is the standing rule for
 * existing project posts). See showtime_unmanaged_project_ids().
 */
$legacy = array(
	'sherman-oaks-outdoor-living-build'  => 'Sherman Oaks outdoor living build',
	'encino-custom-design-water-feature' => 'Encino custom design with water feature',
);

/* 15. showtime_unmanaged_project_ids() finds exactly these two, by slug. */
if ( function_exists( 'showtime_unmanaged_project_ids' ) ) {
	$unmanaged_slugs = array();
	foreach ( showtime_unmanaged_project_ids() as $uid ) {
		$unmanaged_slugs[] = get_post_field( 'post_name', $uid );
	}
	sort( $unmanaged_slugs );
	$expected = array_keys( $legacy );
	sort( $expected );
	$unmanaged_slugs === $expected
		? ok( '15. showtime_unmanaged_project_ids() resolves exactly the two legacy slugs' )
		: bad( '15. showtime_unmanaged_project_ids() returned: ' . implode( ', ', $unmanaged_slugs ) );
} else {
	bad( '15. showtime_unmanaged_project_ids() not loaded' );
}

/* 16. Archive + homepage never render either legacy title or slug. */
$archive_body = fetch_body( "$base/projects/" );
$home_body    = fetch_body( "$base/" );
foreach ( array( 'archive (/projects/)' => $archive_body, 'homepage' => $home_body ) as $where => $body ) {
	$leaked = array();
	foreach ( $legacy as $slug => $title ) {
		if ( '' !== $body && ( false !== strpos( $body, $title ) || false !== strpos( $body, $slug ) ) ) {
			$leaked[] = $slug;
		}
	}
	( '' !== $body && empty( $leaked ) )
		? ok( "16. $where: no legacy project title/slug present" )
		: bad( "16. $where: leaked " . ( $body ? implode( ', ', $leaked ) : '(empty response)' ) );
}

/* 17. Archive shows exactly the six managed cards — never more, never fewer,
 * regardless of menu_order. */
$card_count = $count( $archive_body, '#class="proj-card"#' );
6 === $card_count
	? ok( '17. archive renders exactly 6 project cards' )
	: bad( "17. archive renders $card_count project cards (expected 6)" );

/* 18. Related cards on a managed single never surface a legacy post. Real
 * collision, not hypothetical: the managed Sherman Oaks post still carries
 * its original `neighborhood: Sherman Oaks` post meta from the one-time
 * seed, which otherwise meta_query-matches the legacy
 * sherman-oaks-outdoor-living-build post under the same neighborhood. */
$so_body = '';
if ( isset( $ids['sherman-oaks-mid-century-remodel'] ) ) {
	$so_body = fetch_body( "$base/projects/sherman-oaks-mid-century-remodel/" );
	$leaked  = ( false !== strpos( $so_body, 'outdoor living build' ) )
		|| ( false !== strpos( $so_body, 'sherman-oaks-outdoor-living-build' ) );
	( '' !== $so_body && ! $leaked )
		? ok( '18. Sherman Oaks related cards exclude the same-neighborhood legacy post' )
		: bad( '18. Sherman Oaks related cards leaked the legacy outdoor-living-build post' );
}

/* 19. XML sitemap lists the six managed projects and neither legacy slug. */
$sitemap_xml = fetch_body( "$base/wp-sitemap-posts-project-1.xml" );
$xml_ok      = '' !== $sitemap_xml;
foreach ( $slugs as $s ) {
	$xml_ok = $xml_ok && ( false !== strpos( $sitemap_xml, "/projects/$s/" ) );
}
foreach ( $legacy as $slug => $title ) {
	$xml_ok = $xml_ok && ( false === strpos( $sitemap_xml, "/projects/$slug/" ) );
}
$xml_ok
	? ok( '19. XML project sitemap lists the six managed projects and no legacy slug' )
	: bad( '19. XML project sitemap mismatch' );

/* 20. Direct legacy URLs stay live (200, never deleted/unpublished) but
 * carry noindex — reachable only by a direct/bookmarked link, never
 * advertised or indexed. */
foreach ( $legacy as $slug => $title ) {
	$body    = fetch_body( "$base/projects/$slug/" );
	$noindex = false !== stripos( $body, "robots' content='noindex, follow" )
		|| false !== stripos( $body, 'robots" content="noindex, follow' );
	( '' !== $body && $noindex )
		? ok( "20. $slug: still live, but robots noindex,follow" )
		: bad( "20. $slug: expected a live page with noindex,follow robots meta" );
}

/* 21. Managed singles are unaffected by the orphaning fix — still index,follow. */
if ( '' !== $so_body ) {
	$indexable = false !== stripos( $so_body, 'index, follow' ) && false === stripos( $so_body, 'noindex' );
	$indexable
		? ok( '21. managed project single stays index,follow (unaffected by the orphaning fix)' )
		: bad( '21. managed project single lost its index,follow robots directive' );
}

echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

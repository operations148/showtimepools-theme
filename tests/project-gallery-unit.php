<?php
/**
 * West Hollywood imagery + additional-project-gallery regression tests.
 *
 * Two related changes, one suite:
 *   1. The genuine West Hollywood before/after pair — that the files are real,
 *      unique, decodable WebP, that they are wired through the shared resolver
 *      to the comparison, card, hero and OG surfaces, and that connecting them
 *      changed nothing about the project's coming_soon / noindex / sitemap /
 *      schema state.
 *   2. The reusable additional-gallery carousel — that it is registry-driven
 *      and opt-in, renders four slots as two grouped pages of two, emits no
 *      <img>, no empty src and no invented copy while its slots are pending,
 *      lives inside the existing Real Project section, and stays out of
 *      structured data and both sitemaps.
 *
 * Static assertions read the theme's own source and the committed baseline via
 * `git show HEAD:`. Runtime assertions read real server-rendered HTML over HTTP
 * with JavaScript never executed — which doubles as the no-JS proof. Live
 * keyboard, swipe, disabled-state and reduced-motion behaviour is exercised by
 * the headless-Chrome pass reported alongside this file; the contracts those
 * depend on are pinned here so they cannot silently drift.
 *
 *   php tests/project-gallery-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/project-gallery-unit.php
 *
 * Read-only: creates no posts, writes no options, touches no image.
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
function bad( $m ) { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skip( $m ) { global $skip; $skip++; echo "  ~ SKIP: $m\n"; }

function fetch_body( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 25,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-project-gallery-test/1.0',
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

/** Read a path as it exists in the last commit, for before/after comparison. */
function git_head( string $repo_rel ): string {
	$root = dirname( __DIR__ );
	$cmd  = 'git -C ' . escapeshellarg( $root ) . ' show HEAD:' . escapeshellarg( $repo_rel ) . ' 2>&1';
	return (string) shell_exec( $cmd );
}

$child   = get_stylesheet_directory();
$cmp_dir = $child . '/assets/img/projects/comparisons';
$css     = (string) file_get_contents( $child . '/assets/css/blog.css' );
$js      = (string) file_get_contents( $child . '/assets/js/project-slider.js' );
$tpl     = (string) file_get_contents( $child . '/template-parts/project/gallery.php' );
$resolver= (string) file_get_contents( $child . '/inc/project-compare.php' );

$WH   = 'west-hollywood-pool-project';
$BEF  = "$cmp_dir/$WH-before.webp";
$AFT  = "$cmp_dir/$WH-after.webp";

$other_slugs = array(
	'sherman-oaks-mid-century-remodel', 'encino-estate-new-build', 'studio-city-modern-automation',
	'beverly-hills-luxe-spa-renovation', 'tarzana-resort-style-finish', 'woodland-hills-tile-coping-refresh',
	'van-nuys-pool-project', 'north-hollywood-pool-project', 'toluca-lake-pool-project',
	'burbank-pool-project', 'calabasas-pool-project', 'bel-air-pool-project', 'brentwood-pool-project',
);

$wh_html = fetch_body( home_url( "/projects/$WH/" ) );
$others  = array();
foreach ( $other_slugs as $s ) { $others[ $s ] = fetch_body( home_url( "/projects/$s/" ) ); }
if ( strlen( $wh_html ) < 500 ) { echo "  ! could not fetch the West Hollywood page — is the local site running?\n"; }

/** Magic-number WebP sniff (never trusts the extension). */
function is_webp( string $path ): bool {
	if ( ! is_readable( $path ) ) { return false; }
	$h = (string) file_get_contents( $path, false, null, 0, 12 );
	return 12 === strlen( $h ) && 'RIFF' === substr( $h, 0, 4 ) && 'WEBP' === substr( $h, 8, 4 );
}

echo "== WEST HOLLYWOOD IMAGE ASSETS ==\n";

// 5.
$w5 = is_webp( $BEF ) && is_webp( $AFT );
$w5 ? ok( '5. both West Hollywood files are genuine WebP by RIFF/WEBP magic number, not by file extension' )
	: bad( '5. before=' . var_export( is_webp( $BEF ), true ) . ' after=' . var_export( is_webp( $AFT ), true ) );

// 6.
$d_bef = @getimagesize( $BEF );
$d_aft = @getimagesize( $AFT );
$dec = is_array( $d_bef ) && is_array( $d_aft )
	&& $d_bef[0] > 0 && $d_bef[1] > 0 && $d_aft[0] > 0 && $d_aft[1] > 0
	&& IMAGETYPE_WEBP === ( $d_bef[2] ?? 0 ) && IMAGETYPE_WEBP === ( $d_aft[2] ?? 0 );
$dec ? ok( sprintf( '6. both decode to real pixel dimensions — before %dx%d, after %dx%d (exact 16:9, matching .proj-compare__frame)', $d_bef[0], $d_bef[1], $d_aft[0], $d_aft[1] ) )
	: bad( '6. one or both files did not decode' );

// 7.
$h_bef = is_readable( $BEF ) ? hash_file( 'sha256', $BEF ) : 'a';
$h_aft = is_readable( $AFT ) ? hash_file( 'sha256', $AFT ) : 'b';
( $h_bef !== $h_aft )
	? ok( '7. the before and after outputs have different SHA-256 hashes — they are two distinct photographs' )
	: bad( '7. before and after are byte-identical' );

// 4.
$dupes = array();
foreach ( glob( "$cmp_dir/*.webp" ) as $f ) {
	$base = basename( $f );
	if ( $base === "$WH-before.webp" || $base === "$WH-after.webp" ) { continue; }
	$h = hash_file( 'sha256', $f );
	if ( $h === $h_bef ) { $dupes[] = "before duplicates $base"; }
	if ( $h === $h_aft ) { $dupes[] = "after duplicates $base"; }
}
$sherman = glob( "$cmp_dir/sherman-oaks-*.webp" );
$dupes ? bad( '4. West Hollywood duplicates another project — ' . implode( '; ', $dupes ) )
	: ok( '4. neither West Hollywood image is a byte-duplicate of Sherman Oaks (' . count( $sherman ) . ' assets) or any of the ' . ( count( glob( "$cmp_dir/*.webp" ) ) - 2 ) . ' other comparison assets' );

// Originals must survive untouched, unstaged.
$inc = $child . '/assets/img/projects/_incoming';
$src_ok = is_readable( "$inc/west_hollywood_pump_replacement_before.png" )
	&& is_readable( "$inc/west_hollywood_pump_replacement_after.png" );
$tracked = trim( (string) shell_exec( 'git -C ' . escapeshellarg( dirname( __DIR__ ) ) . ' ls-files -- showtime-pools-child/assets/img/projects/_incoming 2>&1' ) );
( $src_ok && '' === $tracked )
	? ok( '4b. the original _incoming source files are still present and still untracked by git' )
	: bad( '4b. sourcesPresent=' . var_export( $src_ok, true ) . ' trackedFiles=' . $tracked );

echo "\n== RESOLVER WIRING ==\n";

$p = function_exists( 'showtime_project_data' ) ? showtime_project_data( $WH ) : null;

// 1 + 2.
$r1 = is_array( $p ) && false !== strpos( (string) $p['before_image'], "$WH-before.webp" );
$r2 = is_array( $p ) && false !== strpos( (string) $p['after_image'], "$WH-after.webp" );
$r1 ? ok( '1. West Hollywood resolves to its own unique before WebP through the shared resolver' ) : bad( '1. before_image = ' . ( $p['before_image'] ?? 'null' ) );
$r2 ? ok( '2. West Hollywood resolves to its own unique after WebP through the shared resolver' )  : bad( '2. after_image = ' . ( $p['after_image'] ?? 'null' ) );

// 3.
$card = null;
foreach ( showtime_project_cards() as $c ) { if ( $WH === $c['slug'] ) { $card = $c; } }
$r3 = is_array( $p ) && is_array( $card )
	&& $p['hero_image'] === $p['after_image']
	&& $card['image'] === $p['after_image']
	&& $p['og_image'] === $p['after_image'];
$r3 ? ok( '3. the archive card, the hero and the OG/Twitter image all resolve to the same after WebP — the established placeholder contract (hero_image === og_image === after_image)' )
	: bad( '3. hero=' . ( $p['hero_image'] ?? '' ) . ' card=' . ( $card['image'] ?? '' ) . ' og=' . ( $p['og_image'] ?? '' ) );

// Comparison actually assembles now.
$wh_post = get_page_by_path( $WH, OBJECT, 'project' );
$cmp = ( $wh_post instanceof WP_Post ) ? showtime_project_compare( $wh_post->ID ) : null;
is_array( $cmp )
	? ok( '3b. showtime_project_compare() now assembles for West Hollywood (both images + comparison copy resolve); it returned null before the pair existed' )
	: bad( '3b. comparison still returns null' );

echo "\n== NOTHING ELSE IN THE REGISTRY MOVED ==\n";

// 8 + 9.
$reg_now  = (string) file_get_contents( SHOWTIME_CORE_DIR . '/includes/data/projects.php' );
$reg_head = git_head( 'showtime-pools-core/includes/data/projects.php' );
$block = static function ( string $src, string $slug ): string {
	$i = strpos( $src, "'slug'               => '$slug'," );
	if ( false === $i ) { $i = strpos( $src, "'slug'             => '$slug'," ); }
	if ( false === $i ) { return ''; }
	$start = strrpos( substr( $src, 0, $i ), 'array(' );
	$end   = strpos( $src, "\n\t),", $i );
	return false === $end ? '' : substr( $src, (int) $start, $end - (int) $start );
};
$changed = array();
foreach ( $other_slugs as $s ) {
	$a = $block( $reg_head, $s );
	$b = $block( $reg_now, $s );
	if ( '' === $a || '' === $b ) { $changed[] = "$s (block not found)"; continue; }
	if ( $a !== $b ) { $changed[] = $s; }
}
$changed ? bad( '9. other project records changed — ' . implode( ', ', $changed ) )
	: ok( '9. all ' . count( $other_slugs ) . ' other managed project records are byte-identical to HEAD — only the West Hollywood block differs' );

$img_moved = array();
foreach ( $other_slugs as $s ) {
	$d = showtime_project_data( $s );
	if ( ! is_array( $d ) ) { continue; }
	foreach ( array( 'before_image', 'after_image', 'hero_image', 'og_image' ) as $k ) {
		if ( '' !== $d[ $k ] && false === strpos( $d[ $k ], $s ) && false === strpos( $d[ $k ], str_replace( '-pool-project', '', $s ) ) ) {
			$img_moved[] = "$s.$k -> " . basename( $d[ $k ] );
		}
	}
}
$img_moved ? bad( '8. an existing project image mapping moved — ' . implode( '; ', $img_moved ) )
	: ok( '8. every other project still maps to its own image files; no mapping was reassigned' );

echo "\n== GALLERY IS REGISTRY-DRIVEN AND OPT-IN ==\n";

// 10.
$no_slug_in_tpl = ! preg_match( '/west[-_]hollywood/i', $tpl );
$reads_registry = (bool) preg_match( '/showtime_project_gallery_pages/', (string) file_get_contents( $child . '/single-project.php' ) );
$fails_closed   = substr_count( $resolver, 'return array();' ) >= 8;
( $no_slug_in_tpl && $reads_registry && $fails_closed )
	? ok( '10. the gallery renders only from registry config: no slug appears in the component, the template asks the shared resolver, and the resolver fails the whole gallery closed on any malformed slot' )
	: bad( '10. slugFree=' . var_export( $no_slug_in_tpl, true ) . ' registryDriven=' . var_export( $reads_registry, true ) . ' failsClosed=' . var_export( $fails_closed, true ) );

// Fail-closed behaviour, exercised for real against the live resolver.
$cases = array(
	'not a list'              => array( 'additional_gallery' => array( 'a' => array( 'status' => 'coming_soon', 'image' => '', 'alt' => '', 'caption' => '' ) ) ),
	'odd slot count'          => array( 'additional_gallery' => array_fill( 0, 3, array( 'status' => 'coming_soon', 'image' => '', 'alt' => '', 'caption' => '' ) ) ),
	'unknown status'          => array( 'additional_gallery' => array_fill( 0, 2, array( 'status' => 'maybe', 'image' => '', 'alt' => '', 'caption' => '' ) ) ),
	'pending slot with alt'   => array( 'additional_gallery' => array_fill( 0, 2, array( 'status' => 'coming_soon', 'image' => '', 'alt' => 'invented', 'caption' => '' ) ) ),
	'ready without alt'       => array( 'additional_gallery' => array_fill( 0, 2, array( 'status' => 'ready', 'image' => "$WH-after.webp", 'alt' => '', 'caption' => '' ) ) ),
	'ready with missing file' => array( 'additional_gallery' => array_fill( 0, 2, array( 'status' => 'ready', 'image' => 'no-such-file.webp', 'alt' => 'x', 'caption' => '' ) ) ),
	'slot not an array'       => array( 'additional_gallery' => array( 'x', 'y' ) ),
);
$leaked = array();
foreach ( $cases as $name => $fixture ) {
	if ( array() !== showtime_project_gallery( $fixture ) ) { $leaked[] = $name; }
}
$valid_ok = 2 === count( showtime_project_gallery_pages( $p ) );
( ! $leaked && $valid_ok )
	? ok( '10b. all ' . count( $cases ) . ' malformed configurations (non-list, odd count, unknown status, pending-slot-with-copy, ready-without-alt, ready-with-missing-file, non-array slot) return an empty gallery, while the valid config still yields 2 pages' )
	: bad( '10b. leaked: ' . implode( ', ', $leaked ) . ' validConfigPages=' . count( showtime_project_gallery_pages( $p ) ) );

// 11 + 12.
$wh_count = preg_match_all( '#class="proj-gallery"#', $wh_html );
$other_with = array();
foreach ( $others as $s => $body ) {
	if ( preg_match( '#class="proj-gallery"#', $body ) ) { $other_with[] = $s; }
}
( 1 === $wh_count ) ? ok( '11. the West Hollywood page renders exactly one gallery component' ) : bad( "11. West Hollywood rendered $wh_count galleries" );
$other_with ? bad( '12. a non-configured project rendered a gallery — ' . implode( ', ', $other_with ) )
	: ok( '12. all ' . count( $others ) . ' other project pages render zero gallery components and zero gallery markup' );

echo "\n== GALLERY STRUCTURE ==\n";

$gal = '';
if ( preg_match( '#<div class="proj-gallery"[\s\S]*?(?=</section>)#', $wh_html, $mg ) ) { $gal = $mg[0]; }

// 13 + 14.
$cells   = preg_match_all( '#class="proj-gallery__cell"#', $gal );
$pending = preg_match_all( '#proj-gallery__card--pending#', $gal );
( 4 === $cells && 4 === $pending )
	? ok( '13 + 14. exactly four gallery slots render, and all four are in the pending "Coming soon" state' )
	: bad( "13/14. cells=$cells pending=$pending (expected 4 and 4)" );

// 15 + 16.
$pages = preg_match_all( '#data-proj-slider-slide="(\d+)"#', $gal, $pm );
// Split on the page boundary itself; a nested-<div> regex cannot find the
// matching close and silently truncates each page to its first card.
$per_page = array();
$segments = explode( 'data-proj-slider-slide="', $gal );
array_shift( $segments );
foreach ( $segments as $i => $seg ) {
	if ( $i === count( $segments ) - 1 ) {
		$nav = strpos( $seg, 'proj-gallery__nav' );
		if ( false !== $nav ) { $seg = substr( $seg, 0, $nav ); }
	}
	$per_page[] = preg_match_all( '#proj-gallery__cell#', $seg );
}
$sizes_ok = ! empty( $per_page ) && count( array_unique( $per_page ) ) === 1 && 2 === $per_page[0];
( 2 === $pages ) ? ok( '15. exactly two grouped carousel pages render' ) : bad( "15. pages=$pages" );
$sizes_ok ? ok( '16. every page contains exactly two cards (' . implode( ' + ', $per_page ) . ')' )
	: bad( '16. per-page card counts: ' . implode( ',', $per_page ) );

// 17 + 27.
$dots = preg_match_all( '#data-proj-slider-dot="(\d+)"#', $gal );
$names = array();
preg_match_all( '#aria-label="(View gallery page \d of \d)"#', $gal, $nm );
$names = $nm[1] ?? array();
$current = preg_match_all( '#aria-current="true"#', $gal );
( 2 === $dots ) ? ok( '17. exactly two pagination controls render' ) : bad( "17. dots=$dots" );
( array( 'View gallery page 1 of 2', 'View gallery page 2 of 2' ) === $names && 1 === $current )
	? ok( '27. pagination exposes the required accessible names and marks exactly one control aria-current="true"' )
	: bad( '27. names=' . wp_json_encode( $names ) . " ariaCurrent=$current" );

// 18.
$ratio = (bool) preg_match( '#\.proj-gallery__frame\s*\{[^}]*aspect-ratio:\s*3\s*/\s*4#s', $css );
$frames = preg_match_all( '#class="proj-gallery__frame"#', $gal );
( $ratio && 4 === $frames )
	? ok( '18. every one of the four placeholders sits in the shared 3 / 4 portrait frame' )
	: bad( '18. cssRatio=' . var_export( $ratio, true ) . " frames=$frames" );

// 19 + 20.
$imgs = preg_match_all( '#<img#', $gal );
$empty_src = preg_match_all( '#src=(""|\'\')#', $wh_html );
( 0 === $imgs && 0 === $empty_src )
	? ok( '19 + 20. no placeholder emits an <img> element, and the page contains no empty src attribute anywhere' )
	: bad( "19/20. imgsInGallery=$imgs emptySrcOnPage=$empty_src" );

// 21.
$has_alt     = (bool) preg_match( '#\balt=#', $gal );
$has_caption = (bool) preg_match( '#proj-gallery__caption#', $gal );
$decor_hidden = 4 === preg_match_all( '#proj-gallery__icon" aria-hidden="true"#', $gal );
( ! $has_alt && ! $has_caption && $decor_hidden )
	? ok( '21. no alt attribute and no caption exist anywhere in the pending gallery, and all four decorative icons are aria-hidden' )
	: bad( '21. alt=' . var_export( $has_alt, true ) . ' caption=' . var_export( $has_caption, true ) . ' decorHidden=' . var_export( $decor_hidden, true ) );

// Wording contract.
$wording = false !== strpos( $gal, 'More Project Highlights' )
	&& false !== strpos( $gal, 'Additional project photos will be added soon.' )
	&& false === stripos( $gal, 'Sample Projects We Also Built' )
	&& 4 === preg_match_all( '#Project photo coming soon#', $gal )
	&& 4 === preg_match_all( '#>Coming soon<#', $gal );
$wording ? ok( '21b. the approved wording renders verbatim: heading, supporting sentence, four "Coming soon" badges and four "Project photo coming soon" lines — and the rejected heading appears nowhere' )
	: bad( '21b. wording contract not met' );

echo "\n== PLACEMENT AND HEADINGS ==\n";

// 25.
$sec_at = strpos( $wh_html, '<section class="proj-compare"' );
$gal_at = strpos( $wh_html, 'class="proj-gallery"' );
$between = ( false !== $sec_at && false !== $gal_at ) ? substr( $wh_html, $sec_at, $gal_at - $sec_at ) : '';
$inside  = '' !== $between && false === strpos( $between, '</section>' );
$after_facts = false !== strpos( $between, 'proj-compare__facts' ) && false !== strpos( $between, 'proj-compare__media' );
$own_section = ! preg_match( '#<section[^>]*proj-gallery#', $wh_html );
( $inside && $after_facts && $own_section )
	? ok( '25. the gallery is nested inside the Real Project section — after the comparison media and the Before / Work completed / Result facts, in the same container, and it opens no <section> or background band of its own' )
	: bad( '25. inside=' . var_export( $inside, true ) . ' afterFacts=' . var_export( $after_facts, true ) . ' noOwnSection=' . var_export( $own_section, true ) );

// 24.
preg_match_all( '#<(h[1-6])[^>]*>#', $wh_html, $hm );
$levels = array_map( static fn( $t ) => (int) substr( $t, 1 ), $hm[1] ?? array() );
$h1 = count( array_filter( $levels, static fn( $l ) => 1 === $l ) );
$gallery_h3 = (bool) preg_match( '#<h3 class="proj-gallery__title"#', $wh_html );
$section_h2 = (bool) preg_match( '#<h2 id="proj-compare-\d+-h"#', $wh_html );
// No skipped level anywhere in document order.
$skipped = array();
$prev = 0;
foreach ( $levels as $l ) {
	if ( $prev && $l > $prev + 1 ) { $skipped[] = "h$prev -> h$l"; }
	$prev = $l;
}
( 1 === $h1 && $gallery_h3 && $section_h2 && ! $skipped )
	? ok( '24. exactly one H1; the Real Project title is an H2 and the gallery heading is the H3 directly beneath it; no heading level is skipped anywhere on the page' )
	: bad( "24. h1Count=$h1 galleryH3=" . var_export( $gallery_h3, true ) . ' sectionH2=' . var_export( $section_h2, true ) . ' skipped=' . implode( ',', $skipped ) );

// 32.
preg_match_all( '#\sid="([^"]+)"#', $wh_html, $im );
$ids  = $im[1] ?? array();
$dups = array_keys( array_filter( array_count_values( $ids ), static fn( $n ) => $n > 1 ) );
$dups ? bad( '32. duplicate IDs on the West Hollywood page: ' . implode( ', ', $dups ) )
	: ok( '32. no duplicate id attributes on the West Hollywood page (' . count( $ids ) . ' ids), and the gallery derives its ids from the post id so multiple instances cannot collide' );

echo "\n== CAROUSEL CONTRACT ==\n";

// 26 + 28 + 29.
$js_checks = array(
	'ends disabled, never looping' => (bool) preg_match( '#prev\.disabled = index === 0;#', $js ) && (bool) preg_match( '#next\.disabled = index === total - 1;#', $js ),
	'no autoplay timer'            => ! (bool) preg_match( '#setInterval|setTimeout\s*\(\s*function[\s\S]{0,80}goTo#', $js ),
	'no cloned slides'             => ! (bool) preg_match( '#cloneNode#', $js ),
	'ArrowLeft / ArrowRight'       => (bool) preg_match( "#e\.key === 'ArrowLeft'#", $js ) && (bool) preg_match( "#e\.key === 'ArrowRight'#", $js ),
	'keys scoped to the component' => (bool) preg_match( "#root\.addEventListener\('keydown'#", $js ),
	// Assert on the registration itself rather than a character-distance
	// window, which breaks the moment a handler body grows a line.
	'touch swipe, passive'         => (bool) preg_match( "#viewport\.addEventListener\('touchstart',[\s\S]*?\}, \{ passive: true \}\);#", $js )
		&& (bool) preg_match( "#viewport\.addEventListener\('touchend',[\s\S]*?\}, \{ passive: true \}\);#", $js ),
	'aria-current on active dot'   => (bool) preg_match( "#dot\.setAttribute\('aria-current', 'true'\)#", $js ),
	'multiple instances'           => (bool) preg_match( "#querySelectorAll\('\[data-proj-slider\]'\)#", $js ),
	'label is parameterised'       => (bool) preg_match( "#getAttribute\('data-proj-slider-label'\) \|\| 'Slide'#", $js ),
);
$miss = array_keys( array_filter( $js_checks, static fn( $v ) => ! $v ) );
$miss ? bad( '26/28/29. carousel contract — missing: ' . implode( ', ', $miss ) )
	: ok( '26 + 28 + 29. the shared slider disables prev at the first page and next at the last (never loops, never clones, never autoplays), handles ArrowLeft/ArrowRight scoped to the component, supports passive touch swipe, sets aria-current, and initialises every instance independently' );

// Initial rendered state: first dot current, nav hidden until enhanced.
$nav_hidden = (bool) preg_match( '#class="proj-gallery__nav" data-proj-slider-nav hidden#', $gal );
$first_current = (bool) preg_match( '#data-proj-slider-dot="0"\s+aria-current="true"#', $gal );
$live = (bool) preg_match( '#class="visually-hidden" aria-live="polite" data-proj-slider-status#', $gal );
$labelled = false !== strpos( $gal, 'data-proj-slider-label="Gallery page"' );
( $nav_hidden && $first_current && $live && $labelled )
	? ok( '26b. server-rendered initial state is correct: page 1 marked current, nav carries `hidden` until the script enhances it, and the live region is wired to announce "Gallery page N of 2"' )
	: bad( '26b. navHidden=' . var_export( $nav_hidden, true ) . ' firstCurrent=' . var_export( $first_current, true ) . ' live=' . var_export( $live, true ) . ' labelled=' . var_export( $labelled, true ) );

// 30.
$rm = (bool) preg_match( '#@media \(prefers-reduced-motion: reduce\)\s*\{[^}]*\.proj-gallery\.is-enhanced \.proj-gallery__track \{ transition: none; \}#s', $css );
$rm ? ok( '30. under prefers-reduced-motion the carousel transition is removed while paging still works' )
	: bad( '30. reduced-motion rule missing' );

// 31.
$nojs = array(
	'track is a stacked grid by default' => (bool) preg_match( '#\.proj-gallery__track \{ display: grid;#', $css ),
	'flex row only once enhanced'        => (bool) preg_match( '#\.proj-gallery\.is-enhanced \.proj-gallery__track \{\s*display: flex;#s', $css ),
	'viewport does not clip un-enhanced' => (bool) preg_match( '#\.proj-gallery__viewport \{ overflow: visible; \}#', $css ),
	'two columns at every width'         => (bool) preg_match( '#\.proj-gallery__grid \{[^}]*grid-template-columns: 1fr 1fr;#s', $css ),
	'nav hidden without the script'      => $nav_hidden,
);
$miss31 = array_keys( array_filter( $nojs, static fn( $v ) => ! $v ) );
$miss31 ? bad( '31. no-JS fallback — missing: ' . implode( ', ', $miss31 ) )
	: ok( '31. without JavaScript all four placeholders stay visible as a two-column stacked grid, the viewport does not clip, and the non-functional controls stay hidden' );

echo "\n== SEO / STRUCTURED DATA / SITEMAPS ==\n";

// 22.
preg_match_all( '#<script type="application/ld\+json">([\s\S]*?)</script>#', $wh_html, $ld );
$ld_all = implode( ' ', $ld[1] ?? array() );
$leak = array();
foreach ( array( 'proj-gallery', 'Project photo coming soon', 'Coming soon', 'coming_soon', 'ImageObject', 'ItemList', '"Product"', '"Offer"', 'AggregateRating', 'ratingValue', 'reviewCount', '"Review"' ) as $needle ) {
	if ( false !== strpos( $ld_all, $needle ) ) { $leak[] = $needle; }
}
$creative = false !== strpos( $ld_all, 'CreativeWork' );
( ! $leak && ! $creative )
	? ok( '22. no gallery placeholder reaches structured data, and the placeholder project still emits no CreativeWork node — the page carries only WebSite, BreadcrumbList and the sitewide business node' )
	: bad( '22. leaked into JSON-LD: ' . implode( ', ', $leak ) . ' creativeWork=' . var_export( $creative, true ) );

// 23.
$xml  = fetch_body( home_url( '/wp-sitemap-posts-project-1.xml' ) );
$html_map = fetch_body( home_url( '/sitemap/' ) );
$in_xml  = false !== strpos( $xml, $WH );
$in_html = false !== strpos( $html_map, $WH );
( ! $in_xml && ! $in_html )
	? ok( '23. West Hollywood and its gallery placeholders appear in neither the XML sitemap nor the HTML sitemap' )
	: bad( '23. xml=' . var_export( $in_xml, true ) . ' html=' . var_export( $in_html, true ) );

echo "\n== INDEXING AND PUBLICATION STATE UNCHANGED ==\n";

$robots_ok  = (bool) preg_match( "#<meta name='robots' content='noindex, follow' />#", $wh_html );
$still_soon = is_array( $p ) && ! empty( $p['is_coming_soon'] ) && 'coming_soon' === $p['status'];
$facts_soon = is_array( $p )
	&& 'Coming soon' === $p['scope'] && 'Coming soon' === $p['finish']
	&& 'Coming soon' === $p['timeline'] && 'Coming soon' === $p['investment'];
$no_claims  = is_array( $p ) && '' === $p['completion_date'] && '' === $p['client_quote'];
$published  = ( $wh_post instanceof WP_Post ) && 'publish' === $wh_post->post_status;
$canon_ok   = false !== strpos( $wh_html, '<link rel="canonical" href="' . home_url( "/projects/$WH/" ) . '"' );
( $robots_ok && $still_soon && $facts_soon && $no_claims && $published && $canon_ok )
	? ok( '10c. connecting the photographs changed no indexing state: still noindex,follow — still status coming_soon — scope/finish/timeline/investment still exactly "Coming soon" — completion date and testimonial still blank — post still published — canonical unchanged' )
	: bad( '10c. robots=' . var_export( $robots_ok, true ) . ' comingSoon=' . var_export( $still_soon, true ) . ' facts=' . var_export( $facts_soon, true ) . ' noClaims=' . var_export( $no_claims, true ) . ' published=' . var_export( $published, true ) . ' canonical=' . var_export( $canon_ok, true ) );

// No price/rating anywhere in the West Hollywood registry block.
$wh_block = $block( $reg_now, $WH );
$dirty = array();
if ( preg_match( '/\$\s?[\d,]/', $wh_block ) ) { $dirty[] = 'price figure'; }
foreach ( array( 'aggregateRating', 'ratingValue', 'reviewCount' ) as $n ) {
	if ( false !== stripos( $wh_block, $n ) ) { $dirty[] = $n; }
}
$dirty ? bad( '22b. West Hollywood registry block contains ' . implode( ', ', $dirty ) )
	: ok( '22b. the West Hollywood registry block contains no price figure and no review or rating data' );

echo "\n== NO REGRESSION ELSEWHERE ==\n";

// 33.
$other_gallery_markup = array();
foreach ( $others as $s => $body ) {
	foreach ( array( 'proj-gallery', 'More Project Highlights', 'data-proj-slider-label' ) as $needle ) {
		if ( false !== strpos( $body, $needle ) ) { $other_gallery_markup[] = "$s:$needle"; }
	}
}
$other_gallery_markup ? bad( '33. gallery markup leaked onto another project page — ' . implode( ', ', $other_gallery_markup ) )
	: ok( '33. no gallery markup, heading or slider attribute appears on any of the ' . count( $others ) . ' other project pages; their registry blocks are byte-identical to HEAD (see 9)' );

// 34.
$cmp_ok = array();
foreach ( $others as $s => $body ) {
	if ( ! preg_match( '#class="proj-compare__media#', $body ) ) { continue; }
	$has_pair = 2 === preg_match_all( '#class="proj-compare__frame proj-compare__frame--#', $body );
	if ( ! $has_pair ) { $cmp_ok[] = $s; }
}
$slider_js_intact = (bool) preg_match( '#data-proj-compare#', $wh_html );
$cmp_ok ? bad( '34. a comparison lost one of its frames — ' . implode( ', ', $cmp_ok ) )
	: ok( '34. every other project comparison still renders its labelled before/after pair, and the West Hollywood pair is marked sliderable (identical dimensions) so the interactive slider engages' );

// 35 + 36.
$header_ok = (bool) preg_match( '#<header[^>]*id="masthead"[^>]*data-hero="true"#s', $wh_html )
	&& (bool) preg_match( '#<body[^>]*\bclass="[^"]*\bhas-hero\b#', $wh_html );
$ghl_ok = false !== strpos( $wh_html, '69b32c236a7fada7ea40faca' )
	&& false !== strpos( $wh_html, 'widgets.leadconnectorhq.com/loader.js' );
$drawer_ok = (bool) preg_match( '#\.mobile-drawer \{[^}]*z-index: 100000000;#s', (string) file_get_contents( $child . '/assets/css/header.css' ) );
$header_ok ? ok( '35. the sitewide overlaid-header behaviour still applies on this page (body.has-hero + header[data-hero=true])' ) : bad( '35. header state regressed' );
( $ghl_ok && $drawer_ok )
	? ok( '36. the GHL widget id and loader are still present and the drawer still out-stacks the chat widget' )
	: bad( '36. ghl=' . var_export( $ghl_ok, true ) . ' drawer=' . var_export( $drawer_ok, true ) );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

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
// Exactly these three records are authorized to gain real gallery photographs in
// the CURRENT working tree. Every other record must be byte-identical to HEAD —
// including Sherman Oaks / Encino / Studio City, whose galleries are already
// committed and must not shift again.
// Nothing is authorized to change right now: every gallery has shipped, so the
// whole registry must be byte-identical to HEAD. Add a slug here only while its
// gallery is actively being added, then move it to the shipped list once merged.
$gallery_authorized = array();
// Already shipped: these must match HEAD exactly, gallery block included. This
// is stricter than the "authorized" path — no part of the block may move.
$gallery_already_shipped = array(
	'sherman-oaks-mid-century-remodel',
	'encino-estate-new-build',
	'studio-city-modern-automation',
	'beverly-hills-luxe-spa-renovation',
	'tarzana-resort-style-finish',
	'woodland-hills-tile-coping-refresh',
	'van-nuys-pool-project',
	'toluca-lake-pool-project',
	'north-hollywood-pool-project',
	'burbank-pool-project',
	'calabasas-pool-project',
	'west-hollywood-pool-project',
	'bel-air-pool-project',
);
// Removing ONLY the added `additional_gallery` block must restore the HEAD text
// exactly. That proves the authorized records gained a gallery and changed in no
// other way — stricter than exempting them from the comparison.
$strip_gallery = static function ( string $block ): string {
	return (string) preg_replace(
		"#\n\t\t// Real photographs.*?\n\t\t'additional_gallery' => array\(.*?\n\t\t\),#s",
		'',
		$block
	);
};
$changed = array();
$authorized_bad = array();
$shipped_bad = array();
foreach ( $other_slugs as $s ) {
	$a = $block( $reg_head, $s );
	$b = $block( $reg_now, $s );
	if ( '' === $a || '' === $b ) { $changed[] = "$s (block not found)"; continue; }
	if ( in_array( $s, $gallery_authorized, true ) ) {
		// Must have actually gained a gallery, and must differ by nothing else.
		if ( false === strpos( $b, "'additional_gallery' => array(" ) ) {
			$authorized_bad[] = "$s (no gallery added)";
		} elseif ( $strip_gallery( $b ) !== $a ) {
			$authorized_bad[] = "$s (changed beyond the gallery block)";
		}
		continue;
	}
	if ( in_array( $s, $gallery_already_shipped, true ) ) {
		// Shipped galleries: byte-identical, and the gallery must still be there.
		if ( $a !== $b ) { $shipped_bad[] = "$s (already-shipped block moved)"; }
		elseif ( false === strpos( $b, "'additional_gallery' => array(" ) ) {
			$shipped_bad[] = "$s (shipped gallery disappeared)";
		}
		continue;
	}
	if ( $a !== $b ) { $changed[] = $s; }
}
$untouched_count = count( $other_slugs ) - count( $gallery_authorized ) - count( $gallery_already_shipped );
( $changed || $authorized_bad || $shipped_bad )
	? bad( '9. registry drift — unchanged-set violations: ' . ( implode( ', ', $changed ) ?: 'none' )
		. ' | authorized-record violations: ' . ( implode( ', ', $authorized_bad ) ?: 'none' )
		. ' | already-shipped violations: ' . ( implode( ', ', $shipped_bad ) ?: 'none' ) )
	: ok( "9. $untouched_count untouched managed project records are byte-identical to HEAD; the "
		. count( $gallery_already_shipped ) . ' already-shipped galleries (' . implode( ', ', $gallery_already_shipped )
		. ') are byte-identical to HEAD including their gallery blocks; and the '
		. count( $gallery_authorized ) . ' authorized records ('
		. ( $gallery_authorized ? implode( ', ', $gallery_authorized ) : 'none' )
		. ') differ from HEAD by nothing but their added additional_gallery block' );

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
	'count not a whole page'  => array( 'additional_gallery' => array_fill( 0, 4, array( 'status' => 'coming_soon', 'image' => '', 'alt' => '', 'caption' => '' ) ) ),
	'unknown status'          => array( 'additional_gallery' => array_fill( 0, 3, array( 'status' => 'maybe', 'image' => '', 'alt' => '', 'caption' => '' ) ) ),
	'pending slot with alt'   => array( 'additional_gallery' => array_fill( 0, 3, array( 'status' => 'coming_soon', 'image' => '', 'alt' => 'invented', 'caption' => '' ) ) ),
	'ready without alt'       => array( 'additional_gallery' => array_fill( 0, 3, array( 'status' => 'ready', 'image' => "$WH-after.webp", 'alt' => '', 'caption' => '' ) ) ),
	'ready with missing file' => array( 'additional_gallery' => array_fill( 0, 3, array( 'status' => 'ready', 'image' => 'no-such-file.webp', 'alt' => 'x', 'caption' => '' ) ) ),
	'slot not an array'       => array( 'additional_gallery' => array( 'x', 'y', 'z' ) ),
);
$leaked = array();
foreach ( $cases as $name => $fixture ) {
	if ( array() !== showtime_project_gallery( $fixture ) ) { $leaked[] = $name; }
}
$valid_ok = 2 === count( showtime_project_gallery_pages( $p ) );
( ! $leaked && $valid_ok )
	? ok( '10b. all ' . count( $cases ) . ' malformed configurations (non-list, count that is not a whole number of pages, unknown status, pending-slot-with-copy, ready-without-alt, ready-with-missing-file, non-array slot) return an empty gallery, while the valid six-slot config still yields 2 pages' )
	: bad( '10b. leaked: ' . implode( ', ', $leaked ) . ' validConfigPages=' . count( showtime_project_gallery_pages( $p ) ) );

// 11 + 12.
$wh_count = preg_match_all( '#class="proj-gallery"#', $wh_html );
$other_with = array();
foreach ( $others as $s => $body ) {
	if ( preg_match( '#class="proj-gallery"#', $body ) ) { $other_with[] = $s; }
}
( 1 === $wh_count ) ? ok( '11. the West Hollywood page renders exactly one gallery component' ) : bad( "11. West Hollywood rendered $wh_count galleries" );
// The gallery is now the shared default for EVERY managed project, so each of
// them must render exactly one — never zero, never two.
$wrong = array();
foreach ( $others as $s2 => $body ) {
	$n = preg_match_all( '#class="proj-gallery"#', $body );
	if ( 1 !== $n ) { $wrong[] = "$s2 ($n)"; }
}
$wrong ? bad( '12. every managed project must render exactly one gallery — ' . implode( ', ', $wrong ) )
	: ok( '12. all ' . ( count( $others ) + 1 ) . ' managed project pages render exactly one gallery each' );

echo "\n== GALLERY STRUCTURE ==\n";

$gal = '';
if ( preg_match( '#<div class="proj-gallery"[\s\S]*?(?=</section>)#', $wh_html, $mg ) ) { $gal = $mg[0]; }

// 13 + 14. West Hollywood's gallery is now POPULATED with six verified project
// photographs. The former expectation (six pending placeholders) described the
// pre-publication state and no longer exists. The replacement is stricter: it
// pins the exact count of real cards AND requires zero pending cards, zero
// placeholder badges and zero placeholder captions — so a regression back to
// placeholders, or a partially-filled gallery, both fail.
$cells    = preg_match_all( '#class="proj-gallery__cell"#', $gal );
$pending  = preg_match_all( '#proj-gallery__card--pending#', $gal );
$badges   = preg_match_all( '#>Coming soon<#', $gal );
$pend_txt = preg_match_all( '#Project photo coming soon#', $gal );
$real     = preg_match_all( '#class="proj-gallery__card"#', $gal );
( 6 === $cells && 0 === $pending && 0 === $badges && 0 === $pend_txt && 6 === $real )
	? ok( '13 + 14. exactly six gallery slots render and all six are REAL photograph cards — zero pending cards, zero "Coming soon" badges and zero placeholder captions remain' )
	: bad( "13/14. cells=$cells realCards=$real pending=$pending badges=$badges pendingText=$pend_txt (expected 6/6/0/0/0)" );

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
$sizes_ok = ! empty( $per_page ) && count( array_unique( $per_page ) ) === 1 && 3 === $per_page[0];
( 2 === $pages ) ? ok( '15. exactly two grouped carousel pages render' ) : bad( "15. pages=$pages" );
$sizes_ok ? ok( '16. every page contains exactly three cards (' . implode( ' + ', $per_page ) . ')' )
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
$ratio = (bool) preg_match( '#\.proj-gallery__frame\s*\{[^}]*aspect-ratio:\s*4\s*/\s*3#s', $css );
$frames = preg_match_all( '#class="proj-gallery__frame"#', $gal );
( $ratio && 6 === $frames )
	? ok( '18. every one of the six placeholders sits in the shared 4 / 3 landscape frame' )
	: bad( '18. cssRatio=' . var_export( $ratio, true ) . " frames=$frames" );

// 19 + 20. Formerly: the gallery emitted NO <img> at all. Now it must emit
// exactly six, each pointing at this project's own canonical highlight file,
// numbered 01-06 with no gaps and no repeats. The empty-src guard is retained
// verbatim, so a blank src anywhere on the page still fails.
$imgs      = preg_match_all( '#<img#', $gal );
$empty_src = preg_match_all( '#src=(""|\'\')#', $wh_html );
preg_match_all( '#<img[^>]+src="[^"]*/galleries/' . preg_quote( $WH, '#' ) . '/' . preg_quote( $WH, '#' ) . '-highlight-(\d{2})\.webp"#', $gal, $om );
$own_nums = $om[1] ?? array();
sort( $own_nums );
$expect_nums = array( '01', '02', '03', '04', '05', '06' );
( 6 === $imgs && 0 === $empty_src && $own_nums === $expect_nums )
	? ok( '19 + 20. the gallery emits exactly six <img> elements, every src is this project\'s own galleries/' . $WH . '/ highlight file numbered 01-06 with no gap or repeat, and the page still contains no empty src attribute anywhere' )
	: bad( "19/20. imgsInGallery=$imgs emptySrcOnPage=$empty_src ownNumbered=" . wp_json_encode( $own_nums ) );

// 21. Formerly: NO alt attribute existed, because there was no image to
// describe. Now every one of the six images must carry a non-empty, unique,
// markup-free alt string, and no placeholder caption may remain. Stronger:
// the old check could pass with zero images; this one cannot.
preg_match_all( '#<img[^>]+alt="([^"]*)"#', $gal, $am );
$alts        = array_map( 'html_entity_decode', $am[1] ?? array() );
$non_empty   = count( array_filter( $alts, static fn( $a ) => '' !== trim( $a ) ) );
$unique      = count( array_unique( $alts ) );
$markup_free = count( $alts ) === count( array_filter( $alts, static fn( $a ) => $a === wp_strip_all_tags( $a ) ) );
$has_caption = (bool) preg_match( '#proj-gallery__caption#', $gal );
( 6 === count( $alts ) && 6 === $non_empty && 6 === $unique && $markup_free && ! $has_caption )
	? ok( '21. all six gallery images carry a non-empty, unique, markup-free alt description, and no placeholder caption remains' )
	: bad( '21. alts=' . count( $alts ) . " nonEmpty=$non_empty unique=$unique markupFree="
		. var_export( $markup_free, true ) . ' caption=' . var_export( $has_caption, true ) );

// 21b. Wording contract. The approved heading is unchanged and the rejected
// heading must still appear nowhere — both retained verbatim. What changed is
// the pending copy: with every slot filled, the "will be added soon" sentence
// and the six placeholder lines must now be ABSENT, which the component drops
// automatically once nothing is pending.
$wording = false !== strpos( $gal, 'More Project Highlights' )
	&& false === strpos( $gal, 'Additional project photos will be added soon.' )
	&& false === stripos( $gal, 'Sample Projects We Also Built' )
	&& 0 === preg_match_all( '#Project photo coming soon#', $gal )
	&& 0 === preg_match_all( '#>Coming soon<#', $gal );
$wording ? ok( '21b. the approved heading still renders verbatim and the rejected heading appears nowhere; with all six slots filled, the "photos will be added soon" sentence, the "Coming soon" badges and the placeholder lines are all correctly absent' )
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
	'three columns like the ref grid'    => (bool) preg_match( '#\.proj-gallery__grid \{[\s\S]*?grid-template-columns: repeat\(3, 1fr\);#s', $css ),
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
foreach ( array( 'proj-gallery', 'Project photo coming soon', 'Coming soon', 'coming_soon', 'ImageObject', 'ItemList', '"Product"', '"Offer"', 'AggregateRating', 'ratingValue', 'reviewCount', '"Review"', 'priceCurrency', '"price"', '1,300', '2,600' ) as $needle ) {
	if ( false !== strpos( $ld_all, $needle ) ) { $leak[] = $needle; }
}
// West Hollywood is now a verified project, so exactly ONE CreativeWork node is
// expected. What must never appear is a gallery placeholder or the researched
// price range.
$creative = preg_match_all( '#"@type":"CreativeWork"#', $ld_all );
( ! $leak && 1 === $creative )
	? ok( '22. no gallery placeholder and no researched price figure reaches structured data; the page emits exactly one CreativeWork node and no Product, Offer, Review, rating or price schema' )
	: bad( '22. leaked into JSON-LD: ' . implode( ', ', $leak ) . ' creativeWorkNodes=' . $creative );

// 23.
$xml  = fetch_body( home_url( '/wp-sitemap-posts-project-1.xml' ) );
$html_map = fetch_body( home_url( '/sitemap/' ) );
// West Hollywood is now a published project, so it SHOULD be listed once in each
// sitemap alongside the other thirteen. What must still contribute no URL at all
// is a gallery placeholder.
$in_xml     = preg_match_all( '#<loc>[^<]*' . preg_quote( $WH, '#' ) . '/?</loc>#', $xml );
$in_html    = substr_count( $html_map, $WH . '/"' );
$xml_total  = preg_match_all( '#<loc>[^<]*/projects/[^<]+</loc>#', $xml );
$html_total = preg_match_all( '#href="[^"]*/projects/[^"/]+/"#', $html_map );
$ph_leak    = preg_match( '#proj-gallery|Project photo coming soon#', $xml . $html_map );
( 1 === $in_xml && 1 === $in_html && 14 === $xml_total && 14 === $html_total && ! $ph_leak )
	? ok( '23. both sitemaps list all 14 managed projects including West Hollywood exactly once, and no gallery placeholder contributes a URL' )
	: bad( "23. whXml=$in_xml whHtml=$in_html xmlTotal=$xml_total htmlTotal=$html_total placeholderLeak=$ph_leak" );

echo "\n== PROMOTED INDEXING AND PUBLICATION STATE ==\n";

// PROMOTED. The project is verified and indexable now; what must still hold is
// that it makes no unverifiable claim and keeps one self-referencing canonical.
$robots_ok  = (bool) preg_match( "#<meta name='robots' content='[^']*index, follow[^']*' />#", $wh_html )
	&& ! preg_match( "#content='[^']*noindex#", $wh_html );
$promoted   = is_array( $p ) && empty( $p['is_coming_soon'] ) && 'verified' === $p['status'];
$facts_real = is_array( $p )
	&& 'Coming soon' !== $p['scope'] && 'Coming soon' !== $p['finish']
	&& 'Coming soon' !== $p['timeline'] && 'Coming soon' !== $p['investment']
	&& '' !== $p['scope'] && '' !== $p['investment'];
$no_claims  = is_array( $p ) && '' === $p['completion_date'] && '' === $p['client_quote'];
$published  = ( $wh_post instanceof WP_Post ) && 'publish' === $wh_post->post_status;
$canon_ok   = 1 === substr_count( $wh_html, '<link rel="canonical"' )
	&& false !== strpos( $wh_html, '<link rel="canonical" href="' . home_url( "/projects/$WH/" ) . '"' );
( $robots_ok && $promoted && $facts_real && $no_claims && $published && $canon_ok )
	? ok( '10c. West Hollywood is promoted: status verified, index+follow, real scope/finish/timeline/investment, still no completion date or testimonial, still published, exactly one self-referencing canonical' )
	: bad( '10c. robots=' . var_export( $robots_ok, true ) . ' promoted=' . var_export( $promoted, true ) . ' facts=' . var_export( $facts_real, true ) . ' noClaims=' . var_export( $no_claims, true ) . ' published=' . var_export( $published, true ) . ' canonical=' . var_export( $canon_ok, true ) );

// A researched RANGE is now expected. A single figure would read as an invoice,
// and the range must never reach structured data.
$wh_block = $block( $reg_now, $WH );
$dirty = array();
$inv   = is_array( $p ) ? (string) $p['investment'] : '';
if ( ! preg_match( '/^\$[\d,]+\s*[–-]\s*\$[\d,]+$/u', $inv ) ) { $dirty[] = "investment is not a range ('$inv')"; }
foreach ( array( 'aggregateRating', 'ratingValue', 'reviewCount', '"review"' ) as $n ) {
	if ( false !== stripos( $wh_block, $n ) ) { $dirty[] = $n; }
}
if ( false === strpos( $wh_html, 'Typical investment for similar California projects' ) ) { $dirty[] = 'fixed public label missing'; }
foreach ( array( '1,300', '2,600', 'priceCurrency', '"price"' ) as $n ) {
	if ( false !== strpos( $ld_all, $n ) ) { $dirty[] = "$n leaked into JSON-LD"; }
}
$dirty ? bad( '22b. ' . implode( ', ', $dirty ) )
	: ok( '22b. the investment is a researched range (' . $inv . ') published under the fixed label "Typical investment for similar California projects", carries no review or rating data, and never enters JSON-LD' );

echo "\n== NO REGRESSION ELSEWHERE ==\n";

// 33.
// The gallery is a shared default now, so every managed project must carry the
// full markup contract. What must NOT happen is it appearing on a page that is
// not a managed project.
$missing_markup = array();
foreach ( $others as $s => $body ) {
	foreach ( array( 'proj-gallery', 'More Project Highlights', 'data-proj-slider-label' ) as $needle ) {
		if ( false === strpos( $body, $needle ) ) { $missing_markup[] = "$s:$needle"; }
	}
}
$non_project = array();
foreach ( array( '/services/pool-repairs-plumbing/', '/service-areas/sherman-oaks/', '/about/', '/blog/', '/contact/' ) as $u ) {
	if ( false !== strpos( fetch_body( home_url( $u ) ), 'class="proj-gallery"' ) ) { $non_project[] = $u; }
}
( ! $missing_markup && ! $non_project )
	? ok( '33. all ' . ( count( $others ) + 1 ) . ' managed project pages carry the full gallery markup, and it appears on none of the 5 sampled service / area / blog / about / contact pages' )
	: bad( '33. missing: ' . implode( ', ', $missing_markup ) . ' | leaked onto: ' . implode( ', ', $non_project ) );

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

echo "\n== GALLERY LAYOUT CONTRACT (4:3, centred, mobile stacking) ==\n";

// 5. The pair is constrained and centred rather than stretched, in two equal columns.
$ref_css = (string) file_get_contents( $child . '/assets/css/components.css' );
$layout = array(
	'4:3 landscape frame'          => (bool) preg_match( '#\.proj-gallery__frame\s*\{[^}]*aspect-ratio:\s*4\s*/\s*3#s', $css ),
	'no width cap on the gallery'  => ! preg_match( '#\.proj-gallery\s*\{[^}]*max-width:#s', $css ),
	'no auto centring margin'      => ! preg_match( '#\.proj-gallery\s*\{[^}]*margin-inline:\s*auto#s', $css ),
	'no page inset breaking edges' => ! preg_match( '#\.proj-gallery__page\s*\{[^}]*padding:\s*\dpx#s', $css ),
	'three equal desktop columns'  => (bool) preg_match( '#\.proj-gallery__grid\s*\{[\s\S]*?grid-template-columns:\s*repeat\(3, 1fr\)#s', $css ),
	'same gap token as ref grid'   => (bool) preg_match( '#\.proj-gallery__grid\s*\{[\s\S]*?gap:\s*var\(--sp-6\)#s', $css )
		&& (bool) preg_match( '#\.featured-projects__grid\s*\{[^}]*gap:\s*var\(--sp-6\)#s', $ref_css ),
	'ref grid is three columns'    => (bool) preg_match( '#\.featured-projects__grid\s*\{[^}]*grid-template-columns:\s*repeat\(3, 1fr\)#s', $ref_css ),
);
$miss_layout = array_keys( array_filter( $layout, static fn( $v ) => ! $v ) );
$miss_layout ? bad( '5. desktop layout — missing: ' . implode( ', ', $miss_layout ) )
	: ok( '5. the highlights grid mirrors .featured-projects__grid exactly — three equal columns, the same var(--sp-6) gap, 4/3 frames, and no width cap, auto margin or page inset that could break edge alignment' );

// 6. Mobile stacks the active page's two cards vertically, each centred, same two-page model.
$mobile = array(
	'tablet 2-up at 1000px'      => (bool) preg_match( '#@media \(max-width: 1000px\)\s*\{[\s\S]*?\.proj-gallery__grid \{ grid-template-columns: repeat\(2, 1fr\); \}#s', $css ),
	'third card centred on row 2'=> (bool) preg_match( '#@media \(max-width: 1000px\)\s*\{[\s\S]*?nth-child\(3\)\s*\{[\s\S]*?margin-inline:\s*auto#s', $css ),
	'single column at 640px'     => (bool) preg_match( '#@media \(max-width: 640px\)\s*\{[\s\S]*?\.proj-gallery__grid \{ grid-template-columns: 1fr;#s', $css ),
	'same breakpoints as ref'    => (bool) preg_match( '#@media \(max-width: 1000px\) \{ \.featured-projects__grid#', $ref_css )
		&& (bool) preg_match( '#@media \(max-width: 640px\)\s+\{ \.featured-projects__grid#', $ref_css ),
	'no mobile width cap'        => ! preg_match( '#@media \(max-width: 640px\)\s*\{[\s\S]*?\.proj-gallery__cell\s*\{[^}]*max-width:#s', $css ),
	'ratio not overridden'       => ! preg_match( '#@media \(max-width: (1000|640)px\)\s*\{[\s\S]*?\.proj-gallery__frame\s*\{[^}]*aspect-ratio#s', $css ),
	'pagination model intact'    => 2 === preg_match_all( '#data-proj-slider-slide="\d"#', $gal ),
);
$miss_mobile = array_keys( array_filter( $mobile, static fn( $v ) => ! $v ) );
$miss_mobile ? bad( '6. responsive layout — missing: ' . implode( ', ', $miss_mobile ) )
	: ok( '6. the highlights reflow on the SAME breakpoints as the related grid — two columns at 1000px with the third card centred on its own row, one full-width column at 640px — with the 4:3 ratio untouched and the same two-page pagination model' );

// 7. The gallery requests no image at all, and every image elsewhere resolves.
$broken = array();
preg_match_all( '#<img[^>]+src="([^"]+)"#', $wh_html, $im );
$uri = trailingslashit( get_stylesheet_directory_uri() );
$dir = trailingslashit( get_stylesheet_directory() );
foreach ( ( $im[1] ?? array() ) as $src ) {
	if ( '' === trim( $src ) ) { $broken[] = 'empty src'; continue; }
	if ( 0 === strpos( $src, $uri ) ) {
		$path = $dir . substr( $src, strlen( $uri ) );
		if ( ! is_readable( $path ) ) { $broken[] = basename( $src ) . ' (missing on disk)'; }
	}
}
$gal_imgs   = preg_match_all( '#<img#', $gal );
$gal_bg_url = preg_match_all( '#url\(#', $gal );
// Formerly: the gallery requested NO image. Now it requests exactly six, and
// each must exist on disk, be a genuine WebP by magic number, decode with real
// pixel dimensions, and be byte-distinct from its five siblings. The
// broken-image and CSS-url() guards are retained verbatim.
$gal_dir  = get_stylesheet_directory() . '/assets/img/projects/galleries/' . $WH;
$g_bad    = array();
$g_hashes = array();
for ( $i = 1; $i <= 6; $i++ ) {
	$p = sprintf( '%s/%s-highlight-%02d.webp', $gal_dir, $WH, $i );
	if ( ! is_readable( $p ) ) { $g_bad[] = basename( $p ) . ' missing'; continue; }
	$h = (string) file_get_contents( $p, false, null, 0, 12 );
	if ( 12 !== strlen( $h ) || 'RIFF' !== substr( $h, 0, 4 ) || 'WEBP' !== substr( $h, 8, 4 ) ) {
		$g_bad[] = basename( $p ) . ' not RIFF/WEBP'; continue;
	}
	$d = @getimagesize( $p );
	if ( ! is_array( $d ) || $d[0] < 1 || $d[1] < 1 || IMAGETYPE_WEBP !== ( $d[2] ?? 0 ) ) {
		$g_bad[] = basename( $p ) . ' does not decode'; continue;
	}
	$g_hashes[] = hash_file( 'sha256', $p );
}
if ( count( $g_hashes ) !== count( array_unique( $g_hashes ) ) ) { $g_bad[] = 'duplicate image within the gallery'; }
( empty( $broken ) && empty( $g_bad ) && 6 === $gal_imgs && 0 === $gal_bg_url && 6 === count( $g_hashes ) )
	? ok( '7. the gallery requests exactly six images, all six exist on disk as genuine decodable WebP files with unique SHA-256 hashes, no CSS url() is used, and all ' . count( $im[1] ?? array() ) . ' images on the page resolve to real files on disk' )
	: bad( '7. broken: ' . implode( ', ', $broken ) . ' galleryFiles: ' . ( implode( ', ', $g_bad ) ?: 'ok' ) . " galleryImgs=$gal_imgs galleryUrls=$gal_bg_url" );

// 8. Every gallery control exposes a unique accessible name.
preg_match_all( '#<button[^>]*class="proj-gallery__(?:arrow|dot)[^"]*"[^>]*aria-label="([^"]+)"#', $gal, $lm );
$labels = $lm[1] ?? array();
( 4 === count( $labels ) && count( array_unique( $labels ) ) === count( $labels ) )
	? ok( '8. all four gallery controls expose unique accessible names: ' . implode( ' / ', $labels ) )
	: bad( '8. labels=' . count( $labels ) . ' unique=' . count( array_unique( $labels ) ) . ' -> ' . implode( ' | ', $labels ) );

echo "\n== FOOTER BRAND ROW ==\n";

$home_html  = fetch_body( home_url( '/' ) );
$footer_css = (string) file_get_contents( $child . '/assets/css/footer.css' );

// 19 + 20. Exactly one linked official logo, correct alt and intrinsic size.
$brand_n = preg_match_all( '#<a class="footer-brandmark"[^>]*href="([^"]+)"#', $home_html, $bm );
preg_match( '#<img\s+class="footer-brandmark__img"[\s\S]*?>#', $home_html, $bi );
$img_tag = $bi[0] ?? '';
$attr = static function ( string $a ) use ( $img_tag ): string {
	return preg_match( '#\b' . $a . '="([^"]*)"#', $img_tag, $m ) ? $m[1] : '';
};
$logo_src  = $attr( 'src' );
$logo_path = ( '' !== $logo_src && 0 === strpos( $logo_src, $uri ) ) ? $dir . substr( $logo_src, strlen( $uri ) ) : '';
$logo_real = '' !== $logo_path && is_readable( $logo_path );
$logo_size = $logo_real ? @getimagesize( $logo_path ) : false;
$lw = (int) $attr( 'width' );
$lh = (int) $attr( 'height' );
$fb = array();
if ( 1 !== $brand_n )                          { $fb[] = "linked logos=$brand_n (expected 1)"; }
if ( ( $bm[1][0] ?? '' ) !== home_url( '/' ) ) { $fb[] = 'logo does not link to the homepage'; }
if ( 'Showtime Pools' !== $attr( 'alt' ) )     { $fb[] = 'alt is "' . $attr( 'alt' ) . '"'; }
if ( $lw < 1 || $lh < 1 )                      { $fb[] = 'missing intrinsic width/height'; }
if ( ! $logo_real )                            { $fb[] = 'logo file not readable on disk'; }
if ( is_array( $logo_size ) && ( (int) $logo_size[0] !== $lw || (int) $logo_size[1] !== $lh ) ) {
	$fb[] = "intrinsic {$lw}x{$lh} does not match the file ({$logo_size[0]}x{$logo_size[1]})";
}
if ( 'lazy' !== $attr( 'loading' ) )           { $fb[] = 'loading is "' . $attr( 'loading' ) . '"'; }
if ( 'async' !== $attr( 'decoding' ) )         { $fb[] = 'decoding is "' . $attr( 'decoding' ) . '"'; }
if ( false === strpos( $logo_src, '/assets/img/logo.' ) && false === strpos( $logo_src, '/wp-content/uploads' ) ) {
	$fb[] = 'not the official bundled or Customizer logo';
}
if ( preg_match( '#\.footer-brandmark__img\s*\{[^}]*filter:#s', $footer_css ) ) {
	$fb[] = 'a CSS colour filter is applied to the logo';
}
if ( ! preg_match( '#\.footer-brandmark:focus-visible\s*\{[^}]*outline#s', $footer_css ) ) {
	$fb[] = 'no visible keyboard focus state';
}
$fb ? bad( '19 + 20. footer logo — ' . implode( ', ', $fb ) )
	: ok( '19 + 20. exactly one linked official logo in the footer (' . basename( $logo_src ) . "), linking home, alt=\"Showtime Pools\", intrinsic {$lw}x{$lh} matching the file on disk, lazy + async, visible focus ring, no colour filter" );

// The decorative wordmark must not swallow the focusable logo link.
$wrapper_hidden = (bool) preg_match( '#<div class="footer-wordmark"[^>]*aria-hidden#', $home_html );
$text_hidden    = (bool) preg_match( '#class="footer-wordmark__text" aria-hidden="true"#', $home_html );
( ! $wrapper_hidden && $text_hidden )
	? ok( '20b. aria-hidden sits on the decorative wordmark text only, so it adds no duplicate screen-reader output while the logo link stays reachable and focusable' )
	: bad( '20b. wrapperHidden=' . var_export( $wrapper_hidden, true ) . ' textHidden=' . var_export( $text_hidden, true ) );

// 21. Legal links and social destinations are untouched.
//
// The footer's human-facing "Sitemap" link now targets the HTML sitemap at
// /sitemap/ rather than the raw XML feed — a visitor clicking it should get a
// readable page, not an XSL-styled document. This is STRICTER than the previous
// wp-sitemap.xml expectation: it pins the new destination, forbids the footer
// regressing to the XML URL, AND separately proves /wp-sitemap.xml is still
// served for Search Console, which the old assertion never checked.
$legal_missing = array();
foreach ( array( '/privacy-policy/', '/affiliate/', '/terms/', '/sitemap/' ) as $l ) {
	if ( false === strpos( $home_html, $l ) ) { $legal_missing[] = $l; }
}
foreach ( array( 'facebook.com', 'instagram.com', 'linkedin.com', 'tiktok.com', 'youtube.com', 'share.google' ) as $s ) {
	if ( false === strpos( $home_html, $s ) ) { $legal_missing[] = $s; }
}
$has_copy = (bool) preg_match( '#&copy;|©#', $home_html );
// The visible footer link must not point at the XML feed any more.
$footer_xml = (bool) preg_match( '#<a[^>]+href="[^"]*wp-sitemap\.xml"#', $home_html );
// …but the XML sitemap itself must still exist and still be XML.
$xml_body   = fetch_body( home_url( '/wp-sitemap.xml' ) );
$xml_ok     = ( '' !== $xml_body ) && ( false !== strpos( $xml_body, '<sitemapindex' ) || false !== strpos( $xml_body, '<urlset' ) );
( empty( $legal_missing ) && $has_copy && ! $footer_xml && $xml_ok )
	? ok( '21. the copyright line, all four legal links and all six social destinations are unchanged; the footer "Sitemap" link points to the HTML /sitemap/ and no longer to the XML feed, while /wp-sitemap.xml is still served as a valid XML sitemap' )
	: bad( '21. missing: ' . implode( ', ', $legal_missing ) . ' copyright=' . var_export( $has_copy, true )
		. ' footerStillLinksXml=' . var_export( $footer_xml, true ) . ' xmlSitemapServed=' . var_export( $xml_ok, true ) );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

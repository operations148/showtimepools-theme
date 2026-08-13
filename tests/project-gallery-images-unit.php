<?php
/**
 * Project gallery PHOTOGRAPHS — every managed project with real highlight images.
 *
 * Covers the real-image population of the shared "More Project Highlights"
 * gallery: that each project resolves only its OWN photographs, that every
 * published derivative is a genuine, decodable, uniquely-hashed WebP that is
 * actually served, that alt text is real and unique, that projects without
 * photographs still show six pending slots, and that the untracked source
 * originals — when present — are byte-for-byte unchanged.
 *
 * Counts are derived from the registry, never hardcoded, so adding a project's
 * photographs cannot silently leave an assertion behind.
 *
 * Run:  php tests/project-gallery-images-unit.php
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
/** A documented skip: the assertion could not run, and WHY is recorded. */
function skipped( string $m ): void { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

function g_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
function g_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}
/** Magic-number WebP sniff — never trusts the file extension. */
function g_is_webp( string $path ): bool {
	if ( ! is_readable( $path ) ) { return false; }
	$h = (string) file_get_contents( $path, false, null, 0, 12 );
	return 12 === strlen( $h ) && 'RIFF' === substr( $h, 0, 4 ) && 'WEBP' === substr( $h, 8, 4 );
}

$child    = get_stylesheet_directory();
$gal_root = $child . '/assets/img/projects/galleries';
$cmp_dir  = $child . '/assets/img/projects/comparisons';
$incoming = $child . '/assets/img/projects/_incoming/more_project_highlights';

$ALL_MANAGED = array();
foreach ( \Showtime\Projects::all() as $e ) {
	if ( ! empty( $e['managed'] ) ) { $ALL_MANAGED[] = $e['slug']; }
}

// Derive, from the registry, which projects publish photographs and how many.
$resolved   = array();
$with_real  = array();   // slug => count of ready slots
$no_real    = array();   // slugs still fully pending
foreach ( $ALL_MANAGED as $slug ) {
	$slots            = showtime_project_gallery( showtime_project_data( $slug ) );
	$resolved[ $slug ] = $slots;
	$ready            = 0;
	foreach ( $slots as $s ) { if ( 'ready' === $s['status'] ) { $ready++; } }
	if ( $ready > 0 ) { $with_real[ $slug ] = $ready; } else { $no_real[] = $slug; }
}

$bodies = array();
foreach ( $ALL_MANAGED as $s ) { $bodies[ $s ] = g_fetch( home_url( "/projects/$s/" ) ); }

echo "== PROJECTS WITH REAL PHOTOGRAPHS ==\n";

/* 1. The set of projects publishing photographs is exactly the expected six. */
$EXPECT_WITH_REAL = array(
	'sherman-oaks-mid-century-remodel', 'encino-estate-new-build', 'studio-city-modern-automation',
	'beverly-hills-luxe-spa-renovation', 'tarzana-resort-style-finish', 'woodland-hills-tile-coping-refresh',
	'van-nuys-pool-project', 'toluca-lake-pool-project', 'north-hollywood-pool-project',
	'burbank-pool-project', 'calabasas-pool-project', 'west-hollywood-pool-project',
	'bel-air-pool-project',
);
$got = array_keys( $with_real );
sort( $got );
$want = $EXPECT_WITH_REAL;
sort( $want );
( $got === $want )
	? ok( '1. exactly the ' . count( $want ) . ' expected managed projects publish highlight photographs: ' . implode( ', ', $want ) )
	: bad( '1. publishing projects: ' . implode( ', ', $got ) );

/* 2 + 3. Each publishing project resolves only its own files, in slot order. */
$per_project_bad = array();
foreach ( $with_real as $slug => $n ) {
	$files = array();
	foreach ( $resolved[ $slug ] as $i => $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		$files[ $i ] = basename( (string) $s['url'] );
	}
	foreach ( $files as $i => $f ) {
		$expect = sprintf( '%s-highlight-%02d.webp', $slug, $i + 1 );
		if ( $f !== $expect ) { $per_project_bad[] = "$slug slot " . ( $i + 1 ) . " = $f (expected $expect)"; }
	}
}
$own_only = array_values( array_filter( $per_project_bad, static fn( $x ) => false !== strpos( $x, 'expected' ) ) );
$own_only
	? bad( '2. a project resolved a file that is not its own: ' . implode( '; ', $own_only ) )
	: ok( '2. every publishing project resolves only files named for its own slug' );

/* 3. Slot order is exactly 01..06 ascending, with no gaps or reordering. */
$order_bad = array();
foreach ( $with_real as $slug => $n ) {
	$seen = array();
	foreach ( $resolved[ $slug ] as $i => $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		if ( ! preg_match( '/-highlight-(\d{2})\.webp$/', (string) $s['url'], $m ) ) { $order_bad[] = "$slug unparsable slot"; continue; }
		$seen[] = (int) $m[1];
		if ( (int) $m[1] !== $i + 1 ) { $order_bad[] = "$slug position " . ( $i + 1 ) . ' holds highlight-' . $m[1]; }
	}
	$sorted = $seen; sort( $sorted );
	if ( $seen !== $sorted ) { $order_bad[] = "$slug not ascending: " . implode( ',', $seen ); }
}
$order_bad
	? bad( '3. ' . implode( '; ', $order_bad ) )
	: ok( '3. every gallery holds its slots in ascending highlight-01..NN order, each file in the position its number names' );

/* 4. Total published derivatives, and every one exists / is a real WebP. */
$published = array();
foreach ( $with_real as $slug => $n ) {
	foreach ( $resolved[ $slug ] as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		$published[] = $gal_root . '/' . $slug . '/' . basename( (string) $s['url'] );
	}
}
$total_expected = array_sum( $with_real );
( count( $published ) === $total_expected && $total_expected > 0 )
	? ok( "4. the registry publishes $total_expected highlight derivatives across " . count( $with_real ) . ' projects' )
	: bad( '4. published=' . count( $published ) . ' expected=' . $total_expected );

/* 5. Every published file exists and is a genuine, decodable WebP. */
$exist_bad = array();
$mime_bad  = array();
foreach ( $published as $p ) {
	if ( ! is_readable( $p ) ) { $exist_bad[] = basename( $p ) . ' missing'; continue; }
	if ( ! g_is_webp( $p ) ) { $exist_bad[] = basename( $p ) . ' not RIFF/WEBP'; continue; }
	$d = @getimagesize( $p );
	if ( ! is_array( $d ) || $d[0] < 1 || $d[1] < 1 || IMAGETYPE_WEBP !== ( $d[2] ?? 0 ) ) {
		$exist_bad[] = basename( $p ) . ' does not decode as WebP'; continue;
	}
	if ( 'image/webp' !== ( $d['mime'] ?? '' ) ) { $mime_bad[] = basename( $p ) . ' mime=' . ( $d['mime'] ?? '?' ); }
}
$exist_bad
	? bad( '5. ' . implode( ', ', $exist_bad ) )
	: ok( '5. all ' . count( $published ) . ' published derivatives exist, carry the RIFF/WEBP magic number and decode as IMAGETYPE_WEBP with real pixel dimensions' );

/* 6. Reported MIME type is image/webp. */
$mime_bad
	? bad( '6. ' . implode( ', ', $mime_bad ) )
	: ok( '6. all ' . count( $published ) . ' published derivatives report mime type image/webp' );

/* 7. Unique hashes across every published derivative. */
$sums = array();
foreach ( $published as $p ) { if ( is_readable( $p ) ) { $sums[ $p ] = hash_file( 'sha256', $p ); } }
$dupe_pairs = array();
$seen = array();
foreach ( $sums as $p => $h ) {
	if ( isset( $seen[ $h ] ) ) { $dupe_pairs[] = basename( $seen[ $h ] ) . ' == ' . basename( $p ); }
	else { $seen[ $h ] = $p; }
}
( empty( $dupe_pairs ) && count( $sums ) === count( $published ) )
	? ok( '7. all ' . count( $sums ) . ' published derivatives have unique SHA-256 hashes — no photograph is reused within or across projects' )
	: bad( '7. duplicate derivatives: ' . implode( ', ', $dupe_pairs ) );

/* 8. No highlight duplicates a before/after comparison asset. */
$cmp_sums = array();
foreach ( (array) glob( "$cmp_dir/*.webp" ) as $f ) { $cmp_sums[ basename( $f ) ] = hash_file( 'sha256', $f ); }
$collide = array_intersect( $sums, $cmp_sums );
empty( $collide )
	? ok( '8. no published highlight duplicates any of the ' . count( $cmp_sums ) . ' before/after comparison assets' )
	: bad( '8. collides with comparison assets: ' . implode( ', ', array_map( 'basename', array_keys( $collide ) ) ) );

/* 9. No image crosses between project records. */
$cross = array();
$names = array();
foreach ( $with_real as $slug => $n ) {
	foreach ( $resolved[ $slug ] as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		if ( false === strpos( (string) $s['url'], "/galleries/$slug/" ) ) { $cross[] = "$slug -> {$s['url']}"; }
		$names[] = basename( (string) $s['url'] );
	}
}
$shared_name = count( $names ) !== count( array_unique( $names ) );
( ! $cross && ! $shared_name )
	? ok( '9. no image crosses between project records: every resolved URL lives under its own galleries/<slug>/ directory and no filename is shared' )
	: bad( '9. cross-project leakage: ' . implode( '; ', $cross ) . ' sharedFilenames=' . var_export( $shared_name, true ) );

/* 10. A project cannot borrow another project's photograph. */
$victim = 'sherman-oaks-mid-century-remodel';
$borrow = showtime_project_gallery( array(
	'slug'               => 'tarzana-resort-style-finish',
	'additional_gallery' => array_fill( 0, 3, array(
		'status' => 'ready', 'image' => "$victim-highlight-01.webp", 'alt' => 'borrowed', 'caption' => '',
	) ),
) );
( array() === $borrow )
	? ok( "10. a record cannot borrow another project's photograph: naming Sherman Oaks' real file under Tarzana fails the whole gallery closed" )
	: bad( '10. cross-project borrow resolved ' . count( $borrow ) . ' slots' );

/* 10b. The photograph withheld as a duplicate must never reach publication: not
 * as a derivative, not in the registry, not in any rendered page. */
$REJECTED_SHA  = '083197bdd1d9295dcd756dde5ad4adeba5c0fb823e35190bfb86f99d52969e00';
$REJECTED_NAME = 'tarzana-more-projects5.jpg';
$rej = array();
foreach ( $published as $p ) {
	if ( is_readable( $p ) && hash_file( 'sha256', $p ) === $REJECTED_SHA ) {
		$rej[] = 'published as ' . basename( $p );
	}
}
$reg_src = (string) file_get_contents( SHOWTIME_CORE_DIR . '/includes/data/projects.php' );
if ( false !== strpos( $reg_src, $REJECTED_NAME ) ) { $rej[] = 'referenced in the registry'; }
foreach ( $bodies as $slug => $b ) {
	if ( false !== strpos( $b, $REJECTED_NAME ) ) { $rej[] = "rendered on $slug"; }
}
// ...and it must still be sitting untouched on disk, not deleted to make this pass.
$rej_path = "$gal_root/tarzana-resort-style-finish/$REJECTED_NAME";
if ( ! is_readable( $rej_path ) ) { $rej[] = 'the withheld source was deleted'; }
elseif ( hash_file( 'sha256', $rej_path ) !== $REJECTED_SHA ) { $rej[] = 'the withheld source was modified'; }
$rej
	? bad( '10b. withheld duplicate leaked — ' . implode( ', ', $rej ) )
	: ok( '10b. the photograph withheld as a duplicate is published nowhere, referenced in no registry record and rendered on no page, while remaining untouched on disk' );

echo "\n== PLACEHOLDER STATE ==\n";

/* 11. Projects without photographs keep six inert Coming Soon slots. */
$pend_bad = array();
foreach ( $no_real as $slug ) {
	$pend = 0;
	foreach ( $resolved[ $slug ] as $s ) {
		if ( 'coming_soon' !== $s['status'] ) { continue; }
		$pend++;
		if ( '' !== $s['url'] || '' !== $s['alt'] || '' !== $s['caption'] || 0 !== $s['width'] || 0 !== $s['height'] ) {
			$pend_bad[] = "$slug slot carries copy";
		}
	}
	if ( 6 !== $pend ) { $pend_bad[] = "$slug pending=$pend"; }
}
$pend_bad
	? bad( '11. ' . implode( ', ', $pend_bad ) )
	: ok( '11. all ' . count( $no_real ) . ' projects without photographs keep six Coming Soon placeholders carrying no url, alt, caption or dimensions' );

/* 12. A gallery with no pending slot renders no placeholder and no pending note;
 * a gallery that still has one keeps both. Driven by resolver state, not by slug. */
$note_bad = array();
foreach ( $ALL_MANAGED as $slug ) {
	$pending = 0;
	foreach ( $resolved[ $slug ] as $s ) { if ( 'ready' !== $s['status'] ) { $pending++; } }
	$cards = preg_match_all( '#proj-gallery__card--pending#', $bodies[ $slug ] );
	$text  = substr_count( $bodies[ $slug ], 'Project photo coming soon' );
	$note  = ( false !== strpos( $bodies[ $slug ], 'Additional project photos will be added soon' ) );
	if ( $cards !== $pending || $text !== $pending ) {
		$note_bad[] = "$slug pendingSlots=$pending cards=$cards text=$text";
	}
	if ( $note !== ( $pending > 0 ) ) {
		$note_bad[] = "$slug note=" . var_export( $note, true ) . " but pending=$pending";
	}
}
$note_bad
	? bad( '12. ' . implode( '; ', $note_bad ) )
	: ok( '12. placeholder cards, "coming soon" text and the "more photos coming" note appear exactly when — and only when — a slot is genuinely still pending' );

echo "\n== GALLERY SHAPE IS UNCHANGED ==\n";

/* 13. Six slots on every managed project. 14. Two pages of three. 15. Two dots. */
$slot_bad = array(); $page_bad = array(); $dot_bad = array();
foreach ( $ALL_MANAGED as $slug ) {
	$p     = showtime_project_data( $slug );
	$slots = showtime_project_gallery( $p );
	$pages = showtime_project_gallery_pages( $p );
	$sizes = array_map( 'count', $pages );
	$cells = preg_match_all( '#proj-gallery__cell#', $bodies[ $slug ] );
	$dots  = preg_match_all( '#data-proj-slider-dot=#', $bodies[ $slug ] );
	$gals  = substr_count( $bodies[ $slug ], 'class="proj-gallery"' );
	if ( 6 !== count( $slots ) || 6 !== $cells || 1 !== $gals ) {
		$slot_bad[] = "$slug slots=" . count( $slots ) . " cells=$cells galleries=$gals";
	}
	if ( 2 !== count( $pages ) || array( 3, 3 ) !== $sizes ) {
		$page_bad[] = "$slug pages=" . count( $pages ) . ' sizes=' . implode( '/', $sizes );
	}
	if ( 2 !== $dots ) { $dot_bad[] = "$slug dots=$dots"; }
}
$slot_bad ? bad( '13. ' . implode( '; ', $slot_bad ) )
	: ok( '13. all ' . count( $ALL_MANAGED ) . ' managed projects still render exactly one gallery containing exactly six slots' );
$page_bad ? bad( '14. ' . implode( '; ', $page_bad ) )
	: ok( '14. every gallery still chunks into exactly two pages of three' );
$dot_bad ? bad( '15. ' . implode( '; ', $dot_bad ) )
	: ok( '15. every gallery still exposes exactly two pagination dots' );

echo "\n== ALT TEXT ==\n";

/* 16. Unique, non-empty, markup-free, escaped alt text. */
$alts    = array();
$alt_bad = array();
foreach ( $with_real as $slug => $n ) {
	foreach ( $resolved[ $slug ] as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		$a = (string) $s['alt'];
		if ( '' === trim( $a ) ) { $alt_bad[] = "$slug empty alt"; continue; }
		if ( $a !== wp_strip_all_tags( $a ) ) { $alt_bad[] = "$slug alt contains markup"; }
		if ( false === strpos( $bodies[ $slug ], 'alt="' . esc_attr( $a ) . '"' ) ) {
			$alt_bad[] = "$slug alt not rendered escaped";
		}
		$alts[] = $a;
	}
}
$dupe_alt = array_keys( array_filter( array_count_values( $alts ), static fn( $c ) => $c > 1 ) );
if ( $dupe_alt ) { $alt_bad[] = 'duplicate alt text: ' . substr( implode( ' | ', $dupe_alt ), 0, 120 ); }
$alt_bad
	? bad( '16. ' . implode( ', ', array_unique( $alt_bad ) ) )
	: ok( '16. all ' . count( $alts ) . ' published images carry unique, non-empty, markup-free alt text, rendered escaped in the page' );

/* 16b. Alt text asserts nothing unverifiable. */
$banned = array( 'best', 'luxury', 'luxurious', 'premium', 'stunning', 'beautiful', 'award', 'gorgeous',
	'$', 'guarantee', 'warranty', 'permit', 'satisfied', 'happy customer', 'before and after', 'brand new' );
$leak = array();
foreach ( $alts as $a ) {
	foreach ( $banned as $b ) {
		if ( false !== stripos( $a, $b ) ) { $leak[] = "\"$b\" in: " . substr( $a, 0, 44 ); }
	}
}
$leak
	? bad( '16b. alt text makes unsupported claims — ' . implode( '; ', $leak ) )
	: ok( '16b. no alt text contains pricing, a warranty or permit claim, a rating, a satisfaction claim, a before/after assertion or a marketing superlative' );

echo "\n== RENDERED OUTPUT ==\n";

/* 17. No source path, source filename or source extension reaches the browser. */
$leaked = array();
foreach ( $bodies as $slug => $b ) {
	if ( false !== stripos( $b, '_incoming' ) ) { $leaked[] = "$slug _incoming"; }
	if ( preg_match( '#<div class="proj-gallery".*?<div class="proj-gallery__nav#s', $b, $m ) ) {
		if ( preg_match( '#src="[^"]+\.(jpe?g|png)"#i', $m[0] ) ) { $leaked[] = "$slug non-webp img in gallery"; }
		// Both source naming conventions: the original `*-more-projects*` files and
		// the normalized `*-source-NN` files. Neither may ever be served.
		if ( preg_match( '#more-projects?\d*\.(jpe?g|png)#i', $m[0] ) ) { $leaked[] = "$slug source filename in gallery"; }
		if ( preg_match( '#-source-\d{2}\.(jpe?g|png|webp)#i', $m[0] ) ) { $leaked[] = "$slug normalized source filename in gallery"; }
	}
}
$leaked
	? bad( '17. ' . implode( ', ', $leaked ) )
	: ok( '17. no `_incoming` path, source filename or source JPEG/PNG appears in the rendered gallery of any of the ' . count( $bodies ) . ' project pages' );

/* 18. HTTP 200. 19. Intrinsic dimensions match the real file. 20. lazy + async. */
$http_bad = array(); $dim_bad = array(); $attr_bad = array(); $count_bad = array();
$checked = 0;
foreach ( $with_real as $slug => $n ) {
	preg_match_all( '#<img[^>]+src="([^"]+highlight-\d{2}\.webp)"[^>]*>#', $bodies[ $slug ], $m, PREG_SET_ORDER );
	if ( count( $m ) !== $n ) { $count_bad[] = "$slug rendered " . count( $m ) . " highlight images, expected $n"; }
	foreach ( $m as $one ) {
		$checked++;
		$url = html_entity_decode( $one[1] );
		if ( 200 !== g_status( $url ) ) { $http_bad[] = basename( $url ) . ' HTTP ' . g_status( $url ); }
		$file = $gal_root . '/' . $slug . '/' . basename( $url );
		$d    = @getimagesize( $file );
		if ( ! preg_match( '#width="(\d+)"#', $one[0], $w ) || ! preg_match( '#height="(\d+)"#', $one[0], $h ) ) {
			$dim_bad[] = basename( $url ) . ' missing width/height';
		} elseif ( ! is_array( $d ) || (int) $w[1] !== $d[0] || (int) $h[1] !== $d[1] ) {
			$dim_bad[] = basename( $url ) . ' declared ' . $w[1] . 'x' . $h[1]
				. ' but file is ' . ( $d[0] ?? '?' ) . 'x' . ( $d[1] ?? '?' );
		}
		if ( false === strpos( $one[0], 'loading="lazy"' ) ) { $attr_bad[] = basename( $url ) . ' not lazy'; }
		if ( false === strpos( $one[0], 'decoding="async"' ) ) { $attr_bad[] = basename( $url ) . ' not async'; }
	}
}
if ( $count_bad ) { $http_bad = array_merge( $count_bad, $http_bad ); }
$http_bad ? bad( '18. ' . implode( ', ', $http_bad ) )
	: ok( "18. all $checked rendered highlight images are served and return HTTP 200 locally" );
$dim_bad ? bad( '19. ' . implode( ', ', $dim_bad ) )
	: ok( "19. all $checked rendered images declare intrinsic width/height matching the real file on disk (no layout shift)" );
$attr_bad ? bad( '20. ' . implode( ', ', $attr_bad ) )
	: ok( "20. all $checked rendered images keep loading=\"lazy\" and decoding=\"async\"" );

/* 21. Page 2 ships in the initial server-rendered HTML. */
$p2_bad = array();
foreach ( $with_real as $slug => $n ) {
	if ( ! preg_match( '#data-proj-slider-slide="1"(.*?)(?=<div class="proj-gallery__nav)#s', $bodies[ $slug ], $m ) ) {
		$p2_bad[] = "$slug no page 2 block"; continue;
	}
	$imgs = preg_match_all( '#highlight-\d{2}\.webp#', $m[1] );
	$pend = preg_match_all( '#proj-gallery__card--pending#', $m[1] );
	if ( 3 !== ( $imgs + $pend ) ) { $p2_bad[] = "$slug page2 images=$imgs pending=$pend"; }
}
$p2_bad
	? bad( '21. ' . implode( ', ', $p2_bad ) )
	: ok( '21. page 2 ships its three cards in the initial server-rendered HTML for every publishing project — no deferred fetch, no empty second page' );

echo "\n== NOTHING ELSE MOVED ==\n";

/* 22 + 23. Metadata, indexing, schema and sitemap totals unchanged; no gallery
 * photograph enters JSON-LD. */
$meta_bad = array();
$ld_bad   = array();
foreach ( $ALL_MANAGED as $slug ) {
	$b = $bodies[ $slug ];
	if ( ! preg_match( '#<meta name=[\'"]robots[\'"] content=[\'"]([^\'"]+)#i', $b, $m )
		|| false === stripos( $m[1], 'index' ) || false !== stripos( $m[1], 'noindex' ) ) {
		$meta_bad[] = "$slug robots";
	}
	if ( 1 !== preg_match_all( '#<link rel=[\'"]canonical[\'"]#i', $b ) ) { $meta_bad[] = "$slug canonical"; }
	if ( ! preg_match( '#<meta property=[\'"]og:image[\'"]#i', $b ) ) { $meta_bad[] = "$slug og:image"; }
	if ( ! preg_match( '#name=[\'"]twitter:card[\'"]#i', $b ) ) { $meta_bad[] = "$slug twitter"; }
	if ( preg_match_all( '#<script type="application/ld\+json">([\s\S]*?)</script>#', $b, $ld ) ) {
		$json = implode( ' ', $ld[1] );
		if ( false !== strpos( $json, 'highlight-' ) ) { $ld_bad[] = "$slug highlight in JSON-LD"; }
		if ( false !== strpos( $json, '/galleries/' ) ) { $ld_bad[] = "$slug galleries path in JSON-LD"; }
	}
}
$xml  = g_fetch( home_url( '/wp-sitemap-posts-project-1.xml' ) );
$nxml = substr_count( $xml, '/projects/' );
if ( 14 !== $nxml ) { $meta_bad[] = "xml sitemap project urls=$nxml"; }
$meta_bad
	? bad( '22. ' . implode( ', ', array_unique( $meta_bad ) ) )
	: ok( '22. all 14 project pages keep index,follow, exactly one canonical, og:image and twitter tags, and the XML sitemap still lists 14 project URLs' );
$ld_bad
	? bad( '23. ' . implode( ', ', array_unique( $ld_bad ) ) )
	: ok( '23. no gallery highlight photograph or galleries/ path appears in any JSON-LD block on any of the 14 project pages' );

/* 23c. Every gallery derivative that is already committed must be byte-for-byte
 * unchanged. Adding a new project's photographs must never re-encode, re-compress
 * or otherwise disturb a gallery that has already shipped. */
$root_dir = dirname( __DIR__ );
$tracked  = array();
exec( 'git -C ' . escapeshellarg( $root_dir ) . ' ls-files -- showtime-pools-child/assets/img/projects/galleries 2>&1', $tracked );
$moved = array();
foreach ( $tracked as $rel ) {
	$rel = trim( $rel );
	if ( '' === $rel || ! preg_match( '#-highlight-0[1-6]\.webp$#i', $rel ) ) { continue; }
	$out = array();
	exec( 'git -C ' . escapeshellarg( $root_dir ) . ' diff --name-only -- ' . escapeshellarg( $rel ) . ' 2>&1', $out );
	if ( array_filter( array_map( 'trim', $out ) ) ) { $moved[] = basename( $rel ); }
}
if ( empty( $tracked ) ) {
	skipped( '23c. previously published galleries — no tracked gallery assets found (not a git checkout?)' );
} elseif ( $moved ) {
	bad( '23c. already-published derivatives changed: ' . implode( ', ', $moved ) );
} else {
	$n_tracked = count( array_filter( $tracked, static fn( $r ) => (bool) preg_match( '#-highlight-0[1-6]\.webp$#i', trim( $r ) ) ) );
	ok( "23c. all $n_tracked already-committed gallery derivatives are byte-for-byte unchanged — this batch added new files without disturbing any shipped gallery" );
}

/* 24. SOURCE PRESERVATION — production-safe.
 *
 * The source originals are deliberately UNTRACKED: they are working files, not
 * deployable assets, so a production checkout legitimately has none of them.
 * Three outcomes, and only three:
 *   - every expected source present  -> verify each checksum and the exact count
 *   - none present                   -> ONE documented skip (production)
 *   - some present                   -> FAIL (partial, renamed, added or corrupted)
 * This never softens any other assertion, and no photograph is committed merely
 * to make it pass. */
$SOURCES = array(
	// Batch 1 — Sherman Oaks / Encino / Studio City, under _incoming/more_project_highlights/.
	"$incoming/sherman_oaks/sherman-aoks-more-projects1.jpeg"  => '1fb7ef6f86a8a13d20029f4a3f75e9c980b308326b512c9571061f1811251c0e',
	"$incoming/sherman_oaks/sherman-oaks-more-projects.jpeg"   => 'dba19eed887c28eeaf422932bfd8ce0e81afc40c58ecc74d98733de7385703f0',
	"$incoming/sherman_oaks/sherman-oaks-more-projects2.jpeg"  => '7a415f892a838b662d7d9f0607713ba1f41d0504ebf61cf1b422fc05bcfd3373',
	"$incoming/sherman_oaks/sherman-oaks-more-projects3.jpeg"  => 'a007806d620f14d0ac1de3b23c94c6e23dad106b3d5a872923e4e8304fef70f6',
	"$incoming/sherman_oaks/sherman-oaks-more-projects4.jpeg"  => '523eff53f80f34b9311b44e13ef2125125197e11ddef1adb6d318cf9172c41f8',
	"$incoming/sherman_oaks/sherman-oaks-more-projects5.jpeg"  => '9204c395b618e322a325c359578cc8a6ed32b0a4f2676fc6290ce379b2b90828',
	"$incoming/encino/encino-more-projects.jpeg"               => 'f146a87fbd722c98199bdb6a829a936b72c97ac568121fb4fb896106ca446d2f',
	"$incoming/encino/encino-more-projects1.jpeg"              => '2ff13e78bb39eec7d7c105d6c2f445fa66c36452b8c1356aa5496b120c7c5949',
	"$incoming/encino/encino-more-projects2.jpeg"              => 'da8f76f21035c9d0bba4de79dbc843a16a29f946334ae5406ce89015a1b01924',
	"$incoming/encino/encino-more-projects3.jpeg"              => '3b581cb8af9a6aa268f0c679af6b8e8a92c32dddafca3416a3c26cf8b9c69a22',
	"$incoming/encino/encino-more-projects4.jpeg"              => '04007f1df881b9869ed5d8236514d4614b1eb219f3db876f6e04203c72487d7a',
	"$incoming/encino/encino-more-projects5.jpg"               => '083197bdd1d9295dcd756dde5ad4adeba5c0fb823e35190bfb86f99d52969e00',
	"$incoming/studio_city/studio-city-more-project3.jpeg"     => '1994d618966400467702d317ff5efdb8fcaea68c4cd3a3a3989c06321a62ace3',
	"$incoming/studio_city/studio-city-more-projects.jpeg"     => '483075be202dcac039798a136a2c74e181196bb2d0c8b5e8a1df0d4eb6599e90',
	"$incoming/studio_city/studio-city-more-projects1.jpeg"    => 'de3d5d6750900299c510192ae1974228b3c3839453fe9aa0e066103ffe08d38c',
	"$incoming/studio_city/studio-city-more-projects2.jpg"     => 'be7884eda7a20d1915c839530f0ae03f364af7cb980a8660a97bffb389c0cdb3',
	"$incoming/studio_city/studio-city-more-projects4.jpeg"    => '9845cf110de27805b95c48bc5a9cc42109c1325b3b29625a9136865a0c9878ab',
	"$incoming/studio_city/studio-city-more-projects5.jpeg"    => '2b8617059954310b2b00312bd73144000bcd937bac189060abbecd0205f9f64e',
	// Batch 2 — Beverly Hills / Tarzana / Woodland Hills, beside their derivatives.
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-projects.jpg"    => '98b3775af881becb507558da899941fdcf07827c77366fd6311d68ffb3cd628a',
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-projects-1.jpeg" => '6834feeef38391154c7d5da2c63835aadf03b8d4e3b6eb3d2e1871ac008af772',
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-project2.jpeg"   => 'edc9ad8d1ea5d39b2583d8f8059c173c75b26cb11a5437dbd96e6ff19b559c99',
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-projects3.jpg"   => 'b0e79fe70400322b0c8988727e2d7c77ab951049f4ccf7428d4e170e31126e36',
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-projects4.jpg"   => '213411f3df062166e284f4a8f9667f47d99df71144f47ea9078c0e7cb453f8d5',
	"$gal_root/beverly-hills-luxe-spa-renovation/beverly-hills-more-projects5.jpeg"  => 'e9cffaf42fb010738aa8624fb3936c63076a1fb4eb0b68bef0f93491f48e9628',
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects.jpeg"               => '696503517de921daf23154fef32f307334498339c46ac7f84de62baeb1eae54f',
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects1.jpeg"              => '1e860d179ff842350bc60e6bc085819d77202f0559ca72d04a7ff9dde4f0d1cb',
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects2.jpg"               => '91422a0924c6c1d25147171d53e9ec50cdd8ca68e9cf65d96e85b91474ef996c',
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects3.jpeg"              => '354189f030e76ed20c4172dfe868b4c4196deda1f35bfcb712cb7588d87b0703',
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects4.jpeg"              => '668cffd90abdacf8234a5309a4a1138e165578300f215af7131df4567f4f4eed',
	// Withheld as a duplicate of encino-estate-new-build-highlight-06 and never
	// published. It stays in this manifest because it must remain untouched, and
	// it must never appear in the registry or in any derivative mapping.
	"$gal_root/tarzana-resort-style-finish/tarzana-more-projects5.jpg"               => '083197bdd1d9295dcd756dde5ad4adeba5c0fb823e35190bfb86f99d52969e00',
	// Replacement source for Tarzana slot 06. A JPEG carrying the production stem,
	// so it is a SOURCE fixture; its derivative is the .webp of the same stem,
	// which the derivative-exclusion pattern below keeps out of this scan.
	"$gal_root/tarzana-resort-style-finish/tarzana-resort-style-finish-highlight-06.jpeg" => 'ac08d4c06d2919f4d734317a0c262858fd03e5e5f29deeaa86438b2548d617a2',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-projects.jpeg"  => '3cff2f507c15d19b81f219a51aa3f7b935e736066de0960c9723a6ba76c8822b',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-projects1.jpg"  => '86a42eea82b6339e6710a4d17e40acb043da0c325bf7b4cb6417e276d39e46c1',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-projects2.jpeg" => 'dc6aa7a3f277fb151967e30d71d8e261ac50acd96128e14359887ef26ae4f362',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-project3.jpeg"  => '378506733cedc6d5084a0df2f081bc17eb394af61a10ca39ca266363fe178cf5',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-projects4.jpeg" => 'c986f5735bf3e0e6ab2fd5664e5e7e0daa83dbe3eff8dc368bde5f91b21cf8e2',
	"$gal_root/woodland-hills-tile-coping-refresh/woodland-hills-more-projects5.jpeg" => '5d2db4d3d55db10c7bb1aa2f7de9228e2a5acfa63dbeb0fed6a9be04da6aec46',
	// Batch 3 — Van Nuys / Toluca Lake / North Hollywood. Folder and filenames were
	// normalized to the canonical registry slug; the encoded bytes were not touched,
	// so these are the same photographs the owner supplied.
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-01.jpg"   => '85b5a53e89025dd20e7dfee02e840b2aaf6f498c4a691b0e7867bcf044663861',
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-02.jpeg"  => 'dc11e8f41036ed505d9d846ed067f4abb6c0be35753ae6410c1b65eb41974e61',
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-03.jpeg"  => 'b38fd8ed583a4caa51b8c870b9a53f797de4b252ac48c7b0892651d5a27fe514',
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-04.jpg"   => 'a0c480f4ee8d01e38dc66512582e1646023a449e9ecf3b064b75c19406461376',
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-05.jpeg"  => '7a5ea626e1365c8c6ed637c9f3b68c9a03d9971fb930c0b07d1a84427abffbf5',
	"$gal_root/van-nuys-pool-project/van-nuys-pool-project-source-06.jpeg"  => '1e21b2ecd7ccb5d9c7500640ea3a0a8e440e76ebd9ef4428e810776cc0e08436',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-01.jpeg" => '5e933dad5826b720c37160be39c0c89dd48489cc4ba1a3497dda643220eeac35',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-02.jpg"  => '241658d03d7e251b0b6d84c45c8274d26fd848d5fe617906438abb6e5e6beca0',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-03.jpeg" => 'd8fe12b406cda923c36d99b13b4255bce0397e4cb33df6ee3fc902bb40460925',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-04.jpeg" => '37211f8697711bc756ba74a7334507a3fb01c668cb90e76dd29a8f636a7bbc2c',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-05.jpeg" => 'b37939288e34ca779db11687f70e1acc6a0a68d6786b0aaccd1d8e9743a3c263',
	"$gal_root/toluca-lake-pool-project/toluca-lake-pool-project-source-06.jpeg" => 'de456b04e9fcd1b0544797f21b32fbcf059e8a9f4b3317c43d2497572b2eb8f9',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-01.jpeg" => '2440c205d7442443ea35022d02ae3868c423ab35640a8f640f8879c89fc33efe',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-02.jpeg" => '4b763562a9856d225980dbd82ef799c16cb8fd8ca93e4f0f77cca5b01cbdcea3',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-03.jpeg" => 'f90043074ea4d2db2b93bc4b0275f209a3f4b3c9d1b5f79f0989ed390c5ba8a2',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-04.jpeg" => '7e8442bbd96430779891180d6e99db6b81d1a5e9bf77231f00130543defbff40',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-05.jpeg" => 'ed4a9a69619fae1fdc07878c4b7cc5fcd39a5200481461960e9813de71fb08a7',
	"$gal_root/north-hollywood-pool-project/north-hollywood-pool-project-source-06.jpeg" => '78f80a2135a445380d46fd94c8e90822bc15e019d6b8d177125029eeda12cb88',
	// Batch 4 — Burbank / Calabasas / West Hollywood / Bel Air. Folder and file
	// names were normalized to the canonical registry slug; the encoded bytes
	// were never touched, so these remain the owner-supplied photographs.
	"$gal_root/burbank-pool-project/burbank-pool-project-source-01.jpeg" => 'f89a64a7dfd3ef649137b926fa90235712a7d4c081e2b92a32768ca4346b56e0',
	"$gal_root/burbank-pool-project/burbank-pool-project-source-02.jpeg" => '49b9d952065d6b03c8c0f7576125c63ec78eccced8c69e8c5c212fc87bd8520a',
	"$gal_root/burbank-pool-project/burbank-pool-project-source-03.jpg"  => '4bc5cd9207b9bae7320c9a851557b5c3b80031c814c6f379ca11a4c71218e878',
	"$gal_root/burbank-pool-project/burbank-pool-project-source-04.jpeg" => '112881856cf6dce385116fc1de73f2eafa7c9d790670a05b316f6e07c703ada9',
	"$gal_root/burbank-pool-project/burbank-pool-project-source-05.jpeg" => '4ae7659d9425ad99c4806181580d55eec2feec9c4b7b85c78142f74936fe10b0',
	"$gal_root/burbank-pool-project/burbank-pool-project-source-06.jpeg" => 'b94061ca8dde7d0157dee47c241bf48b67fa94ec35102c8f8064a5f63a175cf4',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-01.jpeg" => '8d19bbbbc2bbc712f1d6107730a70b0bf0374905e90bb7075a229a1c18acb08a',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-02.jpeg" => 'e5de566fa94ffdac326db4907c627843a6526503be591008f2380d66972c7e00',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-03.jpeg" => '55e535bd07679f7442746fa66bb05c0b17cfc381dd558e8d47c1d0be788372c0',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-04.jpeg" => 'c7cd9f51130b88991d1343aa8ee18b98a128200eb8f8ba192271c298a32dcc60',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-05.jpeg" => '99ac6f8788f25c10cbabcc8c66e7df3b164b5f8359c072054ae1db1e9b025de4',
	"$gal_root/calabasas-pool-project/calabasas-pool-project-source-06.jpeg" => '972a30b9791823a2b22865d542d00902e7aa7552c978e683cb6ff3437eae931c',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-01.jpeg" => 'c6d1c1abf62811271930255cea5f613ed4332b187325c3358c40aa28da5c3b36',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-02.jpeg" => '916754d9c5060b47c3f58803d221ba79de33ccb3ffe5bd5fb61c84a8acbbb9d0',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-03.jpeg" => 'f29733e26d04675f93fc2f93c5d78777da6067a845fa4f16d0c41b7c023af43f',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-04.jpeg" => 'd89d628cd1d15db557eb5fbba591ed9bdc3b91875dbb41f41b615de040a1d5d2',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-05.jpeg" => '9d0abcd15306d2bbeaea330d1435a5e595a5b587e5d5f3444d213064aec5f024',
	"$gal_root/west-hollywood-pool-project/west-hollywood-pool-project-source-06.jpeg" => 'be7a5b554b3e60e3fb5e547a5d0a02965aee8f4924b85699bee819c4da4816dc',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-01.jpeg" => 'fb6212a699baf706de9ac4ceab81007f290a7004a9f553aff5d350a7ab9d2ce3',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-02.jpeg" => '2b29f71b1516ac9624cccab23e858bf3c0fa15085cd2e3023d5d4abc6abd7923',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-03.jpeg" => '17ac7a3e8465036ed94e67a2a73317caa3d093fac60e4eaa12c3a73e160d6999',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-04.jpeg" => 'c11a70a700df5de57d7b0636e526aba8875f6751391c607dcba4ddebcb8a4461',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-05.jpeg" => '4b41bfa29516d378174ec9978d6c5e6cd4b51ba6e9c5c11a26d560ba424a8f10',
	"$gal_root/bel-air-pool-project/bel-air-pool-project-source-06.jpeg" => '607eefda8eb2bc4370654b08ded973d2f9c4d6d7cfa7d5c8486aa5334410afc4',
);
$present = array();
$absent  = array();
foreach ( $SOURCES as $p => $want ) { is_readable( $p ) ? $present[] = $p : $absent[] = $p; }

// Count every non-derivative file actually sitting in the source locations, so an
// ADDED or RENAMED source is caught, not just a modified one.
$found_extra = array();
$scan_dirs = array(
	"$incoming/sherman_oaks", "$incoming/encino", "$incoming/studio_city",
	"$gal_root/beverly-hills-luxe-spa-renovation", "$gal_root/tarzana-resort-style-finish",
	"$gal_root/woodland-hills-tile-coping-refresh",
	"$gal_root/van-nuys-pool-project", "$gal_root/toluca-lake-pool-project",
	"$gal_root/north-hollywood-pool-project",
	"$gal_root/burbank-pool-project", "$gal_root/calabasas-pool-project",
	"$gal_root/west-hollywood-pool-project", "$gal_root/bel-air-pool-project",
);
$on_disk = 0;
foreach ( $scan_dirs as $d ) {
	foreach ( (array) glob( "$d/*" ) as $f ) {
		if ( preg_match( '#-highlight-0[1-6]\.webp$#i', $f ) ) { continue; }  // generated derivative
		$on_disk++;
		if ( ! array_key_exists( str_replace( '\\', '/', $f ), $SOURCES ) && ! array_key_exists( $f, $SOURCES ) ) {
			$found_extra[] = basename( $f );
		}
	}
}

if ( 0 === count( $present ) ) {
	skipped( '24. source preservation — all ' . count( $SOURCES ) . ' source originals are absent. They are intentionally untracked working files, so a production checkout ships none of them; derivative, mapping, HTTP, rendering, metadata and gallery-shape assertions all still ran above.' );
} elseif ( count( $present ) !== count( $SOURCES ) ) {
	bad( '24. PARTIAL source set: ' . count( $present ) . ' of ' . count( $SOURCES )
		. ' present. Missing: ' . implode( ', ', array_map( 'basename', array_slice( $absent, 0, 6 ) ) )
		. ( count( $absent ) > 6 ? ' (+' . ( count( $absent ) - 6 ) . ' more)' : '' ) );
} else {
	$changed = array();
	foreach ( $SOURCES as $p => $want ) {
		if ( hash_file( 'sha256', $p ) !== $want ) { $changed[] = basename( $p ); }
	}
	if ( $changed || $found_extra || $on_disk !== count( $SOURCES ) ) {
		bad( '24. source set altered — modified: ' . ( implode( ', ', $changed ) ?: 'none' )
			. ' | unexpected files: ' . ( implode( ', ', $found_extra ) ?: 'none' )
			. " | on disk: $on_disk expected: " . count( $SOURCES ) );
	} else {
		ok( '24. all ' . count( $SOURCES ) . ' source originals are byte-for-byte unchanged (SHA-256), and none was added, renamed or removed' );
	}
}

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

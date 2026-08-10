<?php
/**
 * Project gallery PHOTOGRAPHS — Sherman Oaks, Encino, Studio City.
 *
 * Covers the real-image population of the shared "More Project Highlights"
 * gallery: that each authorized project resolves only its OWN six photographs,
 * that every published derivative is a genuine, decodable, uniquely-hashed
 * WebP that is actually served, that alt text is real and unique, that the
 * eleven unauthorized projects still show six pending slots, and that the
 * untouched `_incoming` originals are still byte-for-byte identical.
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

/** Fetch a URL body, following the local site's redirects. */
function g_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 60,
		CURLOPT_SSL_VERIFYPEER => false,
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

/** HTTP status for a URL (HEAD-style, body discarded). */
function g_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_NOBODY         => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 60,
		CURLOPT_SSL_VERIFYPEER => false,
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

$child       = get_stylesheet_directory();
$gal_root    = $child . '/assets/img/projects/galleries';
$incoming    = $child . '/assets/img/projects/_incoming/more_project_highlights';

$AUTHORIZED = array(
	'sherman-oaks-mid-century-remodel' => 'Sherman Oaks',
	'encino-estate-new-build'          => 'Encino',
	'studio-city-modern-automation'    => 'Studio City',
);

$ALL_MANAGED = array();
foreach ( \Showtime\Projects::all() as $e ) {
	if ( ! empty( $e['managed'] ) ) { $ALL_MANAGED[] = $e['slug']; }
}
$UNAUTHORIZED = array_values( array_diff( $ALL_MANAGED, array_keys( $AUTHORIZED ) ) );

$bodies = array();
foreach ( $ALL_MANAGED as $s ) { $bodies[ $s ] = g_fetch( home_url( "/projects/$s/" ) ); }

echo "== ASSIGNED IMAGES RESOLVE PER PROJECT ==\n";

/* 1 + 2 + 3. Each authorized project resolves exactly its own six highlights. */
$resolved = array();
foreach ( array_keys( $AUTHORIZED ) as $slug ) {
	$slots          = showtime_project_gallery( showtime_project_data( $slug ) );
	$resolved[ $slug ] = $slots;
	$files          = array();
	foreach ( $slots as $s ) {
		if ( 'ready' === $s['status'] ) { $files[] = basename( (string) $s['url'] ); }
	}
	$expect = array();
	for ( $i = 1; $i <= 6; $i++ ) { $expect[] = sprintf( '%s-highlight-%02d.webp', $slug, $i ); }
	( $files === $expect )
		? ok( "1-3. $slug resolves exactly its own six highlight images, in slot order 01-06" )
		: bad( "1-3. $slug resolved: " . implode( ', ', $files ) );
}

/* 4. No photograph crosses between project records — every resolved URL sits
 * under the project's own galleries/<slug>/ directory, and no file is shared. */
$cross   = array();
$all_seen = array();
foreach ( $resolved as $slug => $slots ) {
	foreach ( $slots as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		if ( false === strpos( (string) $s['url'], "/galleries/$slug/" ) ) {
			$cross[] = "$slug -> {$s['url']}";
		}
		$all_seen[] = basename( (string) $s['url'] );
	}
}
$dupe_across = count( $all_seen ) !== count( array_unique( $all_seen ) );
( ! $cross && ! $dupe_across )
	? ok( '4. no image crosses between project records: every resolved URL lives under its own galleries/<slug>/ directory and no filename is shared between projects' )
	: bad( '4. cross-project leakage: ' . implode( '; ', $cross ) . ' duplicateFilenames=' . var_export( $dupe_across, true ) );

/* 4b. The resolver is structurally scoped: pointing a record at another
 * project's real filename must fail closed, not silently load that file. */
$borrow = showtime_project_gallery( array(
	'slug'               => 'encino-estate-new-build',
	'additional_gallery' => array_fill( 0, 3, array(
		'status'  => 'ready',
		'image'   => 'sherman-oaks-mid-century-remodel-highlight-01.webp',
		'alt'     => 'borrowed',
		'caption' => '',
	) ),
) );
( array() === $borrow )
	? ok( "4b. a record cannot borrow another project's photograph: naming Sherman Oaks' real file under Encino fails the gallery closed" )
	: bad( '4b. cross-project borrow resolved ' . count( $borrow ) . ' slots' );

echo "\n== PUBLISHED DERIVATIVES ==\n";

/* 5 + 6. Every published file exists and is a genuine WebP by magic number and
 * by real decode — extension and registry string are never trusted. */
$bad_files = array();
$published = array();
foreach ( array_keys( $AUTHORIZED ) as $slug ) {
	for ( $i = 1; $i <= 6; $i++ ) {
		$p = sprintf( '%s/%s/%s-highlight-%02d.webp', $gal_root, $slug, $slug, $i );
		$published[] = $p;
		if ( ! is_readable( $p ) ) { $bad_files[] = basename( $p ) . ' missing'; continue; }
		if ( ! g_is_webp( $p ) ) { $bad_files[] = basename( $p ) . ' not RIFF/WEBP'; continue; }
		$d = @getimagesize( $p );
		if ( ! is_array( $d ) || $d[0] < 1 || $d[1] < 1 || IMAGETYPE_WEBP !== ( $d[2] ?? 0 ) ) {
			$bad_files[] = basename( $p ) . ' does not decode as WebP';
			continue;
		}
		if ( 'image/webp' !== ( $d['mime'] ?? '' ) ) {
			$bad_files[] = basename( $p ) . ' mime=' . ( $d['mime'] ?? '?' );
		}
	}
}
$bad_files
	? bad( '5/6. ' . implode( ', ', $bad_files ) )
	: ok( '5 + 6. all ' . count( $published ) . ' published derivatives exist, carry the RIFF/WEBP magic number, decode as IMAGETYPE_WEBP with real pixel dimensions, and report mime image/webp' );

/* 7. Every published derivative is a distinct photograph. */
$sums = array();
foreach ( $published as $p ) { if ( is_readable( $p ) ) { $sums[ $p ] = hash_file( 'sha256', $p ); } }
( count( $sums ) === count( array_unique( $sums ) ) && count( $sums ) === count( $published ) )
	? ok( '7. all ' . count( $sums ) . ' published derivatives have unique SHA-256 hashes — no photograph is reused within or across the three projects' )
	: bad( '7. hashes: ' . count( $sums ) . ' unique: ' . count( array_unique( $sums ) ) );

/* 7b. No published derivative duplicates an existing before/after asset. */
$cmp_dir  = $child . '/assets/img/projects/comparisons';
$cmp_sums = array();
foreach ( (array) glob( "$cmp_dir/*.webp" ) as $f ) { $cmp_sums[ basename( $f ) ] = hash_file( 'sha256', $f ); }
$collide = array_intersect( $sums, $cmp_sums );
( empty( $collide ) )
	? ok( '7b. no published highlight duplicates any of the ' . count( $cmp_sums ) . ' existing before/after comparison assets' )
	: bad( '7b. collides with comparison assets: ' . implode( ', ', array_keys( $collide ) ) );

/* 8. Slot order is deterministic: highlight-01..06 in ascending order. */
$order_bad = array();
foreach ( $resolved as $slug => $slots ) {
	$n = array();
	foreach ( $slots as $s ) {
		if ( preg_match( '/-highlight-(\d{2})\.webp$/', (string) $s['url'], $m ) ) { $n[] = (int) $m[1]; }
	}
	$sorted = $n;
	sort( $sorted );
	if ( $n !== $sorted || $n !== range( 1, 6 ) ) { $order_bad[] = "$slug: " . implode( ',', $n ); }
}
$order_bad
	? bad( '8. non-deterministic slot order — ' . implode( '; ', $order_bad ) )
	: ok( '8. every authorized gallery holds slots 01-06 in ascending, deterministic natural filename order' );

echo "\n== PLACEHOLDER STATE ==\n";

/* 9. Unfilled slots stay Coming Soon, asserting nothing. */
$pend_bad = array();
foreach ( $UNAUTHORIZED as $slug ) {
	$slots = showtime_project_gallery( showtime_project_data( $slug ) );
	$pend  = 0;
	foreach ( $slots as $s ) {
		if ( 'coming_soon' !== $s['status'] ) { continue; }
		$pend++;
		if ( '' !== $s['url'] || '' !== $s['alt'] || '' !== $s['caption'] || 0 !== $s['width'] || 0 !== $s['height'] ) {
			$pend_bad[] = "$slug slot carries copy";
		}
	}
	if ( 6 !== $pend ) { $pend_bad[] = "$slug pending=$pend"; }
}
$pend_bad
	? bad( '9. ' . implode( ', ', $pend_bad ) )
	: ok( '9. all ' . count( $UNAUTHORIZED ) . ' unauthorized projects keep six Coming Soon placeholders that carry no url, alt, caption or dimensions' );

/* 10. A fully populated gallery contains zero placeholders. */
$ph_bad = array();
foreach ( array_keys( $AUTHORIZED ) as $slug ) {
	$n = preg_match_all( '#proj-gallery__card--pending#', $bodies[ $slug ] );
	$t = substr_count( $bodies[ $slug ], 'Project photo coming soon' );
	if ( 0 !== $n || 0 !== $t ) { $ph_bad[] = "$slug pendingCards=$n pendingText=$t"; }
	// The "more photos coming" note must not survive on a full gallery either.
	if ( false !== strpos( $bodies[ $slug ], 'Additional project photos will be added soon' ) ) {
		$ph_bad[] = "$slug still shows the pending note";
	}
}
$ph_bad
	? bad( '10. ' . implode( ', ', $ph_bad ) )
	: ok( '10. the three fully populated galleries render zero placeholder cards, zero "coming soon" text and no "more photos coming" note' );

/* 10b. That note is still shown where it remains true. */
$note_missing = array();
foreach ( $UNAUTHORIZED as $slug ) {
	if ( false === strpos( $bodies[ $slug ], 'Additional project photos will be added soon' ) ) {
		$note_missing[] = $slug;
	}
}
$note_missing
	? bad( '10b. pending note missing on: ' . implode( ', ', $note_missing ) )
	: ok( '10b. the "more photos coming" note still appears on all ' . count( $UNAUTHORIZED ) . ' projects that genuinely still have pending slots' );

echo "\n== GALLERY SHAPE IS UNCHANGED ==\n";

/* 11 + 12. Six slots, two pages, three per page — on every managed project. */
$shape_bad = array();
foreach ( $ALL_MANAGED as $slug ) {
	$p     = showtime_project_data( $slug );
	$slots = showtime_project_gallery( $p );
	$pages = showtime_project_gallery_pages( $p );
	$sizes = array_map( 'count', $pages );
	if ( 6 !== count( $slots ) || 2 !== count( $pages ) || array( 3, 3 ) !== $sizes ) {
		$shape_bad[] = "$slug slots=" . count( $slots ) . ' pages=' . count( $pages ) . ' sizes=' . implode( '/', $sizes );
	}
	$cells = preg_match_all( '#proj-gallery__cell#', $bodies[ $slug ] );
	$dots  = preg_match_all( '#data-proj-slider-dot=#', $bodies[ $slug ] );
	if ( 6 !== $cells || 2 !== $dots ) { $shape_bad[] = "$slug cells=$cells dots=$dots"; }
}
$shape_bad
	? bad( '11/12. ' . implode( '; ', $shape_bad ) )
	: ok( '11 + 12. all ' . count( $ALL_MANAGED ) . ' managed projects still render exactly six slots across two pages of three, with exactly two pagination dots' );

echo "\n== ALT TEXT ==\n";

/* 13. Real images carry unique, non-empty, escaped alt text. */
$alts     = array();
$alt_bad  = array();
foreach ( $resolved as $slug => $slots ) {
	foreach ( $slots as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		$a = (string) $s['alt'];
		if ( '' === trim( $a ) ) { $alt_bad[] = "$slug empty alt"; continue; }
		if ( $a !== wp_strip_all_tags( $a ) ) { $alt_bad[] = "$slug alt contains markup"; }
		$alts[] = $a;
	}
}
if ( count( $alts ) !== count( array_unique( $alts ) ) ) { $alt_bad[] = 'duplicate alt text'; }
// Rendered alt attributes must be escaped, and must actually appear.
foreach ( $resolved as $slug => $slots ) {
	foreach ( $slots as $s ) {
		if ( 'ready' !== $s['status'] ) { continue; }
		if ( false === strpos( $bodies[ $slug ], 'alt="' . esc_attr( $s['alt'] ) . '"' ) ) {
			$alt_bad[] = "$slug alt not rendered escaped";
		}
	}
}
$alt_bad
	? bad( '13. ' . implode( ', ', array_unique( $alt_bad ) ) )
	: ok( '13. all ' . count( $alts ) . ' published images carry unique, non-empty, markup-free alt text, rendered escaped in the page' );

/* 13b. Alt text asserts nothing unverifiable — no price, date, rating, brand
 * or marketing superlative. */
$banned = array( 'best', 'luxury', 'luxurious', 'premium', 'stunning', 'beautiful', 'award',
	'$', 'guarantee', 'satisfied', 'happy customer', 'before and after', 'brand new' );
$leak = array();
foreach ( $alts as $a ) {
	foreach ( $banned as $b ) {
		if ( false !== stripos( $a, $b ) ) { $leak[] = "\"$b\" in: " . substr( $a, 0, 48 ); }
	}
}
$leak
	? bad( '13b. alt text makes unsupported claims — ' . implode( '; ', $leak ) )
	: ok( '13b. no alt text contains pricing, a rating, a satisfaction claim, a before/after assertion or a marketing superlative' );

echo "\n== RENDERED OUTPUT ==\n";

/* 14. No _incoming path ever reaches the browser. */
$leaked = array();
foreach ( $bodies as $slug => $b ) {
	if ( false !== stripos( $b, '_incoming' ) ) { $leaked[] = $slug; }
	if ( preg_match( '#\.(jpe?g|png)"#i', (string) preg_match( '#<div class="proj-gallery".*?</div>\s*$#s', $b, $m ) ? ( $m[0] ?? '' ) : '' ) ) {
		$leaked[] = "$slug non-webp in gallery";
	}
}
$leaked
	? bad( '14. _incoming leaked into: ' . implode( ', ', $leaked ) )
	: ok( '14. no `_incoming` path and no source JPEG appears in the rendered HTML of any of the ' . count( $bodies ) . ' project pages' );

/* 15 + 16. Every gallery <img> resolves to a real file and returns HTTP 200. */
$url_bad = array();
$checked = 0;
foreach ( array_keys( $AUTHORIZED ) as $slug ) {
	if ( ! preg_match_all( '#<img[^>]+src="([^"]+highlight-\d{2}\.webp)"[^>]*>#', $bodies[ $slug ], $m, PREG_SET_ORDER ) ) {
		$url_bad[] = "$slug rendered no highlight images";
		continue;
	}
	if ( 3 !== count( $m ) && 6 !== count( $m ) ) {
		$url_bad[] = "$slug rendered " . count( $m ) . ' highlight images';
	}
	foreach ( $m as $one ) {
		$checked++;
		$url = html_entity_decode( $one[1] );
		$st  = g_status( $url );
		if ( 200 !== $st ) { $url_bad[] = basename( $url ) . " HTTP $st"; }
		// Intrinsic dimensions must be present and must match the real file.
		$file = $gal_root . '/' . $slug . '/' . basename( $url );
		$d    = @getimagesize( $file );
		if ( ! preg_match( '#width="(\d+)"#', $one[0], $w ) || ! preg_match( '#height="(\d+)"#', $one[0], $h ) ) {
			$url_bad[] = basename( $url ) . ' missing width/height';
		} elseif ( ! is_array( $d ) || (int) $w[1] !== $d[0] || (int) $h[1] !== $d[1] ) {
			$url_bad[] = basename( $url ) . ' declared ' . ( $w[1] ?? '?' ) . 'x' . ( $h[1] ?? '?' )
				. ' but file is ' . ( $d[0] ?? '?' ) . 'x' . ( $d[1] ?? '?' );
		}
		if ( false === strpos( $one[0], 'loading="lazy"' ) ) { $url_bad[] = basename( $url ) . ' not lazy'; }
		if ( false === strpos( $one[0], 'decoding="async"' ) ) { $url_bad[] = basename( $url ) . ' not async'; }
	}
}
$url_bad
	? bad( '15/16. ' . implode( ', ', $url_bad ) )
	: ok( "15 + 16. all $checked rendered highlight images return HTTP 200 and declare intrinsic width/height matching the real file, with loading=lazy and decoding=async" );

/* 16b. Page 2 markup is present in the document, not fetched later. */
$p2_bad = array();
foreach ( array_keys( $AUTHORIZED ) as $slug ) {
	if ( ! preg_match( '#data-proj-slider-slide="1"(.*?)(?=data-proj-slider-slide="2"|</div>\s*</div>\s*<div class="proj-gallery__nav")#s', $bodies[ $slug ], $m ) ) {
		$p2_bad[] = "$slug no page 2 block";
		continue;
	}
	$n = preg_match_all( '#highlight-\d{2}\.webp#', $m[1] );
	if ( 3 !== $n ) { $p2_bad[] = "$slug page2 images=$n"; }
}
$p2_bad
	? bad( '16b. ' . implode( ', ', $p2_bad ) )
	: ok( '16b. page 2 ships its three real images in the initial HTML for all three projects — no deferred fetch, no empty second page' );

echo "\n== NOTHING ELSE MOVED ==\n";

/* 20. Metadata, indexing, schema and sitemap totals are unchanged. */
$meta_bad = array();
foreach ( $ALL_MANAGED as $slug ) {
	$b = $bodies[ $slug ];
	// WordPress emits these with single quotes; match either style.
	if ( ! preg_match( '#<meta name=[\'"]robots[\'"] content=[\'"]([^\'"]+)#i', $b, $m )
		|| false === stripos( $m[1], 'index' ) || false !== stripos( $m[1], 'noindex' ) ) {
		$meta_bad[] = "$slug robots";
	}
	if ( 1 !== preg_match_all( '#<link rel=[\'"]canonical[\'"]#i', $b ) ) { $meta_bad[] = "$slug canonical"; }
	if ( ! preg_match( '#<meta property=[\'"]og:image[\'"]#i', $b ) ) { $meta_bad[] = "$slug og:image"; }
	if ( ! preg_match( '#name=[\'"]twitter:card[\'"]#i', $b ) ) { $meta_bad[] = "$slug twitter"; }
	// No gallery photograph may enter JSON-LD: there is no reviewed gallery schema.
	if ( preg_match_all( '#<script type="application/ld\+json">([\s\S]*?)</script>#', $b, $ld ) ) {
		if ( false !== strpos( implode( ' ', $ld[1] ), 'highlight-' ) ) { $meta_bad[] = "$slug gallery image in JSON-LD"; }
	}
}
$xml  = g_fetch( home_url( '/wp-sitemap-posts-project-1.xml' ) );
$nxml = substr_count( $xml, '/projects/' );
if ( 14 !== $nxml ) { $meta_bad[] = "xml sitemap project urls=$nxml"; }
$meta_bad
	? bad( '20. ' . implode( ', ', array_unique( $meta_bad ) ) )
	: ok( '20. all 14 project pages keep index,follow, exactly one canonical, og:image and twitter tags; no gallery photograph enters JSON-LD; the XML sitemap still lists 14 project URLs' );

/* 21. The `_incoming` originals were never touched. */
$EXPECT = array(
	'sherman_oaks/sherman-aoks-more-projects1.jpeg'  => '1fb7ef6f86a8a13d20029f4a3f75e9c980b308326b512c9571061f1811251c0e',
	'sherman_oaks/sherman-oaks-more-projects.jpeg'   => 'dba19eed887c28eeaf422932bfd8ce0e81afc40c58ecc74d98733de7385703f0',
	'sherman_oaks/sherman-oaks-more-projects2.jpeg'  => '7a415f892a838b662d7d9f0607713ba1f41d0504ebf61cf1b422fc05bcfd3373',
	'sherman_oaks/sherman-oaks-more-projects3.jpeg'  => 'a007806d620f14d0ac1de3b23c94c6e23dad106b3d5a872923e4e8304fef70f6',
	'sherman_oaks/sherman-oaks-more-projects4.jpeg'  => '523eff53f80f34b9311b44e13ef2125125197e11ddef1adb6d318cf9172c41f8',
	'sherman_oaks/sherman-oaks-more-projects5.jpeg'  => '9204c395b618e322a325c359578cc8a6ed32b0a4f2676fc6290ce379b2b90828',
	'encino/encino-more-projects.jpeg'               => 'f146a87fbd722c98199bdb6a829a936b72c97ac568121fb4fb896106ca446d2f',
	'encino/encino-more-projects1.jpeg'              => '2ff13e78bb39eec7d7c105d6c2f445fa66c36452b8c1356aa5496b120c7c5949',
	'encino/encino-more-projects2.jpeg'              => 'da8f76f21035c9d0bba4de79dbc843a16a29f946334ae5406ce89015a1b01924',
	'encino/encino-more-projects3.jpeg'              => '3b581cb8af9a6aa268f0c679af6b8e8a92c32dddafca3416a3c26cf8b9c69a22',
	'encino/encino-more-projects4.jpeg'              => '04007f1df881b9869ed5d8236514d4614b1eb219f3db876f6e04203c72487d7a',
	'encino/encino-more-projects5.jpg'               => '083197bdd1d9295dcd756dde5ad4adeba5c0fb823e35190bfb86f99d52969e00',
	'studio_city/studio-city-more-project3.jpeg'     => '1994d618966400467702d317ff5efdb8fcaea68c4cd3a3a3989c06321a62ace3',
	'studio_city/studio-city-more-projects.jpeg'     => '483075be202dcac039798a136a2c74e181196bb2d0c8b5e8a1df0d4eb6599e90',
	'studio_city/studio-city-more-projects1.jpeg'    => 'de3d5d6750900299c510192ae1974228b3c3839453fe9aa0e066103ffe08d38c',
	'studio_city/studio-city-more-projects2.jpg'     => 'be7884eda7a20d1915c839530f0ae03f364af7cb980a8660a97bffb389c0cdb3',
	'studio_city/studio-city-more-projects4.jpeg'    => '9845cf110de27805b95c48bc5a9cc42109c1325b3b29625a9136865a0c9878ab',
	'studio_city/studio-city-more-projects5.jpeg'    => '2b8617059954310b2b00312bd73144000bcd937bac189060abbecd0205f9f64e',
);
$inc_bad = array();
foreach ( $EXPECT as $relpath => $want ) {
	$p = "$incoming/$relpath";
	if ( ! is_readable( $p ) ) { $inc_bad[] = "$relpath missing"; continue; }
	if ( hash_file( 'sha256', $p ) !== $want ) { $inc_bad[] = "$relpath modified"; }
}
$found = 0;
foreach ( array( 'sherman_oaks', 'encino', 'studio_city' ) as $d ) {
	$found += count( (array) glob( "$incoming/$d/*" ) );
}
if ( count( $EXPECT ) !== $found ) { $inc_bad[] = "file count changed: $found"; }
$inc_bad
	? bad( '21. ' . implode( ', ', $inc_bad ) )
	: ok( '21. all ' . count( $EXPECT ) . ' `_incoming` originals are byte-for-byte unchanged (SHA-256), and none was added, renamed or removed' );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

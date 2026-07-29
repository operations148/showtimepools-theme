<?php
/**
 * Unit tests for per-page Open Graph image resolution (audit item P0-2).
 *
 * Verifies that service and service-area pages advertise their OWN hero image
 * instead of the shared lifestyle_main default, that the template gate from
 * P0-1 still prevents an unrelated page from inheriting one, that image
 * dimensions are either exact or absent — never guessed — and that a
 * same-named JPEG/PNG sibling is only substituted for the visible WebP hero
 * when VERIFIED (same basename + identical pixel dimensions), never merely
 * because the file exists (see tests 15-18: reproduces and guards the area
 * portrait-JPEG-vs-landscape-WebP mismatch found in the P0-2 review).
 *
 *   php tests/og-image-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/og-image-unit.php
 *
 * Creates temporary draft fixtures in the LOCAL test database and force-deletes
 * them. Never run against production.
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

foreach ( array( 'showtime_og_image_data', 'showtime_og_slot_image', 'showtime_registry_slug' ) as $fn ) {
	if ( ! function_exists( $fn ) ) {
		bad( "$fn() not loaded — is the child theme active?" );
		echo "\n== RESULT ==\n  pass: 0   fail: 1\n";
		exit( 1 );
	}
}

$created = array();
function fx( string $slug, ?string $template = null, array $meta = array() ): int {
	global $created;
	$id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => 'FIXTURE ' . $slug,
		'post_name'   => $slug,
	) );
	if ( is_wp_error( $id ) || ! $id ) { return 0; }
	if ( null !== $template ) { update_post_meta( $id, '_wp_page_template', $template ); }
	foreach ( $meta as $k => $v ) { update_post_meta( $id, $k, $v ); }
	$created[] = (int) $id;
	return (int) $id;
}

/** Resolve og image data as if $id were the queried singular object. */
function og_for( int $id ): array {
	global $wp_query, $post;
	$prev_query = $wp_query;
	$prev_post  = $post;

	$wp_query = new WP_Query( array( 'page_id' => $id, 'post_type' => 'page', 'post_status' => 'draft' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- deliberate, restored below.
	$wp_query->the_post();
	$data = showtime_og_image_data();
	wp_reset_postdata();

	$wp_query = $prev_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$post     = $prev_post;  // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	return $data;
}

$services = \Showtime\Services::all();
$areas    = \Showtime\Areas::all();
$svc      = $services[0];
$area     = $areas[0];
$svc_slug = (string) $svc['slug'];
$area_slug= (string) $area['slug'];

echo "\n== OG IMAGE UNIT TESTS (P0-2) ==\n";
echo "  service '{$svc_slug}', area '{$area_slug}'\n\n";

echo "[resolution priority]\n";

// 1. A real featured image outranks the service hero slot.
$feat_src = SHOWTIME_CHILD_DIR . '/assets/img/' . ( file_exists( SHOWTIME_CHILD_DIR . '/assets/img/hero.jpg' ) ? 'hero.jpg' : 'logo.png' );
$att_id   = 0;
if ( file_exists( $feat_src ) ) {
	// Attach an EXISTING bundled file — nothing is uploaded or generated.
	$att_id = wp_insert_attachment( array(
		'post_title'     => 'FIXTURE og featured',
		'post_mime_type' => 'image/' . ( 'hero.jpg' === basename( $feat_src ) ? 'jpeg' : 'png' ),
		'post_status'    => 'inherit',
	), $feat_src );
}
if ( $att_id && ! is_wp_error( $att_id ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $feat_src ) );
	update_post_meta( $att_id, '_wp_attachment_image_alt', 'Fixture featured alt' );

	$id = fx( 'fx-og-featured', 'page-service.php', array( '_showtime_service_slug' => $svc_slug ) );
	set_post_thumbnail( $id, $att_id );
	$d = og_for( $id );
	( false === strpos( $d['url'], 'service_' . $svc_slug ) && 'Fixture featured alt' === $d['alt'] )
		? ok( '1. featured image outranks the service hero slot (and uses its alt)' )
		: bad( '1. featured image not preferred: url=' . $d['url'] . ' alt=' . $d['alt'] );
	wp_delete_attachment( $att_id, true );
} else {
	bad( '1. could not create the featured-image fixture' );
}

// 2. Service page WITH meta resolves its own service hero.
$id  = fx( 'fx-og-svc-meta', 'page-service.php', array( '_showtime_service_slug' => $svc_slug ) );
$d   = og_for( $id );
false !== strpos( $d['url'], 'service_' . $svc_slug )
	? ok( '2. service page with meta resolves service_' . $svc_slug )
	: bad( '2. got ' . $d['url'] );

// 3. Service page MISSING meta resolves via post_name (P0-1 resolver).
$id = fx( $svc_slug, 'page-service.php' );
$d  = og_for( $id );
false !== strpos( $d['url'], 'service_' . $svc_slug )
	? ok( '3. service page without meta resolves via post_name' )
	: bad( '3. got ' . $d['url'] );

// 4. Area page WITH meta.
$id = fx( 'fx-og-area-meta', 'page-area.php', array( '_showtime_area_slug' => $area_slug ) );
$d  = og_for( $id );
false !== strpos( $d['url'], 'area_' . $area_slug )
	? ok( '4. area page with meta resolves area_' . $area_slug )
	: bad( '4. got ' . $d['url'] );

// 5. Area page MISSING meta resolves via post_name.
$id = fx( $area_slug, 'page-area.php' );
$d  = og_for( $id );
false !== strpos( $d['url'], 'area_' . $area_slug )
	? ok( '5. area page without meta resolves via post_name' )
	: bad( '5. got ' . $d['url'] );

// 6. Unrelated page sharing the slug must NOT inherit the service image.
$id = fx( $svc_slug, 'page-legal.php' );
$d  = og_for( $id );
false === strpos( $d['url'], 'service_' . $svc_slug )
	? ok( '6. unrelated template with matching slug does NOT inherit service image' )
	: bad( '6. template gate leaked: ' . $d['url'] );

// 7. Unknown/missing slot falls back safely (never empty, never fatal).
$id = fx( 'fx-og-unknown', 'page-legal.php' );
$d  = og_for( $id );
'' !== $d['url'] ? ok( '7. unknown slot falls back to a resolvable image' ) : bad( '7. empty url' );

echo "\n[alt + dimensions]\n";

// 9. Alt is non-empty and page-specific.
$id = fx( 'fx-og-alt', 'page-service.php', array( '_showtime_service_slug' => $svc_slug ) );
$d  = og_for( $id );
( '' !== trim( $d['alt'] ) && false !== stripos( $d['alt'], (string) $svc['title'] ) )
	? ok( '9. og:image:alt is non-empty and names the service' )
	: bad( '9. alt = "' . $d['alt'] . '"' );

// 11. Dimensions exact-or-both-absent, and never the old hardcoded 1200x675.
$bad_dims = array();
foreach ( $services as $s ) {
	$img = showtime_og_slot_image( 'service_' . $s['slug'] );
	$w = (int) $img['width']; $h = (int) $img['height'];
	if ( ( $w > 0 ) !== ( $h > 0 ) ) { $bad_dims[] = $s['slug'] . ' (half pair)'; }
	if ( 1200 === $w && 675 === $h ) { $bad_dims[] = $s['slug'] . ' (stale 1200x675)'; }
	if ( $w > 0 && 0 === strpos( $img['url'], SHOWTIME_CHILD_URI ) ) {
		$path = SHOWTIME_CHILD_DIR . '/assets/img/' . basename( $img['url'] );
		$real = @getimagesize( $path );
		if ( $real && ( (int) $real[0] !== $w || (int) $real[1] !== $h ) ) {
			$bad_dims[] = $s['slug'] . ' (mismatch)';
		}
	}
}
$bad_dims ? bad( '11. dimension problems: ' . implode( ', ', $bad_dims ) )
          : ok( '11. all service dimensions are exact or both-absent (no 1200x675)' );

echo "\n[visible-hero identity — same-basename JPEG must be a VERIFIED derivative]\n";

/**
 * Hand-build a minimal PNG byte string that reports the given dimensions via
 * getimagesize() — signature + IHDR chunk only (no IDAT/IEND, no CRC
 * validated). getimagesize() detects type from file CONTENT, not the
 * filename, so this works even written to a ".jpg"-named file — which is
 * exactly what's needed to test dimension-based rejection without a GD
 * dependency (this PHP build has no gd extension loaded at all).
 */
function fake_image_bytes( int $w, int $h ): string {
	$ihdr = pack( 'N', $w ) . pack( 'N', $h ) . "\x08\x06\x00\x00\x00";
	return "\x89PNG\x0d\x0a\x1a\x0a" . pack( 'N', strlen( $ihdr ) ) . 'IHDR' . $ihdr . "\x00\x00\x00\x00";
}

// 15. Build a same-basename candidate with DIFFERENT dimensions than the
// visible hero and prove it is rejected. Reproduces the real defect: area
// heroes where the ".jpg" is a different crop from the on-page ".webp".
$probe_slot = 'fx-og-mismatch-probe';
$img_dir    = SHOWTIME_CHILD_DIR . '/assets/img/';
$hero_path  = $img_dir . $probe_slot . '.webp'; // extension irrelevant to getimagesize(); matches the real webp-hero case
$jpg_path   = $img_dir . $probe_slot . '.jpg';
$made_files = array();

file_put_contents( $hero_path, fake_image_bytes( 400, 300 ) ); $made_files[] = $hero_path;
file_put_contents( $jpg_path, fake_image_bytes( 200, 600 ) );  $made_files[] = $jpg_path; // different crop, same basename

$probe_filter = function ( $url, $slot ) use ( $probe_slot, $hero_path ) {
	return $probe_slot === $slot ? SHOWTIME_CHILD_URI . '/assets/img/' . basename( $hero_path ) : $url;
};
add_filter( 'showtime/image/' . $probe_slot, $probe_filter, 5, 2 );

$resolved = showtime_og_slot_image( $probe_slot );

remove_filter( 'showtime/image/' . $probe_slot, $probe_filter, 5 );
foreach ( $made_files as $f ) { @unlink( $f ); }

( false !== strpos( $resolved['url'], '.webp' ) && 400 === (int) $resolved['width'] && 300 === (int) $resolved['height'] )
	? ok( '15. same-basename candidate with DIFFERENT dimensions is rejected — visible-hero kept' )
	: bad( '15. mismatched candidate was wrongly substituted: ' . $resolved['url'] . " ({$resolved['width']}x{$resolved['height']})" );

// 16. A live area whose bundled JPEG is a verified DIFFERENT crop from its
// WebP hero (proven: 960x1280 portrait JPEG vs 1200x896 landscape WebP) must
// still resolve the exact visible WebP hero, not the mismatched JPEG.
$mismatched_area = null;
foreach ( $areas as $a ) {
	$slot = 'area_' . $a['slug'];
	$w = $img_dir . $slot . '.webp'; $j = $img_dir . $slot . '.jpg';
	if ( file_exists( $w ) && file_exists( $j ) ) {
		$dw = @getimagesize( $w ); $dj = @getimagesize( $j );
		if ( $dw && $dj && ( $dw[0] !== $dj[0] || $dw[1] !== $dj[1] ) ) { $mismatched_area = $a; break; }
	}
}
if ( $mismatched_area ) {
	$slot = 'area_' . $mismatched_area['slug'];
	$hero = (string) showtime_image( $slot, 1200 );
	$og   = showtime_og_slot_image( $slot );
	( basename( $og['url'] ) === basename( $hero ) )
		? ok( "16. mismatched area ({$mismatched_area['slug']}) keeps its exact visible WebP hero" )
		: bad( "16. {$mismatched_area['slug']} substituted a non-matching file: " . $og['url'] );
} else {
	bad( '16. no live area with a genuinely mismatched JPEG/WebP pair was found to test against' );
}

// 17. A live area with a VERIFIED same-dimension JPEG derivative may still be
// preferred (proves the correction did not over-correct into "never JPEG").
$verified_area = null;
foreach ( $areas as $a ) {
	$slot = 'area_' . $a['slug'];
	$w = $img_dir . $slot . '.webp'; $j = $img_dir . $slot . '.jpg';
	if ( file_exists( $w ) && file_exists( $j ) ) {
		$dw = @getimagesize( $w ); $dj = @getimagesize( $j );
		if ( $dw && $dj && $dw[0] === $dj[0] && $dw[1] === $dj[1] ) { $verified_area = $a; break; }
	}
}
if ( $verified_area ) {
	$og = showtime_og_slot_image( 'area_' . $verified_area['slug'] );
	false !== strpos( $og['url'], '.jpg' )
		? ok( "17. verified same-dimension area ({$verified_area['slug']}) prefers its JPEG derivative" )
		: bad( "17. {$verified_area['slug']} did not prefer its verified JPEG: " . $og['url'] );
} else {
	bad( '17. no live area with a verified same-dimension JPEG/WebP pair was found to test against' );
}

// 18. Remote (non-bundled) and Media Library URLs pass through unchanged —
// the substitution logic must never touch a URL outside assets/img/.
$remote_url = 'https://images.unsplash.com/photo-test?w=1200';
add_filter( 'showtime/image/fx-og-remote-probe', function () use ( $remote_url ) { return $remote_url; }, 5 );
$og = showtime_og_slot_image( 'fx-og-remote-probe' );
$remote_url === $og['url']
	? ok( '18. a remote URL passes through showtime_og_slot_image() unchanged' )
	: bad( '18. remote URL was altered: ' . $og['url'] );

echo "\n[coverage + uniqueness]\n";

// 12/13. Every service and area resolves a page-specific, non-lifestyle image.
$svc_urls = array(); $generic = array();
foreach ( $services as $s ) {
	$u = showtime_og_slot_image( 'service_' . $s['slug'] )['url'];
	$svc_urls[] = $u;
	if ( '' === $u || false !== stripos( $u, 'lifestyle_main' ) ) { $generic[] = $s['slug']; }
}
$generic ? bad( '12. services still generic: ' . implode( ', ', $generic ) )
         : ok( '12. all ' . count( $services ) . ' services resolve a page-specific image' );

$area_urls = array(); $generic = array();
foreach ( $areas as $a ) {
	$u = showtime_og_slot_image( 'area_' . $a['slug'] )['url'];
	$area_urls[] = $u;
	if ( '' === $u || false !== stripos( $u, 'lifestyle_main' ) ) { $generic[] = $a['slug']; }
}
$generic ? bad( '13. areas still generic: ' . implode( ', ', $generic ) )
         : ok( '13. all ' . count( $areas ) . ' areas resolve a page-specific image' );

// 14. Not all the same URL.
$all = array_merge( $svc_urls, $area_urls );
count( array_unique( $all ) ) === count( $all )
	? ok( '14. all ' . count( $all ) . ' service+area images are unique' )
	: bad( '14. duplicates: only ' . count( array_unique( $all ) ) . ' unique of ' . count( $all ) );

// ── cleanup ────────────────────────────────────────────────────────────────
$deleted = 0;
foreach ( $created as $cid ) { if ( $cid && wp_delete_post( $cid, true ) ) { $deleted++; } }
echo "\n  cleanup: removed $deleted fixture page(s)\n";

echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

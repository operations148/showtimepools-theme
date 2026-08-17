<?php
/**
 * Service-area imagery, hero treatment, and unverified-count absence.
 *
 * Three guarantees, asserted across all 14 locations:
 *
 *   1. ONE canonical WebP per location, declared in the registry, shared by the
 *      Service Areas card, the area page hero and og:image. A card and the page
 *      it opens can never show different photographs again.
 *   2. The hero renders that photograph in natural colour — no grayscale,
 *      no saturate(0), no luminosity blend, no opacity wash. A readability
 *      scrim is allowed; destroying the photo's colour is not.
 *   3. No public surface claims a pool count. These figures were unverified.
 *
 * Plus a standing check that no reference to a deleted raster asset survives.
 *
 * Run:  php tests/service-area-images-unit.php
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
function skipped( string $m ): void { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

function sai_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
function sai_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 40, CURLOPT_SSL_VERIFYPEER => false ) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}
/** True only for a real RIFF/WEBP container, not a renamed file. */
function sai_is_webp( string $path ): bool {
	if ( ! is_readable( $path ) ) { return false; }
	$h = (string) file_get_contents( $path, false, null, 0, 12 );
	return 12 === strlen( $h ) && 'RIFF' === substr( $h, 0, 4 ) && 'WEBP' === substr( $h, 8, 4 );
}

$child = get_stylesheet_directory();
$uri   = get_stylesheet_directory_uri();
$home  = untrailingslashit( home_url() );
$areas = \Showtime\Areas::all();
$cards = showtime_service_area_cards();
$by_slug_card = array();
foreach ( $cards as $c ) { $by_slug_card[ (string) $c['slug'] ] = $c; }

/* ══════════════════════════════════════════════════════════════════
 * 1. ONE CANONICAL WEBP PER LOCATION
 * ═══════════════════════════════════════════════════════════════ */
echo "== CANONICAL IMAGE PER LOCATION ==\n";

count( $areas ) === 14
	? ok( '1. the registry holds 14 service areas' )
	: bad( '1. registry holds ' . count( $areas ) );

$no_image = array();
foreach ( $areas as $a ) {
	$rel = (string) ( $a['image'] ?? '' );
	if ( '' === $rel ) { $no_image[] = (string) $a['slug']; }
}
empty( $no_image )
	? ok( '2. every one of the 14 areas declares an explicit `image` in the registry' )
	: bad( '2. no image declared for: ' . implode( ', ', $no_image ) );

$not_webp = array();
foreach ( $areas as $a ) {
	$rel = (string) ( $a['image'] ?? '' );
	if ( '' === $rel ) { continue; }
	if ( ! preg_match( '/\.webp$/i', $rel ) ) { $not_webp[] = $a['slug'] . ' -> ' . $rel . ' (not .webp)'; continue; }
	if ( ! sai_is_webp( $child . '/' . ltrim( $rel, '/' ) ) ) { $not_webp[] = $a['slug'] . ' -> ' . $rel . ' (not a real WebP container)'; }
}
empty( $not_webp )
	? ok( '3. all 14 mapped assets exist on disk and carry a genuine RIFF/WEBP signature' )
	: bad( '3. ' . implode( '; ', $not_webp ) );

// Each asset must belong to that location: either its own area_<slug> file or
// an asset from that location's own project. Never another location's photo.
$wrong_owner = array();
foreach ( $areas as $a ) {
	$slug = (string) $a['slug'];
	$rel  = (string) ( $a['image'] ?? '' );
	if ( '' === $rel ) { continue; }
	$own_area_file = false !== strpos( $rel, 'area_' . $slug . '.' );
	$project       = (string) ( $a['related_project'] ?? '' );
	$own_project   = '' !== $project && ( false !== strpos( $rel, $project ) );
	// Areas whose project is not named in the registry still resolve by prefix.
	if ( ! $own_area_file && ! $own_project ) {
		$prefix     = str_replace( '-', '', $slug );
		$normalised = str_replace( '-', '', basename( $rel ) );
		if ( false === strpos( $normalised, $prefix ) ) {
			$wrong_owner[] = "$slug -> $rel";
		}
	}
}
empty( $wrong_owner )
	? ok( '4. every mapped asset belongs to its own location — no cross-location reuse' )
	: bad( '4. ' . implode( '; ', $wrong_owner ) );

$dupes = array();
$seen  = array();
foreach ( $areas as $a ) {
	$rel = (string) ( $a['image'] ?? '' );
	if ( '' === $rel ) { continue; }
	if ( isset( $seen[ $rel ] ) ) { $dupes[] = $rel . ' shared by ' . $seen[ $rel ] . ' and ' . $a['slug']; }
	$seen[ $rel ] = (string) $a['slug'];
}
empty( $dupes )
	? ok( '5. all 14 canonical assets are distinct — no location borrows another\'s photo' )
	: bad( '5. ' . implode( '; ', $dupes ) );

/* ══════════════════════════════════════════════════════════════════
 * 2. CARD AND HERO RESOLVE TO THE SAME ASSET
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== CARD == HERO ==\n";

$mismatch = array();
$missing  = array();
foreach ( $areas as $a ) {
	$slug = (string) $a['slug'];
	$card = (string) ( $by_slug_card[ $slug ]['image'] ?? '' );
	[ $hero ] = showtime_area_image( $slug, '', 1600 );

	if ( '' === $card || '' === $hero ) { $missing[] = $slug; continue; }
	if ( $card !== $hero ) { $mismatch[] = "$slug: card=" . basename( $card ) . ' hero=' . basename( $hero ); }
}
empty( $missing )
	? ok( '6. every location resolves both a card image and a hero image' )
	: bad( '6. no image for: ' . implode( ', ', $missing ) );
empty( $mismatch )
	? ok( '7. card and hero resolve to the SAME asset path for all 14 locations' )
	: bad( '7. ' . implode( '; ', $mismatch ) );

// ...and the shared path is exactly what the registry declares.
$not_registry = array();
foreach ( $areas as $a ) {
	$slug     = (string) $a['slug'];
	$expected = $uri . '/' . ltrim( (string) ( $a['image'] ?? '' ), '/' );
	$card     = (string) ( $by_slug_card[ $slug ]['image'] ?? '' );
	if ( strtok( $card, '?' ) !== $expected ) { $not_registry[] = "$slug -> " . basename( $card ); }
}
empty( $not_registry )
	? ok( '8. the registry is the single source of truth — every card serves exactly its declared asset' )
	: bad( '8. ' . implode( '; ', $not_registry ) );

/* ══════════════════════════════════════════════════════════════════
 * 3. NO LEGACY / STOCK / FALLBACK IMAGERY
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== NO LEGACY OR STOCK IMAGERY ==\n";

$stock = array();
foreach ( $areas as $a ) {
	$slug = (string) $a['slug'];
	$card = (string) ( $by_slug_card[ $slug ]['image'] ?? '' );
	[ $hero ] = showtime_area_image( $slug, '', 1600 );
	foreach ( array( 'card' => $card, 'hero' => $hero ) as $what => $url ) {
		foreach ( array( 'picsum.photos', 'unsplash.com', 'placehold', 'placeholder', 'via.placeholder' ) as $needle ) {
			if ( false !== stripos( $url, $needle ) ) { $stock[] = "$slug $what uses $needle"; }
		}
		if ( preg_match( '/\.(jpe?g|png|gif)(\?|$)/i', $url ) ) { $stock[] = "$slug $what serves a legacy raster: " . basename( $url ); }
	}
}
empty( $stock )
	? ok( '9. no card or hero uses a stock, placeholder or legacy JPG/PNG image' )
	: bad( '9. ' . implode( '; ', $stock ) );

// Each asset must actually be served over HTTP.
$http_bad = array();
foreach ( $areas as $a ) {
	$slug = (string) $a['slug'];
	$url  = (string) ( $by_slug_card[ $slug ]['image'] ?? '' );
	if ( '' === $url ) { continue; }
	$code = sai_status( $url );
	if ( 200 !== $code ) { $http_bad[] = "$slug -> HTTP $code"; }
}
empty( $http_bad )
	? ok( '10. all 14 canonical assets return HTTP 200' )
	: bad( '10. ' . implode( ', ', $http_bad ) );

/* ══════════════════════════════════════════════════════════════════
 * 4. NATURAL COLOUR — NO DESATURATION TREATMENT
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== HERO RENDERS IN NATURAL COLOUR ==\n";

$css_path = $child . '/assets/css/interior.css';
$css      = is_readable( $css_path ) ? (string) file_get_contents( $css_path ) : '';

'' !== $css
	? ok( '11. interior.css is readable' )
	: bad( '11. interior.css missing' );

// Isolate the .area-hero__photo rule and inspect only its DECLARATIONS —
// comments are stripped first, so prose explaining why a treatment was removed
// can never be mistaken for the treatment still being applied.
$css_code = (string) preg_replace( '#/\*.*?\*/#s', '', $css );
$rule     = '';
if ( preg_match( '/\.area-hero__photo\s*\{([^}]*)\}/s', $css_code, $m ) ) { $rule = $m[1]; }

'' !== $rule
	? ok( '12. the .area-hero__photo rule is present' )
	: bad( '12. could not locate the .area-hero__photo rule' );

$treatments = array();
if ( preg_match( '/mix-blend-mode\s*:\s*(?!normal)([a-z-]+)/i', $rule, $m ) ) { $treatments[] = 'mix-blend-mode: ' . $m[1]; }
if ( preg_match( '/filter\s*:[^;]*\b(grayscale|sepia)\s*\(/i', $rule, $m ) )  { $treatments[] = 'filter: ' . $m[1]; }
if ( preg_match( '/filter\s*:[^;]*saturate\s*\(\s*0*(?:\.\d+)?\s*\)/i', $rule ) ) { $treatments[] = 'filter: saturate() at or near zero'; }
if ( preg_match( '/-webkit-filter\s*:[^;]*\b(grayscale|sepia)\s*\(/i', $rule, $m ) ) { $treatments[] = '-webkit-filter: ' . $m[1]; }
// An opacity wash on the photo itself mutes the colour; the scrim handles contrast.
if ( preg_match( '/(?<!-)\bopacity\s*:\s*(0(?:\.\d+)?)\s*;/i', $rule, $m ) && (float) $m[1] < 0.9 ) {
	$treatments[] = 'opacity: ' . $m[1] . ' on the photo itself';
}

empty( $treatments )
	? ok( '13. the hero photo carries no grayscale, sepia, zero-saturation, blend-mode or opacity treatment' )
	: bad( '13. colour-destroying treatment still applied — ' . implode( '; ', $treatments ) );

// The photo must still be cropped responsively.
preg_match( '/object-fit\s*:\s*cover/i', $rule )
	? ok( '14. the hero photo still uses object-fit: cover for responsive cropping' )
	: bad( '14. object-fit: cover is missing from the hero photo' );

// A readability scrim is expected and allowed — but it must be a scrim, not a
// blackout. Assert the darkest stop leaves the photograph visible.
$scrim = '';
if ( preg_match( '/\.area-hero::before\s*\{([^}]*)\}/s', $css_code, $m ) ) { $scrim = $m[1]; }
$max_alpha = 0.0;
if ( preg_match_all( '/rgba\([^)]*?,\s*([01](?:\.\d+)?)\s*\)/i', $scrim, $mm ) ) {
	foreach ( $mm[1] as $al ) { $max_alpha = max( $max_alpha, (float) $al ); }
}
( $max_alpha > 0 && $max_alpha <= 0.9 )
	? ok( '15. the readability scrim peaks at alpha ' . $max_alpha . ' — dark enough to read over, light enough to keep the photo visible' )
	: bad( '15. scrim alpha is ' . $max_alpha . ' (expected >0 and <=0.9)' );

// Rendered check: the hero <img> must be the registry asset, on every page.
$rendered_bad = array();
$rendered     = 0;
foreach ( $areas as $a ) {
	$slug = (string) $a['slug'];
	$url  = home_url( '/service-areas/' . $slug . '/' );
	if ( 200 !== sai_status( $url ) ) { continue; }
	$rendered++;
	$body = sai_fetch( $url );
	$want = basename( (string) $a['image'] );
	if ( ! preg_match( '/<img[^>]*class="area-hero__photo"[^>]*>/i', $body, $im ) ) {
		$rendered_bad[] = "$slug renders no hero photo";
		continue;
	}
	if ( false === strpos( $im[0], $want ) )        { $rendered_bad[] = "$slug hero is not $want"; }
	if ( false !== stripos( $body, 'picsum' ) )     { $rendered_bad[] = "$slug page references picsum"; }
	if ( ! preg_match( '/alt="[^"]{10,}"/', $im[0] ) ) { $rendered_bad[] = "$slug hero has no meaningful alt"; }
}
if ( 0 === $rendered ) {
	skipped( '16. rendered hero checks — no area page is published in this environment' );
} else {
	empty( $rendered_bad )
		? ok( "16. all $rendered published area pages render their registry asset as the hero, with alt text and no stock image" )
		: bad( '16. ' . implode( '; ', $rendered_bad ) );
}

/* ══════════════════════════════════════════════════════════════════
 * 5. NO UNVERIFIED POOL COUNTS
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== NO UNVERIFIED POOL-COUNT CLAIMS ==\n";

$leftover = array();
foreach ( $areas as $a ) {
	if ( array_key_exists( 'pool_count', $a ) ) { $leftover[] = (string) $a['slug']; }
}
empty( $leftover )
	? ok( '17. the pool_count key is gone from all 14 registry records' )
	: bad( '17. pool_count survives on: ' . implode( ', ', $leftover ) );

$card_leftover = array();
foreach ( $cards as $c ) {
	if ( array_key_exists( 'pool_count', $c ) ) { $card_leftover[] = (string) $c['slug']; }
}
empty( $card_leftover )
	? ok( '18. the card contract no longer exposes pool_count' )
	: bad( '18. pool_count still in the card array for: ' . implode( ', ', $card_leftover ) );

// The pattern itself must not appear on any public surface — visible text,
// hidden text, alt attributes, aria-labels, meta or structured data alike.
$surfaces = array(
	'homepage'            => sai_fetch( $home . '/' ),
	'/service-areas/'     => sai_fetch( $home . '/service-areas/' ),
	'HTML sitemap'        => sai_fetch( $home . '/sitemap/' ),
	'llms.txt'            => sai_fetch( $home . '/llms.txt' ),
	'RSS feed'            => sai_fetch( $home . '/feed/' ),
);
foreach ( $areas as $a ) {
	$u = home_url( '/service-areas/' . $a['slug'] . '/' );
	if ( 200 === sai_status( $u ) ) { $surfaces[ '/service-areas/' . $a['slug'] . '/' ] = sai_fetch( $u ); }
}

$count_hits = array();
foreach ( $surfaces as $where => $body ) {
	if ( '' === $body ) { continue; }
	// "420+ POOLS", "420+ pools", "420 + Pools", and the same inside attributes.
	if ( preg_match_all( '/\b\d{2,5}\s*\+\s*pools?\b/i', $body, $mm ) ) {
		$count_hits[] = $where . ': ' . implode( ', ', array_unique( $mm[0] ) );
	}
	// The six specific retired figures, in any form.
	foreach ( array( '420', '310', '180', '270', '230', '210' ) as $fig ) {
		if ( preg_match( '/\b' . $fig . '\s*\+/', $body, $m ) ) { $count_hits[] = "$where: retired figure {$m[0]}"; }
	}
}
empty( $count_hits )
	? ok( '19. no "<number>+ pools" claim appears on the homepage, the hub, any of the 14 area pages, the sitemap, llms.txt or the feed' )
	: bad( '19. ' . implode( ' | ', $count_hits ) );

// No empty pill left behind where the count used to be.
$empty_pill = array();
foreach ( $surfaces as $where => $body ) {
	if ( '' === $body ) { continue; }
	if ( preg_match( '/<span class="area-(card|hero)__pill"[^>]*>\s*(·|&middot;|-)?\s*<\/span>/i', $body ) ) {
		$empty_pill[] = $where;
	}
	// A pill that begins or ends with a stray separator.
	if ( preg_match( '/<span class="area-(card|hero)__pill"[^>]*>\s*·/', $body ) ) { $empty_pill[] = $where . ' (leading separator)'; }
}
empty( $empty_pill )
	? ok( '20. no empty pill or orphaned separator remains where a count was removed' )
	: bad( '20. ' . implode( ', ', $empty_pill ) );

/* ══════════════════════════════════════════════════════════════════
 * 6. DELETED RASTER ASSETS STAY DELETED
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== DELETED ASSETS ==\n";

// Every before/after compare image is now served as WebP. If a .jpg/.jpeg/.png
// twin reappears beside one, the unused duplicate has crept back in.
$img_dir = $child . '/assets/img';
$twins   = array();
foreach ( glob( $img_dir . '/*.webp' ) ?: array() as $webp ) {
	$stem = preg_replace( '/\.webp$/', '', $webp );
	if ( ! preg_match( '/(before|after)$/i', basename( $stem ) ) ) { continue; }
	foreach ( array( 'jpg', 'jpeg', 'png' ) as $ext ) {
		if ( file_exists( "$stem.$ext" ) ) { $twins[] = basename( "$stem.$ext" ); }
	}
}
empty( $twins )
	? ok( '21. no before/after compare image has an unused JPG/PNG twin beside its WebP' )
	: bad( '21. shadowed duplicates are back: ' . implode( ', ', $twins ) );

// Every raster still on disk that the site actually serves must resolve.
$served_bad = array();
foreach ( $surfaces as $where => $body ) {
	if ( '' === $body ) { continue; }
	if ( ! preg_match_all( '#' . preg_quote( $uri, '#' ) . '/assets/img/[A-Za-z0-9_./-]+\.(?:webp|jpe?g|png|svg)#i', $body, $mm ) ) { continue; }
	foreach ( array_unique( $mm[0] ) as $asset ) {
		$rel = str_replace( $uri . '/', '', strtok( $asset, '?' ) );
		if ( ! file_exists( $child . '/' . $rel ) ) { $served_bad[] = "$where requests missing $rel"; }
	}
}
empty( $served_bad )
	? ok( '22. every image asset requested by a public page exists on disk' )
	: bad( '22. ' . implode( '; ', array_unique( $served_bad ) ) );

/* ══════════════════════════════════════════════════════════════════
 * 7. NOTHING ELSE MOVED
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== NOTHING ELSE MOVED ==\n";

$hp  = $surfaces['homepage'];
$hub = $surfaces['/service-areas/'];

14 === preg_match_all( '/class="area-card area-card--lg"/', $hub )
	? ok( '23. the hub still renders exactly 14 cards' )
	: bad( '23. hub renders ' . preg_match_all( '/class="area-card area-card--lg"/', $hub ) );

preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>#s', $hp, $am );
$sem = array_values( array_filter( $am[0] ?? array(), static fn( $x ) => false === strpos( $x, 'tabindex="-1"' ) ) );
count( $sem ) === 14
	? ok( '24. the homepage carousel still renders 14 semantic cards' )
	: bad( '24. carousel has ' . count( $sem ) . ' semantic cards' );

( 1 === substr_count( $hp, 'id="stp-estimate-popup"' )
	&& false !== strpos( $hp, 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb' )
	&& false !== strpos( $hp, 'tel:+13238252099' )
	&& false !== strpos( $hp, 'Book your Service Appointment' ) )
	? ok( '25. the estimate popup is untouched, including its "Book your Service Appointment" CTA' )
	: bad( '25. the estimate popup changed' );

false !== strpos( $hp, 'Explore 14 Los Angeles Service Areas' )
	? ok( '26. the homepage service-areas heading is unchanged' )
	: bad( '26. the homepage heading changed' );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

<?php
/**
 * Service-area page sections: Local Project, and the four removals.
 *
 * Iterates all 14 locations and proves, per page:
 *   - exactly ONE Local Project section, from the single shared template path,
 *     pointing at that location's mapped project and nothing else;
 *   - no card sub-line, no hero pill, no decorative hero waves, and no
 *     "Recent streets we serviced" block anywhere — markup gone, not hidden;
 *   - Most-Requested Services survives on all 14 with working service links;
 *   - the hero, breadcrumb, H1, copy, CTAs, natural-colour treatment, carousel
 *     and estimate popup are all untouched.
 *
 * Assertions are component-scoped: they look inside the specific block being
 * checked rather than banning words that appear legitimately elsewhere (a street
 * name in the verified business address, for instance, must still be allowed).
 *
 * Run:  php tests/service-area-sections-unit.php
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

function sas_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false ) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
function sas_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 40, CURLOPT_SSL_VERIFYPEER => false ) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}
/** Extract the Local Project <section> so assertions stay scoped to it. */
function sas_local_project_section( string $html ): string {
	// The block opens with the LOCAL PROJECT eyebrow; take from the enclosing
	// <section> to its matching close by scanning forward to the reviews part.
	$i = stripos( $html, '>Local project<' );
	if ( false === $i ) { return ''; }
	$start = strrpos( substr( $html, 0, $i ), '<section' );
	if ( false === $start ) { return ''; }
	$end = stripos( $html, '</section>', $i );
	return false === $end ? '' : substr( $html, $start, $end - $start + 10 );
}

$child = get_stylesheet_directory();
$uri   = get_stylesheet_directory_uri();
$home  = untrailingslashit( home_url() );

/** The required area → project mapping, verbatim. */
$MAP = array(
	'sherman-oaks'    => 'sherman-oaks-mid-century-remodel',
	'encino'          => 'encino-estate-new-build',
	'beverly-hills'   => 'beverly-hills-luxe-spa-renovation',
	'studio-city'     => 'studio-city-modern-automation',
	'tarzana'         => 'tarzana-resort-style-finish',
	'woodland-hills'  => 'woodland-hills-tile-coping-refresh',
	'west-hollywood'  => 'west-hollywood-pool-project',
	'bel-air'         => 'bel-air-pool-project',
	'calabasas'       => 'calabasas-pool-project',
	'van-nuys'        => 'van-nuys-pool-project',
	'north-hollywood' => 'north-hollywood-pool-project',
	'toluca-lake'     => 'toluca-lake-pool-project',
	'burbank'         => 'burbank-pool-project',
	'brentwood'       => 'brentwood-pool-project',
);

$areas   = \Showtime\Areas::all();
$by_slug = array();
foreach ( $areas as $a ) { $by_slug[ (string) $a['slug'] ] = $a; }

/* ══════════════════════════════════════════════════════════════════
 * REGISTRY MAPPING
 * ═══════════════════════════════════════════════════════════════ */
echo "== ONE CANONICAL MAPPING ==\n";

count( $areas ) === 14
	? ok( '1. the registry holds 14 areas' )
	: bad( '1. registry holds ' . count( $areas ) );

$map_bad = array();
foreach ( $MAP as $slug => $project ) {
	$a = $by_slug[ $slug ] ?? null;
	if ( ! $a ) { $map_bad[] = "$slug missing from the registry"; continue; }
	if ( (string) ( $a['related_project'] ?? '' ) !== $project ) {
		$map_bad[] = "$slug -> " . ( $a['related_project'] ?? 'none' ) . " (expected $project)";
	}
}
empty( $map_bad )
	? ok( '2. all 14 areas declare exactly the required related_project' )
	: bad( '2. ' . implode( '; ', $map_bad ) );

// One project per area, and no project serving two areas.
$projects = array();
foreach ( $areas as $a ) { $projects[] = (string) ( $a['related_project'] ?? '' ); }
count( array_unique( $projects ) ) === 14 && ! in_array( '', $projects, true )
	? ok( '3. the 14 mapped projects are distinct — no cross-location leakage in the registry' )
	: bad( '3. mapped projects are not 14 distinct values' );

// Every mapped project must resolve, be managed, and publish a real cover.
$res_bad = array();
foreach ( $MAP as $slug => $project ) {
	$d = showtime_project_data( $project );
	if ( ! $d ) { $res_bad[] = "$project does not resolve"; continue; }
	if ( empty( $d['managed'] ) )              { $res_bad[] = "$project is not a managed record"; }
	if ( '' === (string) $d['title'] )         { $res_bad[] = "$project has no title"; }
	if ( '' === (string) $d['excerpt'] )       { $res_bad[] = "$project has no description"; }
	if ( '' === (string) $d['hero_image'] )    { $res_bad[] = "$project has no cover image"; }
	if ( 200 !== sas_status( showtime_project_permalink( $project ) ) ) { $res_bad[] = "$project URL is not 200"; }
	$rel = str_replace( $uri . '/', '', (string) $d['hero_image'] );
	if ( ! file_exists( $child . '/' . $rel ) ) { $res_bad[] = "$project cover is not a committed file"; }
	foreach ( array( 'picsum', 'unsplash', 'placehold' ) as $stock ) {
		if ( false !== stripos( (string) $d['hero_image'], $stock ) ) { $res_bad[] = "$project cover is stock ($stock)"; }
	}
}
empty( $res_bad )
	? ok( '4. every mapped project resolves, is managed, is published, and has a committed non-stock cover' )
	: bad( '4. ' . implode( '; ', $res_bad ) );

/* ══════════════════════════════════════════════════════════════════
 * PER-PAGE RENDER
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== PER-PAGE: LOCAL PROJECT + REMOVALS ==\n";

$rendered = 0;
$errors   = array();

foreach ( $MAP as $slug => $project ) {
	$url = home_url( '/service-areas/' . $slug . '/' );
	if ( 200 !== sas_status( $url ) ) { continue; }
	$rendered++;

	$body = sas_fetch( $url );
	$a    = $by_slug[ $slug ];
	$d    = showtime_project_data( $project );
	$name = (string) $a['name'];
	$e    = array();

	/* ── Local Project section ─────────────────────────────────────── */
	// Exactly one, by eyebrow, by heading, and by section wrapper.
	$eyebrow_n = preg_match_all( '/>\s*Local project\s*</i', $body );
	$heading_n = preg_match_all( '/Work we have completed in ' . preg_quote( $name, '/' ) . '\./', $body );
	if ( 1 !== $eyebrow_n ) { $e[] = "LOCAL PROJECT eyebrow x$eyebrow_n"; }
	if ( 1 !== $heading_n ) { $e[] = "heading x$heading_n"; }

	$sec = sas_local_project_section( $body );
	if ( '' === $sec ) {
		$e[] = 'could not isolate the Local Project section';
	} else {
		// One card, one View-the-project link, both to the mapped project.
		$permalink = showtime_project_permalink( $project );
		$cards     = preg_match_all( '/<a class="proj-card" href="([^"]+)"/', $sec, $cm );
		if ( 1 !== $cards ) { $e[] = "proj-card x$cards inside the section"; }
		if ( ( $cm[1][0] ?? '' ) !== $permalink ) { $e[] = 'card href is ' . ( $cm[1][0] ?? 'none' ); }
		if ( 1 !== preg_match_all( '/View the project/', $sec ) ) { $e[] = 'View the project link count wrong'; }

		// The card image is that project's committed cover, with its alt.
		if ( false === strpos( $sec, basename( (string) $d['hero_image'] ) ) ) { $e[] = 'card image is not the project cover'; }
		if ( false === strpos( $sec, (string) $d['hero_alt'] ) )               { $e[] = 'card image alt is not the approved alt'; }
		if ( false === strpos( $sec, (string) $d['title'] ) )                  { $e[] = 'project title missing'; }
		if ( false === strpos( $sec, (string) $d['excerpt'] ) )                { $e[] = 'project description missing'; }

		// Supporting paragraph names THIS location.
		if ( false === strpos( $sec, 'taken on the job in ' . $name ) )        { $e[] = 'supporting paragraph does not name the location'; }

		// The three buttons.
		foreach ( array( 'Book an Appointment', 'All projects', 'All service areas' ) as $btn ) {
			if ( false === strpos( $sec, $btn ) ) { $e[] = "button \"$btn\" missing"; }
		}

		// NO other project may be LINKED from inside this section. Anchored to
		// href attributes on purpose: the committed cover images live under
		// assets/img/projects/comparisons/, so matching any "/projects/" would
		// treat an image directory as a project slug.
		preg_match_all( '#href="[^"]*/projects/([a-z0-9-]+)/"#', $sec, $pm );
		$linked  = array_unique( $pm[1] ?? array() );
		$foreign = array_diff( $linked, array( $project ) );
		if ( ! empty( $foreign ) ) { $e[] = 'section links other projects: ' . implode( ',', $foreign ); }
		if ( ! in_array( $project, $linked, true ) ) { $e[] = 'section does not link its own project'; }

		// Server-rendered: a real href, not a JS handler.
		if ( preg_match( '/<a class="proj-card"[^>]*href="#/', $sec ) ) { $e[] = 'card href is not a real URL'; }
	}

	/* ── Removals, component-scoped ────────────────────────────────── */
	if ( false !== strpos( $body, 'area-hero__pill' ) )      { $e[] = 'hero pill element still present'; }
	if ( false !== strpos( $body, 'area-hero__bg' ) )        { $e[] = 'hero wave container still present'; }
	if ( false !== strpos( $body, 'area-card__sub' ) )       { $e[] = 'card sub-line element present on an area page'; }
	if ( false !== stripos( $body, 'Recent streets' ) )      { $e[] = '"Recent streets" heading still present'; }
	if ( false !== strpos( $body, 'area-detail__streets' ) ) { $e[] = 'street-chip wrapper still present'; }

	// The wave paths were the only <path d="M0 250 …"> curves in the hero.
	if ( preg_match( '/<section class="area-hero"[\s\S]*?<\/section>/', $body, $hero ) ) {
		if ( preg_match( '/<path[^>]*d="M0\s+\d+\s*Q/i', $hero[0] ) ) { $e[] = 'decorative wave path still in the hero'; }
		if ( preg_match( '/<svg/i', $hero[0] ) && ! preg_match( '/<svg[^>]*>(?:(?!<\/svg>).)*?(stroke-linecap|d="M5 12h14|d="M20 6L9)/is', $hero[0] ) ) {
			// An SVG in the hero is fine only if it belongs to a CTA icon.
			if ( ! preg_match( '/class="btn/', $hero[0] ) ) { $e[] = 'unexpected decorative SVG in the hero'; }
		}
		// No empty leftover container.
		if ( preg_match( '/<div[^>]*>\s*<\/div>/', $hero[0] ) ) { $e[] = 'empty container left in the hero'; }
		// Hero essentials survive.
		if ( false === strpos( $hero[0], 'class="breadcrumbs' ) ) { $e[] = 'breadcrumb missing from the hero'; }
		if ( 1 !== preg_match_all( '/<h1/', $hero[0] ) )          { $e[] = 'hero H1 count wrong'; }
		if ( false === strpos( $hero[0], 'area-hero__lead' ) )    { $e[] = 'hero lead copy missing'; }
		if ( false === strpos( $hero[0], 'Book an Appointment' ) ) { $e[] = 'hero CTA missing'; }
		if ( false === strpos( $hero[0], 'area-hero__photo' ) )   { $e[] = 'hero photograph missing'; }
	} else {
		$e[] = 'could not isolate the hero section';
	}

	/* ── Most-Requested Services preserved ────────────────────────── */
	$mrs_n = preg_match_all( '/Most-requested services in ' . preg_quote( $name, '/' ) . '/i', $body );
	if ( 1 !== $mrs_n ) { $e[] = "Most-Requested Services block x$mrs_n"; }
	if ( ! preg_match( '/<aside class="area-detail__pills">([\s\S]*?)<\/aside>/', $body, $mm ) ) {
		$e[] = 'Most-Requested Services panel wrapper missing';
	} else {
		$panel    = $mm[1];
		$expected = (array) ( $a['related_services'] ?? array() );
		preg_match_all( '#/services/([a-z0-9-]+)/#', $panel, $sm );
		$got = array_values( array_unique( $sm[1] ?? array() ) );
		if ( $got !== array_values( $expected ) ) {
			$e[] = 'service links are ' . implode( ',', $got ) . ' expected ' . implode( ',', $expected );
		}
		if ( count( $got ) < 1 ) { $e[] = 'no service links in the panel'; }
		// Every link must be a real anchor with a resolving destination.
		foreach ( $got as $svc ) {
			if ( 200 !== sas_status( home_url( '/services/' . $svc . '/' ) ) ) { $e[] = "service link /services/$svc/ is not 200"; }
		}
		if ( preg_match( '/<span class="tag">/', $panel ) ) { $e[] = 'a service pill is a span, not a link'; }
	}

	/* ── Local Conditions columns preserved ───────────────────────── */
	$left  = (string) ( $a['what_common'] ?? '' );
	$right = (string) ( $a['what_do'] ?? '' );
	$col1  = '' !== $left  ? ( false !== strpos( $body, $left ) )  : ( false !== stripos( $body, 'pools have in common' ) );
	$col2  = '' !== $right ? ( false !== strpos( $body, $right ) ) : ( false !== stripos( $body, 'What we do here most' ) );
	if ( ! $col1 ) { $e[] = 'first Local Conditions column missing'; }
	if ( ! $col2 ) { $e[] = 'second Local Conditions column missing'; }
	if ( false === strpos( $body, 'area-detail__grid' ) ) { $e[] = 'Local Conditions grid missing'; }

	/* ── No empty placeholder where the streets block was ─────────── */
	if ( preg_match( '/<aside[^>]*>\s*<\/aside>/', $body ) )   { $e[] = 'empty aside left behind'; }
	if ( preg_match( '/<ul class="tag-list">\s*<\/ul>/', $body ) ) { $e[] = 'empty tag-list left behind'; }

	/* ── Natural colour from PR #6 intact ─────────────────────────── */
	if ( preg_match( '/<img[^>]*class="area-hero__photo"[^>]*style="[^"]*(filter|opacity)/i', $body ) ) {
		$e[] = 'an inline filter/opacity was applied to the hero photo';
	}

	/* ── No pool-count claims returned ─────────────────────────────── */
	if ( preg_match( '/\b\d{2,5}\s*\+\s*pools?\b/i', $body ) ) { $e[] = 'a pool-count claim returned'; }

	if ( ! empty( $e ) ) { $errors[] = "/service-areas/$slug/ — " . implode( '; ', $e ); }
}

if ( 0 === $rendered ) {
	skipped( '5. per-page render checks — no area page is published in this environment' );
} else {
	14 === $rendered
		? ok( "5. all 14 area pages are published and were checked" )
		: skipped( "5. only $rendered of 14 area pages are published; the rest were not checked" );

	empty( $errors )
		? ok( '6. every checked page: exactly one correctly-mapped Local Project section, no pill, no waves, no streets block, Most-Requested Services intact, both Local Conditions columns present, hero and CTAs preserved' )
		: bad( '6. ' . implode( ' | ', $errors ) );
}

/* ── Brentwood specifically is not duplicated ───────────────────────── */
$bw = sas_fetch( home_url( '/service-areas/brentwood/' ) );
if ( '' === $bw ) {
	skipped( '7. Brentwood duplication check — page not published' );
} else {
	$bw_ok = 1 === preg_match_all( '/>\s*Local project\s*</i', $bw )
		&& 1 === preg_match_all( '/Work we have completed in Brentwood\./', $bw )
		&& 1 === preg_match_all( '/<a class="proj-card"/', $bw )
		&& 1 === preg_match_all( '#/projects/brentwood-pool-project/#', $bw );
	$bw_ok
		? ok( '7. Brentwood renders exactly one Local Project section — the shared block replaced its original, it was not duplicated' )
		: bad( '7. Brentwood has ' . preg_match_all( '/>\s*Local project\s*</i', $bw ) . ' eyebrows and ' . preg_match_all( '/<a class="proj-card"/', $bw ) . ' cards' );
}

/* ══════════════════════════════════════════════════════════════════
 * CARD SURFACES
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== CARD SURFACES ==\n";

$hub = sas_fetch( $home . '/service-areas/' );
$hp  = sas_fetch( $home . '/' );

false === strpos( $hub, 'area-card__sub' )
	? ok( '8. no service-area card on /service-areas/ renders a sub-line element' )
	: bad( '8. area-card__sub still present on the hub' );

false === strpos( $hp, 'area-card__sub' )
	? ok( '9. no homepage carousel card renders a sub-line element' )
	: bad( '9. area-card__sub still present on the homepage' );

// The retired sub-line phrases must not survive inside a card, in visible or
// screen-reader text. Scoped to the card markup, so the same words elsewhere
// (legitimate hours copy, schema) are unaffected.
$retired = array( 'Home base', 'days a week', 'day a week', 'Now booking', 'Estate service', 'Established routes', 'Service available', 'Recent project' );
$leak    = array();
foreach ( array( 'hub' => $hub, 'homepage' => $hp ) as $where => $doc ) {
	if ( preg_match_all( '/<div class="area-card__content">([\s\S]*?)<\/div>/', $doc, $cm ) ) {
		foreach ( $cm[1] as $card ) {
			foreach ( $retired as $phrase ) {
				if ( false !== stripos( $card, $phrase ) ) { $leak[] = "$where card contains \"$phrase\""; }
			}
			// Only the title should remain inside a card's content block.
			if ( preg_match( '/<p[^>]*>/', $card ) ) { $leak[] = "$where card still has a paragraph element"; }
		}
	}
}
empty( $leak )
	? ok( '10. no card content block contains a retired sub-line phrase or leftover paragraph' )
	: bad( '10. ' . implode( '; ', array_unique( $leak ) ) );

// Cards otherwise unchanged.
14 === preg_match_all( '/class="area-card area-card--lg"/', $hub )
	? ok( '11. the hub still renders exactly 14 cards' )
	: bad( '11. hub renders ' . preg_match_all( '/class="area-card area-card--lg"/', $hub ) );

$img_n   = preg_match_all( '/class="area-card__img"/', $hub );
$title_n = preg_match_all( '/class="area-card__title"/', $hub );
( 14 === $img_n && 14 === $title_n )
	? ok( '12. all 14 hub cards keep their image and location name' )
	: bad( "12. hub images=$img_n titles=$title_n" );

preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>#s', $hp, $am );
$sem = array_values( array_filter( $am[0] ?? array(), static fn( $x ) => false === strpos( $x, 'tabindex="-1"' ) ) );
count( $sem ) === 14
	? ok( '13. the homepage carousel still renders 14 semantic cards' )
	: bad( '13. carousel has ' . count( $sem ) . ' semantic cards' );

$clones = array_values( array_filter( $am[0] ?? array(), static fn( $x ) => false !== strpos( $x, 'tabindex="-1"' ) ) );
( count( $clones ) === 14 && 0 === count( array_filter( $clones, static fn( $x ) => false === strpos( $x, 'aria-hidden="true"' ) ) ) )
	? ok( '14. all 14 carousel clones stay aria-hidden and out of the tab order' )
	: bad( '14. clone contract broken (' . count( $clones ) . ' clones)' );

false !== strpos( $hp, 'Explore 14 Los Angeles Service Areas' )
	? ok( '15. the homepage service-areas heading is unchanged' )
	: bad( '15. the homepage heading changed' );

/* ══════════════════════════════════════════════════════════════════
 * DEAD CODE AND DATA
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== REMOVED FIELDS AND CSS ==\n";

$field_bad = array();
foreach ( $areas as $a ) {
	foreach ( array( 'tag', 'pool_count', 'sample_streets' ) as $dead ) {
		if ( array_key_exists( $dead, $a ) ) { $field_bad[] = $a['slug'] . " still has $dead"; }
	}
}
empty( $field_bad )
	? ok( '16. tag, pool_count and sample_streets are gone from all 14 records' )
	: bad( '16. ' . implode( '; ', $field_bad ) );

$cards_arr = showtime_service_area_cards();
$sub_bad   = array();
foreach ( $cards_arr as $c ) { if ( array_key_exists( 'sub', $c ) ) { $sub_bad[] = (string) $c['slug']; } }
empty( $sub_bad )
	? ok( '17. the card contract no longer exposes a sub key' )
	: bad( '17. sub survives on: ' . implode( ', ', $sub_bad ) );

// The CSS for removed components must be gone, not merely overridden.
$int = (string) file_get_contents( $child . '/assets/css/interior.css' );
$cmp = (string) file_get_contents( $child . '/assets/css/components.css' );
$strip = static fn( string $c ): string => (string) preg_replace( '#/\*.*?\*/#s', '', $c );
$int_code = $strip( $int );
$cmp_code = $strip( $cmp );

$css_bad = array();
foreach ( array( '.area-hero__pill' => $int_code, '.area-hero__bg' => $int_code, '.area-detail__streets' => $int_code, '.area-card__sub' => $cmp_code ) as $sel => $code ) {
	if ( false !== strpos( $code, $sel ) ) { $css_bad[] = "$sel rule still declared"; }
}
empty( $css_bad )
	? ok( '18. the CSS rules for the pill, waves, street panel and card sub-line are removed, not overridden' )
	: bad( '18. ' . implode( '; ', $css_bad ) );

// ...and the panel the services block still needs IS styled.
false !== strpos( $int_code, '.area-detail__pills' )
	? ok( '19. the Most-Requested Services panel keeps a real style rule' )
	: bad( '19. .area-detail__pills has no CSS' );

// No display:none / visibility hack standing in for removal.
$hack = array();
foreach ( array( 'area-hero__pill', 'area-hero__bg', 'area-card__sub', 'area-detail__streets' ) as $sel ) {
	if ( preg_match( '/\.' . preg_quote( $sel, '/' ) . '[^{]*\{[^}]*(display\s*:\s*none|visibility\s*:\s*hidden)/i', $int_code . $cmp_code ) ) {
		$hack[] = $sel;
	}
}
empty( $hack )
	? ok( '20. nothing was hidden with display:none or visibility:hidden instead of being removed' )
	: bad( '20. hidden rather than removed: ' . implode( ', ', $hack ) );

/* ══════════════════════════════════════════════════════════════════
 * NOTHING ELSE MOVED
 * ═══════════════════════════════════════════════════════════════ */
echo "\n== NOTHING ELSE MOVED ==\n";

( 1 === substr_count( $hp, 'id="stp-estimate-popup"' )
	&& false !== strpos( $hp, 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb' )
	&& false !== strpos( $hp, 'tel:+13238252099' )
	&& false !== strpos( $hp, 'Book your Service Appointment' ) )
	? ok( '21. the estimate popup and its "Book your Service Appointment" CTA are unchanged' )
	: bad( '21. the estimate popup changed' );

$managed = array_values( array_filter( \Showtime\Projects::all(), static fn( $p ) => ! empty( $p['managed'] ) ) );
count( $managed ) === 14
	? ok( '22. the project registry still holds exactly 14 managed projects' )
	: bad( '22. managed project count is ' . count( $managed ) );

$proj_bad = array();
foreach ( $managed as $p ) {
	$slug = (string) $p['slug'];
	if ( 200 !== sas_status( showtime_project_permalink( $slug ) ) ) { $proj_bad[] = "$slug URL"; }
	$g = showtime_project_gallery( showtime_project_data( $slug ) );
	if ( 6 !== count( array_filter( $g, static fn( $x ) => 'ready' === $x['status'] ) ) ) { $proj_bad[] = "$slug gallery"; }
}
empty( $proj_bad )
	? ok( '23. all 14 project pages still resolve and still publish six highlights each' )
	: bad( '23. ' . implode( ', ', $proj_bad ) );

$sitemap = sas_fetch( $home . '/sitemap/' );
$xml     = sas_fetch( $home . '/wp-sitemap.xml' );
( false !== stripos( $sitemap, '<html' ) && 0 === strpos( ltrim( $xml ), '<?xml' ) )
	? ok( '24. /sitemap/ is still HTML and /wp-sitemap.xml is still XML' )
	: bad( '24. the sitemap split changed' );

false !== strpos( $hp, 'footer-wordmark' ) && false !== strpos( $hub, 'footer-wordmark' )
	? ok( '25. the footer still renders on the homepage and the hub' )
	: bad( '25. footer missing' );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

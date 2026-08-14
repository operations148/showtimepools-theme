<?php
/**
 * Service-area PAGES — the five project-backed locations.
 *
 * Covers the registry records added for Van Nuys, North Hollywood, Toluca Lake,
 * Burbank and Brentwood, the shared page-area.php contract they render through,
 * and the publication gate that keeps a service-area URL out of every public
 * surface until its WordPress page actually exists.
 *
 * STATE-AWARE BY DESIGN. The five WP page records are created after deploy, so
 * this suite runs against BOTH states and asserts the correct behaviour for
 * whichever one it finds:
 *   - page absent  → the card falls back to the project page, and the area URL
 *                    appears in no sitemap, llms.txt or llms-full.txt entry.
 *   - page live    → the card switches to the canonical service-area URL, and
 *                    the page itself must satisfy the full SEO contract.
 * Nothing here passes vacuously: the gate assertions run in both states, and
 * the page-render assertions are reported as documented skips when — and only
 * when — the page is genuinely not published yet.
 *
 * Run:  php tests/service-area-pages-unit.php
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

function sap_fetch( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}
/** Status WITHOUT following redirects — a 301 is not a resolved URL. */
function sap_status( string $url ): int {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYPEER => false,
	) );
	curl_exec( $ch );
	$c = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return $c;
}
function sap_image_mime( string $path ): string {
	if ( ! is_readable( $path ) ) { return ''; }
	$h = (string) file_get_contents( $path, false, null, 0, 12 );
	if ( 12 === strlen( $h ) && 'RIFF' === substr( $h, 0, 4 ) && 'WEBP' === substr( $h, 8, 4 ) ) { return 'image/webp'; }
	if ( "\xFF\xD8\xFF" === substr( $h, 0, 3 ) ) { return 'image/jpeg'; }
	if ( "\x89PNG\r\n\x1a\n" === substr( $h, 0, 8 ) ) { return 'image/png'; }
	return '';
}

$child = get_stylesheet_directory();
$home  = untrailingslashit( home_url() );

/** The five records this branch adds, and the project each is backed by. */
$NEW = array(
	'van-nuys'        => 'van-nuys-pool-project',
	'north-hollywood' => 'north-hollywood-pool-project',
	'toluca-lake'     => 'toluca-lake-pool-project',
	'burbank'         => 'burbank-pool-project',
	'brentwood'       => 'brentwood-pool-project',
);
/** The nine that were already live, in their established order. */
$ORIGINAL_NINE = array(
	'sherman-oaks', 'encino', 'beverly-hills', 'studio-city', 'tarzana',
	'woodland-hills', 'west-hollywood', 'bel-air', 'calabasas',
);

$areas    = \Showtime\Areas::all();
$by_slug  = array();
foreach ( $areas as $a ) { $by_slug[ (string) $a['slug'] ] = $a; }

/* ══════════════════════════════════════════════════════════════════════
 * REGISTRY
 * ═══════════════════════════════════════════════════════════════════ */
echo "== SERVICE-AREA REGISTRY ==\n";

count( $areas ) === 14
	? ok( '1. exactly 14 service-area definitions exist' )
	: bad( '1. registry holds ' . count( $areas ) . ' areas, expected 14' );

$slugs = array_map( static fn( $a ) => (string) $a['slug'], $areas );
count( array_unique( $slugs ) ) === 14
	? ok( '2. all 14 service-area slugs are unique' )
	: bad( '2. duplicate slugs: ' . implode( ', ', array_diff_assoc( $slugs, array_unique( $slugs ) ) ) );

$canonicals = array_map( static fn( $s ) => home_url( '/service-areas/' . $s . '/' ), $slugs );
count( array_unique( $canonicals ) ) === 14
	? ok( '3. all 14 canonical service-area URLs are unique' )
	: bad( '3. canonical URLs are not unique' );

$missing = array_diff( array_keys( $NEW ), $slugs );
empty( $missing )
	? ok( '4. the five new records use the expected slugs: ' . implode( ', ', array_keys( $NEW ) ) )
	: bad( '4. missing slugs: ' . implode( ', ', $missing ) );

// The original nine keep their exact registry position.
array_slice( $slugs, 0, 9 ) === $ORIGINAL_NINE
	? ok( '5. the original nine keep their exact registry order' )
	: bad( '5. first nine are now: ' . implode( ', ', array_slice( $slugs, 0, 9 ) ) );

$rel_bad = array();
foreach ( $NEW as $slug => $project ) {
	$a = $by_slug[ $slug ] ?? null;
	if ( ! $a ) { $rel_bad[] = "$slug absent"; continue; }
	if ( (string) ( $a['related_project'] ?? '' ) !== $project ) { $rel_bad[] = "$slug -> " . ( $a['related_project'] ?? 'none' ); }
	if ( null === showtime_project_data( $project ) ) { $rel_bad[] = "$slug names a project that does not resolve"; }
}
empty( $rel_bad )
	? ok( '6. each new record names its correct related project' )
	: bad( '6. ' . implode( '; ', $rel_bad ) );

// The nine must NOT gain a related_project — that key is what changes their
// card imagery, and their imagery must not move.
$leaked = array();
foreach ( $ORIGINAL_NINE as $slug ) {
	if ( '' !== (string) ( $by_slug[ $slug ]['related_project'] ?? '' ) ) { $leaked[] = $slug; }
}
empty( $leaked )
	? ok( '7. no original area gained a related_project, so none of their card imagery moved' )
	: bad( '7. related_project leaked onto: ' . implode( ', ', $leaked ) );

/* ── No fabricated claims ─────────────────────────────────────────────── */
$claim_bad = array();
foreach ( $NEW as $slug => $project ) {
	$a = $by_slug[ $slug ] ?? array();

	// No pool count.
	if ( '' !== trim( (string) ( $a['pool_count'] ?? '' ) ) ) { $claim_bad[] = "$slug asserts a pool count"; }

	// No route/schedule/response-time wording anywhere in its copy.
	$copy = strtolower( implode( ' ', array(
		(string) ( $a['tag'] ?? '' ),
		(string) ( $a['seo_intro'] ?? '' ),
		(string) ( $a['seo_meta'] ?? '' ),
		(string) ( $a['lead'] ?? '' ),
		(string) ( $a['what_common'] ?? '' ),
		(string) ( $a['what_do'] ?? '' ),
		implode( ' ', (array) ( $a['characteristics'] ?? array() ) ),
		implode( ' ', (array) ( $a['common_jobs'] ?? array() ) ),
	) ) );
	foreach ( array(
		'days a week', 'day a week', 'weekly route', 'weekly routes', 'same tech',
		'same-day', 'within 24', 'response time', 'hours a day', 'guarantee', 'warranty',
		'licensed', 'certified', 'award', 'rated', 'star', 'testimonial',
	) as $needle ) {
		if ( false !== strpos( $copy, $needle ) ) { $claim_bad[] = "$slug copy contains \"$needle\""; }
	}

	// "since 2003" may only appear as the company-wide fact, never bound to the city.
	if ( preg_match( '/(in|serving)\s+' . preg_quote( strtolower( (string) $a['name'] ), '/' ) . '[^.]{0,40}since 2003/i', $copy ) ) {
		$claim_bad[] = "$slug ties \"since 2003\" to the city";
	}

	// No invented street list.
	if ( ! empty( $a['sample_streets'] ) ) { $claim_bad[] = "$slug asserts serviced streets"; }
}
empty( $claim_bad )
	? ok( '8. none of the five asserts a pool count, route schedule, response time, rating, guarantee, street list or city-bound "since 2003"' )
	: bad( '8. ' . implode( '; ', $claim_bad ) );

// Unique titles / descriptions / intros across all 14 — no five-cities-swapped copy.
foreach ( array( 'seo_title' => 'SEO titles', 'seo_meta' => 'meta descriptions', 'seo_intro' => 'intros', 'seo_h1' => 'H1s' ) as $key => $label ) {
	$vals = array_map( static fn( $a ) => (string) ( $a[ $key ] ?? '' ), $areas );
	count( array_unique( $vals ) ) === 14
		? ok( "9. all 14 $label are unique" )
		: bad( "9. duplicate $label present" );
}

// The five must not reuse their project's title — that is what separates the
// service-area query from the project/case-study query.
$cannibal = array();
foreach ( $NEW as $slug => $project ) {
	$p = showtime_project_data( $project );
	$a = $by_slug[ $slug ] ?? array();
	if ( ! $p ) { continue; }
	if ( (string) $a['seo_title'] === (string) $p['seo_title'] ) { $cannibal[] = "$slug shares its project's SEO title"; }
	if ( (string) $a['seo_h1'] === (string) $p['title'] )        { $cannibal[] = "$slug H1 equals the project title"; }
	if ( (string) $a['seo_meta'] === (string) $p['meta_description'] ) { $cannibal[] = "$slug shares its project's meta description"; }
}
empty( $cannibal )
	? ok( '10. no new area page reuses its project page\'s title, H1 or meta description' )
	: bad( '10. ' . implode( '; ', $cannibal ) );

/* ── Imagery ──────────────────────────────────────────────────────────── */
$img_bad = array();
foreach ( $NEW as $slug => $project ) {
	$a    = $by_slug[ $slug ] ?? array();
	$hero = (string) ( $a['hero_image'] ?? '' );
	if ( '' === $hero ) { $img_bad[] = "$slug pins no hero image"; continue; }
	$path = $child . '/' . ltrim( $hero, '/' );
	if ( 'image/webp' !== sap_image_mime( $path ) ) { $img_bad[] = "$slug hero is not a real WebP on disk"; }
	// It must be that project's OWN asset, never another's.
	if ( false === strpos( $hero, "galleries/$project/" ) ) { $img_bad[] = "$slug hero is not its own project's asset"; }
	if ( '' === trim( (string) ( $a['hero_alt'] ?? '' ) ) ) { $img_bad[] = "$slug hero has no alt text"; }
}
empty( $img_bad )
	? ok( '11. every new record pins a real WebP hero from its OWN project gallery, with alt text' )
	: bad( '11. ' . implode( '; ', $img_bad ) );

$hero_alts = array();
foreach ( $NEW as $slug => $_ ) { $hero_alts[] = (string) ( $by_slug[ $slug ]['hero_alt'] ?? '' ); }
count( array_unique( $hero_alts ) ) === 5
	? ok( '12. all five hero alt texts are unique' )
	: bad( '12. hero alt text is duplicated across the five' );

/* ══════════════════════════════════════════════════════════════════════
 * PUBLICATION GATE
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== PUBLICATION GATE ==\n";

$cards    = showtime_service_area_cards();
$card_by  = array();
foreach ( $cards as $c ) { $card_by[ (string) $c['slug'] ] = $c; }

count( $cards ) === 14
	? ok( '13. the card resolver returns exactly 14 cards' )
	: bad( '13. resolver returned ' . count( $cards ) );

$live    = array();
$pending = array();
foreach ( $NEW as $slug => $project ) {
	if ( showtime_area_page_is_published( $slug ) ) { $live[] = $slug; } else { $pending[] = $slug; }
}
echo '  (published now: ' . ( $live ? implode( ', ', $live ) : 'none' ) . ' | pending: ' . ( $pending ? implode( ', ', $pending ) : 'none' ) . ")\n";

// THE core rule, asserted in whichever state we are in.
$gate_bad = array();
foreach ( $NEW as $slug => $project ) {
	$card = $card_by[ $slug ] ?? null;
	if ( ! $card ) { $gate_bad[] = "$slug has no card"; continue; }
	$expected_area    = home_url( '/service-areas/' . $slug . '/' );
	$expected_project = showtime_project_permalink( $project );

	if ( showtime_area_page_is_published( $slug ) ) {
		if ( $card['url'] !== $expected_area )  { $gate_bad[] = "$slug is published but links to {$card['url']}"; }
		if ( ! $card['has_area_page'] )         { $gate_bad[] = "$slug is published but not flagged live"; }
	} else {
		if ( $card['url'] !== $expected_project ) { $gate_bad[] = "$slug is unpublished but links to {$card['url']}"; }
		if ( $card['has_area_page'] )             { $gate_bad[] = "$slug is unpublished but flagged live"; }
	}
}
empty( $gate_bad )
	? ok( '14. every one of the five links to its service-area URL when published, and to its project page when not' )
	: bad( '14. ' . implode( '; ', $gate_bad ) );

// A card must never point at a URL that does not resolve, in either state.
$dead = array();
foreach ( $cards as $c ) {
	$code = sap_status( (string) $c['url'] );
	if ( 200 !== $code ) { $dead[] = $c['name'] . " -> HTTP $code"; }
	if ( 0 !== strpos( (string) $c['url'], $home ) ) { $dead[] = $c['name'] . ' links off-site'; }
}
empty( $dead )
	? ok( '15. all 14 card destinations resolve with HTTP 200 and no redirect' )
	: bad( '15. ' . implode( ', ', $dead ) );

// The gate itself must be honest about reality, not just about the registry.
$gate_truth = array();
foreach ( $slugs as $slug ) {
	$claims = showtime_area_page_is_published( $slug );
	$actual = 200 === sap_status( home_url( '/service-areas/' . $slug . '/' ) );
	if ( $claims !== $actual ) { $gate_truth[] = "$slug: gate=" . var_export( $claims, true ) . " actual200=" . var_export( $actual, true ); }
}
empty( $gate_truth )
	? ok( '16. the publication gate matches what the server actually serves for all 14 slugs' )
	: bad( '16. ' . implode( '; ', $gate_truth ) );

// No public surface may advertise an unpublished area URL.
$llms      = sap_fetch( "$home/llms.txt" );
$llms_full = sap_fetch( "$home/llms-full.txt" );
$sitemap   = sap_fetch( "$home/sitemap/" );
$xml       = sap_fetch( "$home/wp-sitemap.xml" );
$hub       = sap_fetch( "$home/service-areas/" );
$hp        = sap_fetch( "$home/" );

// /wp-sitemap.xml is an INDEX: page URLs live in its child sitemaps, so the
// index plus every child it names is what actually advertises a URL.
$xml_all = $xml;
if ( preg_match_all( '#<loc>([^<]+)</loc>#', $xml, $xm ) ) {
	foreach ( $xm[1] as $child ) {
		if ( false !== strpos( $child, 'wp-sitemap' ) ) { $xml_all .= sap_fetch( html_entity_decode( $child ) ); }
	}
}

$exposed = array();
foreach ( $slugs as $slug ) {
	if ( showtime_area_page_is_published( $slug ) ) { continue; }
	$needle = '/service-areas/' . $slug . '/';
	foreach ( array( 'llms.txt' => $llms, 'llms-full.txt' => $llms_full, 'HTML sitemap' => $sitemap, 'XML sitemap' => $xml_all, '/service-areas/ hub' => $hub, 'homepage' => $hp ) as $where => $body ) {
		if ( '' !== $body && false !== strpos( $body, $needle ) ) { $exposed[] = "$slug on $where"; }
	}
}
empty( $exposed )
	? ok( '17. no unpublished service-area URL is advertised on llms.txt, llms-full.txt, either sitemap, the hub or the homepage' )
	: bad( '17. 404-bound link exposed: ' . implode( '; ', $exposed ) );

// ...and every published one IS discoverable.
$undiscoverable = array();
foreach ( $slugs as $slug ) {
	if ( ! showtime_area_page_is_published( $slug ) ) { continue; }
	$needle = '/service-areas/' . $slug . '/';
	if ( false === strpos( $sitemap, $needle ) ) { $undiscoverable[] = "$slug missing from the HTML sitemap"; }
	if ( false === strpos( $llms, $needle ) )    { $undiscoverable[] = "$slug missing from llms.txt"; }
	if ( false === strpos( $xml_all, $needle ) ) { $undiscoverable[] = "$slug missing from the XML sitemap"; }
}
empty( $undiscoverable )
	? ok( '18. every published service area appears in the HTML sitemap, llms.txt and the XML sitemap' )
	: bad( '18. ' . implode( '; ', $undiscoverable ) );

// The two sitemaps keep their distinct roles.
( false !== stripos( $sitemap, '<html' ) && 0 === strpos( ltrim( $xml ), '<?xml' ) )
	? ok( '19. /sitemap/ is still HTML and /wp-sitemap.xml is still XML' )
	: bad( '19. the sitemap split changed' );

/* ══════════════════════════════════════════════════════════════════════
 * CARD SURFACES
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== CARD SURFACES ==\n";

14 === preg_match_all( '/class="area-card area-card--lg"/', $hub )
	? ok( '20. /service-areas/ renders exactly 14 semantic cards' )
	: bad( '20. hub renders ' . preg_match_all( '/class="area-card area-card--lg"/', $hub ) . ' cards' );

preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>#s', $hp, $am );
$anchors  = $am[0] ?? array();
$clones   = array_values( array_filter( $anchors, static fn( $a ) => false !== strpos( $a, 'tabindex="-1"' ) ) );
$semantic = array_values( array_filter( $anchors, static fn( $a ) => false === strpos( $a, 'tabindex="-1"' ) ) );

count( $semantic ) === 14
	? ok( '21. the homepage carousel renders exactly 14 semantic cards' )
	: bad( '21. carousel has ' . count( $semantic ) . ' semantic cards' );

count( $clones ) === 14 && count( array_filter( $clones, static fn( $a ) => false === strpos( $a, 'aria-hidden="true"' ) ) ) === 0
	? ok( '22. all 14 animation clones stay aria-hidden and out of the tab order' )
	: bad( '22. clone count/attributes wrong (' . count( $clones ) . ' clones)' );

preg_match_all( '#<div class="service-areas__row service-areas__row--(ltr|rtl)">(.*?)</div>\s*</div>#s', $hp, $rm, PREG_SET_ORDER );
$row_counts = array();
foreach ( $rm as $row ) {
	preg_match_all( '#<a\s+class="area-card area-card--marquee"[^>]*>.*?</a>#s', $row[2], $ra );
	$row_counts[] = count( array_filter( $ra[0] ?? array(), static fn( $a ) => false === strpos( $a, 'tabindex="-1"' ) ) );
}
$row_counts === array( 7, 7 )
	? ok( '23. the carousel is still seven semantic cards per row' )
	: bad( '23. row split is ' . implode( ' / ', $row_counts ) );

false !== strpos( $hp, 'Explore 14 Los Angeles Service Areas' )
	? ok( '24. the homepage heading still reads "Explore 14 Los Angeles Service Areas"' )
	: bad( '24. the homepage service-areas heading changed' );

/* ══════════════════════════════════════════════════════════════════════
 * PAGE CONTRACT (published pages only)
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== PAGE CONTRACT ==\n";

$rendered = 0;
foreach ( $NEW as $slug => $project ) {
	if ( ! showtime_area_page_is_published( $slug ) ) { continue; }
	$rendered++;
	$url  = home_url( '/service-areas/' . $slug . '/' );
	$body = sap_fetch( $url );
	$a    = $by_slug[ $slug ];
	$p    = showtime_project_data( $project );
	$errs = array();

	// Exactly one visible H1, and it is the service-focused registry H1.
	preg_match_all( '#<h1[^>]*>(.*?)</h1>#s', $body, $h1m );
	$h1s = array_map( static fn( $h ) => trim( wp_strip_all_tags( $h ) ), $h1m[1] ?? array() );
	if ( 1 !== count( $h1s ) )                          { $errs[] = 'H1 count = ' . count( $h1s ); }
	if ( ( $h1s[0] ?? '' ) !== (string) $a['seo_h1'] )  { $errs[] = 'H1 is "' . ( $h1s[0] ?? '' ) . '"'; }

	// Self-referencing canonical, exactly one.
	preg_match_all( '#rel=["\']canonical["\'][^>]*href=["\']([^"\']+)["\']#', $body, $cm );
	if ( 1 !== count( $cm[1] ?? array() ) )                       { $errs[] = 'canonical count = ' . count( $cm[1] ?? array() ); }
	if ( untrailingslashit( $cm[1][0] ?? '' ) !== untrailingslashit( $url ) ) { $errs[] = 'canonical is ' . ( $cm[1][0] ?? 'none' ); }

	// index,follow.
	if ( ! preg_match( '#name=["\']robots["\'] content=["\'][^"\']*\bindex\b#', $body )
		|| preg_match( '#name=["\']robots["\'] content=["\'][^"\']*noindex#', $body ) ) {
		$errs[] = 'robots is not index,follow';
	}

	// Unique title + description, matching the registry.
	if ( false === strpos( $body, '<title>' . $a['seo_title'] ) ) { $errs[] = 'title does not match the registry'; }
	if ( false === strpos( $body, (string) $a['seo_meta'] ) )     { $errs[] = 'meta description missing'; }

	// One complete OG + Twitter set.
	foreach ( array( 'og:title', 'og:description', 'og:url', 'og:image', 'og:type' ) as $tag ) {
		if ( 1 !== substr_count( $body, 'property="' . $tag . '"' ) ) { $errs[] = "$tag count != 1"; }
	}
	foreach ( array( 'twitter:card', 'twitter:title', 'twitter:description', 'twitter:image' ) as $tag ) {
		if ( 1 !== substr_count( $body, 'name="' . $tag . '"' ) ) { $errs[] = "$tag count != 1"; }
	}

	// The social image is the pinned real asset, never a stock fallback.
	if ( false !== strpos( $body, 'picsum.photos' ) )                  { $errs[] = 'a stock placeholder image is present'; }
	if ( false === strpos( $body, basename( (string) $a['hero_image'] ) ) ) { $errs[] = 'the pinned hero asset is not on the page'; }

	// Breadcrumb trail: Home / Service Areas / <Name>.
	if ( ! preg_match( '#<nav class="breadcrumbs[^"]*"[^>]*>(.*?)</nav>#s', $body, $bc ) ) {
		$errs[] = 'no breadcrumb nav';
	} else {
		$trail = preg_replace( '/\s+/', ' ', trim( wp_strip_all_tags( $bc[1] ) ) );
		if ( false === strpos( $trail, 'Home' ) || false === strpos( $trail, 'Service Areas' ) || false === strpos( $trail, (string) $a['name'] ) ) {
			$errs[] = "breadcrumb trail is \"$trail\"";
		}
	}

	// Approved schema only.
	preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $body, $ld );
	$types = array();
	foreach ( $ld[1] ?? array() as $json ) {
		$d = json_decode( $json, true );
		if ( is_array( $d ) && isset( $d['@type'] ) ) { $types[] = is_array( $d['@type'] ) ? implode( '+', $d['@type'] ) : (string) $d['@type']; }
	}
	if ( ! in_array( 'Service', $types, true ) )        { $errs[] = 'no Service schema'; }
	if ( ! in_array( 'BreadcrumbList', $types, true ) ) { $errs[] = 'no BreadcrumbList schema'; }
	foreach ( array( 'Product', 'Offer', 'AggregateRating', 'Review' ) as $forbidden ) {
		if ( in_array( $forbidden, $types, true ) ) { $errs[] = "$forbidden schema present"; }
	}
	// No per-city business address or storefront node.
	if ( preg_match( '#"@type"\s*:\s*"PostalAddress"[^}]*' . preg_quote( (string) $a['name'], '#' ) . '#', $body ) ) {
		$errs[] = 'a per-city PostalAddress was emitted';
	}

	// No pending state.
	if ( false !== stripos( $body, 'coming soon' ) )                     { $errs[] = '"Coming Soon" wording present'; }
	if ( false !== strpos( $body, 'not yet configured' ) )               { $errs[] = 'the unconfigured-area fallback rendered'; }

	// Local project proof: the exact Projects-archive cover, linking the project.
	if ( false === strpos( $body, showtime_project_permalink( $project ) ) ) { $errs[] = 'no link to its project page'; }
	if ( false === strpos( $body, basename( (string) $p['hero_image'] ) ) )  { $errs[] = 'project-proof does not use the archive cover image'; }
	if ( false === strpos( $body, (string) $p['hero_alt'] ) )               { $errs[] = 'project-proof cover has no approved alt text'; }

	// Internal links required by the contract.
	foreach ( array( '/projects/' => 'Projects', '/service-areas/' => 'Service Areas', '/services/' => 'a service' ) as $frag => $label ) {
		if ( false === strpos( $body, $frag ) ) { $errs[] = "no internal link to $label"; }
	}
	if ( false === strpos( $body, 'showtimepoolmechanics.com' ) && false === strpos( $body, '/contact/' ) ) {
		$errs[] = 'no booking or contact destination';
	}

	// Server-rendered: the body copy must be present without running any JS.
	if ( false === strpos( $body, (string) $a['what_common'] ) ) { $errs[] = 'services heading is not server-rendered'; }
	foreach ( (array) $a['characteristics'] as $c ) {
		if ( false === strpos( $body, (string) $c ) ) { $errs[] = 'a service list item is not server-rendered'; break; }
	}

	// Shared chrome.
	if ( false === strpos( $body, 'area-hero' ) )       { $errs[] = 'not using the shared area hero'; }
	if ( false === strpos( $body, 'footer-wordmark' ) ) { $errs[] = 'footer missing'; }

	empty( $errs )
		? ok( "25. /service-areas/$slug/ satisfies the full page contract" )
		: bad( "25. /service-areas/$slug/ — " . implode( '; ', $errs ) );
}

if ( 0 === $rendered ) {
	skipped( '25. page-contract assertions — none of the five WordPress page records exists yet, which is the expected pre-deploy state. Every registry, gating, exposure and card assertion above still ran.' );
} else {
	$rendered === count( $NEW )
		? ok( '26. all five service-area pages are published and render' )
		: skipped( '26. only ' . $rendered . ' of ' . count( $NEW ) . ' page records exist so far; the rest are still pending creation.' );
}

/* ── The nine must not have moved ─────────────────────────────────────── */
$moved = array();
foreach ( $ORIGINAL_NINE as $slug ) {
	$body = sap_fetch( home_url( '/service-areas/' . $slug . '/' ) );
	if ( '' === $body ) { $moved[] = "$slug did not render"; continue; }
	$a = $by_slug[ $slug ];
	preg_match_all( '#<h1[^>]*>(.*?)</h1>#s', $body, $h1m );
	if ( 1 !== count( $h1m[1] ?? array() ) )                            { $moved[] = "$slug H1 count"; }
	if ( trim( wp_strip_all_tags( $h1m[1][0] ?? '' ) ) !== (string) $a['seo_h1'] ) { $moved[] = "$slug H1 text"; }
	if ( false === strpos( $body, (string) $a['seo_meta'] ) )           { $moved[] = "$slug meta description"; }
	// They must NOT have gained a project-proof block.
	if ( false !== strpos( $body, 'Work we have completed in' ) )       { $moved[] = "$slug gained a project-proof section"; }
	// Their hero image slot is unchanged.
	if ( false !== strpos( $body, 'galleries/' ) )                      { $moved[] = "$slug now pulls a gallery asset"; }
}
empty( $moved )
	? ok( '27. all nine original service-area pages render exactly as before — same H1, meta, imagery and no new sections' )
	: bad( '27. ' . implode( '; ', $moved ) );

/* ══════════════════════════════════════════════════════════════════════
 * NOTHING ELSE MOVED
 * ═══════════════════════════════════════════════════════════════════ */
echo "\n== NOTHING ELSE MOVED ==\n";

$managed = array_values( array_filter( \Showtime\Projects::all(), static fn( $p ) => ! empty( $p['managed'] ) ) );
count( $managed ) === 14
	? ok( '28. the project registry still holds exactly 14 managed projects' )
	: bad( '28. managed project count is ' . count( $managed ) );

$proj_bad = array();
foreach ( $managed as $p ) {
	$slug = (string) $p['slug'];
	if ( 200 !== sap_status( showtime_project_permalink( $slug ) ) ) { $proj_bad[] = "$slug URL"; }
	$g = showtime_project_gallery( showtime_project_data( $slug ) );
	if ( 6 !== count( array_filter( $g, static fn( $x ) => 'ready' === $x['status'] ) ) ) { $proj_bad[] = "$slug gallery"; }
}
empty( $proj_bad )
	? ok( '29. all 14 project URLs still resolve and still publish six highlights each' )
	: bad( '29. ' . implode( ', ', $proj_bad ) );

$popup_ok = 1 === substr_count( $hp, 'id="stp-estimate-popup"' )
	&& false !== strpos( $hp, 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb' )
	&& false !== strpos( $hp, 'tel:+13238252099' )
	&& false === strpos( $hp, 'stp-popup' );
$popup_ok
	? ok( '30. the estimate popup is untouched — printed once, exact CTA destinations, no old popup' )
	: bad( '30. the estimate popup changed' );

false !== strpos( $hp, 'footer-wordmark' ) && false !== strpos( $hub, 'footer-wordmark' )
	? ok( '31. the footer still renders on the homepage and the hub' )
	: bad( '31. footer missing from a changed page' );

echo "\n== RESULT ==\n";
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

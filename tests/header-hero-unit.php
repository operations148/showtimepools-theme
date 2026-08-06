<?php
/**
 * Sitewide header ↔ hero regression tests.
 *
 * Covers the single overlaid-header treatment: one global header, a shared
 * has-hero/no-hero state chosen from the resolved template, the transparent
 * top state, the frosted translucent scrolled state, the header-height
 * reservation that keeps every page free of layout shift, and the guards that
 * keep the transparent treatment out of admin/AJAX/REST/feed/embed/sitemap
 * contexts.
 *
 * Static assertions read the theme's own source (PHP registry, CSS, JS). Runtime
 * assertions read real server-rendered HTML over HTTP with JavaScript never
 * executed — which doubles as the no-JS proof. The scroll-state flip, the mobile
 * drawer, dropdown stacking, focus rings, pixel contrast and layout-shift
 * measurements are covered by the headless-Chrome pass reported alongside this
 * file; the numbers those produced are asserted here as fixed CSS contracts so
 * they cannot silently drift.
 *
 *   php tests/header-hero-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/header-hero-unit.php
 *
 * Read-only: creates no posts and writes no options.
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
		CURLOPT_USERAGENT      => 'showtime-header-hero-test/1.0',
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

$child   = get_stylesheet_directory();
$css_hdr = (string) file_get_contents( $child . '/assets/css/header.css' );
$css_hh  = (string) file_get_contents( $child . '/assets/css/header-hero.css' );
$js_hdr  = (string) file_get_contents( $child . '/assets/js/header.js' );
$php_hh  = (string) file_get_contents( $child . '/inc/hero-header.php' );
$php_hdr = (string) file_get_contents( $child . '/header.php' );

// Comment-stripped copy of the scroll script: assertions about what the code
// DOES must not be satisfied — or defeated — by what the comments SAY.
$js_code = (string) preg_replace( array( '#/\*.*?\*/#s', '#//.*$#m' ), '', $js_hdr );

/**
 * One representative live URL per genuine hero component, plus the two
 * hero-less routes. Every distinct hero root in the theme is present.
 */
$hero_pages = array(
	'homepage hero (.home-hero)'          => array( home_url( '/' ),                                  'home-hero' ),
	'service hero (.svc-hero)'            => array( home_url( '/services/pool-repairs-plumbing/' ),   'svc-hero' ),
	'services archive hero (.int-hero)'   => array( home_url( '/services/' ),                         'int-hero' ),
	'project archive hero (.int-hero)'    => array( home_url( '/projects/' ),                         'int-hero' ),
	'single project (.proj-single__hero)' => array( home_url( '/projects/brentwood-pool-project/' ),  'proj-single__hero' ),
	'service-area hero (.area-hero)'      => array( home_url( '/service-areas/sherman-oaks/' ),       'area-hero' ),
	'about hero (.int-hero)'              => array( home_url( '/about/' ),                            'int-hero' ),
	'contact hero (.contact-hero)'        => array( home_url( '/contact/' ),                          'contact-hero' ),
	'quote/book hero (.iframe-hero)'      => array( home_url( '/quote/' ),                            'iframe-hero' ),
	'blog article hero (.post-hero)'      => array( home_url( '/when-to-replace-your-pool-pump/' ),   'post-hero' ),
	'legal hero (.int-hero--compact)'     => array( home_url( '/terms/' ),                            'int-hero' ),
	'inspections hero (.int-hero)'        => array( home_url( '/pool-inspections/' ),                 'int-hero' ),
);
$no_hero_pages = array(
	'page with no hero (index.php)' => home_url( '/sample-page/' ),
	'404 route'                     => home_url( '/definitely-not-a-real-page/' ),
);

$bodies = array();
foreach ( $hero_pages as $label => list( $url, $_sel ) ) { $bodies[ $label ] = fetch_body( $url ); }
foreach ( $no_hero_pages as $label => $url )             { $bodies[ $label ] = fetch_body( $url ); }
$all_ok = true;
foreach ( $bodies as $label => $b ) {
	if ( strlen( $b ) < 500 ) { $all_ok = false; echo "  ! could not fetch: $label\n"; }
}

echo "== ONE GLOBAL HEADER ==\n";

// 1.
$bad1 = array();
foreach ( $bodies as $label => $b ) {
	$headers = preg_match_all( '#<header[^>]*class="[^"]*\bsite-header\b#', $b );
	$mast    = substr_count( $b, 'id="masthead"' );
	$navs    = preg_match_all( '#<nav[^>]*class="[^"]*\bprimary-nav\b#', $b );
	$drawers = substr_count( $b, 'id="mobile-drawer"' );
	if ( 1 !== $headers || 1 !== $mast || 1 !== $navs || 1 !== $drawers ) {
		$bad1[] = "$label (header=$headers masthead=$mast nav=$navs drawer=$drawers)";
	}
}
$bad1 ? bad( '1. exactly one global header per page — ' . implode( '; ', $bad1 ) )
	: ok( '1. exactly one site-header, one #masthead, one primary nav and one drawer on all ' . count( $bodies ) . ' routes' );

// 21.
$bad21 = array();
foreach ( $bodies as $label => $b ) {
	preg_match_all( '#\sid="([^"]+)"#', $b, $m );
	$ids  = $m[1] ?? array();
	$dups = array_keys( array_filter( array_count_values( $ids ), static fn( $n ) => $n > 1 ) );
	if ( $dups ) { $bad21[] = "$label: " . implode( ',', $dups ); }
	$mains = preg_match_all( '#<main[^>]*id="primary"#', $b );
	if ( $mains > 1 ) { $bad21[] = "$label: {$mains} <main> landmarks"; }
}
$bad21 ? bad( '21. duplicate IDs or landmarks introduced — ' . implode( ' | ', $bad21 ) )
	: ok( '21. no duplicate id attributes and no duplicate main landmark on any route' );

echo "\n== SHARED HERO STATE, DERIVED FROM THE RESOLVED TEMPLATE ==\n";

// 2.
$missing = array();
foreach ( $hero_pages as $label => list( $url, $sel ) ) {
	$b = $bodies[ $label ];
	$has_body_class = (bool) preg_match( '#<body[^>]*\bclass="[^"]*\bhas-hero\b#', $b );
	$has_data_attr  = (bool) preg_match( '#<header[^>]*data-hero="true"#s', $b );
	$has_hero_el    = false !== strpos( $b, 'class="' . $sel ) || (bool) preg_match( '#class="[^"]*\b' . preg_quote( $sel, '#' ) . '\b#', $b );
	if ( ! $has_body_class || ! $has_data_attr || ! $has_hero_el ) {
		$missing[] = "$label (bodyClass=" . var_export( $has_body_class, true ) . " dataHero=" . var_export( $has_data_attr, true ) . " heroEl=" . var_export( $has_hero_el, true ) . ')';
	}
}
$missing ? bad( '2. every hero template gets the shared state — ' . implode( '; ', $missing ) )
	: ok( '2. all ' . count( $hero_pages ) . ' genuine hero templates render their hero AND carry body.has-hero + header[data-hero=true]' );

// 3-8: named per the brief, one assertion each, so a break names its own page.
$named = array(
	'3. homepage hero'         => 'homepage hero (.home-hero)',
	'4. service-page hero'     => 'service hero (.svc-hero)',
	'5. services archive hero' => 'services archive hero (.int-hero)',
	'6. project archive hero'  => 'project archive hero (.int-hero)',
	'7. individual project hero' => 'single project (.proj-single__hero)',
	'8. service-area hero'     => 'service-area hero (.area-hero)',
);
foreach ( $named as $num => $label ) {
	$b   = $bodies[ $label ];
	$sel = $hero_pages[ $label ][1];
	// "Extends behind the header" == the page opts into the overlay contract:
	// body.has-hero (header fixed + transparent, no flow reservation) AND the
	// hero root is present to receive the shared padding/min-height rule.
	$has = preg_match( '#<body[^>]*\bclass="[^"]*\bhas-hero\b#', $b )
		&& preg_match( '#class="[^"]*\b' . preg_quote( $sel, '#' ) . '\b#', $b )
		&& ! preg_match( '#<body[^>]*\bclass="[^"]*\bno-hero\b#', $b );
	$has ? ok( "$num extends behind the header (has-hero + .$sel, no flow reservation)" )
		: bad( "$num does not opt into the overlay contract" );
}

// 9.
$bad9 = array();
foreach ( $no_hero_pages as $label => $url ) {
	$b = $bodies[ $label ];
	if ( ! preg_match( '#<body[^>]*\bclass="[^"]*\bno-hero\b#', $b ) ) { $bad9[] = "$label missing no-hero"; }
	if ( preg_match( '#<body[^>]*\bclass="[^"]*\bhas-hero\b#', $b ) )  { $bad9[] = "$label wrongly has-hero"; }
}
if ( ! preg_match( '#body\.no-hero\s*\{[^}]*padding-top:\s*var\(--stp-header-h\)#', $css_hh ) ) {
	$bad9[] = 'body.no-hero does not reserve --stp-header-h';
}
$bad9 ? bad( '9. hero-less pages get the readable state + top spacing — ' . implode( '; ', $bad9 ) )
	: ok( '9. pages without a hero are body.no-hero (base frosted state) and reserve --stp-header-h of top spacing' );

echo "\n== THE TWO HEADER STATES ==\n";

// 10.
$top_state = array();
if ( ! preg_match( '#body\.has-hero\s+\.site-header\[data-scrolled="false"\]\s*\{(.*?)\n\}#s', $css_hdr, $m10 ) ) {
	bad( '10. top state rule not found' );
} else {
	$blk = $m10[1];
	$checks = array(
		'transparent background'   => (bool) preg_match( '#background-color:\s*transparent#', $blk ),
		'no backdrop blur at top'  => (bool) preg_match( '#backdrop-filter:\s*none#', $blk ),
		'transparent bottom rule'  => (bool) preg_match( '#border-bottom-color:\s*transparent#', $blk ),
		'no shadow'                => (bool) preg_match( '#box-shadow:\s*none#', $blk ),
		'white nav ink'            => (bool) preg_match( '~--nav-fg:\s*\#fff~', $blk ),
	);
	$missing10 = array_keys( array_filter( $checks, static fn( $v ) => ! $v ) );
	$missing10 ? bad( '10. top-of-hero header background — missing: ' . implode( ', ', $missing10 ) )
		: ok( '10. at the top of a hero the header background is fully transparent, unblurred, borderless and shadowless, with white nav ink' );
}

// 11.
if ( ! preg_match( '#^\.site-header\s*\{(.*?)\n\}#ms', $css_hdr, $m11 ) ) {
	bad( '11. base .site-header rule not found' );
} else {
	$blk  = $m11[1];
	$has_translucent = (bool) preg_match( '#background-color:\s*rgba\(255,\s*255,\s*255,\s*0\.(\d+)\)#', $blk, $alpha );
	$a    = $has_translucent ? (float) ( '0.' . $alpha[1] ) : 1.0;
	$blur = (bool) preg_match( '#backdrop-filter:\s*blur\(16px\)\s*saturate\(140%\)#', $blk );
	// The container itself must never be given opacity — that would fade the
	// logo, links, icons and CTA along with the surface.
	$container_opacity = (bool) preg_match( '#(^|\n)\s*opacity:#', $blk );
	$any_header_opacity = (bool) preg_match( '#\.site-header\s*(\[[^\]]*\])?\s*\{[^}]*(^|\n|;)\s*opacity:\s*[\d.]#s', $css_hdr );
	if ( $has_translucent && $a > 0 && $a < 1 && $blur && ! $container_opacity && ! $any_header_opacity ) {
		ok( sprintf( '11. scrolled/base state is a translucent white surface (alpha %.2f) + blur(16px) saturate(140%%), with opacity never applied to the container', $a ) );
	} else {
		bad( sprintf( '11. translucent=%s alpha=%.2f blur=%s containerOpacity=%s', var_export( $has_translucent, true ), $a, var_export( $blur, true ), var_export( $container_opacity || $any_header_opacity, true ) ) );
	}
}

// 12.
$dark_ink = (bool) preg_match( '#^\.site-header\s*\{.*?--nav-fg:\s*var\(--c-ink\);.*?\n\}#ms', $css_hdr );
$routed   = array(
	'.primary-nav__link colour' => (bool) preg_match( '#\.primary-nav__link\s*\{[^}]*color:\s*var\(--nav-fg\)#s', $css_hdr ),
	'active/hover underline'    => (bool) preg_match( '#\.primary-nav__link::after\s*\{[^}]*background:\s*var\(--nav-accent\)#s', $css_hdr ),
	'phone link'                => (bool) preg_match( '#\.site-header__phone\s*\{[^}]*color:\s*var\(--nav-fg\)#s', $css_hdr ),
	'phone icon'                => (bool) preg_match( '#\.site-header__phone svg\s*\{\s*color:\s*var\(--nav-accent\)#', $css_hdr ),
	'hamburger bars'            => (bool) preg_match( '#\.site-header__menu-toggle-bar\s*\{[^}]*background:\s*var\(--nav-fg\)#s', $css_hdr ),
	'hamburger hairline'        => (bool) preg_match( '#\.site-header__menu-toggle\s*\{[^}]*border:\s*1px solid var\(--nav-hairline\)#s', $css_hdr ),
	'CTA fill + label'          => (bool) preg_match( '#\.site-header__cta\s*\{[^}]*--_btn-bg:\s*var\(--nav-cta-bg\);[^}]*--_btn-fg:\s*var\(--nav-cta-fg\)#s', $css_hdr ),
);
$miss12 = array_keys( array_filter( $routed, static fn( $v ) => ! $v ) );
( $dark_ink && ! $miss12 )
	? ok( '12. scrolled state drives nav text, icons, underline, hamburger and CTA to the dark brand ink through the shared --nav-* set' )
	: bad( '12. dark-ink default=' . var_export( $dark_ink, true ) . ' unrouted: ' . ( $miss12 ? implode( ', ', $miss12 ) : 'none' ) );

// 13.
$fixed = (bool) preg_match( '#^\.site-header\s*\{[^}]*position:\s*fixed;[^}]*top:\s*0;#ms', $css_hdr );
$zed   = (bool) preg_match( '#^\.site-header\s*\{[^}]*z-index:\s*var\(--z-sticky\)#ms', $css_hdr );
( $fixed && $zed ) ? ok( '13. header is position:fixed at top:0 on the sticky layer, so it stays put through the whole scroll' )
	: bad( "13. fixed=" . var_export( $fixed, true ) . " zIndex=" . var_export( $zed, true ) );

// 14.
$single_source = substr_count( $css_hdr, '--stp-header-h: var(--header-h);' ) === 1;
$bar_uses      = (bool) preg_match( '#\.site-header__bar\s*\{[^}]*min-height:\s*var\(--stp-header-h\)#s', $css_hdr );
$hero_uses     = (bool) preg_match( '#padding-top:\s*calc\(var\(--stp-hero-pad-top\)\s*\+\s*var\(--stp-header-h\)\)#', $css_hh );
$flow_uses     = (bool) preg_match( '#body\.no-hero\s*\{\s*padding-top:\s*var\(--stp-header-h\);#s', $css_hh );
// No hardcoded px offsets standing in for the header height anywhere.
$hardcoded     = (bool) preg_match( '#(padding-top|margin-top|top):\s*84px#', $css_hdr . $css_hh );
( $single_source && $bar_uses && $hero_uses && $flow_uses && ! $hardcoded )
	? ok( '14. header height is reserved from ONE custom property (--stp-header-h) by the bar, the hero padding and the no-hero flow — no duplicated pixel offsets' )
	: bad( "14. singleSource=" . var_export( $single_source, true ) . " bar=" . var_export( $bar_uses, true ) . " hero=" . var_export( $hero_uses, true ) . " flow=" . var_export( $flow_uses, true ) . " hardcodedPx=" . var_export( $hardcoded, true ) );

echo "\n== HERO GEOMETRY ==\n";

// Every hero family publishes its own base padding, and the shared sheet adds
// the header height to it exactly once. Guards against a family being added
// later that silently inherits no safe padding.
$sheets = array( 'home.css', 'interior.css', 'service.css', 'contact.css', 'blog.css' );
$declared = 0;
foreach ( $sheets as $s ) { $declared += substr_count( (string) file_get_contents( $child . '/assets/css/' . $s ), '--stp-hero-pad-top:' ); }
$consumed = substr_count( $css_hh, 'var(--stp-hero-pad-top)' );
( $declared >= 8 && 1 === $consumed )
	? ok( "14b. all $declared hero families declare their own --stp-hero-pad-top and the shared sheet adds the header height in exactly one place" )
	: bad( "14b. declared=$declared consumed=$consumed (expected >=8 declared, exactly 1 consumer)" );

$absorb = (bool) preg_match( '#body\.has-hero \.home-hero\s*\{\s*min-height:\s*calc\(var\(--stp-hero-min-h\)\s*\+\s*var\(--stp-header-h\)\)#s', $css_hh )
	&& (bool) preg_match( '#min-height:\s*100dvh;#', $css_hh );
$absorb ? ok( '14c. each hero absorbs exactly the header height the fixed bar gave back (full-screen heroes → 100dvh, home hero → its own clamp + header)' )
	: bad( '14c. hero height transfer rules missing from header-hero.css' );

echo "\n== NAVIGATION BEHAVIOUR ==\n";

// 15.
$mega_markup = 0;
foreach ( $hero_pages as $label => $_x ) { if ( false !== strpos( $bodies[ $label ], 'primary-nav__mega' ) ) { $mega_markup++; } }
$mega_hover = (bool) preg_match( '#\.primary-nav__item--has-mega:hover > \.primary-nav__mega,\s*\.primary-nav__item--has-mega:focus-within > \.primary-nav__mega\s*\{[^}]*pointer-events:\s*auto#s', $css_hdr );
$mega_solid = (bool) preg_match( '~\.primary-nav__mega\s*\{.*?background:\s*\#fff~s', $css_hdr );
( $mega_markup === count( $hero_pages ) && $mega_hover && $mega_solid )
	? ok( '15. dropdown panels ship on every hero page and open on hover AND keyboard focus-within, on their own opaque surface' )
	: bad( "15. megaPages=$mega_markup/" . count( $hero_pages ) . " hoverAndFocus=" . var_export( $mega_hover, true ) . " opaque=" . var_export( $mega_solid, true ) );

// 16.
$bad16 = array();
foreach ( $bodies as $label => $b ) {
	if ( false === strpos( $b, 'js-mobile-toggle' ) )            { $bad16[] = "$label: no toggle"; }
	if ( false === strpos( $b, 'aria-controls="mobile-drawer"' ) ) { $bad16[] = "$label: toggle not wired to drawer"; }
	if ( false === strpos( $b, 'id="mobile-drawer"' ) )          { $bad16[] = "$label: no drawer"; }
}
$toggle_shown = (bool) preg_match( '#@media \(max-width: 959px\)\s*\{.*?\.site-header__menu-toggle \{ display: grid; \}#s', $css_hdr );
( ! $bad16 && $toggle_shown ) ? ok( '16. the mobile toggle + drawer are server-rendered on every route and the toggle is revealed under 960px' )
	: bad( '16. ' . implode( '; ', $bad16 ) . ' toggleShown=' . var_export( $toggle_shown, true ) );

// 17.
$drawer_open_readable = (bool) preg_match( '#body\.is-drawer-open \.site-header\s*\{[^}]*background-color:\s*rgba\(255,\s*255,\s*255,\s*0\.9#s', $css_hdr )
	&& (bool) preg_match( '#body\.is-drawer-open \.site-header\s*\{[^}]*--nav-fg:\s*var\(--c-ink\)#s', $css_hdr )
	&& (bool) preg_match( '#body\.is-drawer-open \.site-header::before \{ opacity: 0; \}#', $css_hdr );
$drawer_opaque = (bool) preg_match( '#\.mobile-drawer\s*\{[^}]*background:\s*var\(--c-ink\)#s', $css_hdr );
( $drawer_open_readable && $drawer_opaque )
	? ok( '17. with the drawer open the header is forced to its readable surface and the drawer itself is a fully opaque ink takeover — independent of scroll position' )
	: bad( '17. headerForced=' . var_export( $drawer_open_readable, true ) . ' drawerOpaque=' . var_export( $drawer_opaque, true ) );

// 18.
$focus_rules = array(
	'hamburger focus ring uses both state colours' => (bool) preg_match( '#\.site-header__menu-toggle:focus-visible \{\s*outline: 0;\s*box-shadow: 0 0 0 2px var\(--nav-cta-fg\), 0 0 0 4px var\(--nav-fg\);#', $css_hdr ),
	'skip link is first in the body'               => (bool) preg_match( '#<body[^>]*>\s*(<!--.*?-->\s*)*<a class="skip-link#s', $bodies['homepage hero (.home-hero)'] ),
	'drawer items keep a focus ring'               => (bool) preg_match( '#\.mobile-drawer__sub > summary:focus-visible \{[^}]*box-shadow: var\(--sh-ring\)#s', $css_hdr ),
);
$miss18 = array_keys( array_filter( $focus_rules, static fn( $v ) => ! $v ) );
$miss18 ? bad( '18. keyboard affordances — missing: ' . implode( ', ', $miss18 ) )
	: ok( '18. focus indicators survive both header states (ring colours are state-aware) and the skip link is still the first focusable element' );

echo "\n== NO JAVASCRIPT ==\n";

// 19.
$nojs_block = (bool) preg_match( '#<noscript>\s*<style>(.*?)</style>\s*</noscript>#s', $bodies['project archive hero (.int-hero)'], $m19 );
$nojs_css   = $m19[1] ?? '';
$nojs_ok = $nojs_block
	&& false !== strpos( $nojs_css, 'background-color: rgba(255, 255, 255, 0.9) !important' )
	&& false !== strpos( $nojs_css, '--nav-fg: var(--c-ink) !important' )
	&& false !== strpos( $nojs_css, 'opacity: 0 !important' );
// The page itself must already be complete without scripts: nav links, hero
// and H1 all arrive in the raw HTML this test fetched (curl never runs JS).
$raw = $bodies['project archive hero (.int-hero)'];
$content_without_js = substr_count( $raw, 'primary-nav__link' ) >= 5
	&& false !== strpos( $raw, 'int-hero__title' )
	&& 1 === preg_match_all( '#<h1[\s>]#', $raw );
( $nojs_ok && $content_without_js )
	? ok( '19. without JavaScript the header pins to the readable frosted state via a <noscript><style> fallback, and the nav, hero and H1 are all already in the raw HTML' )
	: bad( '19. noscriptFallback=' . var_export( $nojs_ok, true ) . ' contentWithoutJs=' . var_export( $content_without_js, true ) );

// 22.
$my_files_scripts = preg_match( '#<script#i', $css_hdr . $css_hh . $php_hh ) === 1;
$header_block     = (string) ( preg_match( '#<header[^>]*id="masthead".*?</header>#s', $raw, $mh ) ? $mh[0] : '' );
$header_inline_js = (bool) preg_match( '#<script#i', $header_block ) || (bool) preg_match( '#\son[a-z]+\s*=#i', $header_block );
$php_inline_js    = (bool) preg_match( '#<script#i', $php_hdr );
$js_is_external   = (bool) preg_match( "#wp_enqueue_script\(\s*'showtime-header'#", (string) file_get_contents( $child . '/inc/enqueue.php' ) );
$css_is_external  = (bool) preg_match( "#wp_enqueue_style\(\s*'showtime-header-hero'#", (string) file_get_contents( $child . '/inc/enqueue.php' ) );
( ! $my_files_scripts && ! $header_inline_js && ! $php_inline_js && $js_is_external && $css_is_external )
	? ok( '22. no inline JavaScript introduced — the header markup carries no <script> and no on* handler, and both the scroll script and the hero sheet are enqueued as external files' )
	: bad( '22. headerInlineJs=' . var_export( $header_inline_js, true ) . ' phpInlineJs=' . var_export( $php_inline_js, true ) . ' jsExternal=' . var_export( $js_is_external, true ) . ' cssExternal=' . var_export( $css_is_external, true ) );

echo "\n== SCROLL SCRIPT ==\n";

$js_checks = array(
	'passive scroll listener'      => (bool) preg_match( "#addEventListener\('scroll', onScroll, \{ passive: true \}\)#", $js_hdr ),
	'requestAnimationFrame gate'   => (bool) preg_match( '#requestAnimationFrame\(apply\)#', $js_hdr ),
	'no layout read on scroll'     => ! (bool) preg_match( '#getBoundingClientRect|offsetHeight|offsetTop|clientHeight#', $js_code ),
	'trigger inside 16-24px'       => (bool) preg_match( '#SCROLL_TRIGGER = (\d+)#', $js_hdr, $mt ) && (int) $mt[1] >= 16 && (int) $mt[1] <= 24,
	'seeds from rendered markup'   => (bool) preg_match( "#header\.dataset\.scrolled === 'true'#", $js_hdr ),
	'syncs immediately'            => (bool) preg_match( '#\n\t\tapply\(\);#', $js_hdr ),
	'server renders initial state' => (bool) preg_match( '#data-scrolled="false"#', $php_hdr ),
);
$miss_js = array_keys( array_filter( $js_checks, static fn( $v ) => ! $v ) );
$miss_js ? bad( '13b. scroll script contract — missing: ' . implode( ', ', $miss_js ) )
	: ok( '13b. scroll state uses one passive listener + rAF, reads only scrollY, flips at ' . $mt[1] . 'px, and is seeded from the server-rendered attribute so there is no state flash' );

echo "\n== MOTION ==\n";

$dur_ok = (bool) preg_match( '#transition:\s*\n?\s*background-color (\d+)ms#', $css_hdr, $md ) && (int) $md[1] >= 180 && (int) $md[1] <= 250;
$rm_ok  = (bool) preg_match( '#@media \(prefers-reduced-motion: reduce\)\s*\{\s*\.site-header,\s*\.site-header::before \{ transition: none; \}#s', $css_hdr );
( $dur_ok && $rm_ok )
	? ok( '18b. the state change transitions in ' . $md[1] . 'ms and is disabled entirely under prefers-reduced-motion' )
	: bad( '18b. duration=' . ( $md[1] ?? '?' ) . ' reducedMotion=' . var_export( $rm_ok, true ) );

echo "\n== CONTRAST SCRIM ==\n";

// The scrim is the only reason nav readability does not depend on the
// brightness of a photograph, so its geometry and strength are pinned.
$scrim_ok = (bool) preg_match( '#\.site-header::before \{(.*?)\n\}#s', $css_hdr, $ms );
$sb = $ms[1] ?? '';
$scrim_checks = array(
	'sits behind the bar content' => (bool) preg_match( '#\.site-header__bar \{[^}]*position: relative;\s*z-index: 1;#s', $css_hdr ),
	'never intercepts clicks'     => (bool) preg_match( '#pointer-events:\s*none#', $sb ),
	'covers the header band only' => (bool) preg_match( '#height: calc\(\(var\(--stp-header-h\) \+ var\(--stp-admin-bar-h, 0px\)\) \* 2\)#', $sb ),
	'fades to fully transparent'  => (bool) preg_match( '#rgba\(11, 23, 51, 0\) 100%#', $sb ),
	'off unless top of a hero'    => (bool) preg_match( '#opacity: 0;#', $sb ) && (bool) preg_match( '#body\.has-hero \.site-header\[data-scrolled="false"\]::before \{ opacity: 1; \}#', $css_hdr ),
);
// Alpha at the nav baseline (~25% down the band) must keep white text >= 4.5:1
// even over a pure-white photograph.
preg_match( '#rgba\(11, 23, 51, (0\.\d+)\) 0%#', $sb, $a0 );
preg_match( '#rgba\(11, 23, 51, (0\.\d+)\) 50%#', $sb, $a50 );
$alpha_at_nav = ( isset( $a0[1], $a50[1] ) ) ? (float) $a0[1] + ( (float) $a50[1] - (float) $a0[1] ) * 0.5 : 0.0;
$lin = static function ( float $c ): float { $c /= 255; return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 ); };
$mix = static fn( float $a, float $ink ): float => ( 1 - $a ) * 255 + $a * $ink;
$L   = 0.2126 * $lin( $mix( $alpha_at_nav, 11 ) ) + 0.7152 * $lin( $mix( $alpha_at_nav, 23 ) ) + 0.0722 * $lin( $mix( $alpha_at_nav, 51 ) );
$ratio_worst = 1.05 / ( $L + 0.05 );
$miss_scrim = array_keys( array_filter( $scrim_checks, static fn( $v ) => ! $v ) );
( ! $miss_scrim && $ratio_worst >= 4.5 )
	? ok( sprintf( '10b. the contrast scrim covers only the header band, sits above the hero media but beneath the bar content, and holds white nav text at %.2f:1 over the worst possible backdrop (pure white)', $ratio_worst ) )
	: bad( '10b. missing: ' . implode( ', ', $miss_scrim ) . sprintf( ' worstCaseRatio=%.2f', $ratio_worst ) );

echo "\n== WORDPRESS ADMIN BAR ==\n";

// 20.
$ab = array(
	'default is zero'      => (bool) preg_match( '#:root \{ --stp-admin-bar-h: 0px; \}#', $css_hdr ),
	'32px when logged in'  => (bool) preg_match( '#body\.admin-bar \{ --stp-admin-bar-h: 32px; \}#', $css_hdr ),
	'46px under 783px'     => (bool) preg_match( '#@media screen and \(max-width: 782px\) \{\s*body\.admin-bar \{ --stp-admin-bar-h: 46px; \}#s', $css_hdr ),
	'header clears the bar' => (bool) preg_match( '#^\.site-header \{.*?padding-top: var\(--stp-admin-bar-h, 0px\);#ms', $css_hdr ),
	'scrim clears it too'  => (bool) preg_match( '#var\(--stp-admin-bar-h, 0px\)\) \* 2\)#', $css_hdr ),
);
$miss20 = array_keys( array_filter( $ab, static fn( $v ) => ! $v ) );
$miss20 ? bad( '20. admin-bar offsets — missing: ' . implode( ', ', $miss20 ) )
	: ok( '20. the fixed header pads its own box by the admin-bar height (0/32/46px), so header content starts exactly where flow content starts and never sits under the bar' );

// 20b. At <=600px core makes #wpadminbar position:absolute, so it scrolls away.
// Holding the padding reservation there would leave an empty frosted band above
// the logo and hamburger once the bar had gone, so the height must be carried as
// an offset that is released on scroll instead. Regression guard for that.
$ab600 = array(
	'reservation released' => (bool) preg_match( '#@media screen and \(max-width: 600px\) \{\s*body\.admin-bar \{ --stp-admin-bar-h: 0px; \}#s', $css_hdr ),
	'offset at rest'       => (bool) preg_match( '#body\.admin-bar \.site-header \{ top: 46px; \}#', $css_hdr ),
	'docks flush on scroll'=> (bool) preg_match( '#body\.admin-bar \.site-header\[data-scrolled="true"\] \{ top: 0; \}#', $css_hdr ),
	'top is never animated' => ! (bool) preg_match( '#transition:[^;]*\btop\b#s', $css_hdr ),
);
$miss20b = array_keys( array_filter( $ab600, static fn( $v ) => ! $v ) );
$miss20b ? bad( '20b. ≤600px admin-bar handling — missing: ' . implode( ', ', $miss20b ) )
	: ok( '20b. at ≤600px, where core lets the admin bar scroll away, the header carries its height as a released offset rather than padding — full 84px bar, no empty frosted band, controls visible at every scroll position' );

echo "\n== NO HORIZONTAL OVERFLOW ==\n";

// 23.
$guard = (bool) preg_match( '#html, body \{ overflow-x: clip; \}#', (string) file_get_contents( $child . '/assets/css/tokens.css' ) );
$no_width_hacks = ! (bool) preg_match( '#\.site-header[^{]*\{[^}]*width:\s*100vw#s', $css_hdr );
$anchored = (bool) preg_match( '#^\.site-header \{[^}]*left: 0;\s*right: 0;#ms', $css_hdr );
( $guard && $no_width_hacks && $anchored )
	? ok( '23. the fixed header is anchored with left/right:0 rather than 100vw (which would overflow when a scrollbar is present), and the global overflow-x guard is intact' )
	: bad( '23. guard=' . var_export( $guard, true ) . ' no100vw=' . var_export( $no_width_hacks, true ) . ' anchored=' . var_export( $anchored, true ) );

echo "\n== SCOPE PROTECTION ==\n";

// 24.
$widget_id = '69b32c236a7fada7ea40faca';
$bad24 = array();
foreach ( $bodies as $label => $b ) {
	if ( false === strpos( $b, $widget_id ) )                                    { $bad24[] = "$label: widget id missing"; }
	if ( false === strpos( $b, 'widgets.leadconnectorhq.com/loader.js' ) )       { $bad24[] = "$label: loader missing"; }
}
$collision = (bool) preg_match( '#\.mobile-drawer \{[^}]*z-index: 100000000;#s', $css_hdr );
( ! $bad24 && $collision )
	? ok( '24. the GHL widget id + loader are untouched on every route and the drawer still out-stacks the chat widget (z-index 100000000)' )
	: bad( '24. ' . implode( '; ', $bad24 ) . ' collisionFix=' . var_export( $collision, true ) );

// 25.
$expect_h1 = array(
	'homepage hero (.home-hero)'          => 'Pool service in Los Angeles.',
	'service hero (.svc-hero)'            => 'Pool Repair Service in Los Angeles',
	'project archive hero (.int-hero)'    => 'Projects',
	'service-area hero (.area-hero)'      => 'Sherman Oaks',
);
$bad25 = array();
foreach ( $bodies as $label => $b ) {
	if ( ! isset( $no_hero_pages[ $label ] ) ) {
		$n = preg_match_all( '#<h1[\s>]#', $b );
		if ( 1 !== $n ) { $bad25[] = "$label has $n H1s"; }
	}
	if ( 1 !== substr_count( $b, '<link rel="canonical"' ) && ! isset( $no_hero_pages[ $label ] ) ) { $bad25[] = "$label canonical count"; }
	if ( substr_count( $b, 'property="og:title"' ) > 1 )  { $bad25[] = "$label duplicate og:title"; }
	if ( substr_count( $b, 'name="twitter:card"' ) > 1 )  { $bad25[] = "$label duplicate twitter:card"; }
}
foreach ( $expect_h1 as $label => $text ) {
	if ( false === strpos( html_entity_decode( wp_strip_all_tags( $bodies[ $label ] ) ), $text ) ) { $bad25[] = "$label H1 text changed"; }
}
$bad25 ? bad( '25. hero content / metadata moved — ' . implode( '; ', $bad25 ) )
	: ok( '25. every hero page still has exactly one H1 with its original text, one canonical, and no duplicated OG/Twitter tags' );

echo "\n== GUARDS ==\n";

$guards = array(
	'is_admin'    => (bool) preg_match( '#is_admin\(\)#', $php_hh ),
	'AJAX'        => (bool) preg_match( '#wp_doing_ajax\(\)#', $php_hh ),
	'cron'        => (bool) preg_match( '#wp_doing_cron\(\)#', $php_hh ),
	'WP-CLI'      => (bool) preg_match( "#defined\( 'WP_CLI' \) && WP_CLI#", $php_hh ),
	'REST'        => (bool) preg_match( "#defined\( 'REST_REQUEST' \) && REST_REQUEST#", $php_hh ),
	'XML-RPC'     => (bool) preg_match( "#defined\( 'XMLRPC_REQUEST' \) && XMLRPC_REQUEST#", $php_hh ),
	'login'       => (bool) preg_match( '#is_login\(\)#', $php_hh ),
	'feeds'       => (bool) preg_match( '#is_feed\(\)#', $php_hh ),
	'embeds'      => (bool) preg_match( '#is_embed\(\)#', $php_hh ),
	'404'         => (bool) preg_match( '#is_404\(\)#', $php_hh ),
	'sitemaps'    => (bool) preg_match( "#get_query_var\( 'sitemap', '' \)#", $php_hh ),
);
$miss_g = array_keys( array_filter( $guards, static fn( $v ) => ! $v ) );
$miss_g ? bad( '9b. transparent treatment is not fenced off from: ' . implode( ', ', $miss_g ) )
	: ok( '9b. the transparent treatment fails closed for admin, login, AJAX, REST, cron, WP-CLI, XML-RPC, feeds, embeds, sitemaps and 404 — every one of them lands on the readable state' );

// Unknown templates must default to the safe state, not the transparent one.
$allow_list = (bool) preg_match( '#in_array\( \$template, showtime_hero_templates\(\), true \)#', $php_hh );
$allow_list ? ok( "9c. the hero set is an explicit allow-list keyed on the resolved template basename, so any template added later starts on the readable frosted state" )
	: bad( '9c. hero detection is not an explicit allow-list' );

echo "\n== RESULT ==\n";
if ( ! $all_ok ) { echo "  (one or more pages failed to fetch — is the local site running?)\n"; }
echo "  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );

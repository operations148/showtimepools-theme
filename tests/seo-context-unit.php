<?php
/**
 * Unit tests for registry SEO slug resolution (audit item P0-1).
 *
 * Covers the case the HTTP smoke tests structurally cannot: a service/area page
 * whose `_showtime_*_slug` post meta is MISSING must still resolve its registry
 * seo_title / seo_meta via a template-gated post_name fallback — while an
 * unrelated page that merely shares a slug must NOT inherit that SEO.
 *
 *   php tests/seo-context-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/seo-context-unit.php
 *
 * Creates temporary draft fixtures in the LOCAL test database and deletes them
 * again (force delete, no trash). Never run against production.
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

if ( ! function_exists( 'showtime_registry_slug' ) ) {
	bad( 'showtime_registry_slug() not loaded — is the child theme active?' );
	echo "\n== RESULT ==\n  pass: 0   fail: 1\n";
	exit( 1 );
}

/** Create a throwaway page; returns ID. */
function fx_page( string $slug, ?string $template = null, array $meta = array() ): int {
	$id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => 'FIXTURE ' . $slug,
		'post_name'   => $slug,
	) );
	if ( is_wp_error( $id ) || ! $id ) { return 0; }
	if ( null !== $template ) { update_post_meta( $id, '_wp_page_template', $template ); }
	foreach ( $meta as $k => $v ) { update_post_meta( $id, $k, $v ); }
	return (int) $id;
}

$created = array();
$mk = function ( ...$a ) use ( &$created ) { $id = fx_page( ...$a ); $created[] = $id; return $id; };

// Pick real registry entries — never hardcode slugs.
$svc  = class_exists( '\\Showtime\\Services' ) ? ( \Showtime\Services::all()[0] ?? null ) : null;
$area = class_exists( '\\Showtime\\Areas' )    ? ( \Showtime\Areas::all()[0] ?? null )    : null;
if ( ! $svc || ! $area ) {
	bad( 'Registries unavailable (showtime-pools-core inactive?)' );
	echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
	exit( 1 );
}
$svc_slug  = (string) $svc['slug'];
$area_slug = (string) $area['slug'];

echo "\n== SEO CONTEXT UNIT TESTS ==\n";
echo "  using service '{$svc_slug}', area '{$area_slug}'\n\n";

echo "[services]\n";

// 1. Correct meta present -> registry slug.
$id = $mk( 'fx-svc-with-meta', 'page-service.php', array( '_showtime_service_slug' => $svc_slug ) );
showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' ) === $svc_slug
	? ok( '1. meta present resolves registry slug' )
	: bad( '1. meta present did not resolve' );

// 2. Meta MISSING but correct template -> post_name fallback (the P0-1 bug).
$id = $mk( $svc_slug . '', 'page-service.php' );
showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' ) === $svc_slug
	? ok( '2. missing meta falls back to post_name on page-service.php' )
	: bad( '2. missing meta did NOT fall back (P0-1 regression)' );

// 3. Unrelated page with a matching slug must NOT inherit service SEO.
$id = $mk( $svc_slug . '', 'page-legal.php' );
'' === showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' )
	? ok( '3. matching slug on a DIFFERENT template is not treated as a service' )
	: bad( '3. template gate leaked — unrelated page inherited service SEO' );

// 3b. Default template (no _wp_page_template) also must not inherit.
$id = $mk( $svc_slug . '', null );
'' === showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' )
	? ok( '3b. matching slug on the default template is not a service' )
	: bad( '3b. default-template page inherited service SEO' );

// 5. Post meta keeps priority over a conflicting post_name.
$other = null;
foreach ( \Showtime\Services::all() as $s ) { if ( $s['slug'] !== $svc_slug ) { $other = (string) $s['slug']; break; } }
$id = $mk( $other, 'page-service.php', array( '_showtime_service_slug' => $svc_slug ) );
showtime_registry_slug( $id, '_showtime_service_slug', 'page-service.php' ) === $svc_slug
	? ok( '5. post meta wins over a differing post_name' )
	: bad( '5. post_name overrode post meta' );

echo "\n[areas]\n";

$id = $mk( 'fx-area-with-meta', 'page-area.php', array( '_showtime_area_slug' => $area_slug ) );
showtime_registry_slug( $id, '_showtime_area_slug', 'page-area.php' ) === $area_slug
	? ok( '4a. meta present resolves registry slug' )
	: bad( '4a. meta present did not resolve' );

$id = $mk( $area_slug . '', 'page-area.php' );
showtime_registry_slug( $id, '_showtime_area_slug', 'page-area.php' ) === $area_slug
	? ok( '4b. missing meta falls back to post_name on page-area.php' )
	: bad( '4b. missing meta did NOT fall back' );

$id = $mk( $area_slug . '', 'page-legal.php' );
'' === showtime_registry_slug( $id, '_showtime_area_slug', 'page-area.php' )
	? ok( '4c. matching slug on a DIFFERENT template is not treated as an area' )
	: bad( '4c. template gate leaked for areas' );

echo "\n[registry completeness]\n";

// 7. Every service has non-empty seo_title + seo_meta.
$missing = array();
foreach ( \Showtime\Services::all() as $s ) {
	if ( '' === trim( (string) ( $s['seo_title'] ?? '' ) ) ) { $missing[] = $s['slug'] . ':title'; }
	if ( '' === trim( (string) ( $s['seo_meta'] ?? '' ) ) )  { $missing[] = $s['slug'] . ':meta'; }
}
$missing ? bad( '7. missing registry SEO: ' . implode( ', ', $missing ) )
         : ok( '7. all ' . count( \Showtime\Services::all() ) . ' services have seo_title + seo_meta' );

// 8. Those values are genuinely distinct (not all one default).
$titles = $metas = array();
foreach ( \Showtime\Services::all() as $s ) { $titles[] = $s['seo_title'] ?? ''; $metas[] = $s['seo_meta'] ?? ''; }
$n = count( $titles );
( count( array_unique( $titles ) ) === $n && count( array_unique( $metas ) ) === $n )
	? ok( "8. all $n service titles and descriptions are unique" )
	: bad( '8. duplicate SEO values: ' . count( array_unique( $titles ) ) . " unique titles, " . count( array_unique( $metas ) ) . " unique metas of $n" );

// ── cleanup ────────────────────────────────────────────────────────────────
$deleted = 0;
foreach ( array_filter( $created ) as $cid ) { if ( wp_delete_post( $cid, true ) ) { $deleted++; } }
echo "\n  cleanup: removed $deleted fixture page(s)\n";

echo "\n== RESULT ==\n  pass: $pass   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );

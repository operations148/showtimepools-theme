<?php
/**
 * SEO / MetaSync migration audit — READ-ONLY by default.
 *
 * Run on the server against the live WordPress install:
 *   wp eval-file scripts/seo-audit.php --allow-root --path=/www/wwwroot/showtimepools.com
 *
 * Reports (writes nothing):
 *   1. Posts/pages whose title/excerpt/content contains retired "…Mechanics" branding.
 *   2. MetaSync / OTTO / Search Atlas post-meta keys in use (+ counts).
 *   3. Duplicate published titles.
 *   4. Duplicate excerpts (proxy for duplicate meta descriptions).
 *   5. Published pages/posts with no excerpt (no custom meta description).
 *   6. MetaSync head-owner status (via showtime_metasync_status(), if loaded).
 *
 * There is NO write mode here on purpose. Any content change (fixing an
 * excerpt, retiring branding, changing a page to draft) is CONTENT and must be
 * done in wp-admin / via explicit `wp post update` after export — never by a
 * migration script (per the project deployment contract). The commands are
 * printed at the end for the operator to run deliberately.
 *
 * @package ShowtimePools
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via: wp eval-file scripts/seo-audit.php\n" );
	exit( 1 );
}

global $wpdb;
$line = str_repeat( '=', 70 );
echo "\n$line\nSHOWTIME POOLS — SEO / METASYNC AUDIT (read-only)\n$line\n";

/* 1. Retired "Mechanics" branding in content. */
echo "\n[1] Retired 'Mechanics' branding in post title/excerpt/content:\n";
$rows = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title
	 FROM {$wpdb->posts}
	 WHERE post_status IN ('publish','draft','pending','private')
	   AND ( post_title LIKE '%Pools Mechanics%' OR post_title LIKE '%Pool Mechanics%'
	      OR post_excerpt LIKE '%Pools Mechanics%' OR post_excerpt LIKE '%Pool Mechanics%'
	      OR post_content LIKE '%Pools Mechanics%' OR post_content LIKE '%Pool Mechanics%' )
	 ORDER BY post_type, ID"
);
if ( $rows ) {
	foreach ( $rows as $r ) {
		printf( "    #%-5d %-8s %-9s  %s\n", $r->ID, $r->post_type, $r->post_status, $r->post_title );
	}
	echo "    -> Fix each in wp-admin (title/excerpt/body). Keep operations@showtimepoolmechanics.com (email) as-is.\n";
} else {
	echo "    none.\n";
}

/* 2. MetaSync / OTTO post-meta keys. */
echo "\n[2] MetaSync / OTTO / Search Atlas post-meta keys in use:\n";
$meta = $wpdb->get_results(
	"SELECT meta_key, COUNT(*) AS n
	 FROM {$wpdb->postmeta}
	 WHERE meta_key LIKE '%metasync%' OR meta_key LIKE '%otto%' OR meta_key LIKE '%searchatlas%' OR meta_key LIKE '%search_atlas%'
	 GROUP BY meta_key ORDER BY n DESC"
);
if ( $meta ) {
	foreach ( $meta as $m ) {
		printf( "    %-45s %d rows\n", $m->meta_key, $m->n );
	}
	echo "    -> These are MetaSync-owned. Migrate any needed values into theme/ACF fields BEFORE deactivating MetaSync.\n";
} else {
	echo "    none found in post_meta (MetaSync may store config in wp_options instead).\n";
}

/* 3. Duplicate published titles. */
echo "\n[3] Duplicate published titles:\n";
$dupes = $wpdb->get_results(
	"SELECT post_title, COUNT(*) AS n
	 FROM {$wpdb->posts}
	 WHERE post_status='publish' AND post_type IN ('page','post','project')
	 GROUP BY post_title HAVING n > 1 ORDER BY n DESC"
);
if ( $dupes ) {
	foreach ( $dupes as $d ) { printf( "    (%dx) %s\n", $d->n, $d->post_title ); }
} else { echo "    none.\n"; }

/* 4. Duplicate excerpts (proxy for duplicate meta descriptions). */
echo "\n[4] Duplicate non-empty excerpts (proxy for duplicate meta descriptions):\n";
$dexc = $wpdb->get_results(
	"SELECT LEFT(post_excerpt,60) AS ex, COUNT(*) AS n
	 FROM {$wpdb->posts}
	 WHERE post_status='publish' AND post_excerpt <> ''
	 GROUP BY post_excerpt HAVING n > 1 ORDER BY n DESC"
);
if ( $dexc ) {
	foreach ( $dexc as $d ) { printf( "    (%dx) %s…\n", $d->n, trim( $d->ex ) ); }
} else { echo "    none.\n"; }

/* 5. Published pages/posts without an excerpt (no custom description). */
echo "\n[5] Published pages/posts with NO excerpt (no custom meta description):\n";
$noexc = $wpdb->get_results(
	"SELECT ID, post_type, post_title
	 FROM {$wpdb->posts}
	 WHERE post_status='publish' AND post_type IN ('page','post','project') AND (post_excerpt IS NULL OR post_excerpt='')
	 ORDER BY post_type, ID"
);
if ( $noexc ) {
	printf( "    %d item(s) rely on the theme's generated fallback description:\n", count( $noexc ) );
	foreach ( array_slice( $noexc, 0, 40 ) as $r ) { printf( "    #%-5d %-8s %s\n", $r->ID, $r->post_type, $r->post_title ); }
	if ( count( $noexc ) > 40 ) { echo "    …(+" . ( count( $noexc ) - 40 ) . " more)\n"; }
} else { echo "    none — every published item has a custom excerpt.\n"; }

/* 6. MetaSync head-owner status. */
echo "\n[6] MetaSync head-owner status:\n";
if ( function_exists( 'showtime_metasync_status' ) ) {
	$s = showtime_metasync_status();
	printf( "    plugin active: %s\n", $s['metasync_plugin_active'] ? 'YES' : 'no' );
	printf( "    otto-tracker handles found: %s\n", $s['tracker_handles'] ? implode( ', ', $s['tracker_handles'] ) : 'none (front-end)' );
} else {
	echo "    (theme metasync-compat not loaded in this context)\n";
}

echo "\n$line\nDONE (read-only). No data was modified.\n$line\n";

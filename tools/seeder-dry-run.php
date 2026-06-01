<?php
/**
 * Local-only CLI bootstrap to dry-run the seeder without WP-CLI.
 *
 * Usage:
 *   php tools/seeder-dry-run.php           # dry-run
 *   php tools/seeder-dry-run.php --write   # actually writes
 *
 * Not committed. Lives in tools/ for ad-hoc verification during build.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "CLI only.\n" );
}

$write = in_array( '--write', $argv, true );

// Locate wp-load.php. This script lives in <repo>/tools, the WP install is at
// <repo-parent>/wp (XAMPP layout discovered via prior agent inspection).
$candidates = array(
	dirname( __DIR__, 2 ) . '/wp/wp-load.php',
	'C:/xampp/htdocs/showtimepools/wp/wp-load.php',
);
$wp_load = null;
foreach ( $candidates as $c ) {
	if ( file_exists( $c ) ) {
		$wp_load = $c;
		break;
	}
}
if ( ! $wp_load ) {
	fwrite( STDERR, "Could not locate wp-load.php; tried:\n  " . implode( "\n  ", $candidates ) . "\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_load;

if ( ! class_exists( '\\Showtime\\Admin\\Seeder' ) ) {
	fwrite( STDERR, "Showtime\\Admin\\Seeder not found — is the plugin active?\n" );
	exit( 1 );
}

echo $write ? "⚡ Writing to DB.\n" : "🔍 Dry run (no writes).\n";
echo str_repeat( '─', 64 ) . "\n";

$seeder  = new \Showtime\Admin\Seeder();
$results = $seeder->seed_all( $write );

$text = $results['text'];
$img  = $results['images'];

echo "TEXT FIELDS\n";
printf(
	"  Page meta:    %3d %s, %3d skipped (already set)\n",
	$text['written'],
	$write ? 'written' : 'would write',
	$text['skipped']
);
printf(
	"  Sitewide:     %3d %s, %3d skipped (already set)\n",
	$text['options_written'],
	$write ? 'written' : 'would write',
	$text['options_skipped']
);

if ( ! empty( $text['by_page'] ) ) {
	echo "  By page:\n";
	foreach ( $text['by_page'] as $label => $r ) {
		printf( "    · %-55s  %2d / skip %d\n", $label, $r['written'], $r['skipped'] );
	}
}

echo "\nIMAGES\n";
printf(
	"  Imports:      %3d %s\n  Reused:       %3d existing attachments\n  Skipped:      %3d slots already set\n  Missing src:  %3d slots\n",
	$img['imported'],
	$write ? 'imported' : 'would import',
	$img['reused'],
	$img['skipped'],
	$img['missing']
);

if ( ! empty( $img['errors'] ) ) {
	echo "  Errors:\n";
	foreach ( $img['errors'] as $e ) {
		echo "    ! $e\n";
	}
}

if ( ! empty( $img['per_slot'] ) ) {
	echo "  Per slot:\n";
	foreach ( $img['per_slot'] as $slot => $status ) {
		printf( "    · %-40s  %s\n", $slot, $status );
	}
}

echo str_repeat( '─', 64 ) . "\n";
echo ( $write ? "✓ Seeding complete." : "✓ Dry run complete." ) . "\n";

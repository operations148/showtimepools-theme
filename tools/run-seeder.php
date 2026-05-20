<?php
/**
 * One-shot seeder runner. Run from the CLI:
 *   php tools/run-seeder.php
 *
 * Bootstraps WordPress, then calls
 * \Showtime\Admin\PageSeeder::run_all_idempotent() so the Project CPT
 * + blog seeding logic added in Phase C executes against the live DB
 * without requiring a plugin deactivate/reactivate cycle.
 *
 * Safe to re-run — every seed step is idempotent.
 */

declare( strict_types = 1 );

// Locate the local WP install (sibling of this repo).
$wp_load = realpath( __DIR__ . '/../../wp/wp-load.php' );
if ( ! $wp_load || ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at $wp_load\n" );
	exit( 1 );
}
require $wp_load;

if ( ! class_exists( '\\Showtime\\Admin\\PageSeeder' ) ) {
	fwrite( STDERR, "PageSeeder class not autoloaded. Is showtime-pools-core active?\n" );
	exit( 2 );
}

// Force re-run by removing the first-run gate (idempotent — no posts duplicate).
delete_option( 'showtime_first_run_complete' );

$seeder = new \Showtime\Admin\PageSeeder();
$result = $seeder->run_all_idempotent();

update_option( 'showtime_first_run_complete', '1' );
update_option( 'showtime_first_run_result', $result );
update_option( 'showtime_first_run_at', gmdate( 'c' ) );

flush_rewrite_rules();

fwrite( STDOUT, "Seed result: " . wp_json_encode( $result ) . "\n" );
fwrite( STDOUT, "Projects: " . wp_count_posts( 'project' )->publish . " published\n" );
$blog_posts = wp_count_posts( 'post' );
fwrite( STDOUT, "Blog posts: " . ( $blog_posts->publish ?? 0 ) . " published\n" );
fwrite( STDOUT, "Categories: " . wp_count_terms( array( 'taxonomy' => 'category' ) ) . "\n" );

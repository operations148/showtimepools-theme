<?php
/**
 * Lightweight WP hardening (theme-level).
 *
 * Wordfence handles malware, brute force, IP reputation. This file handles
 * the cheap-and-obvious WP exposure surface that doesn't need a plugin.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Hide WP version from <head> and feeds. Reduces fingerprinting.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Strip ?ver= from script/style URLs ONLY when the version equals the WP
// core version (which is the actual fingerprinting risk). File-mtime-based
// versions (numeric epoch timestamps) MUST pass through, otherwise the
// browser has no cache-busting signal when we ship CSS/JS edits.
$showtime_strip_core_ver = function ( $src ) {
	$wp_version = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '';
	if ( '' === $wp_version ) {
		return $src;
	}
	$qs = wp_parse_url( $src, PHP_URL_QUERY );
	if ( ! $qs ) {
		return $src;
	}
	parse_str( $qs, $args );
	if ( isset( $args['ver'] ) && (string) $args['ver'] === $wp_version ) {
		return remove_query_arg( 'ver', $src );
	}
	return $src;
};
add_filter( 'style_loader_src',  $showtime_strip_core_ver, 9999 );
add_filter( 'script_loader_src', $showtime_strip_core_ver, 9999 );

// Disable XML-RPC entirely. We don't use Jetpack/legacy clients.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// Disable WLW / RSD links.
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

// Generic login error so attackers can't enumerate valid usernames.
add_filter(
	'login_errors',
	function () {
		return __( 'Login failed. Check your credentials and try again.', 'showtime-pools' );
	}
);

// Disable file editing in admin (themes/plugins). Belt-and-suspenders with wp-config.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Block the user enumeration trick (?author=1 redirect).
add_action(
	'init',
	function () {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}
);

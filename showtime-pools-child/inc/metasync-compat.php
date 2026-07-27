<?php
/**
 * MetaSync / Search Atlas OTTO compatibility layer.
 *
 * The theme is the single authoritative owner of the document head — title,
 * meta description, canonical, robots, Open Graph, Twitter, and JSON-LD
 * (see inc/seo.php + inc/seo-defaults.php). This file only handles the ONE
 * piece the theme can safely touch from its own side without editing vendor
 * files or output-buffering the page: the front-end OTTO tracking script.
 *
 * IMPORTANT — what this file does NOT and CANNOT do:
 *   OTTO's server-side metadata rewriting (the double-brand <title>,
 *   data-metasync-otto attributes, injected <meta>/JSON-LD) is produced by the
 *   MetaSync plugin's own PHP. Neutralising it reliably requires MetaSync's
 *   supported settings/hooks, which are NOT in this repo and must not be
 *   guessed. Per the deployment report, OTTO metadata deployment must be turned
 *   OFF in the Search Atlas dashboard (or the plugin deactivated after its
 *   approved metadata is migrated into the theme/ACF SEO fields). This file is
 *   the theme-side half; the dashboard is the other half.
 *
 * Fails safe: every action is a no-op when MetaSync is not installed.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the front-end OTTO tracker script. We match on the exact script
 * FILENAME (`otto-tracker`) rather than a guessed handle, and only ever touch
 * a handle whose src actually points at that file — nothing else MetaSync or
 * WordPress enqueues is affected. Disable via the filter if the tracker is ever
 * needed for reporting.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}
		if ( ! apply_filters( 'showtime/metasync/strip_tracker', true ) ) {
			return;
		}
		foreach ( showtime_metasync_tracker_handles() as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	},
	99
);

/**
 * Resolve the registered script handle(s) whose source is the OTTO tracker
 * file. Returns an empty array when MetaSync isn't present — the caller no-ops.
 *
 * @return string[]
 */
function showtime_metasync_tracker_handles(): array {
	$needle   = (string) apply_filters( 'showtime/metasync/tracker_filename', 'otto-tracker' );
	$handles  = array();
	$wp_scripts = wp_scripts();
	if ( ! $wp_scripts instanceof WP_Scripts ) {
		return $handles;
	}
	foreach ( $wp_scripts->registered as $handle => $script ) {
		$src = isset( $script->src ) ? (string) $script->src : '';
		if ( '' !== $src && false !== strpos( $src, $needle ) ) {
			$handles[] = (string) $handle;
		}
	}
	return $handles;
}

/**
 * Detection helper for reporting / smoke tests: is MetaSync still active as a
 * head owner on this install? Read-only; returns a small status array. Callable
 * from `wp eval` / the audit script.
 *
 * @return array<string,mixed>
 */
function showtime_metasync_status(): array {
	$plugin_active = false;
	if ( function_exists( 'is_plugin_active' ) ) {
		$plugin_active = is_plugin_active( 'metasync/metasync.php' );
	} elseif ( in_array( 'metasync/metasync.php', (array) get_option( 'active_plugins', array() ), true ) ) {
		$plugin_active = true;
	}
	return array(
		'metasync_plugin_active' => $plugin_active,
		'tracker_handles'        => showtime_metasync_tracker_handles(),
		'note'                   => 'OTTO metadata rewriting is controlled in the Search Atlas dashboard, not here. See .claude/audits.',
	);
}

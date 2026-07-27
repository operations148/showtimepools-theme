<?php
/**
 * MetaSync / Search Atlas OTTO compatibility layer.
 *
 * The theme is the single authoritative owner of the document head — title,
 * meta description, canonical, robots, Open Graph, Twitter, hreflang, and
 * JSON-LD (see inc/seo.php + inc/seo-defaults.php). MetaSync registers its own
 * wp_head callbacks that emit a competing copy of that metadata, which is what
 * produced the duplicate canonical / description / robots / og:url and the
 * `<meta name="otto">` block observed live.
 *
 * This file unhooks those FRONT-END callbacks only. It does not edit the
 * vendor plugin, does not use output buffering or HTML/regex rewriting, and
 * leaves Search Atlas's admin UI, auditing, REST, sitemap, and robots.txt
 * behaviour untouched.
 *
 * Fails safe: every action is a no-op when MetaSync is not installed.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * The MetaSync front-end `wp_head` callbacks this theme removes.
 *
 * Keyed by the absolute-path SUFFIX of the file that DEFINES the callback;
 * value is the list of callback names from that file to remove, or `'*'` to
 * remove every callback defined in that file.
 *
 * Sourced from a read-only production `$wp_filter['wp_head']` inventory taken
 * on 2026-07-28 — every entry below is a verified callback name + priority +
 * source file, not a guess:
 *
 *   p1  metasync_output_otto_meta_description        otto/metasync-otto-seo-functions.php
 *   p1  Closure                                      otto/otto_pixel.php
 *   p1  Metasync_Seo_Output::hook_metasync_metatags  public/class-metasync-seo-output.php
 *   p1  Metasync_output_dyo_init_flag                metasync.php
 *   p2  Metasync_SEO_Sidebar::output_seo_meta_description   admin/class-metasync-seo-sidebar.php
 *   p4  Metasync_Hreflang_Output::output_hreflang_tags      public/class-metasync-hreflang-output.php
 *   p5  Metasync_Breadcrumbs_Schema::output_breadcrumb_schema  breadcrumbs/class-metasync-breadcrumbs-schema.php
 *   p6  Metasync_OpenGraph::output_opengraph_tags    includes/class-metasync-opengraph.php
 *   p6  Metasync_OpenGraph::output_article_tags      includes/class-metasync-opengraph.php
 *
 * `otto/otto_pixel.php` uses `'*'` because its callback is an anonymous
 * Closure — it has no name to pass to remove_action(), and that file exists
 * solely to emit the OTTO pixel/base block.
 *
 * DELIBERATELY NOT REMOVED (verified present in the same inventory, left
 * registered so Search Atlas keeps working):
 *   p10 Metasync_Admin::metasync_admin_bar_style        — admin UI
 *   p10 Metasync_Code_Snippets::get_header_snippet      — the site owner's own
 *        header snippets (analytics / site-verification tags) are not ours to drop
 *   p10 Metasync_Schema_Markup::output_schema_markup    — currently emits nothing
 *        live (the only JSON-LD on the page is the theme's); left alone as it is
 *        outside the canonical/robots/description/og:url scope of this fix
 *
 * @return array<string, string[]> Path suffix => callback names (or ['*']).
 */
function showtime_metasync_head_removals(): array {
	return (array) apply_filters(
		'showtime/metasync/head_removals',
		array(
			'/plugins/metasync/otto/metasync-otto-seo-functions.php'      => array( 'metasync_output_otto_meta_description' ),
			'/plugins/metasync/otto/otto_pixel.php'                       => array( '*' ),
			'/plugins/metasync/public/class-metasync-seo-output.php'      => array( 'Metasync_Seo_Output::hook_metasync_metatags' ),
			'/plugins/metasync/metasync.php'                              => array( 'Metasync_output_dyo_init_flag' ),
			'/plugins/metasync/admin/class-metasync-seo-sidebar.php'      => array( 'Metasync_SEO_Sidebar::output_seo_meta_description' ),
			'/plugins/metasync/public/class-metasync-hreflang-output.php' => array( 'Metasync_Hreflang_Output::output_hreflang_tags' ),
			'/plugins/metasync/breadcrumbs/class-metasync-breadcrumbs-schema.php' => array( 'Metasync_Breadcrumbs_Schema::output_breadcrumb_schema' ),
			'/plugins/metasync/includes/class-metasync-opengraph.php'     => array(
				'Metasync_OpenGraph::output_opengraph_tags',
				'Metasync_OpenGraph::output_article_tags',
			),
		)
	);
}

/**
 * Resolve a registered callback to its human-readable name and the file that
 * defines it. Handles plain functions, closures, and static/instance methods
 * uniformly — which matters because the OTTO pixel callback is a Closure and
 * cannot be addressed by name.
 *
 * @param mixed $callback A value from $wp_filter[...]->callbacks[$p][$i]['function'].
 * @return array{name:string,file:string} Empty strings when unresolvable.
 */
function showtime_metasync_resolve_callback( $callback ): array {
	$none = array(
		'name' => '',
		'file' => '',
	);

	try {
		if ( $callback instanceof Closure ) {
			$ref = new ReflectionFunction( $callback );
			return array(
				'name' => 'Closure',
				'file' => (string) $ref->getFileName(),
			);
		}

		if ( is_string( $callback ) ) {
			// "Class::method" strings resolve as methods, plain names as functions.
			if ( false !== strpos( $callback, '::' ) ) {
				list( $class, $method ) = explode( '::', $callback, 2 );
				if ( ! class_exists( $class ) || ! method_exists( $class, $method ) ) {
					return $none;
				}
				$ref = new ReflectionMethod( $class, $method );
				return array(
					'name' => $class . '::' . $method,
					'file' => (string) $ref->getFileName(),
				);
			}
			if ( ! function_exists( $callback ) ) {
				return $none;
			}
			$ref = new ReflectionFunction( $callback );
			return array(
				'name' => $callback,
				'file' => (string) $ref->getFileName(),
			);
		}

		if ( is_array( $callback ) && isset( $callback[0], $callback[1] ) ) {
			$class  = is_object( $callback[0] ) ? get_class( $callback[0] ) : (string) $callback[0];
			$method = (string) $callback[1];
			if ( ! method_exists( $class, $method ) ) {
				return $none;
			}
			$ref = new ReflectionMethod( $class, $method );
			return array(
				'name' => $class . '::' . $method,
				'file' => (string) $ref->getFileName(),
			);
		}

		if ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
			$ref = new ReflectionMethod( $callback, '__invoke' );
			return array(
				'name' => get_class( $callback ) . '::__invoke',
				'file' => (string) $ref->getFileName(),
			);
		}
	} catch ( ReflectionException $e ) {
		return $none;
	}

	return $none;
}

/**
 * Decide whether a resolved callback is one of the MetaSync head emitters we
 * remove. Pure function of (file, name) so it is directly unit-testable
 * without a live MetaSync install.
 *
 * Guard rails:
 *   - Never matches anything outside the MetaSync plugin directory.
 *   - Path suffixes are anchored with a leading "/…/plugins/metasync/" so a
 *     target like "metasync.php" cannot accidentally match
 *     "includes/class-metasync.php".
 *
 * @param string $file Absolute path of the defining file.
 * @param string $name Resolved callback name.
 */
function showtime_metasync_is_head_target( string $file, string $name ): bool {
	if ( '' === $file ) {
		return false;
	}

	$path = str_replace( '\\', '/', $file );

	// Hard boundary: only ever consider files inside the MetaSync plugin.
	if ( false === strpos( $path, '/plugins/metasync/' ) ) {
		return false;
	}

	foreach ( showtime_metasync_head_removals() as $suffix => $names ) {
		$len = strlen( $suffix );
		if ( strlen( $path ) < $len || substr( $path, -$len ) !== $suffix ) {
			continue;
		}
		if ( in_array( '*', (array) $names, true ) ) {
			return true;
		}
		if ( in_array( $name, (array) $names, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Unhook the MetaSync front-end head emitters.
 *
 * Runs on `template_redirect`, which fires only for real front-end page
 * requests — never for wp-admin, admin-ajax, WP-CLI, or REST — so Search
 * Atlas's dashboard, auditing, and API surfaces are untouched by construction.
 * XML sitemaps and robots.txt do not call wp_head, so they are unaffected too.
 *
 * Removal goes through remove_action() with the ACTUAL callable pulled from the
 * registry, so WordPress recomputes the same internal unique ID — this is what
 * lets an anonymous Closure be removed without touching $wp_filter by hand.
 */
function showtime_metasync_strip_head(): void {
	if ( is_admin() ) {
		return;
	}
	if ( ! apply_filters( 'showtime/metasync/strip_head', true ) ) {
		return;
	}

	global $wp_filter;
	if ( empty( $wp_filter['wp_head'] ) || ! isset( $wp_filter['wp_head']->callbacks ) ) {
		return;
	}

	// Collect first, then remove — never mutate while iterating the registry.
	$targets = array();
	foreach ( (array) $wp_filter['wp_head']->callbacks as $priority => $callbacks ) {
		foreach ( (array) $callbacks as $entry ) {
			if ( ! isset( $entry['function'] ) ) {
				continue;
			}
			$resolved = showtime_metasync_resolve_callback( $entry['function'] );
			if ( showtime_metasync_is_head_target( $resolved['file'], $resolved['name'] ) ) {
				$targets[] = array(
					'callback' => $entry['function'],
					'priority' => (int) $priority,
				);
			}
		}
	}

	foreach ( $targets as $t ) {
		remove_action( 'wp_head', $t['callback'], $t['priority'] );
	}
}
add_action( 'template_redirect', 'showtime_metasync_strip_head', 1 );

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

	// Which MetaSync head emitters are still registered right now?
	$still_hooked = array();
	global $wp_filter;
	if ( ! empty( $wp_filter['wp_head'] ) && isset( $wp_filter['wp_head']->callbacks ) ) {
		foreach ( (array) $wp_filter['wp_head']->callbacks as $priority => $callbacks ) {
			foreach ( (array) $callbacks as $entry ) {
				if ( ! isset( $entry['function'] ) ) {
					continue;
				}
				$resolved = showtime_metasync_resolve_callback( $entry['function'] );
				if ( showtime_metasync_is_head_target( $resolved['file'], $resolved['name'] ) ) {
					$still_hooked[] = $resolved['name'] . ' @' . (int) $priority;
				}
			}
		}
	}

	return array(
		'metasync_plugin_active' => $plugin_active,
		'tracker_handles'        => showtime_metasync_tracker_handles(),
		'head_targets_still_hooked' => $still_hooked,
		'note'                   => 'Head emitters are unhooked on template_redirect (front end only); admin/REST/sitemap/robots untouched.',
	);
}

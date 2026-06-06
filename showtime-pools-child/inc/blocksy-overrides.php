<?php
/**
 * Blocksy parent theme overrides.
 *
 * Hook in here to retarget Blocksy filters/actions without forking the parent.
 * Keep overrides surgical: every override is a coupling point that can break
 * on a parent update. Document the why for each.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lock Blocksy's container max-width to our design system value (1240px).
 * Ensures every Blocksy block respects the same canvas as our child blocks.
 */
add_filter(
	'blocksy:dynamic-styles:custom-width',
	function ( $width ) {
		return 1240;
	}
);

/**
 * Comments hardening. We're a service business with no need for blog comments
 * (a known spam vector). Default-deny: comments are CLOSED on every post type,
 * and only re-open for a post if something explicitly opts it back in via the
 * `showtime/comments/enabled` filter (no DB edits, no plugin). Pingbacks and
 * trackbacks are closed the same way to kill linkback spam at the post level.
 *
 * To re-enable comments on posts later:
 *   add_filter( 'showtime/comments/enabled', '__return_true' );
 */
add_filter(
	'comments_open',
	function ( $open, $post_id ) {
		if ( get_post_type( $post_id ) !== 'post' ) {
			return false;
		}
		return (bool) apply_filters( 'showtime/comments/enabled', false, $post_id );
	},
	10,
	2
);

add_filter(
	'pings_open',
	function ( $open, $post_id ) {
		return (bool) apply_filters( 'showtime/comments/enabled', false, $post_id );
	},
	10,
	2
);

/**
 * TODO when Blocksy Companion Pro is active in target env:
 *   - Register custom header layouts via blocksy_register_header_section
 *   - Register custom footer rows via blocksy_register_footer_section
 *   - Add custom mega-menu modules
 * These hooks fire only when Companion Pro is loaded, so this stub is a no-op
 * until then. Phase 1D fills it in.
 */

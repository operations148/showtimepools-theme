<?php
/**
 * Code-first edit mode — global ACF neutralizer.
 *
 * When SHOWTIME_CODE_FIRST is on, null out every OPTION-scope ACF value so the
 * ~13 templates that read `get_field( '...', 'option' )` inline fall through to
 * their hardcoded PHP `__()` defaults — without editing each template body.
 * Flip the constant off (functions.php) to restore WordPress/ACF content.
 *
 * Scope is deliberately limited to option-scope fields:
 *  - Global site copy (hero, about, contact, footer, homepage sections) → code.
 *  - Per-post CPT fields (individual project / service detail pages) are read at
 *    post-ID scope and are left untouched — they have no single code source.
 *
 * Config is read via get_option() (booking/form URLs, consent, popup, reviews,
 * UTM), not ACF, so it is never affected here. Image resolution is gated
 * separately in inc/imagery.php; native Site-Content text in inc/acf-readers.php.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST ) {
	add_filter(
		'acf/load_value',
		function ( $value, $post_id, $field ) {
			// Only neutralize option-scope content so templates use their PHP
			// defaults. ACF options resolve to a 'option'/'options' post_id (or
			// an 'option' prefix for sub-fields), never a numeric post ID.
			if ( 'option' === $post_id || 'options' === $post_id
				|| ( is_string( $post_id ) && str_starts_with( $post_id, 'option' ) ) ) {
				return null;
			}
			return $value;
		},
		99,
		3
	);
}

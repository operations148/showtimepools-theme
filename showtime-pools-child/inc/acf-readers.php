<?php
/**
 * ACF reader helpers — shared progressive-enhancement pattern.
 *
 * Every template that reads from the Site Content options page calls
 * `showtime_acf_rows( 'field_name', $php_default )`. If ACF Pro is loaded
 * AND the field has rows, the user-edited rows win; otherwise the PHP
 * default array passed in is used. Net effect: site keeps working with
 * ACF inactive, gets editable when ACF is on.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a repeater field from the global "options" scope (ACF options
 * page), falling back to a PHP-array default when empty or unavailable.
 *
 * @param string             $field_name ACF field key (the `name`, not the `key`).
 * @param array              $default    PHP fallback rows.
 * @param string             $scope      ACF scope. Default 'option'.
 * @return array
 */
function showtime_acf_rows( string $field_name, array $default, string $scope = 'option' ): array {
	// Code-first edit mode: render the PHP default, ignore the CMS value.
	if ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST ) {
		return $default;
	}
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$rows = get_field( $field_name, $scope );
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return $default;
	}
	return $rows;
}

/**
 * Read a single ACF field (string/text/etc.) from the options scope,
 * falling back to a default value when empty.
 */
function showtime_acf_text( string $field_name, string $default = '', string $scope = 'option' ): string {
	// Code-first edit mode: render the PHP default, ignore the CMS value.
	if ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST ) {
		return $default;
	}
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = (string) get_field( $field_name, $scope );
	return '' !== $value ? $value : $default;
}

/**
 * Read a content string with the full priority chain:
 *   Priority 0: native wp_options via Showtime Pools → Site Content admin
 *   Priority 1: ACF Pro option field (when ACF Pro active)
 *   Priority 2: PHP hardcoded $default
 *
 * Usage in any template:
 *   $h1 = showtime_ct( 'hub_services_h1', 'Pool services in Los Angeles.', 'hero_title' );
 *
 * @param string $ct_key    Key for the native ContentPage option (showtime_ct_ prefix).
 * @param string $default   PHP fallback string.
 * @param string $acf_key   Optional ACF field name to try as Priority 1.
 */
function showtime_ct( string $ct_key, string $default = '', string $acf_key = '' ): string {
	// Code-first edit mode: render the PHP default, ignore the native + ACF
	// CMS values (the global acf/load_value filter does not cover the native
	// showtime_ct_* wp_options, so this short-circuit is required here).
	if ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST ) {
		return $default;
	}

	// Priority 0: native wp_options.
	if ( class_exists( '\\Showtime\\Admin\\ContentPage' ) ) {
		$native = \Showtime\Admin\ContentPage::get( $ct_key );
		if ( '' !== $native ) {
			return $native;
		}
	}

	// Priority 1: ACF option field.
	if ( '' !== $acf_key && function_exists( 'get_field' ) ) {
		$acf_val = (string) get_field( $acf_key, 'option' );
		if ( '' !== $acf_val ) {
			return $acf_val;
		}
	}

	// Priority 2: PHP fallback.
	return $default;
}

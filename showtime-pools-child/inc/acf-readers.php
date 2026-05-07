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
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = (string) get_field( $field_name, $scope );
	return '' !== $value ? $value : $default;
}

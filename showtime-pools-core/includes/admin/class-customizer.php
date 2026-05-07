<?php
/**
 * Customizer — Brand Identity panel.
 *
 * Exposes Steve-editable fields (phone, email, hours, tagline, socials)
 * via Appearance → Customize. Values are read by templates through the
 * existing `showtime/business/*` filters; this class just hooks the
 * Customizer values into those filters when present, otherwise the PHP
 * defaults already in templates win. Net result: zero breaking change.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class Customizer {

	/**
	 * Wire the panel + register all settings/controls.
	 */
	public function register(): void {
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		// Bridge Customizer → existing filters. Templates don't change.
		add_filter( 'showtime/business/phone',        array( $this, 'filter_phone' ) );
		add_filter( 'showtime/business/email',        array( $this, 'filter_email' ) );
		add_filter( 'showtime/business/hours_short',  array( $this, 'filter_hours_short' ) );
		add_filter( 'showtime/business/socials',      array( $this, 'filter_socials' ) );
	}

	/**
	 * Register the Customizer panel + sections + settings + controls.
	 */
	public function customize_register( \WP_Customize_Manager $wp_customize ): void {

		$wp_customize->add_panel(
			'showtime_brand',
			array(
				'title'       => __( 'Showtime Pools — Brand', 'showtime-pools' ),
				'description' => __( 'Phone, email, hours, tagline, social URLs. Editable here so the dev team never has to change PHP for a contact-info update.', 'showtime-pools' ),
				'priority'    => 20,
			)
		);

		// ─── Section: Contact ───────────────────────────────────────────
		$wp_customize->add_section(
			'showtime_brand_contact',
			array(
				'title' => __( 'Contact', 'showtime-pools' ),
				'panel' => 'showtime_brand',
			)
		);

		$wp_customize->add_setting(
			'showtime_phone',
			array(
				'default'           => '(323) 825-2099',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'showtime_phone',
			array(
				'label'       => __( 'Phone', 'showtime-pools' ),
				'description' => __( 'Public-facing phone, formatted: (323) 825-2099.', 'showtime-pools' ),
				'section'     => 'showtime_brand_contact',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'showtime_email',
			array(
				'default'           => 'operations@showtimepoolmechanics.com',
				'sanitize_callback' => 'sanitize_email',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'showtime_email',
			array(
				'label'       => __( 'Email', 'showtime-pools' ),
				'description' => __( 'Verified inbox. Showed in the footer, contact page, and quote-fallback card.', 'showtime-pools' ),
				'section'     => 'showtime_brand_contact',
				'type'        => 'email',
			)
		);

		$wp_customize->add_setting(
			'showtime_hours_short',
			array(
				'default'           => 'Mon-Sat 8am-5pm',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'showtime_hours_short',
			array(
				'label'       => __( 'Hours (short)', 'showtime-pools' ),
				'description' => __( 'Compact label for the utility bar / fallback cards. Long hours table stays in the footer.', 'showtime-pools' ),
				'section'     => 'showtime_brand_contact',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'showtime_tagline',
			array(
				'default'           => 'Stop juggling contractors. One team handles repairs, weekly service, remodels, and new equipment across Los Angeles.',
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'showtime_tagline',
			array(
				'label'       => __( 'Footer tagline', 'showtime-pools' ),
				'description' => __( 'Short business description shown under the wordmark in the footer.', 'showtime-pools' ),
				'section'     => 'showtime_brand_contact',
				'type'        => 'textarea',
			)
		);

		// ─── Section: Socials ───────────────────────────────────────────
		$wp_customize->add_section(
			'showtime_brand_socials',
			array(
				'title'       => __( 'Social URLs', 'showtime-pools' ),
				'panel'       => 'showtime_brand',
				'description' => __( 'Leave blank to hide a network from the footer.', 'showtime-pools' ),
			)
		);

		foreach ( $this->social_defaults() as $key => $cfg ) {
			$wp_customize->add_setting(
				"showtime_social_{$key}",
				array(
					'default'           => $cfg['url'],
					'sanitize_callback' => 'esc_url_raw',
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				"showtime_social_{$key}",
				array(
					'label'   => $cfg['label'],
					'section' => 'showtime_brand_socials',
					'type'    => 'url',
				)
			);
		}
	}

	/**
	 * Default social labels + URLs. Mirrors the array previously hardcoded
	 * in footer-main.php so existing values flow through unchanged.
	 *
	 * @return array<string,array{label:string,url:string}>
	 */
	private function social_defaults(): array {
		return array(
			'facebook'  => array( 'label' => 'Facebook',  'url' => 'https://facebook.com/share/18DZy64EfX/' ),
			'instagram' => array( 'label' => 'Instagram', 'url' => 'https://instagram.com/showtime_pools' ),
			'google'    => array( 'label' => 'Google',    'url' => 'https://share.google/ltdNPoJBWevHTvrzq' ),
			'linkedin'  => array( 'label' => 'LinkedIn',  'url' => 'https://linkedin.com/in/showtimepoolssocal/' ),
			'tiktok'    => array( 'label' => 'TikTok',    'url' => 'https://tiktok.com/@showtimepools' ),
			'youtube'   => array( 'label' => 'YouTube',   'url' => 'https://youtube.com/channel/UC3Dw1LtPvuX1JSGT7_KLntw' ),
		);
	}

	// ───────────────────────────────────────────────────────────────────
	// Filter bridges — Customizer values flow into the filters templates
	// already use. Each falls back to the upstream default if the
	// Customizer setting is empty.
	// ───────────────────────────────────────────────────────────────────

	public function filter_phone( $value ): string {
		$set = (string) get_theme_mod( 'showtime_phone', '' );
		return '' !== $set ? $set : (string) $value;
	}

	public function filter_email( $value ): string {
		$set = (string) get_theme_mod( 'showtime_email', '' );
		return '' !== $set ? $set : (string) $value;
	}

	public function filter_hours_short( $value ): string {
		$set = (string) get_theme_mod( 'showtime_hours_short', '' );
		return '' !== $set ? $set : (string) $value;
	}

	/**
	 * Replace the hardcoded socials array with Customizer URLs.
	 *
	 * IMPORTANT: two templates call this filter with two different data
	 * shapes:
	 *   - footer-main.php passes a list-of-dicts: [ ['label'=>, 'url'=>], ... ]
	 *   - footer-legal.php passes a dict-with-keys: [ 'instagram' => 'url', ... ]
	 * We detect the input shape and return the same shape, so neither
	 * template breaks when the filter intercepts.
	 *
	 * @param array $value Default array passed by the filter caller.
	 * @return array
	 */
	public function filter_socials( $value ): array {
		if ( ! is_array( $value ) || empty( $value ) ) {
			return (array) $value;
		}

		$first_key   = array_key_first( $value );
		$first_value = $value[ $first_key ];
		$is_list_of_dicts = is_int( $first_key ) && is_array( $first_value );

		$defaults = $this->social_defaults();

		if ( $is_list_of_dicts ) {
			// Return list-of-dicts shape (footer-main.php).
			$out = array();
			foreach ( $defaults as $key => $cfg ) {
				$url = (string) get_theme_mod( "showtime_social_{$key}", $cfg['url'] );
				if ( '' === $url ) {
					continue;
				}
				$out[] = array(
					'label' => $cfg['label'],
					'url'   => $url,
				);
			}
			return $out ?: $value;
		}

		// Return dict-with-keys shape (footer-legal.php).
		$out = array();
		foreach ( $defaults as $key => $cfg ) {
			$url = (string) get_theme_mod( "showtime_social_{$key}", $cfg['url'] );
			if ( '' === $url ) {
				continue;
			}
			$out[ $key ] = $url;
		}
		return $out ?: $value;
	}
}

<?php
/**
 * Settings page: Showtime → Settings under WP admin menu.
 *
 * Houses every API key + webhook URL + token used by the plugin so they live
 * in wp_options (DB), not in code. Server-side only — never echoed to JS.
 *
 * Phase 1B ships the page shell + GHL webhook fields. OpenAI, Mapbox, GBP
 * fields are added in their respective phases (1I, 2A, 2B) by extending the
 * settings_fields() method below.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class SettingsPage {

	private const OPTION_GROUP = 'showtime_settings';
	private const PAGE_SLUG    = 'showtime-settings';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Showtime Pools', 'showtime-pools-core' ),
			__( 'Showtime Pools', 'showtime-pools-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' ),
			'dashicons-admin-customizer',
			58
		);
	}

	public function register_settings(): void {
		// GHL — for FluentForms + AI chat lead forwarding (Phase 1H, 1I).
		register_setting(
			self::OPTION_GROUP,
			'showtime_ghl_webhook_url',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);
		register_setting(
			self::OPTION_GROUP,
			'showtime_ghl_webhook_secret',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// GHL — iframe URLs for /quote/ and /book/ landing pages.
		register_setting( self::OPTION_GROUP, 'showtime_ghl_quote_url', array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_book_url',  array( 'sanitize_callback' => 'esc_url_raw' ) );

		// OpenAI — Phase 1I.
		register_setting( self::OPTION_GROUP, 'showtime_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( self::OPTION_GROUP, 'showtime_openai_assistant_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Mapbox — Phase 2A.
		register_setting( self::OPTION_GROUP, 'showtime_mapbox_token', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Google Business Profile — Phase 2B.
		register_setting( self::OPTION_GROUP, 'showtime_gbp_account_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( self::OPTION_GROUP, 'showtime_gbp_location_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		add_settings_section(
			'showtime_section_ghl',
			__( 'GHL (GoHighLevel)', 'showtime-pools-core' ),
			function () {
				echo '<p>' . esc_html__( 'Inbound webhook URL to push WP-captured leads (newsletter, contact, AI chat) into GHL.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'showtime_ghl_webhook_url',
			__( 'GHL Webhook URL', 'showtime-pools-core' ),
			function () {
				$value = (string) get_option( 'showtime_ghl_webhook_url', '' );
				printf(
					'<input type="url" name="showtime_ghl_webhook_url" value="%s" class="regular-text" placeholder="https://services.leadconnectorhq.com/hooks/...">',
					esc_attr( $value )
				);
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_webhook_secret',
			__( 'GHL Shared Secret', 'showtime-pools-core' ),
			function () {
				$value = (string) get_option( 'showtime_ghl_webhook_secret', '' );
				printf(
					'<input type="password" name="showtime_ghl_webhook_secret" value="%s" class="regular-text" autocomplete="off">',
					esc_attr( $value )
				);
				echo '<p class="description">' . esc_html__( 'Optional — used to HMAC-sign outgoing payloads if GHL is configured to verify.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_quote_url',
			__( 'GHL Quote Form URL', 'showtime-pools-core' ),
			function () {
				$value = (string) get_option( 'showtime_ghl_quote_url', '' );
				printf(
					'<input type="url" name="showtime_ghl_quote_url" value="%s" class="regular-text" placeholder="https://app.gohighlevel.com/widget/...">',
					esc_attr( $value )
				);
				echo '<p class="description">' . esc_html__( 'Embedded into /quote/ landing page. Leave blank to show fallback messaging.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_book_url',
			__( 'GHL Inspection Booking URL', 'showtime-pools-core' ),
			function () {
				$value = (string) get_option( 'showtime_ghl_book_url', '' );
				printf(
					'<input type="url" name="showtime_ghl_book_url" value="%s" class="regular-text" placeholder="https://app.gohighlevel.com/widget/booking/...">',
					esc_attr( $value )
				);
				echo '<p class="description">' . esc_html__( 'Embedded into /book/ landing page. Leave blank to show fallback messaging.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		// Phase 1I, 2A, 2B sections get added by their respective subsystem registrations.
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

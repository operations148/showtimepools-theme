<?php
/**
 * Settings page: Showtime → Settings + Site Images under WP admin menu.
 *
 * The "Settings" tab houses every API key + webhook URL used by the plugin so
 * they live in wp_options (DB), not in code.
 *
 * The "Site Images" sub-page provides a native WP Media Library picker for
 * every image slot used across the site — no ACF Pro required. Images saved
 * here take Priority 0 in showtime_image(), overriding bundled/CDN fallbacks.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class SettingsPage {

	private const OPTION_GROUP = 'showtime_settings';
	private const PAGE_SLUG    = 'showtime-settings';
	private const IMAGES_SLUG  = 'showtime-images';

	/** Option prefix for native image slots. */
	public const IMG_OPTION_PREFIX = 'showtime_img_';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_images_assets' ) );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// MENUS
	// ─────────────────────────────────────────────────────────────────────────

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

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'Site Images', 'showtime-pools-core' ),
			__( 'Site Images', 'showtime-pools-core' ),
			'manage_options',
			self::IMAGES_SLUG,
			array( $this, 'render_images' )
		);
	}

	// ─────────────────────────────────────────────────────────────────────────
	// API / SETTINGS TAB
	// ─────────────────────────────────────────────────────────────────────────

	public function register_settings(): void {
		// GHL — lead forwarding.
		register_setting( self::OPTION_GROUP, 'showtime_ghl_webhook_url',           array( 'sanitize_callback' => 'esc_url_raw',          'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_webhook_url_affiliate', array( 'sanitize_callback' => 'esc_url_raw',          'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_webhook_secret', array( 'sanitize_callback' => 'sanitize_text_field',  'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_quote_url',      array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_book_url',       array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( self::OPTION_GROUP, 'showtime_ghl_contact_url',    array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( self::OPTION_GROUP, 'showtime_popup_form_url',     array( 'sanitize_callback' => 'esc_url_raw' ) );

		// Cloudflare Turnstile (CAPTCHA for public forms).
		register_setting( self::OPTION_GROUP, 'showtime_turnstile_site_key', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting(
			self::OPTION_GROUP,
			'showtime_turnstile_secret',
			array(
				// Write-only: a blank submission keeps the stored secret, so the
				// value is never echoed back into the admin DOM (L1).
				'sanitize_callback' => static function ( $value ) {
					$value = trim( (string) $value );
					return '' === $value
						? (string) get_option( 'showtime_turnstile_secret', '' )
						: sanitize_text_field( $value );
				},
				'default'           => '',
			)
		);

		// OpenAI.
		register_setting( self::OPTION_GROUP, 'showtime_openai_api_key',       array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( self::OPTION_GROUP, 'showtime_openai_assistant_id',  array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Mapbox.
		register_setting( self::OPTION_GROUP, 'showtime_mapbox_token', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Google Business Profile.
		register_setting( self::OPTION_GROUP, 'showtime_gbp_account_id',  array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( self::OPTION_GROUP, 'showtime_gbp_location_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		add_settings_section(
			'showtime_section_ghl',
			__( 'GHL (GoHighLevel)', 'showtime-pools-core' ),
			function () {
				echo '<p>' . esc_html__( 'Inbound webhook URL to push WP-captured leads into GHL.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'showtime_ghl_webhook_url',
			__( 'GHL Webhook URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_webhook_url', '' );
				printf( '<input type="url" name="showtime_ghl_webhook_url" value="%s" class="regular-text" placeholder="https://services.leadconnectorhq.com/hooks/...">', esc_attr( $v ) );
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_webhook_url_affiliate',
			__( 'GHL Affiliate Webhook URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_webhook_url_affiliate', '' );
				printf( '<input type="url" name="showtime_ghl_webhook_url_affiliate" value="%s" class="regular-text" placeholder="https://services.leadconnectorhq.com/hooks/...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Dedicated webhook for Partner Program signups (/affiliate). Leave blank to reuse the main webhook above.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_webhook_secret',
			__( 'GHL Shared Secret', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_webhook_secret', '' );
				printf( '<input type="password" name="showtime_ghl_webhook_secret" value="%s" class="regular-text" autocomplete="off">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Optional HMAC signing secret.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_quote_url',
			__( 'GHL Quote Form URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_quote_url', '' );
				printf( '<input type="url" name="showtime_ghl_quote_url" value="%s" class="regular-text" placeholder="https://app.gohighlevel.com/widget/...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Embedded into /quote/ page.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_book_url',
			__( 'GHL Inspection Booking URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_book_url', '' );
				printf( '<input type="url" name="showtime_ghl_book_url" value="%s" class="regular-text" placeholder="https://app.gohighlevel.com/widget/booking/...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Embedded into /book/ page.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_ghl_contact_url',
			__( 'GHL Contact Form URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_ghl_contact_url', '' );
				printf( '<input type="url" name="showtime_ghl_contact_url" value="%s" class="regular-text" placeholder="https://app.showtimepoolmechanics.com/widget/form/...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Embedded into the /contact/ page form area. Leave blank to use the bundled default form. UTM (website / organic / contact_form) is appended automatically.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		add_settings_field(
			'showtime_popup_form_url',
			__( 'GHL Popup Form URL', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_popup_form_url', '' );
				printf( '<input type="url" name="showtime_popup_form_url" value="%s" class="regular-text" placeholder="https://app.showtimepoolmechanics.com/widget/form/...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Embedded into the sitewide "Weekly Maintenance" popup. Leave blank to use the bundled default form. UTM (website / popup / weekly_maintenance) is appended automatically.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_ghl'
		);

		// ── Cloudflare Turnstile ──────────────────────────────────────────────
		add_settings_section(
			'showtime_section_turnstile',
			__( 'Cloudflare Turnstile (form CAPTCHA)', 'showtime-pools-core' ),
			function () {
				echo '<p>' . esc_html__( 'Spam protection for the public Contact and Affiliate forms. Get keys at Cloudflare dashboard → Turnstile → Add widget. Leave both blank to disable (forms keep working).', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'showtime_turnstile_site_key',
			__( 'Turnstile Site Key', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_turnstile_site_key', '' );
				printf( '<input type="text" name="showtime_turnstile_site_key" value="%s" class="regular-text" placeholder="0x4AAAAAAA...">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Public key — rendered into the form widget.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_turnstile'
		);

		add_settings_field(
			'showtime_turnstile_secret',
			__( 'Turnstile Secret Key', 'showtime-pools-core' ),
			function () {
				// Write-only: never echo the stored secret. Show only whether one is saved.
				$saved = '' !== (string) get_option( 'showtime_turnstile_secret', '' );
				echo '<input type="password" name="showtime_turnstile_secret" value="" class="regular-text" autocomplete="off" placeholder="' . esc_attr( $saved ? __( 'Saved — leave blank to keep', 'showtime-pools-core' ) : __( 'Paste secret key', 'showtime-pools-core' ) ) . '">';
				echo '<p class="description">' . esc_html__( 'Private key. Stored write-only; leave blank to keep the current value.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_turnstile'
		);

		$this->register_consent_settings();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// TRACKING & CONSENT TAB SECTION
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * GTM container + cookie-consent banner config. Stored in wp_options so the
	 * theme (inc/consent.php) renders Consent Mode + the banner with zero code
	 * changes. Pixels themselves NEVER live here — they live in GTM.
	 */
	private function register_consent_settings(): void {
		// Checkbox sanitizer: coerce to '1'/'0'. A hidden '0' field paired with
		// each checkbox guarantees the key always posts, so unchecking sticks.
		$checkbox = static fn( $v ): string => '1' === (string) $v ? '1' : '0';

		// Banner message allows a single inline link to the privacy policy.
		$message_kses = static fn( $v ): string => wp_kses(
			(string) $v,
			array(
				'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
				'strong' => array(),
				'em'     => array(),
				'br'     => array(),
			)
		);

		register_setting( self::OPTION_GROUP, 'showtime_gtm_id',              array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_gtm_inject',          array( 'sanitize_callback' => $checkbox,             'default' => '0' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_enabled',     array( 'sanitize_callback' => $checkbox,             'default' => '1' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_heading',     array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_message',     array( 'sanitize_callback' => $message_kses,         'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_accept_label', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_reject_label', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_prefs_label', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
		register_setting( self::OPTION_GROUP, 'showtime_consent_policy_url',  array( 'sanitize_callback' => 'esc_url_raw',          'default' => '' ) );

		add_settings_section(
			'showtime_section_consent',
			__( 'Tracking & Consent', 'showtime-pools-core' ),
			function () {
				echo '<p>' . esc_html__( 'Google Tag Manager + cookie-consent banner. Tracking pixels (Meta, TikTok, Google Ads) live inside GTM — never paste a Pixel ID here. The banner gates analytics/marketing tags via Google Consent Mode v2 (denied until the visitor accepts).', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		// ── GTM container ─────────────────────────────────────────────────────
		add_settings_field(
			'showtime_gtm_id',
			__( 'GTM Container ID', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_gtm_id', '' );
				printf( '<input type="text" name="showtime_gtm_id" value="%s" class="regular-text" placeholder="GTM-XXXXXXX">', esc_attr( $v ) );
				echo '<p class="description">' . esc_html__( 'Your Google Tag Manager container ID. Used for the Consent Mode default and, optionally, to inject the container (below).', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		add_settings_field(
			'showtime_gtm_inject',
			__( 'Inject GTM from theme', 'showtime-pools-core' ),
			function () {
				$on = '1' === (string) get_option( 'showtime_gtm_inject', '0' );
				echo '<input type="hidden" name="showtime_gtm_inject" value="0">';
				printf( '<label><input type="checkbox" name="showtime_gtm_inject" value="1" %s> %s</label>', checked( $on, true, false ), esc_html__( 'Print the GTM container from the theme', 'showtime-pools-core' ) );
				echo '<p class="description">' . esc_html__( 'Leave OFF if GTM is already added by a plugin or header code (avoids a double container). Turn ON only if you want the theme to own GTM so script order is guaranteed. Either way, Consent Mode still applies.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		// ── Consent banner ────────────────────────────────────────────────────
		add_settings_field(
			'showtime_consent_enabled',
			__( 'Show consent banner', 'showtime-pools-core' ),
			function () {
				$on = '1' === (string) get_option( 'showtime_consent_enabled', '1' );
				echo '<input type="hidden" name="showtime_consent_enabled" value="0">';
				printf( '<label><input type="checkbox" name="showtime_consent_enabled" value="1" %s> %s</label>', checked( $on, true, false ), esc_html__( 'Display the cookie-consent banner to visitors', 'showtime-pools-core' ) );
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		add_settings_field(
			'showtime_consent_heading',
			__( 'Banner heading', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_consent_heading', '' );
				printf( '<input type="text" name="showtime_consent_heading" value="%s" class="regular-text" placeholder="%s">', esc_attr( $v ), esc_attr__( 'We value your privacy', 'showtime-pools-core' ) );
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		add_settings_field(
			'showtime_consent_message',
			__( 'Banner message', 'showtime-pools-core' ),
			function () {
				$v = (string) get_option( 'showtime_consent_message', '' );
				printf( '<textarea name="showtime_consent_message" class="large-text" rows="3" placeholder="%s">%s</textarea>', esc_attr__( 'We use cookies to improve your experience and for marketing. Choose which to allow.', 'showtime-pools-core' ), esc_textarea( $v ) );
				echo '<p class="description">' . esc_html__( 'A single link is allowed, e.g. <a href="/legal/">Privacy Policy</a>.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		add_settings_field(
			'showtime_consent_labels',
			__( 'Button labels', 'showtime-pools-core' ),
			function () {
				$accept = (string) get_option( 'showtime_consent_accept_label', '' );
				$reject = (string) get_option( 'showtime_consent_reject_label', '' );
				$prefs  = (string) get_option( 'showtime_consent_prefs_label', '' );
				printf( '<p><label>%s<br><input type="text" name="showtime_consent_accept_label" value="%s" class="regular-text" placeholder="%s"></label></p>', esc_html__( 'Accept button', 'showtime-pools-core' ), esc_attr( $accept ), esc_attr__( 'Accept all', 'showtime-pools-core' ) );
				printf( '<p><label>%s<br><input type="text" name="showtime_consent_reject_label" value="%s" class="regular-text" placeholder="%s"></label></p>', esc_html__( 'Reject button', 'showtime-pools-core' ), esc_attr( $reject ), esc_attr__( 'Reject non-essential', 'showtime-pools-core' ) );
				printf( '<p><label>%s<br><input type="text" name="showtime_consent_prefs_label" value="%s" class="regular-text" placeholder="%s"></label></p>', esc_html__( 'Preferences button', 'showtime-pools-core' ), esc_attr( $prefs ), esc_attr__( 'Preferences', 'showtime-pools-core' ) );
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);

		add_settings_field(
			'showtime_consent_policy_url',
			__( 'Privacy policy link', 'showtime-pools-core' ),
			function () {
				$v           = (string) get_option( 'showtime_consent_policy_url', '' );
				$fallback    = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
				$placeholder = $fallback ? $fallback : home_url( '/legal/' );
				printf( '<input type="url" name="showtime_consent_policy_url" value="%s" class="regular-text" placeholder="%s">', esc_attr( $v ), esc_attr( $placeholder ) );
				echo '<p class="description">' . esc_html__( 'Where the banner link points. Leave blank to use the WordPress Privacy Policy page (Settings → Privacy), falling back to /legal/.', 'showtime-pools-core' ) . '</p>';
			},
			self::PAGE_SLUG,
			'showtime_section_consent'
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'API keys and integration settings. Go to Site Images to upload photos.', 'showtime-pools-core' ); ?></p>
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

	// ─────────────────────────────────────────────────────────────────────────
	// SITE IMAGES TAB
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * All image slots, grouped for the admin UI.
	 * Slot names match showtime_image() exactly.
	 *
	 * @return array<string, list<array{slot:string, label:string}>>
	 */
	private function get_image_slots(): array {
		return array(
			'Branding' => array(
				array( 'slot' => 'footer_logo',    'label' => 'Footer logo (defaults to the bundled Showtime Pools logo)' ),
			),
			'Heroes & Backgrounds' => array(
				array( 'slot' => 'hero',           'label' => 'Homepage hero photo' ),
				array( 'slot' => 'hero_poster',    'label' => 'Homepage hero video poster (still shown on mobile + while the video loads)' ),
				array( 'slot' => 'about_split',    'label' => 'Homepage About section photo (separate from the About page background)' ),
				array( 'slot' => 'about_hero',     'label' => 'About page background' ),
				array( 'slot' => 'founder',        'label' => 'Founder portrait (Steve)' ),
				array( 'slot' => 'inspections_bg', 'label' => 'Inspections callout background' ),
				array( 'slot' => 'process_bg',     'label' => 'Process section background' ),
			),
			'Lifestyle (About section)' => array(
				array( 'slot' => 'lifestyle_main', 'label' => 'Lifestyle main (left stacked photo)' ),
				array( 'slot' => 'lifestyle_1',    'label' => 'Lifestyle 1' ),
				array( 'slot' => 'lifestyle_2',    'label' => 'Lifestyle 2' ),
				array( 'slot' => 'lifestyle_3',    'label' => 'Lifestyle 3' ),
				array( 'slot' => 'lifestyle_4',    'label' => 'Lifestyle 4' ),
			),
			'Service Heroes' => array(
				array( 'slot' => 'service_pool-repairs-plumbing',           'label' => 'Pool Repairs & Plumbing' ),
				array( 'slot' => 'service_pool-leak-detection',            'label' => 'Pool Leak Detection' ),
				array( 'slot' => 'service_weekly-pool-maintenance',         'label' => 'Weekly Pool Maintenance' ),
				array( 'slot' => 'service_pool-tile-cleaning',              'label' => 'Pool Tile Cleaning' ),
				array( 'slot' => 'service_pool-remodeling-resurfacing',     'label' => 'Pool Remodeling & Resurfacing' ),
				array( 'slot' => 'service_equipment-installation-upgrades', 'label' => 'Equipment Installation & Upgrades' ),
				array( 'slot' => 'service_pool-inspections-diagnostics',    'label' => 'Pool Inspections & Diagnostics' ),
				array( 'slot' => 'service_smart-pool-automation',           'label' => 'Smart Pool Automation' ),
				array( 'slot' => 'service_custom-pool-design-construction', 'label' => 'Custom Pool Design & Construction' ),
				array( 'slot' => 'service_spa-installation-renovations',    'label' => 'Spa Installation & Renovations' ),
				array( 'slot' => 'service_tile-coping-plaster-decking',     'label' => 'Tile, Coping & Plaster' ),
				array( 'slot' => 'service_outdoor-living-hardscape',        'label' => 'Outdoor Living & Hardscape' ),
				array( 'slot' => 'service_outdoor-kitchens-bbq',            'label' => 'Outdoor Kitchens & BBQ' ),
				array( 'slot' => 'service_fire-water-features',             'label' => 'Fire & Water Features' ),
			),
			'Service Areas' => array(
				array( 'slot' => 'area_sherman-oaks',   'label' => 'Sherman Oaks' ),
				array( 'slot' => 'area_encino',         'label' => 'Encino' ),
				array( 'slot' => 'area_beverly-hills',  'label' => 'Beverly Hills' ),
				array( 'slot' => 'area_studio-city',    'label' => 'Studio City' ),
				array( 'slot' => 'area_tarzana',        'label' => 'Tarzana' ),
				array( 'slot' => 'area_woodland-hills', 'label' => 'Woodland Hills' ),
			),
			'Projects' => array(
				array( 'slot' => 'project_1', 'label' => 'Project 1 (Sherman Oaks remodel)' ),
				array( 'slot' => 'project_2', 'label' => 'Project 2 (Encino new build)' ),
				array( 'slot' => 'project_3', 'label' => 'Project 3 (Studio City automation)' ),
				array( 'slot' => 'project_4', 'label' => 'Project 4 (Beverly Hills spa)' ),
				array( 'slot' => 'project_5', 'label' => 'Project 5 (Tarzana resort)' ),
				array( 'slot' => 'project_6', 'label' => 'Project 6 (Woodland Hills)' ),
				array( 'slot' => 'project_7', 'label' => 'Project 7 (Outdoor living)' ),
				array( 'slot' => 'project_8', 'label' => 'Project 8 (Water features)' ),
				array( 'slot' => 'project_9', 'label' => 'Project 9' ),
			),
			'Blog Images' => array(
				array( 'slot' => 'blog_default',   'label' => 'Blog default (fallback)' ),
				array( 'slot' => 'blog_trends',    'label' => 'Blog: Pool Trends' ),
				array( 'slot' => 'blog_tips',      'label' => 'Blog: Maintenance Tips' ),
				array( 'slot' => 'blog_equipment', 'label' => 'Blog: Equipment Guides' ),
			),
		);
	}

	/**
	 * Convert a slot name to a wp_options key.
	 * "area_sherman-oaks" → "showtime_img_area_sherman_oaks"
	 */
	public static function slot_to_option( string $slot ): string {
		return self::IMG_OPTION_PREFIX . str_replace( '-', '_', $slot );
	}

	/**
	 * Get the current attachment ID or URL for a slot (0 if unset).
	 */
	private function get_slot_value( string $slot ): string {
		return (string) get_option( self::slot_to_option( $slot ), '' );
	}

	/**
	 * Enqueue WP Media + picker JS only on the images admin page.
	 */
	public function enqueue_images_assets( string $hook ): void {
		// Hook format for sub-pages: {parent_slug}_page_{child_slug}
		if ( 'showtime-pools_page_' . self::IMAGES_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_media();

		// Inline JS: WP media picker wired to each slot row.
		$js = <<<'JS'
(function($){
    'use strict';
    $(document).on('click', '.stp-img-upload', function(e){
        e.preventDefault();
        var row    = $(this).closest('.stp-img-row');
        var input  = row.find('.stp-img-id');
        var preview= row.find('.stp-img-preview');
        var remove = row.find('.stp-img-remove');
        var frame  = wp.media({
            title  : 'Select image',
            button : { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            input.val(att.id);
            var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
            preview.html('<img src="'+src+'" style="max-width:120px;max-height:80px;border-radius:6px;object-fit:cover;">');
            remove.show();
        });
        frame.open();
    });
    $(document).on('click', '.stp-img-remove', function(e){
        e.preventDefault();
        var row = $(this).closest('.stp-img-row');
        row.find('.stp-img-id').val('');
        row.find('.stp-img-preview').html('<span style="color:#aaa;font-size:12px">No image</span>');
        $(this).hide();
    });
}(jQuery));
JS;
		wp_add_inline_script( 'media-upload', $js );
	}

	/**
	 * Render the Site Images admin page. Handles its own save via POST.
	 */
	public function render_images(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// ── Save ──────────────────────────────────────────────────────────────
		$saved  = false;
		$errors = array();
		if ( isset( $_POST['showtime_images_nonce'] ) &&
			 wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_images_nonce'] ) ), 'showtime_save_images' ) ) {

			foreach ( $this->get_image_slots() as $slots ) {
				foreach ( $slots as $item ) {
					$opt_key = self::slot_to_option( $item['slot'] );
					$posted  = isset( $_POST[ $opt_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $opt_key ] ) ) : '';

					if ( '' === $posted ) {
						delete_option( $opt_key );
					} elseif ( is_numeric( $posted ) ) {
						$att_id = absint( $posted );
						if ( get_post_type( $att_id ) === 'attachment' ) {
							update_option( $opt_key, $att_id, false );
						} else {
							$errors[] = esc_html( $item['label'] ) . ': invalid attachment ID.';
						}
					} else {
						// URL string stored directly.
						update_option( $opt_key, esc_url_raw( $posted ), false );
					}
				}
			}
			$saved = empty( $errors );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Images', 'showtime-pools-core' ); ?></h1>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Images saved.', 'showtime-pools-core' ); ?></p></div>
			<?php endif; ?>
			<?php foreach ( $errors as $err ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $err ); ?></p></div>
			<?php endforeach; ?>

			<?php if ( function_exists( 'acf_add_options_page' ) ) : ?>
				<div class="notice notice-info"><p>
					<?php esc_html_e( 'ACF Pro is active. You can also upload images via ', 'showtime-pools-core' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=showtime-content-images' ) ); ?>"><?php esc_html_e( 'Site Content → Images', 'showtime-pools-core' ); ?></a>.
					<?php esc_html_e( 'Images saved here take priority over ACF values.', 'showtime-pools-core' ); ?>
				</p></div>
			<?php endif; ?>

			<p style="color:#666;margin-bottom:1.5rem;">
				<?php esc_html_e( 'Upload photos from your Media Library. Each image overrides the built-in fallback for that slot sitewide. Leave blank to keep the current fallback.', 'showtime-pools-core' ); ?>
			</p>

			<form method="post" action="">
				<?php wp_nonce_field( 'showtime_save_images', 'showtime_images_nonce' ); ?>

				<?php foreach ( $this->get_image_slots() as $group_label => $slots ) : ?>
					<h2 style="border-bottom:1px solid #ddd;padding-bottom:.4em;margin-top:2rem;">
						<?php echo esc_html( $group_label ); ?>
					</h2>
					<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:1.5rem;">

					<?php foreach ( $slots as $item ) :
						$opt_key    = self::slot_to_option( $item['slot'] );
						$saved_val  = $this->get_slot_value( $item['slot'] );
						$att_id     = is_numeric( $saved_val ) ? (int) $saved_val : 0;
						$thumb_url  = '';
						if ( $att_id > 0 ) {
							$thumb_url = (string) wp_get_attachment_thumb_url( $att_id );
						} elseif ( '' !== $saved_val && ! is_numeric( $saved_val ) ) {
							$thumb_url = $saved_val;
						}
						?>
						<div class="stp-img-row" style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:14px;display:flex;flex-direction:column;gap:8px;">
							<strong style="font-size:13px;"><?php echo esc_html( $item['label'] ); ?></strong>
							<code style="font-size:10px;color:#888;"><?php echo esc_html( $item['slot'] ); ?></code>

							<div class="stp-img-preview" style="min-height:60px;display:flex;align-items:center;">
								<?php if ( $thumb_url ) : ?>
									<img src="<?php echo esc_url( $thumb_url ); ?>" style="max-width:120px;max-height:80px;border-radius:6px;object-fit:cover;">
								<?php else : ?>
									<span style="color:#aaa;font-size:12px"><?php esc_html_e( 'No image', 'showtime-pools-core' ); ?></span>
								<?php endif; ?>
							</div>

							<input type="hidden"
								   class="stp-img-id"
								   name="<?php echo esc_attr( $opt_key ); ?>"
								   value="<?php echo esc_attr( $saved_val ); ?>">

							<div style="display:flex;gap:8px;align-items:center;">
								<button type="button" class="button button-secondary stp-img-upload">
									<?php echo $saved_val ? esc_html__( 'Change image', 'showtime-pools-core' ) : esc_html__( 'Upload image', 'showtime-pools-core' ); ?>
								</button>
								<a href="#" class="stp-img-remove" style="color:#b00;font-size:12px;<?php echo $saved_val ? '' : 'display:none;'; ?>">
									<?php esc_html_e( 'Remove', 'showtime-pools-core' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
					</div>
				<?php endforeach; ?>

				<?php submit_button( __( 'Save all images', 'showtime-pools-core' ) ); ?>
			</form>
		</div>
		<?php
	}
}

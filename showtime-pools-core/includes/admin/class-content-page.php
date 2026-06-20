<?php
/**
 * Native "Site Content" admin page — no ACF Pro required.
 *
 * Stores sitewide text content in wp_options with the prefix `showtime_ct_`.
 * page-about.php, page-founder.php and other templates read from these options
 * first (Priority 0), then fall back to ACF fields (Priority 1), then PHP
 * hardcoded strings (last resort).
 *
 * Tabs: About, Team, Certifications
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class ContentPage {

	private const PARENT_SLUG = 'showtime-settings';
	private const PAGE_SLUG   = 'showtime-content';

	/** Option prefix. */
	public const PREFIX = 'showtime_ct_';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Site Content', 'showtime-pools-core' ),
			__( 'Site Content', 'showtime-pools-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'showtime-pools_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_media();
		// Inline media picker JS (same pattern as class-settings-page.php images tab).
		$js = <<<'JS'
(function($){
    'use strict';
    $(document).on('click', '.stp-pick-photo', function(e){
        e.preventDefault();
        var btn     = $(this);
        var wrap    = btn.closest('.stp-photo-field');
        var input   = wrap.find('.stp-photo-id');
        var preview = wrap.find('.stp-photo-preview');
        var remove  = wrap.find('.stp-photo-remove');
        var frame = wp.media({ title:'Select photo', button:{text:'Use this photo'}, multiple:false, library:{type:'image'} });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            input.val(att.id);
            var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
            preview.html('<img src="'+src+'" style="max-width:100px;height:100px;object-fit:cover;border-radius:8px;">');
            remove.show();
            btn.text('Change photo');
        });
        frame.open();
    });
    $(document).on('click', '.stp-photo-remove', function(e){
        e.preventDefault();
        var wrap = $(this).closest('.stp-photo-field');
        wrap.find('.stp-photo-id').val('');
        wrap.find('.stp-photo-preview').html('<span style="color:#aaa;font-size:12px">No photo</span>');
        $(this).hide();
        wrap.find('.stp-pick-photo').text('Upload photo');
    });
}(jQuery));
JS;
		wp_add_inline_script( 'media-upload', $js );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// HELPERS
	// ─────────────────────────────────────────────────────────────────────────

	public static function get( string $key, string $default = '' ): string {
		$v = get_option( self::PREFIX . $key, '' );
		return '' !== (string) $v ? (string) $v : $default;
	}

	private function save( string $key, string $value ): void {
		if ( '' === $value ) {
			delete_option( self::PREFIX . $key );
		} else {
			update_option( self::PREFIX . $key, $value, false );
		}
	}

	private function save_photo( string $key, string $raw ): void {
		if ( '' === $raw ) {
			delete_option( self::PREFIX . $key );
		} elseif ( is_numeric( $raw ) && get_post_type( (int) $raw ) === 'attachment' ) {
			update_option( self::PREFIX . $key, (int) $raw, false );
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// RENDER
	// ─────────────────────────────────────────────────────────────────────────

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'about';
		$saved      = false;

		// ── Save ─────────────────────────────────────────────────────────────
		if ( isset( $_POST['showtime_ct_nonce'] ) &&
			 wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_ct_nonce'] ) ), 'showtime_ct_save' ) ) {

			$this->process_save( $active_tab );
			$saved = true;
		}

		$tabs = array(
			'home'    => __( 'Homepage', 'showtime-pools-core' ),
			'hubs'    => __( 'Hub Pages', 'showtime-pools-core' ),
			'about'   => __( 'About Page', 'showtime-pools-core' ),
			'team'    => __( 'Team Members', 'showtime-pools-core' ),
			'creds'   => __( 'Certifications', 'showtime-pools-core' ),
			'reviews' => __( 'Reviews Widget', 'showtime-pools-core' ),
		);
		$base_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Content', 'showtime-pools-core' ); ?></h1>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Content saved.', 'showtime-pools-core' ); ?></p></div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper" style="margin-bottom:1.5rem;">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a href="<?php echo esc_url( $base_url . '&tab=' . $slug ); ?>"
					   class="nav-tab<?php echo $active_tab === $slug ? ' nav-tab-active' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="">
				<?php wp_nonce_field( 'showtime_ct_save', 'showtime_ct_nonce' ); ?>
				<input type="hidden" name="showtime_ct_tab" value="<?php echo esc_attr( $active_tab ); ?>">

				<?php
				match ( $active_tab ) {
					'home'    => $this->render_home(),
					'hubs'    => $this->render_hubs(),
					'team'    => $this->render_team(),
					'creds'   => $this->render_creds(),
					'reviews' => $this->render_reviews(),
					default   => $this->render_about(),
				};
				?>

				<?php submit_button( __( 'Save', 'showtime-pools-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	// ─────────────────────────────────────────────────────────────────────────
	// ABOUT TAB
	// ─────────────────────────────────────────────────────────────────────────

	private function render_about(): void {
		$fields = array(
			array( 'key' => 'about_h1',        'label' => 'Hero headline (H1)',           'type' => 'text',     'default' => 'Complete pool care, start to finish.' ),
			array( 'key' => 'about_lead',       'label' => 'Hero lead paragraph',          'type' => 'textarea', 'default' => 'Showtime Pools designs, builds, and transforms pools and outdoor spaces that elevate the way you live.' ),
			array( 'key' => 'about_wwa_title',  'label' => '"Who We Are" section title',   'type' => 'text',     'default' => 'Years of hands-on experience. Built on quality, transparency, and reliability.' ),
			array( 'key' => 'about_wwa_body',   'label' => '"Who We Are" body (paragraphs)','type' => 'wysiwyg', 'default' => '' ),
			array( 'key' => 'about_values_title','label' => '"What We Believe" section title','type' => 'text',  'default' => 'Five commitments. Every project, every visit.' ),
		);
		$this->render_field_list( $fields );
	}

	private function process_about(): void {
		$keys = array( 'about_h1', 'about_lead', 'about_wwa_title', 'about_wwa_body', 'about_values_title' );
		foreach ( $keys as $key ) {
			$posted = isset( $_POST[ 'ct_' . $key ] ) ? wp_kses_post( wp_unslash( $_POST[ 'ct_' . $key ] ) ) : '';
			if ( 'about_h1' === $key || 'about_wwa_title' === $key || 'about_values_title' === $key ) {
				$posted = sanitize_text_field( $posted );
			}
			$this->save( $key, $posted );
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// HOMEPAGE TAB
	//
	// Sitewide homepage settings that are not page copy: form attribution
	// (UTM) defaults today; hero video URL + popup toggle are appended here by
	// later commits. UTM values save as standalone wp_options (no showtime_ct_
	// prefix) so the theme reads them directly via get_option() — same pattern
	// as the Reviews tab — without coupling templates to this admin class.
	// ─────────────────────────────────────────────────────────────────────────

	/** UTM attribution defaults for the native /contact/ form. key => default. */
	private static function utm_defaults(): array {
		return array(
			'utm_source'   => 'website',
			'utm_medium'   => 'organic',
			'utm_campaign' => 'site_form',
			'utm_content'  => 'contact_form',
		);
	}

	private function render_home(): void {
		?>
		<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;">
			<h3 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Contact form attribution (UTM → GHL)', 'showtime-pools-core' ); ?></h3>
			<p style="color:#666;font-size:12px;margin:0 0 14px;">
				<?php esc_html_e( 'Default UTM values sent with every native Contact-page form submission so n8n can attribute the source. If a visitor lands on /contact/ with real ?utm_… parameters in the URL, those override these defaults for that submission.', 'showtime-pools-core' ); ?>
			</p>
			<?php
			$labels = array(
				'utm_source'   => __( 'utm_source', 'showtime-pools-core' ),
				'utm_medium'   => __( 'utm_medium', 'showtime-pools-core' ),
				'utm_campaign' => __( 'utm_campaign', 'showtime-pools-core' ),
				'utm_content'  => __( 'utm_content', 'showtime-pools-core' ),
			);
			foreach ( self::utm_defaults() as $key => $default ) {
				$val = (string) get_option( 'showtime_' . $key, $default );
				$this->text_field( 'ct_' . $key, $labels[ $key ], $val );
			}
			?>
		</div>
		<?php
	}

	private function process_home(): void {
		foreach ( array_keys( self::utm_defaults() ) as $key ) {
			$posted = isset( $_POST[ 'ct_' . $key ] )
				? sanitize_text_field( wp_unslash( $_POST[ 'ct_' . $key ] ) )
				: '';
			if ( '' === $posted ) {
				delete_option( 'showtime_' . $key );
			} else {
				update_option( 'showtime_' . $key, $posted, false );
			}
		}
	}

	/**
	 * Hub Pages tab. No sitewide settings here yet — hub intros are edited per
	 * page (Pages → hub → Update). Rendered as a neutral note so the tab never
	 * fatals on an unimplemented renderer.
	 */
	private function render_hubs(): void {
		echo '<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;">';
		echo '<p style="color:#666;margin:0;">' . esc_html__( 'Hub page headings and intros are edited directly on each hub page (Pages → the hub page → Update). There are no sitewide settings on this tab.', 'showtime-pools-core' ) . '</p>';
		echo '</div>';
	}

	// ─────────────────────────────────────────────────────────────────────────
	// TEAM TAB
	// ─────────────────────────────────────────────────────────────────────────

	private static function team_defaults(): array {
		return array(
			array( 'name' => 'Steve Adams', 'role' => 'Founder & CEO',          'initials' => 'SA', 'note' => 'On every quote, walks every site, pulls every permit personally. The phone you call rings on his desk.',    'href' => '/the-founder/' ),
			array( 'name' => 'Viktor O',    'role' => 'Repair Manager',          'initials' => 'VO', 'note' => 'Runs the repair line. Diagnoses the failure before he quotes the fix. Pentair- and Jandy-certified.',       'href' => '' ),
			array( 'name' => 'Felipe A',    'role' => 'Pool Service Technician', 'initials' => 'FA', 'note' => 'Senior route tech. Same customers every week. Photo report after every visit before he leaves the driveway.','href' => '' ),
			array( 'name' => 'George C',    'role' => 'Senior Cleaner',          'initials' => 'GC', 'note' => 'Owns chemistry and detail. Tile-line wipe-down, full chemistry balance, equipment runtime check.',          'href' => '' ),
		);
	}

	/**
	 * Return a team member's data from wp_options (native) with PHP fallback.
	 *
	 * @return array{name:string,role:string,initials:string,note:string,href:string,photo:string}
	 */
	public static function get_team_member( int $n ): array {
		$defaults = self::team_defaults();
		$d        = $defaults[ $n - 1 ] ?? array( 'name' => '', 'role' => '', 'initials' => '', 'note' => '', 'href' => '' );

		return array(
			'name'     => self::get( "team_{$n}_name",     $d['name'] ),
			'role'     => self::get( "team_{$n}_role",     $d['role'] ),
			'initials' => self::get( "team_{$n}_initials", $d['initials'] ),
			'note'     => self::get( "team_{$n}_note",     $d['note'] ),
			'href'     => self::get( "team_{$n}_href",     $d['href'] ),
			'photo'    => (string) get_option( self::PREFIX . "team_{$n}_photo", '' ),
		);
	}

	/**
	 * Return all team members (up to 6). Always returns at least the 4 defaults.
	 *
	 * @return list<array{name:string,role:string,initials:string,note:string,href:string,photo:string}>
	 */
	public static function get_all_team(): array {
		$members = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			$m = self::get_team_member( $i );
			// Skip slots beyond the defaults if they're empty.
			if ( '' === $m['name'] && $i > 4 ) {
				continue;
			}
			$members[] = $m;
		}
		return $members;
	}

	private function render_team(): void {
		$style_card = 'background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;';
		$style_grid = 'display:grid;grid-template-columns:1fr 1fr;gap:14px;';
		?>
		<p style="color:#666;margin-bottom:1.5rem;"><?php esc_html_e( 'Edit team member info. Photo is separate from the Site Images picker — set it here per member.', 'showtime-pools-core' ); ?></p>
		<?php for ( $n = 1; $n <= 4; $n++ ) :
			$m         = self::get_team_member( $n );
			$photo_val = $m['photo'];
			$thumb     = '';
			if ( is_numeric( $photo_val ) && (int) $photo_val > 0 ) {
				$thumb = (string) wp_get_attachment_thumb_url( (int) $photo_val );
			}
		?>
			<div style="<?php echo esc_attr( $style_card ); ?>">
				<h3 style="margin-top:0;font-size:15px;"><?php echo esc_html( sprintf( __( 'Team Member %d', 'showtime-pools-core' ), $n ) ); ?></h3>
				<div style="<?php echo esc_attr( $style_grid ); ?>">
					<?php $this->text_field( "ct_team_{$n}_name",     __( 'Name', 'showtime-pools-core' ),     $m['name'] ); ?>
					<?php $this->text_field( "ct_team_{$n}_role",     __( 'Role / Title', 'showtime-pools-core' ), $m['role'] ); ?>
					<?php $this->text_field( "ct_team_{$n}_initials", __( 'Initials (fallback avatar)', 'showtime-pools-core' ), $m['initials'] ); ?>
					<?php $this->text_field( "ct_team_{$n}_href",     __( 'Profile link URL', 'showtime-pools-core' ), $m['href'] ); ?>
				</div>
				<?php $this->textarea_field( "ct_team_{$n}_note", __( 'Short bio note', 'showtime-pools-core' ), $m['note'], 2 ); ?>

				<div class="stp-photo-field" style="margin-top:12px;display:flex;align-items:center;gap:14px;">
					<div class="stp-photo-preview" style="min-width:60px;">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" style="max-width:100px;height:100px;object-fit:cover;border-radius:8px;">
						<?php else : ?>
							<span style="color:#aaa;font-size:12px"><?php esc_html_e( 'No photo', 'showtime-pools-core' ); ?></span>
						<?php endif; ?>
					</div>
					<input type="hidden" class="stp-photo-id" name="ct_team_<?php echo (int) $n; ?>_photo" value="<?php echo esc_attr( $photo_val ); ?>">
					<div>
						<button type="button" class="button stp-pick-photo">
							<?php echo $thumb ? esc_html__( 'Change photo', 'showtime-pools-core' ) : esc_html__( 'Upload photo', 'showtime-pools-core' ); ?>
						</button>
						<a href="#" class="stp-photo-remove" style="margin-left:8px;color:#b00;font-size:12px;<?php echo $thumb ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'showtime-pools-core' ); ?></a>
					</div>
				</div>
			</div>
		<?php endfor; ?>
		<?php
	}

	private function process_team(): void {
		for ( $n = 1; $n <= 4; $n++ ) {
			foreach ( array( 'name', 'role', 'initials', 'note', 'href' ) as $key ) {
				$posted = isset( $_POST[ "ct_team_{$n}_{$key}" ] )
					? sanitize_text_field( wp_unslash( $_POST[ "ct_team_{$n}_{$key}" ] ) )
					: '';
				$this->save( "team_{$n}_{$key}", $posted );
			}
			// Photo attachment ID.
			$photo = isset( $_POST[ "ct_team_{$n}_photo" ] )
				? sanitize_text_field( wp_unslash( $_POST[ "ct_team_{$n}_photo" ] ) )
				: '';
			$this->save_photo( "team_{$n}_photo", $photo );
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// CERTIFICATIONS TAB
	// ─────────────────────────────────────────────────────────────────────────

	private static function creds_defaults(): array {
		return array(
			array( 'title' => 'Pentair Authorized Service',    'body' => 'Manufacturer warranty pass-through on IntelliFlo, IntelliCenter, MasterTemp, and IC40 salt cells.' ),
			array( 'title' => 'Jandy Authorized Service',      'body' => 'AquaLink, AquaPure, JXi heater, and Stealth pump warranty pass-through.' ),
			array( 'title' => 'PebbleTec Certified Applicator','body' => 'Five-year written finish warranty backed by PebbleTec. Annual applicator training.' ),
			array( 'title' => 'California Code Compliance',    'body' => 'Every permit, bonding inspection, and electrical sign-off pulled through LA County and city counters in-house.' ),
		);
	}

	/**
	 * Return all certifications from wp_options with PHP fallback.
	 *
	 * @return list<array{h:string,b:string}>
	 */
	public static function get_all_creds(): array {
		$defaults = self::creds_defaults();
		$out = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$d     = $defaults[ $i - 1 ] ?? array( 'title' => '', 'body' => '' );
			$out[] = array(
				'h' => self::get( "cred_{$i}_title", $d['title'] ),
				'b' => self::get( "cred_{$i}_body",  $d['body'] ),
			);
		}
		return $out;
	}

	private function render_creds(): void {
		$defaults = self::creds_defaults();
		for ( $n = 1; $n <= 4; $n++ ) :
			$d = $defaults[ $n - 1 ] ?? array( 'title' => '', 'body' => '' );
			$title = self::get( "cred_{$n}_title", $d['title'] );
			$body  = self::get( "cred_{$n}_body",  $d['body'] );
			?>
			<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:16px;">
				<h3 style="margin-top:0;font-size:15px;"><?php echo esc_html( sprintf( __( 'Certification %d', 'showtime-pools-core' ), $n ) ); ?></h3>
				<?php $this->text_field( "ct_cred_{$n}_title", __( 'Title', 'showtime-pools-core' ), $title ); ?>
				<?php $this->textarea_field( "ct_cred_{$n}_body", __( 'Description', 'showtime-pools-core' ), $body, 2 ); ?>
			</div>
		<?php endfor;
	}

	private function process_creds(): void {
		for ( $n = 1; $n <= 4; $n++ ) {
			$title = isset( $_POST[ "ct_cred_{$n}_title" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "ct_cred_{$n}_title" ] ) ) : '';
			$body  = isset( $_POST[ "ct_cred_{$n}_body" ] )  ? sanitize_textarea_field( wp_unslash( $_POST[ "ct_cred_{$n}_body" ] ) ) : '';
			$this->save( "cred_{$n}_title", $title );
			$this->save( "cred_{$n}_body",  $body );
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// SHARED SAVE DISPATCHER
	// ─────────────────────────────────────────────────────────────────────────

	private function process_save( string $tab ): void {
		match ( $tab ) {
			'home'    => $this->process_home(),
			'hubs'    => null, // No editable settings on this tab yet.
			'team'    => $this->process_team(),
			'creds'   => $this->process_creds(),
			'reviews' => $this->process_reviews(),
			default   => $this->process_about(),
		};
	}

	// ─────────────────────────────────────────────────────────────────────────
	// REVIEWS WIDGET TAB
	// ─────────────────────────────────────────────────────────────────────────

	private function render_reviews(): void {
		$shortcode         = (string) get_option( 'showtime_reviews_shortcode', '' );
		$shortcode_compact = (string) get_option( 'showtime_reviews_shortcode_compact', '' );
		$gbp_url           = (string) get_option( 'showtime_gbp_public_url', '' );
		?>
		<p style="color:#666;margin-bottom:1.5rem;">
			<?php esc_html_e( 'Paste the shortcode from your Google Reviews widget plugin (default: Trustindex). Both the Reviews page and the homepage reviews section render from these settings, so the live rating and review count are never hardcoded in the theme.', 'showtime-pools-core' ); ?>
		</p>

		<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;">
			<h3 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Main widget shortcode', 'showtime-pools-core' ); ?></h3>
			<p style="color:#666;font-size:12px;margin:0 0 10px;">
				<?php esc_html_e( 'Renders on /reviews/ and (by default) on the homepage reviews section. Leave empty to use the Trustindex default: [trustindex no-registration=google]', 'showtime-pools-core' ); ?>
			</p>
			<input type="text" name="ct_reviews_shortcode" value="<?php echo esc_attr( $shortcode ); ?>" class="large-text" placeholder="[trustindex no-registration=google]">
		</div>

		<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;">
			<h3 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Compact variant (homepage only) — optional', 'showtime-pools-core' ); ?></h3>
			<p style="color:#666;font-size:12px;margin:0 0 10px;">
				<?php esc_html_e( 'Optional separate shortcode for the homepage reviews section if the plugin offers a smaller widget. Leave empty to use the main shortcode on both surfaces.', 'showtime-pools-core' ); ?>
			</p>
			<input type="text" name="ct_reviews_shortcode_compact" value="<?php echo esc_attr( $shortcode_compact ); ?>" class="large-text" placeholder="">
		</div>

		<div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;">
			<h3 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Google Business Profile URL', 'showtime-pools-core' ); ?></h3>
			<p style="color:#666;font-size:12px;margin:0 0 10px;">
				<?php esc_html_e( 'Public URL to your Google Business Profile listing. Used only as the "View Google reviews" CTA fallback if the widget plugin is ever inactive — never shown alongside the widget itself. If empty, the fallback uses a Google search URL.', 'showtime-pools-core' ); ?>
			</p>
			<input type="url" name="ct_gbp_public_url" value="<?php echo esc_attr( $gbp_url ); ?>" class="large-text" placeholder="https://g.page/r/...">
		</div>
		<?php
	}

	private function process_reviews(): void {
		// Stored as standalone wp_options (no showtime_ct_ prefix) so the
		// theme-side reviews-widget.php helper can read them directly via
		// get_option() without depending on the plugin class.
		if ( isset( $_POST['ct_reviews_shortcode'] ) ) {
			$v = sanitize_text_field( wp_unslash( $_POST['ct_reviews_shortcode'] ) );
			if ( '' === $v ) {
				delete_option( 'showtime_reviews_shortcode' );
			} else {
				update_option( 'showtime_reviews_shortcode', $v, false );
			}
		}
		if ( isset( $_POST['ct_reviews_shortcode_compact'] ) ) {
			$v = sanitize_text_field( wp_unslash( $_POST['ct_reviews_shortcode_compact'] ) );
			if ( '' === $v ) {
				delete_option( 'showtime_reviews_shortcode_compact' );
			} else {
				update_option( 'showtime_reviews_shortcode_compact', $v, false );
			}
		}
		if ( isset( $_POST['ct_gbp_public_url'] ) ) {
			$v = esc_url_raw( wp_unslash( $_POST['ct_gbp_public_url'] ) );
			if ( '' === $v ) {
				delete_option( 'showtime_gbp_public_url' );
			} else {
				update_option( 'showtime_gbp_public_url', $v, false );
			}
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// FIELD HELPERS
	// ─────────────────────────────────────────────────────────────────────────

	private function render_field_list( array $fields ): void {
		foreach ( $fields as $f ) {
			if ( 'wysiwyg' === $f['type'] ) {
				$value = self::get( $f['key'], $f['default'] ?? '' );
				echo '<div style="margin-bottom:1.2rem;">';
				echo '<label style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html( $f['label'] ) . '</label>';
				wp_editor(
					$value,
					'ct_' . $f['key'],
					array(
						'textarea_name' => 'ct_' . $f['key'],
						'media_buttons' => false,
						'teeny'         => true,
						'textarea_rows' => 6,
					)
				);
				echo '</div>';
			} elseif ( 'textarea' === $f['type'] ) {
				$this->textarea_field( 'ct_' . $f['key'], $f['label'], self::get( $f['key'], $f['default'] ?? '' ) );
			} else {
				$this->text_field( 'ct_' . $f['key'], $f['label'], self::get( $f['key'], $f['default'] ?? '' ) );
			}
		}
	}

	private function text_field( string $name, string $label, string $value ): void {
		echo '<div style="margin-bottom:1rem;">';
		printf(
			'<label style="display:block;font-weight:600;margin-bottom:4px;">%s</label>',
			esc_html( $label )
		);
		printf(
			'<input type="text" name="%s" value="%s" class="large-text">',
			esc_attr( $name ),
			esc_attr( $value )
		);
		echo '</div>';
	}

	private function textarea_field( string $name, string $label, string $value, int $rows = 4 ): void {
		echo '<div style="margin-bottom:1rem;">';
		printf( '<label style="display:block;font-weight:600;margin-bottom:4px;">%s</label>', esc_html( $label ) );
		printf( '<textarea name="%s" rows="%d" class="large-text">%s</textarea>', esc_attr( $name ), (int) $rows, esc_textarea( $value ) );
		echo '</div>';
	}
}

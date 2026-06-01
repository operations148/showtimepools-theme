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
			'home'   => __( 'Homepage', 'showtime-pools-core' ),
			'hubs'   => __( 'Hub Pages', 'showtime-pools-core' ),
			'about'  => __( 'About Page', 'showtime-pools-core' ),
			'team'   => __( 'Team Members', 'showtime-pools-core' ),
			'creds'  => __( 'Certifications', 'showtime-pools-core' ),
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
					'home'  => $this->render_home(),
				'hubs'  => $this->render_hubs(),
				'team'  => $this->render_team(),
					'creds' => $this->render_creds(),
					default => $this->render_about(),
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
			'team'  => $this->process_team(),
			'creds' => $this->process_creds(),
			default => $this->process_about(),
		};
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

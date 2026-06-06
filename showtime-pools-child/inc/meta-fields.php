<?php
/**
 * Native WordPress meta boxes — zero plugins required.
 *
 * Registers labelled editing panels inside the WP Admin page editor
 * for every page template that has editable content fields.
 *
 * HOW TO EDIT (for the client):
 *   WP Admin → Pages → [page name] → Edit
 *   Fill in the fields below the editor → Update
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper: render a single text input row inside a meta box.
 */
function showtime_meta_field( string $key, string $label, int $post_id, bool $textarea = false ): void {
	$value = (string) get_post_meta( $post_id, $key, true );
	echo '<p style="margin-bottom:14px;">';
	echo '<label for="' . esc_attr( $key ) . '" style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html( $label ) . '</label>';
	if ( $textarea ) {
		echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" style="width:100%;">';
	}
	echo '</p>';
}

// ─────────────────────────────────────────────────────────────────────────────
// SERVICES HUB — /services/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_services_hub_fields',
		__( 'Services Hub — Page Content', 'showtime-pools' ),
		'showtime_services_hub_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_services_hub_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-services-hub.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Services Hub page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_services_hub_save', 'showtime_services_hub_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'hero_eyebrow', 'Eyebrow chip (small text above headline)', $post->ID );
	showtime_meta_field( 'hero_lead',    'Lead paragraph (under the H1)',           $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Core Services Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'core_eyebrow', 'Eyebrow chip',  $post->ID );
	showtime_meta_field( 'core_h2',      'Section heading (H2)', $post->ID );
	showtime_meta_field( 'core_lead',    'Section lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Outdoor & Custom Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'outdoor_eyebrow', 'Eyebrow chip',  $post->ID );
	showtime_meta_field( 'outdoor_h2',      'Section heading (H2)', $post->ID );
	showtime_meta_field( 'outdoor_lead',    'Section lead paragraph', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_services_hub_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_services_hub_nonce'] ) ), 'showtime_services_hub_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$fields = array(
		'hero_eyebrow', 'hero_lead',
		'core_eyebrow', 'core_h2', 'core_lead',
		'outdoor_eyebrow', 'outdoor_h2', 'outdoor_lead',
	);

	foreach ( $fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// INSPECTIONS HUB — /pool-inspections/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_inspections_hub_fields',
		__( 'Inspections Hub — Page Content', 'showtime-pools' ),
		'showtime_inspections_hub_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_inspections_hub_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-inspections.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Inspections Hub page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_inspections_hub_save', 'showtime_inspections_hub_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'hero_lead', 'Lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Types Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'types_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'types_h2',      'Section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( '"Why Mechanics" Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'why_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'why_h2',      'Section heading', $post->ID );
	showtime_meta_field( 'why_para1',   'Paragraph 1', $post->ID, true );
	showtime_meta_field( 'why_para2',   'Paragraph 2', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_inspections_hub_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_inspections_hub_nonce'] ) ), 'showtime_inspections_hub_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'hero_lead', 'types_eyebrow', 'types_h2', 'why_eyebrow', 'why_h2', 'why_para1', 'why_para2' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// FOUNDER PAGE — /the-founder/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_founder_fields',
		__( 'Founder Page — Content', 'showtime-pools' ),
		'showtime_founder_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_founder_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-founder.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to The Founder page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_founder_save', 'showtime_founder_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	echo '<p style="color:#666;font-size:12px;">' . esc_html__( 'H1 comes from this page\'s Title field. Bio text comes from the page Content (Gutenberg editor). Portrait comes from Featured Image.', 'showtime-pools' ) . '</p>';
	showtime_meta_field( 'founder_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'founder_lead',    'Hero lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Story Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'founder_name',     'Founder name', $post->ID );
	showtime_meta_field( 'founder_title',    'Founder job title', $post->ID );
	showtime_meta_field( 'founder_story_h2', 'Story section H2', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Pull Quote', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'founder_quote',      'Quote text', $post->ID, true );
	showtime_meta_field( 'founder_quote_attr', 'Quote attribution', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Promises Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'founder_promises_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'founder_promises_h2',      'Section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Contact Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'founder_contact_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'founder_contact_h2',      'Section heading', $post->ID );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_founder_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_founder_nonce'] ) ), 'showtime_founder_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	$keys = array(
		'founder_eyebrow', 'founder_lead', 'founder_name', 'founder_title',
		'founder_story_h2', 'founder_quote', 'founder_quote_attr',
		'founder_promises_eyebrow', 'founder_promises_h2',
		'founder_contact_eyebrow', 'founder_contact_h2',
	);
	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// ABOUT PAGE — /about/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_about_fields',
		__( 'About Page — Content', 'showtime-pools' ),
		'showtime_about_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_about_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-about.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the About page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_about_save', 'showtime_about_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'about_hero_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'about_h1',           'Hero headline (H1)', $post->ID );
	showtime_meta_field( 'about_hero_lead',    'Hero lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( '"Who We Are" Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'about_eyebrow',      'Eyebrow chip', $post->ID );
	showtime_meta_field( 'about_wwa_title',    'Section heading', $post->ID );
	showtime_meta_field( 'about_wwa_body',     'Body (paragraphs)', $post->ID, true );
	showtime_meta_field( 'about_photo_caption','Photo caption', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( '"What We Believe" Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'about_values_eyebrow','Eyebrow chip', $post->ID );
	showtime_meta_field( 'about_values_title', 'Section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Team Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'about_team_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'about_team_h2',      'Section heading', $post->ID );
	showtime_meta_field( 'about_team_lead',    'Lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Certifications Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'about_creds_eyebrow','Eyebrow chip', $post->ID );
	showtime_meta_field( 'about_creds_h2',     'Section heading', $post->ID );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_about_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_about_nonce'] ) ), 'showtime_about_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	$keys = array(
		'about_hero_eyebrow', 'about_h1', 'about_hero_lead',
		'about_eyebrow', 'about_wwa_title', 'about_wwa_body', 'about_photo_caption',
		'about_values_eyebrow', 'about_values_title',
		'about_team_eyebrow', 'about_team_h2', 'about_team_lead',
		'about_creds_eyebrow', 'about_creds_h2',
	);
	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// CONTACT PAGE — /contact/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_contact_fields',
		__( 'Contact Page — Content', 'showtime-pools' ),
		'showtime_contact_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_contact_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-contact.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Contact page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_contact_save', 'showtime_contact_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'contact_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'contact_h1',      'Hero headline (H1)', $post->ID );
	showtime_meta_field( 'contact_lead',    'Hero lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Form Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'contact_form_title', 'Form section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Sidebar', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'contact_sidebar_h2',        'Sidebar heading', $post->ID );
	showtime_meta_field( 'contact_existing_customer', '"Already a customer?" label', $post->ID );
	showtime_meta_field( 'contact_existing_body',     '"Already a customer?" body', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_contact_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_contact_nonce'] ) ), 'showtime_contact_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'contact_eyebrow', 'contact_h1', 'contact_lead', 'contact_form_title', 'contact_sidebar_h2', 'contact_existing_customer', 'contact_existing_body' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// INDIVIDUAL INSPECTION PAGES — /pool-inspections/[type]/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_inspection_fields',
		__( 'Inspection Page — Content Overrides', 'showtime-pools' ),
		'showtime_inspection_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_inspection_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-inspection.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to individual inspection pages.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_inspection_save', 'showtime_inspection_nonce' );
	echo '<p style="color:#666;font-size:12px;margin-bottom:12px;">' . esc_html__( 'Leave blank to use the default text from the registry.', 'showtime-pools' ) . '</p>';
	showtime_meta_field( 'insp_h1',         'Hero H1 override',                      $post->ID );
	showtime_meta_field( 'insp_lead',        'Hero lead paragraph override',           $post->ID, true );
	showtime_meta_field( 'insp_who_h2',      '"Who this is for" heading',              $post->ID );
	showtime_meta_field( 'insp_what_h2',     '"What you get" heading',                 $post->ID );
	showtime_meta_field( 'insp_process_h2',  '"How it works" heading',                 $post->ID );
	showtime_meta_field( 'insp_faq_h2',      'FAQ section heading',                    $post->ID );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_inspection_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_inspection_nonce'] ) ), 'showtime_inspection_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'insp_h1', 'insp_lead', 'insp_who_h2', 'insp_what_h2', 'insp_process_h2', 'insp_faq_h2' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// INDIVIDUAL AREA PAGES — /service-areas/[neighborhood]/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_area_fields',
		__( 'Area Page — Content Overrides', 'showtime-pools' ),
		'showtime_area_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_area_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-area.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to individual area pages.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_area_save', 'showtime_area_nonce' );
	echo '<p style="color:#666;font-size:12px;margin-bottom:12px;">' . esc_html__( 'Leave blank to use the default text from the registry.', 'showtime-pools' ) . '</p>';
	showtime_meta_field( 'area_h1',           'Hero H1 override',                    $post->ID );
	showtime_meta_field( 'area_lead',         'Hero lead paragraph override',         $post->ID, true );
	showtime_meta_field( 'area_what_common',  '"What [area] pools have in common" heading', $post->ID );
	showtime_meta_field( 'area_what_do',      '"What we do here most" heading',       $post->ID );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_area_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_area_nonce'] ) ), 'showtime_area_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'area_h1', 'area_lead', 'area_what_common', 'area_what_do' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// IFRAME PAGES — /quote/ and /book/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_iframe_fields',
		__( 'Quote / Book Page Content', 'showtime-pools' ),
		'showtime_iframe_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_iframe_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-iframe.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Quote and Book pages.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_iframe_save', 'showtime_iframe_nonce' );

	showtime_meta_field( 'iframe_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'iframe_title',   'Hero headline (H1)', $post->ID );
	showtime_meta_field( 'iframe_lead',    'Hero lead paragraph', $post->ID, true );

	foreach ( array( 1, 2, 3 ) as $n ) {
		echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html( sprintf( __( 'Step %d', 'showtime-pools' ), $n ) ) . '</h4>';
		showtime_meta_field( "iframe_step{$n}_title", 'Step title', $post->ID );
		showtime_meta_field( "iframe_step{$n}_body",  'Step body',  $post->ID, true );
	}
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_iframe_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_iframe_nonce'] ) ), 'showtime_iframe_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	$keys = array( 'iframe_eyebrow', 'iframe_title', 'iframe_lead',
		'iframe_step1_title', 'iframe_step1_body',
		'iframe_step2_title', 'iframe_step2_body',
		'iframe_step3_title', 'iframe_step3_body',
	);
	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// REVIEWS — /reviews/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_reviews_fields',
		__( 'Reviews — Page Content', 'showtime-pools' ),
		'showtime_reviews_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_reviews_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-reviews.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Reviews page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_reviews_save', 'showtime_reviews_nonce' );
	showtime_meta_field( 'hero_eyebrow', 'Hero eyebrow (e.g. rating summary)', $post->ID );
	showtime_meta_field( 'hero_lead',    'Hero lead paragraph', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_reviews_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_reviews_nonce'] ) ), 'showtime_reviews_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'hero_eyebrow', 'hero_lead' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// BLOG HUB — /blog/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_blog_hub_fields',
		__( 'Blog Hub — Page Content', 'showtime-pools' ),
		'showtime_blog_hub_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_blog_hub_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-blog.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Blog Hub page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_blog_hub_save', 'showtime_blog_hub_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'hero_eyebrow',   'Eyebrow chip', $post->ID );
	showtime_meta_field( 'hero_lead',      'Lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Categories Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'cats_h2', 'Section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Feed Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'feed_h2', 'Section heading', $post->ID );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Sidebar CTA', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'sidebar_cta_title', 'CTA title', $post->ID );
	showtime_meta_field( 'sidebar_cta_body',  'CTA body',  $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_blog_hub_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_blog_hub_nonce'] ) ), 'showtime_blog_hub_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'hero_eyebrow', 'hero_lead', 'cats_h2', 'feed_h2', 'sidebar_cta_title', 'sidebar_cta_body' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// PROJECTS HUB — /projects/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_projects_hub_fields',
		__( 'Projects — Page Content', 'showtime-pools' ),
		'showtime_projects_hub_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_projects_hub_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-projects.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Projects page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_projects_hub_save', 'showtime_projects_hub_nonce' );
	showtime_meta_field( 'hero_eyebrow', 'Hero eyebrow chip', $post->ID );
	showtime_meta_field( 'hero_lead',    'Hero lead paragraph', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_projects_hub_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_projects_hub_nonce'] ) ), 'showtime_projects_hub_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( 'hero_eyebrow', 'hero_lead' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// SERVICE AREAS HUB — /service-areas/
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_areas_hub_fields',
		__( 'Service Areas Hub — Page Content', 'showtime-pools' ),
		'showtime_areas_hub_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_areas_hub_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-areas.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Service Areas Hub page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_areas_hub_save', 'showtime_areas_hub_nonce' );

	echo '<h4 style="margin:0 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( 'Hero', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'hero_eyebrow', 'Eyebrow chip', $post->ID );
	showtime_meta_field( 'hero_lead',    'Lead paragraph', $post->ID, true );

	echo '<h4 style="margin:14px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html__( '"Outside the Route" Section', 'showtime-pools' ) . '</h4>';
	showtime_meta_field( 'outside_h2',   'Heading', $post->ID );
	showtime_meta_field( 'outside_body', 'Body paragraph', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_areas_hub_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_areas_hub_nonce'] ) ), 'showtime_areas_hub_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	foreach ( array( 'hero_eyebrow', 'hero_lead', 'outside_h2', 'outside_body' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// ─────────────────────────────────────────────────────────────────────────────
// AFFILIATE / PARTNER PROGRAM — /affiliate/
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Full editable field list for the Affiliate page. Single source so the meta
 * box and the save handler never drift.
 *
 * @return array<int, string>
 */
function showtime_affiliate_field_keys(): array {
	$keys = array(
		'affiliate_hero_eyebrow', 'affiliate_h1', 'affiliate_hero_lead', 'affiliate_hero_cta',
		'affiliate_trust1', 'affiliate_trust2', 'affiliate_trust3', 'affiliate_trust4',
		'affiliate_benefits_eyebrow', 'affiliate_benefits_h2', 'affiliate_benefits_lead',
		'affiliate_process_eyebrow', 'affiliate_process_h2', 'affiliate_process_lead',
		'affiliate_faq_eyebrow', 'affiliate_faq_h2',
		'affiliate_form_eyebrow', 'affiliate_form_h2', 'affiliate_form_lead',
		'affiliate_promote_options', 'affiliate_submit_label', 'affiliate_consent_text',
	);
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		$keys[] = "affiliate_benefit{$n}_title";
		$keys[] = "affiliate_benefit{$n}_body";
	}
	foreach ( array( 1, 2, 3 ) as $n ) {
		$keys[] = "affiliate_step{$n}_title";
		$keys[] = "affiliate_step{$n}_body";
	}
	foreach ( array( 1, 2, 3, 4, 5 ) as $n ) {
		$keys[] = "affiliate_faq{$n}_q";
		$keys[] = "affiliate_faq{$n}_a";
	}
	return $keys;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_affiliate_fields',
		__( 'Affiliate Page — Content', 'showtime-pools' ),
		'showtime_affiliate_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_affiliate_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-affiliate.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Affiliate / Partner Program page.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_affiliate_save', 'showtime_affiliate_nonce' );

	$head = static function ( string $label ): void {
		echo '<h4 style="margin:18px 0 10px;border-bottom:1px solid #ddd;padding-bottom:6px;">' . esc_html( $label ) . '</h4>';
	};

	$head( __( 'Hero', 'showtime-pools' ) );
	showtime_meta_field( 'affiliate_hero_eyebrow', 'Eyebrow chip (small text above headline)', $post->ID );
	showtime_meta_field( 'affiliate_h1', 'Headline (H1)', $post->ID, true );
	showtime_meta_field( 'affiliate_hero_lead', 'Lead paragraph', $post->ID, true );
	showtime_meta_field( 'affiliate_hero_cta', 'Hero button label', $post->ID );

	$head( __( 'Trust strip (chips under the hero)', 'showtime-pools' ) );
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		showtime_meta_field( "affiliate_trust{$n}", sprintf( 'Chip %d', $n ), $post->ID );
	}

	$head( __( 'Benefits section', 'showtime-pools' ) );
	showtime_meta_field( 'affiliate_benefits_eyebrow', 'Eyebrow', $post->ID );
	showtime_meta_field( 'affiliate_benefits_h2', 'Heading (H2)', $post->ID, true );
	showtime_meta_field( 'affiliate_benefits_lead', 'Lead paragraph', $post->ID, true );
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		showtime_meta_field( "affiliate_benefit{$n}_title", sprintf( 'Benefit %d — title', $n ), $post->ID );
		showtime_meta_field( "affiliate_benefit{$n}_body", sprintf( 'Benefit %d — body', $n ), $post->ID, true );
	}

	$head( __( 'Process section', 'showtime-pools' ) );
	showtime_meta_field( 'affiliate_process_eyebrow', 'Eyebrow', $post->ID );
	showtime_meta_field( 'affiliate_process_h2', 'Heading (H2)', $post->ID, true );
	showtime_meta_field( 'affiliate_process_lead', 'Lead paragraph', $post->ID, true );
	foreach ( array( 1, 2, 3 ) as $n ) {
		showtime_meta_field( "affiliate_step{$n}_title", sprintf( 'Step %d — title', $n ), $post->ID );
		showtime_meta_field( "affiliate_step{$n}_body", sprintf( 'Step %d — body', $n ), $post->ID, true );
	}

	$head( __( 'FAQ (leave a Q&A blank to hide it)', 'showtime-pools' ) );
	showtime_meta_field( 'affiliate_faq_eyebrow', 'Eyebrow', $post->ID );
	showtime_meta_field( 'affiliate_faq_h2', 'Heading (H2)', $post->ID, true );
	foreach ( array( 1, 2, 3, 4, 5 ) as $n ) {
		showtime_meta_field( "affiliate_faq{$n}_q", sprintf( 'FAQ %d — question', $n ), $post->ID );
		showtime_meta_field( "affiliate_faq{$n}_a", sprintf( 'FAQ %d — answer', $n ), $post->ID, true );
	}

	$head( __( 'Signup form', 'showtime-pools' ) );
	showtime_meta_field( 'affiliate_form_eyebrow', 'Eyebrow', $post->ID );
	showtime_meta_field( 'affiliate_form_h2', 'Heading (H2)', $post->ID, true );
	showtime_meta_field( 'affiliate_form_lead', 'Lead paragraph', $post->ID, true );
	showtime_meta_field( 'affiliate_promote_options', 'Referral-source options (one per line)', $post->ID, true );
	showtime_meta_field( 'affiliate_submit_label', 'Submit button label', $post->ID );
	showtime_meta_field( 'affiliate_consent_text', 'Consent checkbox text', $post->ID, true );
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_affiliate_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_affiliate_nonce'] ) ), 'showtime_affiliate_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	foreach ( showtime_affiliate_field_keys() as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
} );

// -----------------------------------------------------------------------------
// LEGAL - /privacy-policy/ and /terms/
// -----------------------------------------------------------------------------

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'showtime_legal_fields',
		__( 'Legal Page Content', 'showtime-pools' ),
		'showtime_legal_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function showtime_legal_meta_box( WP_Post $post ): void {
	if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-legal.php' ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'These fields only apply to the Privacy and Terms pages.', 'showtime-pools' ) . '</p>';
		return;
	}
	wp_nonce_field( 'showtime_legal_save', 'showtime_legal_nonce' );

	showtime_meta_field( 'legal_eyebrow', 'Eyebrow (e.g. "Last updated ...")', $post->ID );
	showtime_meta_field( 'legal_lead', 'Lead paragraph (under the title)', $post->ID, true );

	echo '<p style="margin:14px 0 4px;font-weight:600;">' . esc_html__( 'Body (HTML allowed - headings, paragraphs, links)', 'showtime-pools' ) . '</p>';
	echo '<p style="margin:0 0 8px;color:#666;">' . esc_html__( 'Leave blank to use the built-in default text for this page.', 'showtime-pools' ) . '</p>';
	wp_editor(
		(string) get_post_meta( $post->ID, 'legal_body', true ),
		'legal_body',
		array(
			'textarea_name' => 'legal_body',
			'media_buttons' => false,
			'textarea_rows' => 18,
		)
	);
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if (
		! isset( $_POST['showtime_legal_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['showtime_legal_nonce'] ) ), 'showtime_legal_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	if ( isset( $_POST['legal_eyebrow'] ) ) {
		update_post_meta( $post_id, 'legal_eyebrow', sanitize_text_field( wp_unslash( $_POST['legal_eyebrow'] ) ) );
	}
	if ( isset( $_POST['legal_lead'] ) ) {
		update_post_meta( $post_id, 'legal_lead', sanitize_textarea_field( wp_unslash( $_POST['legal_lead'] ) ) );
	}
	if ( isset( $_POST['legal_body'] ) ) {
		update_post_meta( $post_id, 'legal_body', wp_kses_post( wp_unslash( $_POST['legal_body'] ) ) );
	}
} );

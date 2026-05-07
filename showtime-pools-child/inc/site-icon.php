<?php
/**
 * Favicon / site icon — bundled defaults with Customizer override.
 *
 * WordPress emits its own <link rel="icon"> tags when the user uploads a
 * Site Icon via Customizer → Site Identity. If they haven't, we fall back
 * to the favicon variants bundled with the theme so the site never ships
 * iconless. The Customizer upload always wins — this hook gets out of the
 * way as soon as `site_icon` is set.
 *
 * Source: assets/img/logo.png (1060×1060). Generated variants live at
 * assets/img/favicons/. Regenerate with the PowerShell script in
 * tools/generate-favicons.ps1 if the source logo changes.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		// If the user has uploaded a Site Icon in Customizer, WP handles
		// rendering the <link rel="icon"> tags itself. Don't double up.
		if ( has_site_icon() ) {
			return;
		}

		$base = SHOWTIME_CHILD_URI . '/assets/img/favicons';
		$ver  = SHOWTIME_CHILD_VERSION;

		printf(
			'<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n",
			esc_url( "{$base}/favicon-32.png?v={$ver}" )
		);
		printf(
			'<link rel="icon" type="image/png" sizes="16x16" href="%s">' . "\n",
			esc_url( "{$base}/favicon-16.png?v={$ver}" )
		);
		printf(
			'<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n",
			esc_url( "{$base}/apple-touch-icon.png?v={$ver}" )
		);
		printf(
			'<link rel="manifest" href="%s">' . "\n",
			esc_url( SHOWTIME_CHILD_URI . '/assets/img/favicons/site.webmanifest?v=' . $ver )
		);
		printf(
			'<meta name="theme-color" content="%s">' . "\n",
			esc_attr( '#0A0A0A' )
		);
	},
	2
);

/**
 * Admin notice nudging the user to upload a Site Icon via Customizer once
 * the site is live. Suppresses itself the moment a Site Icon is set.
 * Capability check ensures only admins see it.
 */
add_action(
	'admin_notices',
	function () {
		if ( has_site_icon() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$customize_url = admin_url( 'customize.php?autofocus[section]=title_tagline' );
		printf(
			'<div class="notice notice-info is-dismissible"><p><strong>%s</strong> %s <a href="%s">%s</a>.</p></div>',
			esc_html__( 'Showtime Pools:', 'showtime-pools' ),
			esc_html__( 'A theme-bundled favicon is active. For the cleanest browser-tab presentation, upload a custom Site Icon in', 'showtime-pools' ),
			esc_url( $customize_url ),
			esc_html__( 'Customizer → Site Identity', 'showtime-pools' )
		);
	}
);

<?php
/**
 * Cookie consent + Google Consent Mode v2 wiring.
 *
 * The owner controls everything from Showtime → Settings → "Tracking & Consent"
 * (core plugin, stored in wp_options). This file only renders:
 *
 *   1. The Consent Mode v2 *default* (everything denied) at wp_head priority 0,
 *      BEFORE any GTM container evaluates — required for GDPR/CCPA. It also
 *      re-applies a previously-saved choice synchronously so returning visitors
 *      who already consented get their tags on first paint.
 *   2. The GTM container itself, but ONLY if the owner opted the theme in
 *      (showtime_gtm_inject). Left off, GTM stays wherever it already lives and
 *      we just layer Consent Mode on top.
 *
 * The banner UI + the interactive consent *update* live in consent.js, wired in
 * the asset/footer block below.
 *
 * Pixels (Meta, TikTok, Google Ads) are NEVER hardcoded here — they live in GTM.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolved tracking/consent config from wp_options, with display defaults.
 *
 * @return array{gtm_id:string,gtm_inject:bool,enabled:bool,heading:string,message:string,accept_label:string,reject_label:string,prefs_label:string,policy_url:string}
 */
function showtime_consent_config(): array {
	$policy = (string) get_option( 'showtime_consent_policy_url', '' );
	if ( '' === $policy && function_exists( 'get_privacy_policy_url' ) ) {
		$policy = (string) get_privacy_policy_url();
	}
	if ( '' === $policy ) {
		$policy = home_url( '/legal/' );
	}

	$default_message = __( 'We use cookies to improve your experience, analyse traffic, and for marketing. Choose which cookies to allow.', 'showtime-pools' );

	return array(
		'gtm_id'       => trim( (string) get_option( 'showtime_gtm_id', '' ) ),
		'gtm_inject'   => '1' === (string) get_option( 'showtime_gtm_inject', '0' ),
		'enabled'      => '1' === (string) get_option( 'showtime_consent_enabled', '1' ),
		'heading'      => showtime_consent_text( 'showtime_consent_heading', __( 'We value your privacy', 'showtime-pools' ) ),
		'message'      => showtime_consent_text( 'showtime_consent_message', $default_message ),
		'accept_label' => showtime_consent_text( 'showtime_consent_accept_label', __( 'Accept all', 'showtime-pools' ) ),
		'reject_label' => showtime_consent_text( 'showtime_consent_reject_label', __( 'Reject non-essential', 'showtime-pools' ) ),
		'prefs_label'  => showtime_consent_text( 'showtime_consent_prefs_label', __( 'Preferences', 'showtime-pools' ) ),
		'policy_url'   => $policy,
	);
}

/**
 * Option value with a fallback when the stored value is empty.
 */
function showtime_consent_text( string $option, string $default ): string {
	$v = trim( (string) get_option( $option, '' ) );
	return '' === $v ? $default : $v;
}

/**
 * Whether any tracking/consent output should run on this request. True when the
 * banner is enabled OR a GTM ID is configured (Consent Mode still applies even
 * if the banner is hidden).
 */
function showtime_consent_active(): bool {
	if ( is_admin() ) {
		return false;
	}
	$cfg = showtime_consent_config();
	return $cfg['enabled'] || '' !== $cfg['gtm_id'];
}

/**
 * 1) Consent Mode v2 default (denied) + stored-choice replay. Priority 0 so it
 *    precedes any GTM injected by a plugin at the default priority.
 * 2) The GTM container — only when the theme is opted in.
 */
add_action(
	'wp_head',
	function () {
		if ( ! showtime_consent_active() ) {
			return;
		}
		$cfg = showtime_consent_config();
		?>
<script><?php // Consent Mode v2 — must run before GTM. ?>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{'ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied','analytics_storage':'denied','functionality_storage':'granted','security_storage':'granted','wait_for_update':500});
gtag('set','ads_data_redaction',true);gtag('set','url_passthrough',true);
try{var m=document.cookie.match(/(?:^|;\s*)stp_consent=([^;]+)/);if(m){var c=JSON.parse(decodeURIComponent(m[1]));gtag('consent','update',{'analytics_storage':c.a?'granted':'denied','ad_storage':c.m?'granted':'denied','ad_user_data':c.m?'granted':'denied','ad_personalization':c.m?'granted':'denied'});dataLayer.push({event:'stp_consent_update',stp_consent:{analytics:!!c.a,marketing:!!c.m,source:'stored'}});}}catch(e){}
</script>
		<?php
		if ( $cfg['gtm_inject'] && '' !== $cfg['gtm_id'] ) {
			$id = esc_js( $cfg['gtm_id'] );
			echo "<!-- Google Tag Manager (theme-injected) -->\n";
			echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $id . "');</script>\n";
			echo "<!-- End Google Tag Manager -->\n";
		}
	},
	0
);

/**
 * GTM <noscript> fallback — only when the theme injects the container.
 */
add_action(
	'wp_body_open',
	function () {
		$cfg = showtime_consent_config();
		if ( is_admin() || ! $cfg['gtm_inject'] || '' === $cfg['gtm_id'] ) {
			return;
		}
		printf(
			'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
			esc_attr( $cfg['gtm_id'] )
		);
	}
);

/**
 * Banner assets (deferred) — only when the banner is enabled.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$cfg = showtime_consent_config();
		if ( is_admin() || ! $cfg['enabled'] ) {
			return;
		}

		[ $uri, $ver ] = showtime_asset( 'assets/css/consent.css' );
		wp_enqueue_style( 'showtime-consent', $uri, array( 'showtime-tokens' ), $ver );

		[ $uri, $ver ] = showtime_asset( 'assets/js/consent.js' );
		wp_enqueue_script( 'showtime-consent', $uri, array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		wp_localize_script(
			'showtime-consent',
			'ShowtimeConsent',
			array(
				'cookie'  => 'stp_consent',
				'days'    => 180,
				'version' => 1,
			)
		);
	}
);

/**
 * Render the banner markup in the footer (hidden until consent.js reveals it).
 */
add_action(
	'wp_footer',
	function () {
		$cfg = showtime_consent_config();
		if ( is_admin() || ! $cfg['enabled'] ) {
			return;
		}
		get_template_part( 'template-parts/global/consent-banner' );
	}
);

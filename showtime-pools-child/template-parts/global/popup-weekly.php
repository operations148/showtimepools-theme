<?php
/**
 * Sitewide "Weekly Maintenance" lead popup (GHL form pZm1SEhLB9YMX21EvIV5).
 *
 * Output once in wp_footer by inc/popup.php (gated by the CMS toggle and page
 * rules). The GHL <iframe> is NOT printed here — assets/js/popup.js injects it
 * the first time the modal opens, so nothing loads at initial paint and LCP is
 * never affected. The embed src (with popup UTM) comes from
 * showtime_popup_form_url() so it stays in one filterable place.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$stp_popup_src = function_exists( 'showtime_popup_form_url' ) ? showtime_popup_form_url() : '';
if ( '' === $stp_popup_src ) {
	return;
}
?>
<div class="stp-popup" id="stp-popup-weekly" data-popup hidden>
	<div class="stp-popup__backdrop" data-popup-close></div>
	<div class="stp-popup__dialog"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Weekly pool maintenance — get a quote', 'showtime-pools' ); ?>">
		<button type="button" class="stp-popup__close" data-popup-close aria-label="<?php esc_attr_e( 'Close', 'showtime-pools' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
		<div class="stp-popup__embed"
			data-popup-embed
			data-src="<?php echo esc_url( $stp_popup_src ); ?>"
			data-form-id="pZm1SEhLB9YMX21EvIV5"
			data-title="<?php esc_attr_e( 'Popup – Weekly Maintenance', 'showtime-pools' ); ?>">
			<noscript>
				<iframe src="<?php echo esc_url( $stp_popup_src ); ?>" style="width:100%;min-height:520px;border:0" title="<?php esc_attr_e( 'Popup – Weekly Maintenance', 'showtime-pools' ); ?>"></iframe>
			</noscript>
		</div>
	</div>
</div>

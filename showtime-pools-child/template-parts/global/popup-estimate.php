<?php
/**
 * Sitewide delayed "Get a Free Estimate" popup.
 *
 * Printed once in wp_footer by inc/popup.php, which also gates the request
 * context. Ships hidden ([hidden] + aria-hidden) and stays inert until
 * assets/js/popup.js reveals it 15s after load, so it costs nothing at first
 * paint and cannot shift layout.
 *
 * The GHL booking calendar is NEVER embedded here — the primary CTA is a plain
 * same-tab link, so no request reaches app.showtimepoolmechanics.com until the
 * visitor actually clicks it.
 *
 * Icons are inline SVG (stroke: currentColor), matching the icon approach used
 * elsewhere in the theme; no third-party icon library is introduced.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Same official asset the header and footer resolve: Customizer logo first,
// then the bundled file, in the same extension order. Intrinsic dimensions are
// read from the file so the header row reserves its box and cannot shift.
$pe_logo   = '';
$pe_width  = 0;
$pe_height = 0;

$pe_custom = (int) get_theme_mod( 'custom_logo' );
if ( $pe_custom > 0 ) {
	$pe_src = wp_get_attachment_image_src( $pe_custom, 'full' );
	if ( is_array( $pe_src ) && ! empty( $pe_src[0] ) ) {
		$pe_logo   = (string) $pe_src[0];
		$pe_width  = (int) $pe_src[1];
		$pe_height = (int) $pe_src[2];
	}
}
if ( '' === $pe_logo ) {
	foreach ( array( 'svg', 'webp', 'png', 'jpg' ) as $pe_ext ) {
		$pe_path = SHOWTIME_CHILD_DIR . "/assets/img/logo.{$pe_ext}";
		if ( ! file_exists( $pe_path ) ) {
			continue;
		}
		$pe_logo = SHOWTIME_CHILD_URI . "/assets/img/logo.{$pe_ext}";
		$pe_size = @getimagesize( $pe_path );
		if ( is_array( $pe_size ) ) {
			$pe_width  = (int) $pe_size[0];
			$pe_height = (int) $pe_size[1];
		}
		break;
	}
}

$pe_checklist = array(
	'Free, no-obligation estimate',
	'Response within 1 business day',
	'Upfront pricing before any work begins',
	'Serving 50+ Los Angeles communities',
);

$pe_reassurance = array( 'No spam', 'No pressure', 'Fast response' );
?>
<div class="stp-estimate" id="stp-estimate-popup" data-estimate-popup hidden>
	<div class="stp-estimate__backdrop" data-estimate-close></div>

	<div class="stp-estimate__dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="stp-estimate-title"
		aria-describedby="stp-estimate-desc">

		<div class="stp-estimate__header">
			<?php if ( '' !== $pe_logo && $pe_width > 0 && $pe_height > 0 ) : ?>
				<img
					class="stp-estimate__logo"
					src="<?php echo esc_url( $pe_logo ); ?>"
					alt=""
					width="<?php echo esc_attr( (string) $pe_width ); ?>"
					height="<?php echo esc_attr( (string) $pe_height ); ?>"
					loading="lazy"
					decoding="async"
					aria-hidden="true">
			<?php endif; ?>

			<p class="stp-estimate__eyebrow">LOS ANGELES POOL EXPERTS</p>
			<h2 class="stp-estimate__title" id="stp-estimate-title">Get a Free Estimate</h2>

			<button type="button"
				class="stp-estimate__close"
				data-estimate-close
				aria-label="Close the free estimate dialog">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg>
			</button>
		</div>

		<div class="stp-estimate__body">
			<p class="stp-estimate__lede" id="stp-estimate-desc">Tell us about your pool. We&rsquo;ll follow up with a clear, no-pressure quote.</p>

			<ul class="stp-estimate__list">
				<?php foreach ( $pe_checklist as $pe_item ) : ?>
					<li class="stp-estimate__list-item">
						<svg class="stp-estimate__check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m20 6-11 11-5-5"/></svg>
						<span><?php echo esc_html( $pe_item ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<a class="stp-estimate__cta"
				href="<?php echo esc_url( showtime_popup_booking_url() ); ?>"
				data-estimate-cta>Fill Out the Estimate Form</a>

			<p class="stp-estimate__or"><span>or</span></p>

			<a class="stp-estimate__call"
				href="<?php echo esc_url( showtime_popup_tel_url() ); ?>"
				data-estimate-cta>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
				<span>Call (323) 825&ndash;2099</span>
			</a>

			<ul class="stp-estimate__reassure">
				<?php foreach ( $pe_reassurance as $pe_note ) : ?>
					<li><?php echo esc_html( $pe_note ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>

<?php
/**
 * Pricing — soft "starting at" + turnaround pair, with disclaimer.
 *
 * Pricing is intentionally directional. Real numbers come back via the
 * GHL quote flow. Section is suppressed entirely if both fields are empty.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx        = $GLOBALS['showtime_service_ctx'] ?? array();
$price      = (string) ( $ctx['price'] ?? '' );
$turnaround = (string) ( $ctx['turnaround'] ?? '' );
$disclaimer = (string) ( $ctx['disclaimer'] ?? '' );

if ( '' === $price && '' === $turnaround ) {
	return;
}

$default_disclaimer = __( 'Final price depends on pool size, equipment, finish selection, and site access. We give you a written quote within 48 hours of the free site visit. No mid-job upcharges.', 'showtime-pools' );

$phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$tel   = preg_replace( '/[^0-9+]/', '', $phone );
?>
<section class="svc-pricing section" data-reveal>
	<div class="container">
		<div class="svc-pricing__card">
			<div class="svc-pricing__pair">
				<?php if ( $price ) : ?>
					<div class="svc-pricing__cell">
						<span class="svc-pricing__label"><?php esc_html_e( 'Investment', 'showtime-pools' ); ?></span>
						<span class="svc-pricing__value"><?php echo esc_html( $price ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $turnaround ) : ?>
					<div class="svc-pricing__cell">
						<span class="svc-pricing__label"><?php esc_html_e( 'Timeline', 'showtime-pools' ); ?></span>
						<span class="svc-pricing__value"><?php echo esc_html( $turnaround ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<p class="svc-pricing__disclaimer">
				<?php echo esc_html( '' !== $disclaimer ? $disclaimer : $default_disclaimer ); ?>
			</p>

			<div class="cluster">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get my quote', 'showtime-pools' ); ?>
				</a>
				<a class="btn btn--link" href="<?php echo esc_url( 'tel:' . $tel ); ?>">
					<?php echo esc_html( sprintf( __( 'Or call %s', 'showtime-pools' ), $phone ) ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

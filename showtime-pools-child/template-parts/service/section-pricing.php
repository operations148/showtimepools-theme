<?php
/**
 * Pricing — soft "starting at" investment figure, optional itemized
 * breakdown, with disclaimer. Timeline/turnaround intentionally not
 * displayed here (removed from the card per request); the underlying
 * registry data is left intact in case it's needed elsewhere.
 *
 * Pricing is intentionally directional. Real numbers come back via the
 * GHL quote flow. Section is suppressed entirely if there's no price.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx         = $GLOBALS['showtime_service_ctx'] ?? array();
$price       = (string) ( $ctx['price'] ?? '' );
$price_was   = (string) ( $ctx['price_was'] ?? '' );
$price_badge = (string) ( $ctx['price_badge'] ?? '' );
$price_items = (array)  ( $ctx['price_items'] ?? array() );
$disclaimer  = (string) ( $ctx['disclaimer'] ?? '' );

if ( '' === $price ) {
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
				<div class="svc-pricing__cell">
					<span class="svc-pricing__label"><?php esc_html_e( 'Investment', 'showtime-pools' ); ?></span>
					<?php if ( '' !== $price_badge ) : ?>
						<span class="svc-pricing__badge"><?php echo esc_html( $price_badge ); ?></span>
					<?php endif; ?>
					<span class="svc-pricing__value-row">
						<span class="svc-pricing__value"><?php echo esc_html( $price ); ?></span>
						<?php if ( '' !== $price_was ) : ?>
							<span class="svc-pricing__was"><?php echo esc_html( $price_was ); ?></span>
						<?php endif; ?>
					</span>
				</div>
			</div>

			<?php if ( ! empty( $price_items ) ) : ?>
				<ul class="svc-pricing__items">
					<?php foreach ( $price_items as $item ) :
						$item_label = (string) ( $item['label'] ?? '' );
						$item_price = (string) ( $item['price'] ?? '' );
						if ( '' === $item_label || '' === $item_price ) { continue; }
					?>
						<li class="svc-pricing__item">
							<span class="svc-pricing__item-label"><?php echo esc_html( $item_label ); ?></span>
							<span class="svc-pricing__item-price"><?php echo esc_html( $item_price ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<p class="svc-pricing__disclaimer">
				<?php echo esc_html( '' !== $disclaimer ? $disclaimer : $default_disclaimer ); ?>
			</p>

			<div class="cluster">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>">
					<?php esc_html_e( 'Get my quote', 'showtime-pools' ); ?>
				</a>
				<a class="btn btn--link" href="<?php echo esc_url( 'tel:' . $tel ); ?>">
					<?php echo esc_html( sprintf( __( 'Or call %s', 'showtime-pools' ), $phone ) ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
/**
 * Service at a glance — a short, specific direct-answer block for AEO/GEO.
 * Answers what the service is, who it's for, scope, and (only when verified)
 * starting price + timeframe. Renders nothing if the registry has no glance
 * data, so incomplete services never show an empty block.
 *
 * All data comes from the service registry ($ctx['glance'] + reused price /
 * turnaround). No fabricated facts, no JSON-LD (schema is owned elsewhere).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx    = $GLOBALS['showtime_service_ctx'] ?? array();
$glance = (array) ( $ctx['glance'] ?? array() );
$answer = (string) ( $glance['answer'] ?? '' );

if ( '' === $answer ) {
	return;
}

$best_for   = (string) ( $glance['best_for'] ?? '' );
$area       = (string) ( $glance['area'] ?? apply_filters( 'showtime/business/primary_area', 'Sherman Oaks & greater Los Angeles' ) );
$price      = (string) ( $ctx['price'] ?? '' );
$turnaround = (string) ( $ctx['turnaround'] ?? '' );
$turnaround_detail = (string) ( $ctx['turnaround_detail'] ?? '' );
$reviewed   = function_exists( 'showtime_aeo_reviewed_date' ) ? showtime_aeo_reviewed_date() : '';

// Timeline caveats: the per-service nuance (turnaround_detail) plus the shared
// global helper. Shown small + muted beneath the "Typical timeframe" fact, not
// in a tooltip, so the planning-estimate caveat is always readable.
$timeline_helper = function_exists( 'showtime_timeline_helper' ) ? showtime_timeline_helper() : '';

// Quote CTA + disclaimer live here now (the standalone pricing/"Investment"
// section was retired — the starting price shows as a fact above and the
// itemized breakdown lives in the cost guide).
$disclaimer = (string) ( $ctx['disclaimer'] ?? '' );
if ( '' === $disclaimer ) {
	$disclaimer = __( 'Final price depends on pool size, equipment, finish selection, and site access. We give you a written quote within 48 hours of the free site visit. No mid-job upcharges.', 'showtime-pools' );
}
$phone = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$tel   = preg_replace( '/[^0-9+]/', '', $phone );

// Only rows with a value render — no empty facts.
$facts = array(
	array( 'label' => __( 'Typical starting price', 'showtime-pools' ), 'value' => $price ),
	array( 'label' => __( 'Typical timeframe', 'showtime-pools' ),      'value' => $turnaround ),
	array( 'label' => __( 'Primary service area', 'showtime-pools' ),   'value' => $area ),
	array( 'label' => __( 'Best suited for', 'showtime-pools' ),        'value' => $best_for ),
);
?>
<section class="svc-glance section section--surface" data-reveal>
	<div class="container">
		<div class="svc-glance__card">
			<div class="svc-glance__answer">
				<span class="eyebrow"><?php esc_html_e( 'Service at a glance', 'showtime-pools' ); ?></span>
				<p class="svc-glance__lead"><?php echo esc_html( $answer ); ?></p>
				<?php if ( '' !== $reviewed ) : ?>
					<p class="svc-glance__reviewed">
						<?php echo esc_html( sprintf( /* translators: %s: month/year */ __( 'Last reviewed: %s', 'showtime-pools' ), $reviewed ) ); ?>
					</p>
				<?php endif; ?>
			</div>
			<dl class="svc-glance__facts">
				<?php foreach ( $facts as $fact ) :
					if ( '' === (string) $fact['value'] ) { continue; }
				?>
					<div class="svc-glance__fact">
						<dt><?php echo esc_html( $fact['label'] ); ?></dt>
						<dd><?php echo esc_html( (string) $fact['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>

			<div class="svc-glance__foot">
				<?php if ( '' !== $turnaround && ( '' !== $turnaround_detail || '' !== $timeline_helper ) ) : ?>
					<div class="svc-glance__timeline">
						<?php if ( '' !== $turnaround_detail ) : ?>
							<p class="svc-glance__timeline-detail"><?php echo esc_html( $turnaround_detail ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $timeline_helper ) : ?>
							<p class="svc-glance__timeline-helper"><?php echo esc_html( $timeline_helper ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<p class="svc-glance__disclaimer"><?php echo esc_html( $disclaimer ); ?></p>
				<div class="cluster svc-glance__ctas">
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>">
						<?php esc_html_e( 'Get my quote', 'showtime-pools' ); ?>
					</a>
					<a class="btn btn--link" href="<?php echo esc_url( 'tel:' . $tel ); ?>">
						<?php echo esc_html( sprintf( /* translators: %s: phone number */ __( 'Or call %s', 'showtime-pools' ), $phone ) ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

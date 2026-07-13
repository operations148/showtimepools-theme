<?php
/**
 * Estimator strip — dark promo card linking out to EstimatorPro. No form
 * fields live here; it is a CTA card with a static "preview" example.
 * Lives inside the hero section (template-parts/home/section-01-hero.php),
 * just below the trust row — this file outputs the card only, no <section>
 * wrapper, so the hero controls placement/spacing.
 *
 * Every string is ACF-editable from Site Content → Page Copy → Estimator
 * Strip (option scope), same pattern as every other homepage section, so
 * nothing here is hardcoded beyond a sane fallback.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$opt = function_exists( 'get_field' ) ? 'option' : false;

$eyebrow     = $opt ? (string) get_field( 'estimator_eyebrow', $opt )     : '';
$headline    = $opt ? (string) get_field( 'estimator_headline', $opt )    : '';
$description = $opt ? (string) get_field( 'estimator_description', $opt ) : '';
$cta_label   = $opt ? (string) get_field( 'estimator_cta_label', $opt )   : '';
$cta_url     = $opt ? (string) get_field( 'estimator_cta_url', $opt )     : '';
$trust_line  = $opt ? (string) get_field( 'estimator_trust_line', $opt )  : '';

$preview_label   = $opt ? (string) get_field( 'estimator_preview_label', $opt )   : '';
$preview_service = $opt ? (string) get_field( 'estimator_preview_service', $opt ) : '';
$preview_range   = $opt ? (string) get_field( 'estimator_preview_range', $opt )   : '';
$preview_note    = $opt ? (string) get_field( 'estimator_preview_note', $opt )    : '';

$eyebrow     = '' !== $eyebrow     ? $eyebrow     : __( 'Fast & Free', 'showtime-pools' );
$headline    = '' !== $headline    ? $headline    : __( 'Get Your Pool Project Estimate in Minutes', 'showtime-pools' );
$description = '' !== $description ? $description : __( 'Answer a few quick questions and EstimatorPro will give you a clear starting price range for your pool service, repair, equipment upgrade, or remodel.', 'showtime-pools' );
$cta_label   = '' !== $cta_label   ? $cta_label   : __( 'Start Free Estimate', 'showtime-pools' );
$cta_url     = '' !== $cta_url     ? $cta_url     : showtime_estimator_url();
$trust_line  = '' !== $trust_line  ? $trust_line  : __( 'No pressure. No obligation. Takes less than 60 seconds.', 'showtime-pools' );

$preview_label   = '' !== $preview_label   ? $preview_label   : __( 'Estimated Range Preview', 'showtime-pools' );
$preview_service = '' !== $preview_service ? $preview_service : __( 'Pool Heater Repair', 'showtime-pools' );
$preview_range   = '' !== $preview_range   ? $preview_range   : __( '$4,000 – $8,000', 'showtime-pools' );
$preview_note    = '' !== $preview_note    ? $preview_note    : __( 'Based on selected service type', 'showtime-pools' );
?>
<div class="estimator-strip__card">

	<div class="estimator-strip__copy">
		<span class="eyebrow estimator-strip__eyebrow">
			<?php echo esc_html( $eyebrow ); ?>
			<span class="estimator-strip__eyebrow-line" aria-hidden="true"></span>
		</span>
		<h2 class="estimator-strip__title balance"><?php echo esc_html( $headline ); ?></h2>
		<p class="estimator-strip__lead"><?php echo esc_html( $description ); ?></p>
	</div>

	<div class="estimator-strip__panel">
		<div class="estimator-strip__preview">
			<span class="estimator-strip__preview-label"><?php echo esc_html( $preview_label ); ?></span>
			<span class="estimator-strip__preview-service"><?php echo esc_html( $preview_service ); ?></span>
			<span class="estimator-strip__preview-range"><?php echo esc_html( $preview_range ); ?></span>
			<span class="estimator-strip__preview-note"><?php echo esc_html( $preview_note ); ?></span>
		</div>

		<a class="btn btn--primary btn--lg estimator-strip__cta" href="<?php echo esc_url( $cta_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $cta_label ); ?>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
		</a>

		<?php if ( '' !== $trust_line ) : ?>
			<p class="estimator-strip__trust"><?php echo esc_html( $trust_line ); ?></p>
		<?php endif; ?>
	</div>

</div>

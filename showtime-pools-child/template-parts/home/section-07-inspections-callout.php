<?php
/**
 * Inspections callout — clean centered band. Single H2 + lead + dual CTA.
 * Brikly bones, main palette (no sub-brand color fork).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$opt = function_exists( 'get_field' ) ? 'option' : false;

$insp_title = $opt ? (string) get_field( 'inspections_cta_title', $opt ) : '';
$insp_body  = $opt ? (string) get_field( 'inspections_cta_body', $opt ) : '';
$insp_label = $opt ? (string) get_field( 'inspections_cta_label', $opt ) : '';

$insp_title = '' !== $insp_title ? $insp_title : __( 'Buying a house with a pool? We inspect it before you sign.', 'showtime-pools' );
$insp_body  = '' !== $insp_body  ? $insp_body  : __( 'Pre-purchase inspections, seasonal diagnostics, and equipment health checks. Written report in 24 hours so you negotiate with leverage — or walk away clean.', 'showtime-pools' );
$insp_label = '' !== $insp_label ? $insp_label : __( 'Book an Inspection', 'showtime-pools' );
?>
<section class="inspections-callout" data-reveal>
	<div class="container inspections-callout__inner">
		<span class="eyebrow inspections-callout__eyebrow">
			<?php esc_html_e( 'Showtime Pools Mechanics · Inspections', 'showtime-pools' ); ?>
		</span>

		<h2 class="inspections-callout__title balance">
			<?php echo esc_html( $insp_title ); ?>
		</h2>

		<p class="inspections-callout__lead">
			<?php echo esc_html( $insp_body ); ?>
		</p>

		<div class="cluster inspections-callout__ctas">
			<a class="btn btn--primary btn--lg btn--pill" href="<?php echo esc_url( home_url( '/pool-inspections/' ) ); ?>">
				<?php echo esc_html( $insp_label ); ?>
			</a>
			<a class="btn btn--ghost-on-dark btn--lg btn--pill" href="<?php echo esc_url( home_url( '/pool-inspections/pre-purchase-inspection/' ) ); ?>">
				<?php esc_html_e( 'See What\'s Covered', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</div>
	</div>
</section>

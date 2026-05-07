<?php
/**
 * Footer CTA tier — single-imperative closer that sits atop the main
 * footer columns. Lives inside <footer> so it always sits flush against
 * the rest of the footer with zero white seam between sections.
 *
 * Filterable via showtime/footer_cta to let landing variants override
 * the headline/lead/button without forking the file.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$cta = apply_filters(
	'showtime/footer_cta',
	array(
		'eyebrow'   => __( 'Ready to start', 'showtime-pools' ),
		'headline'  => __( 'Stop juggling contractors.', 'showtime-pools' ),
		'accent'    => __( 'Start with one team.', 'showtime-pools' ),
		'lead'      => __( 'Repairs, weekly service, remodels, and new equipment, end-to-end across LA. Tell us about your pool and Steve gets back inside one business day with an itemized written quote.', 'showtime-pools' ),
		'cta_label' => __( 'Get a Free Quote', 'showtime-pools' ),
		'cta_url'   => home_url( '/quote/' ),
		'fineprint' => __( 'Free site visit on jobs over $500. Itemized PDF, no surprise upcharges.', 'showtime-pools' ),
	)
);
?>
<section class="footer-cta" aria-label="<?php esc_attr_e( 'Get in touch', 'showtime-pools' ); ?>">
	<div class="footer-cta__pattern" aria-hidden="true"></div>
	<div class="container">
		<div class="footer-cta__inner">

			<div class="footer-cta__copy">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $cta['eyebrow'] ); ?></span>
				<h2 class="footer-cta__title balance">
					<?php echo esc_html( $cta['headline'] ); ?>
					<em><?php echo esc_html( $cta['accent'] ); ?></em>
				</h2>
				<p class="footer-cta__lead"><?php echo esc_html( $cta['lead'] ); ?></p>
			</div>

			<div class="footer-cta__action">
				<a class="btn btn--invert btn--lg footer-cta__btn" href="<?php echo esc_url( $cta['cta_url'] ); ?>">
					<?php echo esc_html( $cta['cta_label'] ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
				</a>
				<p class="footer-cta__fineprint"><?php echo esc_html( $cta['fineprint'] ); ?></p>
			</div>

		</div>
	</div>
</section>

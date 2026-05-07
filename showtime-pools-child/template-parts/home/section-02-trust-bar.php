<?php
/**
 * Trust pillars — Brikly-style floating card that overlaps the hero edge.
 * 3 pillars, hairline dividers, simple icons, founder voice.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$pillars = apply_filters(
	'showtime/home_trust_pillars',
	array(
		array(
			'icon'  => 'shield',
			'title' => __( 'Licensed & Insured', 'showtime-pools' ),
			'body'  => __( 'CSLB #985241 with $2M liability + full workers comp on every crew.', 'showtime-pools' ),
		),
		array(
			'icon'  => 'clock',
			'title' => __( 'On Time, On Budget', 'showtime-pools' ),
			'body'  => __( 'We respect your time with clear timelines and predictable pricing — no surprises.', 'showtime-pools' ),
		),
		array(
			'icon'  => 'sparkle',
			'title' => __( 'Quality Workmanship', 'showtime-pools' ),
			'body'  => __( 'Pentair + Jandy authorized service, PebbleTec certified — built right the first time.', 'showtime-pools' ),
		),
	)
);

$icons = array(
	'shield'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
	'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	'sparkle' => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/>',
);
?>
<section class="trust-pillars" data-reveal>
	<div class="container">
		<div class="trust-pillars__card">
			<?php foreach ( $pillars as $i => $p ) : ?>
				<div class="trust-pillars__cell">
					<span class="trust-pillars__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<?php echo $icons[ $p['icon'] ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG path strings ?>
						</svg>
					</span>
					<div class="trust-pillars__copy">
						<h3 class="trust-pillars__title"><?php echo esc_html( $p['title'] ); ?></h3>
						<p class="trust-pillars__body"><?php echo esc_html( $p['body'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="trust-pillars__credentials">
			<?php esc_html_e( 'CSLB License #985241  ·  EPA Lead-Safe Certified  ·  Pentair + Jandy Authorized  ·  PebbleTec Certified Applicator', 'showtime-pools' ); ?>
		</p>
	</div>
</section>

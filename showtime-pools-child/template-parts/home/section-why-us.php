<?php
/**
 * Why us — 5 trust pillars from the canonical site copy:
 * Licensed and experienced professionals, Clear timelines and transparent
 * pricing, High-quality materials and trusted manufacturers, California
 * and LA regulation compliance, Residential / commercial / luxury
 * project expertise.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$pillars_default = array(
	array(
		'icon'  => 'shield',
		'title' => __( 'In-house crew · 100% supervised', 'showtime-pools' ),
		'body'  => __( 'Trained professionals with deep field experience across construction, remodeling, equipment, and weekly service. The same W-2 team start to finish — and Steve personally supervises the exclusive partner crews we use for Replaster and demo.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'clock',
		'title' => __( 'Clear timelines, transparent pricing', 'showtime-pools' ),
		'body'  => __( 'Itemized written quotes, written schedules, and 24-hour reminders before crew visits. No verbal estimates, no surprise upcharges.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'gem',
		'title' => __( 'Quality materials, trusted brands', 'showtime-pools' ),
		'body'  => __( 'Pentair and Jandy authorized service. PebbleTec Certified Applicator. We install what we would put in our own pools.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'check',
		'title' => __( 'California & LA regulation compliance', 'showtime-pools' ),
		'body'  => __( 'Every permit, bonding inspection, and code requirement handled in-house. We pull permits at the LA County and Sherman Oaks counters ourselves.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'star',
		'title' => __( 'Residential, commercial, luxury', 'showtime-pools' ),
		'body'  => __( 'From weekly maintenance on a single-family home to ground-up luxury builds and commercial property service. One team, every scope.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'compass',
		'title' => __( 'Root-cause diagnosis, not patches', 'showtime-pools' ),
		'body'  => __( 'We identify the root cause first so you do not waste money on temporary fixes. Diagnostics are firewalled from the construction quote.', 'showtime-pools' ),
	),
);
$pillars = apply_filters( 'showtime/why_us_pillars', showtime_acf_rows( 'why_us_pillars', $pillars_default ) );

$icon = function ( string $key ): string {
	$svgs = array(
		'shield'  => '<path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5l-8-3z"/><path d="M9 12l2 2 4-4"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'gem'     => '<path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20"/><path d="M11 3l1 6 1-6"/>',
		'check'   => '<path d="M20 6L9 17l-5-5"/>',
		'star'    => '<path d="M12 2l2.6 6.3 6.8.5-5.2 4.5 1.6 6.7L12 16.5 6.2 20l1.6-6.7L2.6 8.8l6.8-.5z"/>',
		'compass' => '<circle cx="12" cy="12" r="9"/><path d="M16 8l-2 6-6 2 2-6 6-2z"/>',
	);
	$d = $svgs[ $key ] ?? $svgs['check'];
	return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};
?>
<section class="why-us" data-reveal>
	<div class="container">
		<header class="why-us__header">
			<span class="eyebrow"><em>05</em> &mdash; <?php esc_html_e( 'Why Us', 'showtime-pools' ); ?></span>
			<h2 class="why-us__title"><?php esc_html_e( 'Six reasons LA homeowners switch to us and stay.', 'showtime-pools' ); ?></h2>
			<p class="why-us__lead">
				<?php esc_html_e( 'A pool company should not feel like a series of contractors. Here is what we hold ourselves to, every project, every visit.', 'showtime-pools' ); ?>
			</p>
		</header>

		<div class="why-us__grid" data-stagger>
			<?php foreach ( $pillars as $p ) : ?>
				<article class="why-card">
					<div class="why-card__icon" aria-hidden="true"><?php echo $icon( (string) $p['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<h3 class="why-card__title"><?php echo esc_html( (string) $p['title'] ); ?></h3>
					<p class="why-card__body"><?php echo esc_html( (string) $p['body'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

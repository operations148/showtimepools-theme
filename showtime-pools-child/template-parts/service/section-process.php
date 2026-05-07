<?php
/**
 * Process — 4-step horizontal flow. Same for every service (consistency).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$steps = apply_filters(
	'showtime/service_process_steps',
	array(
		array(
			'n'     => '01',
			'title' => __( 'Free site visit', 'showtime-pools' ),
			'body'  => __( 'Steve or a senior tech walks the property, measures, photographs, and discusses scope with you in person.', 'showtime-pools' ),
		),
		array(
			'n'     => '02',
			'title' => __( 'Written quote', 'showtime-pools' ),
			'body'  => __( 'Itemized quote in 48 hours. No hidden line items, no upcharge surprises mid-job.', 'showtime-pools' ),
		),
		array(
			'n'     => '03',
			'title' => __( 'Schedule', 'showtime-pools' ),
			'body'  => __( 'Pick a start date that works for your household. We commit in writing and send a 24-hour reminder before each crew visit.', 'showtime-pools' ),
		),
		array(
			'n'     => '04',
			'title' => __( 'Execute & sign-off', 'showtime-pools' ),
			'body'  => __( 'Same W-2 crew start to finish. Daily photo updates. Final walk-through and warranty docs delivered before you pay the balance.', 'showtime-pools' ),
		),
	)
);
?>
<section class="svc-process section section--surface" data-reveal>
	<div class="container stack stack--lg">
		<header class="stack stack--sm">
			<span class="eyebrow"><?php esc_html_e( 'How we work', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'Four steps. Written commitments at every step.', 'showtime-pools' ); ?></h2>
		</header>

		<ol class="svc-process__list" role="list">
			<?php foreach ( $steps as $step ) : ?>
				<li class="svc-process__step">
					<span class="svc-process__num"><?php echo esc_html( $step['n'] ); ?></span>
					<h3 class="svc-process__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="svc-process__body"><?php echo esc_html( $step['body'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

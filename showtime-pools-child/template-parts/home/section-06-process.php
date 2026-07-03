<?php
/**
 * Process — 3-step Brikly flow per Showtime Mechanics canonical copy:
 * Request Free Assessment → We Assess And Provide Options → Expert Execution.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$steps_default = array(
	array(
		'n'     => '01',
		'title' => __( 'Request Your Free Assessment', 'showtime-pools' ),
		'body'  => __( 'Takes 30 seconds. Tell us what you need and your address so we can route you correctly. Phone, form, or text: all routes hit Steve\'s desk.', 'showtime-pools' ),
	),
	array(
		'n'     => '02',
		'title' => __( 'We Assess And Provide Options', 'showtime-pools' ),
		'body'  => __( 'Photo or virtual review, or onsite visit when needed. Then clear recommendations for next steps with itemized pricing. No verbal estimates, no pressure.', 'showtime-pools' ),
	),
	array(
		'n'     => '03',
		'title' => __( 'Expert Execution, Start to Finish', 'showtime-pools' ),
		'body'  => __( 'Pick the option you want. We handle the work, send daily updates, and walk through the punch list before you pay the balance.', 'showtime-pools' ),
	),
);
$steps = apply_filters( 'showtime/home_process_steps', showtime_acf_rows( 'process_steps', $steps_default ) );
?>
<section class="process section section--ink" data-reveal>
	<div class="container">
		<header class="process__header">
			<span class="eyebrow eyebrow--invert"><em>06</em> &mdash; <?php esc_html_e( 'How We Work', 'showtime-pools' ); ?></span>
			<h2 class="process__title"><?php esc_html_e( 'Three steps from "interested" to "done."', 'showtime-pools' ); ?></h2>
			<p class="process__lead"><?php esc_html_e( 'No drip campaigns, no junior reps, no pressure. Same team owns the job from the first call to the final walk-through.', 'showtime-pools' ); ?></p>
		</header>

		<div class="process__flow">
			<svg class="process__path" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
				<path d="M0 60 Q200 0 400 60 T800 60 T1200 60" stroke="rgba(92,138,158,0.5)" stroke-width="2" fill="none" stroke-dasharray="3 6"/>
			</svg>

			<ol class="process__steps">
				<?php foreach ( $steps as $step ) : ?>
					<li class="process__step">
						<span class="process__num"><?php echo esc_html( $step['n'] ); ?></span>
						<h3 class="process__step-title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="process__step-body"><?php echo esc_html( $step['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

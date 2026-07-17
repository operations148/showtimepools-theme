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
$reviewed   = function_exists( 'showtime_aeo_reviewed_date' ) ? showtime_aeo_reviewed_date() : '';

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
		</div>
	</div>
</section>

<?php
/**
 * Decision guide — a service-specific two-option comparison (e.g. repair vs
 * replace) with a responsive table: "choose this when", limitations, and
 * expected longevity where verified, plus a recommendation. All content is
 * per-service from the registry ($ctx['decision']); renders nothing without it.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx      = $GLOBALS['showtime_service_ctx'] ?? array();
$decision = (array) ( $ctx['decision'] ?? array() );
$a        = (array) ( $decision['a'] ?? array() );
$b        = (array) ( $decision['b'] ?? array() );

if ( '' === (string) ( $a['label'] ?? '' ) || '' === (string) ( $b['label'] ?? '' ) ) {
	return;
}

$heading = (string) ( $decision['heading'] ?? __( 'Which option is right for your pool?', 'showtime-pools' ) );
$intro   = (string) ( $decision['intro'] ?? '' );
$reco    = (string) ( $decision['reco'] ?? '' );

$render_when = static function ( $when ): string {
	$when = is_array( $when ) ? implode( '. ', array_filter( array_map( 'strval', $when ) ) ) : (string) $when;
	return $when;
};
?>
<section class="svc-decision section section--surface" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-decision__head">
			<span class="eyebrow"><?php esc_html_e( 'Decision guide', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( '' !== $intro ) : ?>
				<p class="svc-decision__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>

		<div class="svc-decision__table-wrap" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Comparison table', 'showtime-pools' ); ?>">
			<table class="svc-decision__table">
				<thead>
					<tr>
						<th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Consideration', 'showtime-pools' ); ?></span></th>
						<th scope="col"><?php echo esc_html( (string) $a['label'] ); ?></th>
						<th scope="col"><?php echo esc_html( (string) $b['label'] ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Choose this when', 'showtime-pools' ); ?></th>
						<td><?php echo esc_html( $render_when( $a['when'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( $render_when( $b['when'] ?? '' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Limitations', 'showtime-pools' ); ?></th>
						<td><?php echo esc_html( (string) ( $a['limits'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $b['limits'] ?? '' ) ); ?></td>
					</tr>
					<?php if ( '' !== (string) ( $a['longevity'] ?? '' ) || '' !== (string) ( $b['longevity'] ?? '' ) ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Expected longevity', 'showtime-pools' ); ?></th>
							<td><?php echo esc_html( (string) ( $a['longevity'] ?? '—' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $b['longevity'] ?? '—' ) ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php if ( '' !== $reco ) : ?>
			<p class="svc-decision__reco"><strong><?php esc_html_e( 'Our recommendation:', 'showtime-pools' ); ?></strong> <?php echo esc_html( $reco ); ?></p>
		<?php endif; ?>
	</div>
</section>

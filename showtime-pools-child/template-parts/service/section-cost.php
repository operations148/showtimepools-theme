<?php
/**
 * Cost guide — natural-language H2, a short direct answer, and a responsive
 * pricing table. Rows come straight from the registry ($ctx['cost']); any
 * row missing a price is dropped so no "TBD"/placeholder pricing ever shows
 * publicly. Renders nothing if there is no cost data at all.
 *
 * Pricing is real registry data only — nothing invented here.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx  = $GLOBALS['showtime_service_ctx'] ?? array();
$cost = (array) ( $ctx['cost'] ?? array() );
$rows = array_values( array_filter(
	(array) ( $cost['rows'] ?? array() ),
	static fn( $r ) => is_array( $r ) && '' !== (string) ( $r['type'] ?? '' ) && '' !== (string) ( $r['price'] ?? '' )
) );

if ( empty( $rows ) ) {
	return;
}

$heading = (string) ( $cost['heading'] ?? __( 'How much does this service cost in Los Angeles?', 'showtime-pools' ) );
$intro   = (string) ( $cost['intro'] ?? '' );
$disc    = (string) ( $cost['disclaimer'] ?? __( 'Pricing varies based on pool size, equipment condition, access, materials, permits, and the final scope confirmed during assessment.', 'showtime-pools' ) );
?>
<section class="svc-cost section" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-cost__head">
			<span class="eyebrow"><?php esc_html_e( 'Cost guide', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( '' !== $intro ) : ?>
				<p class="svc-cost__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>

		<div class="svc-cost__table-wrap" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Pricing table', 'showtime-pools' ); ?>">
			<table class="svc-cost__table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Service / project', 'showtime-pools' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Starting price', 'showtime-pools' ); ?></th>
						<th scope="col"><?php esc_html_e( 'What affects the price', 'showtime-pools' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Typical timeframe', 'showtime-pools' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $r ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( (string) $r['type'] ); ?></th>
							<td><?php echo esc_html( (string) $r['price'] ); ?></td>
							<td><?php echo esc_html( (string) ( $r['factors'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $r['timeframe'] ?? '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<p class="svc-cost__disclaimer"><?php echo esc_html( $disc ); ?></p>
	</div>
</section>

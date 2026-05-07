<?php
/**
 * "What's included" — 6-up check-bullet grid.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx      = $GLOBALS['showtime_service_ctx'] ?? array();
$includes = (array) ( $ctx['includes'] ?? array() );

if ( empty( $includes ) ) {
	return;
}
?>
<section class="svc-includes section" data-reveal>
	<div class="container stack stack--lg">
		<header class="stack stack--sm">
			<span class="eyebrow"><?php esc_html_e( 'What you get', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'Every job includes the following.', 'showtime-pools' ); ?></h2>
		</header>

		<ul class="svc-includes__grid" role="list">
			<?php foreach ( $includes as $item ) : ?>
				<li class="svc-includes__item">
					<svg class="svc-includes__check" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20 6L9 17l-5-5"/>
					</svg>
					<span><?php echo esc_html( (string) $item ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php
/**
 * Common problems / customer questions — 4–6 per-service items, each a natural
 * question (H3) with a direct first-sentence answer, likely causes, and the
 * appropriate next step. Distinct from the FAQ accordion (which stays as-is).
 * Content is per-service from the registry ($ctx['problems']).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx      = $GLOBALS['showtime_service_ctx'] ?? array();
$problems = array_values( array_filter(
	(array) ( $ctx['problems'] ?? array() ),
	static fn( $p ) => is_array( $p ) && '' !== (string) ( $p['q'] ?? '' ) && '' !== (string) ( $p['a'] ?? '' )
) );

if ( empty( $problems ) ) {
	return;
}

$heading = (string) ( $ctx['problems_heading'] ?? __( 'Common problems this service solves', 'showtime-pools' ) );
?>
<section class="svc-problems section" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-problems__head">
			<span class="eyebrow"><?php esc_html_e( 'Common problems', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php echo esc_html( $heading ); ?></h2>
		</header>

		<div class="svc-problems__grid">
			<?php foreach ( $problems as $p ) : ?>
				<article class="svc-problems__item">
					<h3 class="svc-problems__q"><?php echo esc_html( (string) $p['q'] ); ?></h3>
					<p class="svc-problems__a"><?php echo esc_html( (string) $p['a'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

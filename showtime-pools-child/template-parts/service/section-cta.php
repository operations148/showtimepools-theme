<?php
/**
 * Service close-out CTA — mirrors home section 11.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx   = $GLOBALS['showtime_service_ctx'] ?? array();
$title = (string) ( $ctx['title'] ?? __( 'your pool', 'showtime-pools' ) );
?>
<section class="cta-banner section section--brand" data-reveal>
	<div class="container">
		<div class="cta-banner__inner stack stack--md">
			<h2 class="balance" style="color:#fff">
				<?php
				/* translators: %s: service name */
				printf( esc_html__( 'Ready to talk about %s?', 'showtime-pools' ), esc_html( $title ) );
				?>
			</h2>
			<p class="lead" style="color:rgba(255,255,255,0.85);max-width:50ch">
				<?php esc_html_e( 'Free site visit for any job over $500. Same crew start to finish, no subcontractors, no surprise upcharges.', 'showtime-pools' ); ?>
			</p>
			<div class="cluster">
				<a class="btn btn--invert btn--lg" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a Free Quote', 'showtime-pools' ); ?></a>
				<a class="btn btn--ghost btn--lg" style="color:#fff;border-color:rgba(255,255,255,0.4)" href="<?php echo esc_url( home_url( '/book/' ) ); ?>"><?php esc_html_e( 'Book an Inspection', 'showtime-pools' ); ?></a>
			</div>
		</div>
	</div>
</section>

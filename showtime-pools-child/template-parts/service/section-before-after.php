<?php
/**
 * Before/after trust section — real-results proof band under "How we work".
 * Same layout for every service; copy and images key off the service slug so
 * each page reads as tailored without hand-written duplicate content. Photos
 * resolve through showtime_image() (slots service_{slug}_before/_after), so
 * dropping a real photo at assets/img/service_{slug}_before.jpg overrides the
 * placeholder with zero further code changes.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx   = $GLOBALS['showtime_service_ctx'] ?? array();
$slug  = (string) ( $ctx['slug'] ?? '' );
$title = (string) ( $ctx['title'] ?? '' );

if ( '' === $slug || ! function_exists( 'showtime_image' ) ) {
	return;
}

// Dedicated before/after photos aren't shot yet for most services, and the
// resolver's final fallback (random Picsum stock) reads as broken next to a
// "real results" claim. Until a real pair is dropped in at
// assets/img/service_{slug}_before|after.{ext}, default both sides to the
// service's own on-topic bundled photo instead.
$service_photo = showtime_image( 'service_' . $slug, 900 );
$has_local_photo = static function ( string $slot ): bool {
	foreach ( array( 'webp', 'avif', 'jpg', 'jpeg', 'png' ) as $ext ) {
		if ( file_exists( SHOWTIME_CHILD_DIR . "/assets/img/{$slot}.{$ext}" ) ) {
			return true;
		}
	}
	return false;
};
$before_img = $has_local_photo( 'service_' . $slug . '_before' ) ? showtime_image( 'service_' . $slug . '_before', 900 ) : $service_photo;
$after_img  = $has_local_photo( 'service_' . $slug . '_after' )  ? showtime_image( 'service_' . $slug . '_after', 900 )  : $service_photo;

$title_lc = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
?>
<section class="svc-before-after section" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-before-after__head">
			<span class="eyebrow"><?php esc_html_e( 'Real results', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php echo esc_html( sprintf( __( 'See the difference real %s makes.', 'showtime-pools' ), $title_lc ) ); ?></h2>
			<p class="svc-before-after__lead">
				<?php echo esc_html( sprintf( __( 'A look at the kind of transformation our crew delivers on every %s job.', 'showtime-pools' ), $title_lc ) ); ?>
			</p>
		</header>

		<div class="svc-before-after__grid">
			<figure class="svc-before-after__item">
				<img src="<?php echo esc_url( $before_img ); ?>" alt="<?php echo esc_attr( sprintf( __( '%s, before', 'showtime-pools' ), $title ) ); ?>" loading="lazy" decoding="async" width="900" height="675">
				<span class="svc-before-after__badge svc-before-after__badge--before"><?php esc_html_e( 'Before', 'showtime-pools' ); ?></span>
			</figure>
			<figure class="svc-before-after__item">
				<img src="<?php echo esc_url( $after_img ); ?>" alt="<?php echo esc_attr( sprintf( __( '%s, after', 'showtime-pools' ), $title ) ); ?>" loading="lazy" decoding="async" width="900" height="675">
				<span class="svc-before-after__badge svc-before-after__badge--after"><?php esc_html_e( 'After', 'showtime-pools' ); ?></span>
			</figure>
		</div>

		<div class="svc-before-after__cta">
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>">
				<?php echo esc_html( sprintf( __( 'See what %s can do for my pool', 'showtime-pools' ), $title_lc ) ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</div>
	</div>
</section>

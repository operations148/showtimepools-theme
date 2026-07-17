<?php
/**
 * Real projects — replaces the old before/after "Real results" band. Shows a
 * genuine completed-work before/after pair for this service, but ONLY when a
 * real photo pair actually exists on disk (assets/img/service_{slug}_before
 * and _after). If either is missing the section hides itself rather than
 * showing a stock/placeholder image next to a "real project" claim.
 *
 * Metadata (scope / materials) renders only when verified data is present in
 * the registry ($ctx['real_project']). Location, duration, completion date,
 * investment, customer names and testimonials are intentionally NOT invented
 * here — absent verified data, those fields simply do not appear.
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

$has_local_photo = static function ( string $slot ): bool {
	foreach ( array( 'webp', 'avif', 'jpg', 'jpeg', 'png' ) as $ext ) {
		if ( file_exists( SHOWTIME_CHILD_DIR . "/assets/img/{$slot}.{$ext}" ) ) {
			return true;
		}
	}
	return false;
};

// Verified pair required — no fallback stock imagery for a "real project".
if ( ! $has_local_photo( 'service_' . $slug . '_before' ) || ! $has_local_photo( 'service_' . $slug . '_after' ) ) {
	return;
}

$before_img = showtime_image( 'service_' . $slug . '_before', 900 );
$after_img  = showtime_image( 'service_' . $slug . '_after', 900 );
$title_lc   = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );

// Optional verified project detail (scope, materials). Only shown when present.
$proj     = (array) ( $ctx['real_project'] ?? array() );
$scope    = (string) ( $proj['scope'] ?? '' );
$material = (string) ( $proj['materials'] ?? '' );
$meta     = array_filter( array(
	__( 'Scope', 'showtime-pools' )               => $scope,
	__( 'Materials & equipment', 'showtime-pools' ) => $material,
) );
?>
<section class="svc-projects section" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-projects__head">
			<span class="eyebrow"><?php esc_html_e( 'Real projects', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php echo esc_html( sprintf( /* translators: %s: service name (lowercase) */ __( 'Real %s work by our crew.', 'showtime-pools' ), $title_lc ) ); ?></h2>
			<p class="svc-projects__lead">
				<?php echo esc_html( sprintf( /* translators: %s: service name (lowercase) */ __( 'An actual %s job completed by Showtime Pools in the Los Angeles area, shown before and after.', 'showtime-pools' ), $title_lc ) ); ?>
			</p>
		</header>

		<div class="svc-projects__grid">
			<figure class="svc-projects__item">
				<img src="<?php echo esc_url( $before_img ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: service name */ __( '%s project before, Los Angeles', 'showtime-pools' ), $title ) ); ?>" loading="lazy" decoding="async" width="900" height="675">
				<span class="svc-projects__badge svc-projects__badge--before"><?php esc_html_e( 'Before', 'showtime-pools' ); ?></span>
			</figure>
			<figure class="svc-projects__item">
				<img src="<?php echo esc_url( $after_img ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: service name */ __( '%s project after, Los Angeles', 'showtime-pools' ), $title ) ); ?>" loading="lazy" decoding="async" width="900" height="675">
				<span class="svc-projects__badge svc-projects__badge--after"><?php esc_html_e( 'After', 'showtime-pools' ); ?></span>
			</figure>
		</div>

		<?php if ( ! empty( $meta ) ) : ?>
			<dl class="svc-projects__meta">
				<?php foreach ( $meta as $label => $value ) : ?>
					<div><dt><?php echo esc_html( (string) $label ); ?></dt><dd><?php echo esc_html( (string) $value ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>

		<div class="svc-projects__cta">
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>">
				<?php echo esc_html( sprintf( /* translators: %s: service name (lowercase) */ __( 'Start my %s project', 'showtime-pools' ), $title_lc ) ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
			<a class="btn btn--link" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'See more projects', 'showtime-pools' ); ?></a>
		</div>
	</div>
</section>

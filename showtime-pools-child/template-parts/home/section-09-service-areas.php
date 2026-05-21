<?php
/**
 * Service areas — 6-neighborhood grid pulled from the Areas registry.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$areas = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();
?>
<section class="service-areas" data-reveal>
	<div class="container">
		<header class="service-areas__header">
			<div>
				<span class="eyebrow"><em>09</em> &mdash; <?php esc_html_e( 'Where We Work', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Six neighborhoods on the route. Pick yours.', 'showtime-pools' ); ?></h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>">
				<?php esc_html_e( 'All areas', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>

		<div class="service-areas__grid" data-stagger>
			<?php foreach ( $areas as $area ) :
				$slug    = (string) ( $area['slug'] ?? '' );
				$img_url = function_exists( 'showtime_image' ) ? showtime_image( 'area_' . $slug, 800 ) : '';
			?>
				<a class="area-card" href="<?php echo esc_url( home_url( '/service-areas/' . $slug . '/' ) ); ?>" style="--_area-grad: <?php echo esc_attr( $area['gradient'] ?? 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ); ?>">
					<?php if ( $img_url ) : ?>
						<img class="area-card__img" src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
					<div class="area-card__overlay" aria-hidden="true"></div>
					<div class="area-card__content">
						<span class="area-card__pill"><?php echo esc_html( (string) ( $area['pool_count'] ?? '' ) ); ?> <?php esc_html_e( 'pools', 'showtime-pools' ); ?></span>
						<h3 class="area-card__title"><?php echo esc_html( (string) ( $area['name'] ?? '' ) ); ?></h3>
						<p class="area-card__sub"><?php echo esc_html( (string) ( $area['tag'] ?? '' ) ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>

		<p class="service-areas__outside">
			<?php esc_html_e( 'Outside this zone? Construction, remodel, and inspection are still available across LA County. Weekly service is route-restricted to keep the same-tech promise.', 'showtime-pools' ); ?>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Ask anyway →', 'showtime-pools' ); ?></a>
		</p>
	</div>
</section>

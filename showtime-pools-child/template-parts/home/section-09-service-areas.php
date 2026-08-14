<?php
/**
 * Service areas — two auto-scrolling rows pulled from the Areas registry.
 * Row 1 drifts left→right, row 2 drifts right→left, both looping forever.
 * Each row pauses independently on hover; the other keeps moving. Each
 * row's card set is duplicated once in the DOM (the second copy is
 * aria-hidden) so the CSS animation loops seamlessly with no visible seam.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// The canonical 14-location card set (see inc/service-areas.php) — the same
// list, in the same order, that the /service-areas/ hub renders.
$areas = function_exists( 'showtime_service_area_cards' ) ? showtime_service_area_cards() : array();
// Split across the two marquee rows: first half (rounded up) drifts one way,
// the rest the other — 14 locations split to exactly 7 and 7. Each row's set
// is duplicated in the markup below so the loop stays seamless whatever the
// row count is.
$split = (int) ceil( count( $areas ) / 2 );
$row_1 = array_slice( $areas, 0, $split );
$row_2 = array_slice( $areas, $split );

/**
 * One marquee card.
 *
 * The duplicate copy exists only to make the CSS loop seamless: it is
 * aria-hidden, removed from the tab order, and its image alt is emptied, so
 * assistive technology and search engines encounter each location exactly once.
 */
$render_card = static function ( array $area, bool $duplicate = false ): void {
	$img_url = (string) ( $area['image'] ?? '' );
	?>
	<a
		class="area-card area-card--marquee"
		href="<?php echo esc_url( (string) ( $area['url'] ?? '' ) ); ?>"
		style="--_area-grad: <?php echo esc_attr( $area['gradient'] ?? 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ); ?>"
		<?php if ( $duplicate ) : ?>aria-hidden="true" tabindex="-1"<?php endif; ?>
	>
		<?php if ( '' !== $img_url ) : ?>
			<img class="area-card__img" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $duplicate ? '' : (string) ( $area['alt'] ?? '' ) ); ?>" loading="lazy" decoding="async" width="800" height="600">
		<?php endif; ?>
		<div class="area-card__overlay" aria-hidden="true"></div>
		<div class="area-card__content">
			<?php $card_count = (string) ( $area['pool_count'] ?? '' ); ?>
			<?php if ( '' !== $card_count ) : ?>
				<span class="area-card__pill"><?php echo esc_html( $card_count ); ?> <?php esc_html_e( 'pools', 'showtime-pools' ); ?></span>
			<?php endif; ?>
			<h3 class="area-card__title"><?php echo esc_html( (string) ( $area['name'] ?? '' ) ); ?></h3>
			<p class="area-card__sub"><?php echo esc_html( (string) ( $area['sub'] ?? '' ) ); ?></p>
		</div>
	</a>
	<?php
};
?>
<section class="service-areas" data-reveal>
	<div class="container">
		<header class="service-areas__header">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Where We Work', 'showtime-pools' ); ?></span>
				<h2 class="balance">
					<?php
					// Count stays derived so the heading cannot go stale, but the
					// wording no longer claims weekly-route coverage: five of the
					// locations shown have a published project page, not a service
					// route, and asserting "on the route" for them would be a claim.
					printf(
						/* translators: %s: number of service areas */
						esc_html__( 'Explore %s Los Angeles Service Areas', 'showtime-pools' ),
						esc_html( number_format_i18n( count( $areas ) ) )
					);
					?>
				</h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>">
				<?php esc_html_e( 'All areas', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>
	</div>

	<?php if ( ! empty( $row_1 ) || ! empty( $row_2 ) ) : ?>
		<div class="service-areas__marquee">
			<?php if ( ! empty( $row_1 ) ) : ?>
				<div class="service-areas__row service-areas__row--ltr">
					<div class="service-areas__track">
						<?php foreach ( $row_1 as $area ) { $render_card( $area ); } ?>
						<?php foreach ( $row_1 as $area ) { $render_card( $area, true ); } ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $row_2 ) ) : ?>
				<div class="service-areas__row service-areas__row--rtl">
					<div class="service-areas__track">
						<?php foreach ( $row_2 as $area ) { $render_card( $area ); } ?>
						<?php foreach ( $row_2 as $area ) { $render_card( $area, true ); } ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="container">
		<p class="service-areas__outside">
			<?php esc_html_e( 'Outside this zone? Construction, remodel, and inspection are still available across LA County. Weekly service is route-restricted to keep the same-tech promise.', 'showtime-pools' ); ?>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Ask anyway →', 'showtime-pools' ); ?></a>
		</p>
	</div>
</section>

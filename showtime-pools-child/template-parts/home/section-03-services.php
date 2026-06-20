<?php
/**
 * Services — editorial numbered index. No card boxes, no glyphs.
 * Hairline-divided rows: italic Playfair numeral · title · brief · CTA arrow.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
?>
<section class="services-index" data-reveal>
	<div class="container">

		<header class="services-index__header">
			<span class="eyebrow services-index__eyebrow">
				<em>02</em> &mdash; <?php esc_html_e( 'Services', 'showtime-pools' ); ?>
			</span>
			<h2 class="services-index__title balance">
				<?php esc_html_e( 'Twelve services. ', 'showtime-pools' ); ?>
				<em><?php esc_html_e( 'One supervised crew.', 'showtime-pools' ); ?></em>
			</h2>
			<p class="services-index__lead">
				<?php esc_html_e( 'From new construction to next Tuesday\'s chemistry, every job is staffed by the same W-2 crew on every job — supervised by Steve, end to end.', 'showtime-pools' ); ?>
			</p>
		</header>

		<div class="svc-carousel" data-carousel>
			<div class="svc-carousel__track" data-carousel-track role="group" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Services', 'showtime-pools' ); ?>">
			<?php
			$i = 1;
			foreach ( $services as $svc ) :
				$slug    = (string) ( $svc['slug'] ?? '' );
				$title   = (string) ( $svc['title'] ?? '' );
				$summary = (string) ( $svc['summary'] ?? '' );
				if ( ! $slug || ! $title ) { continue; }
				$num = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
			?>
				<a class="svc-tile" href="<?php echo esc_url( home_url( '/services/' . $slug . '/' ) ); ?>">
					<span class="svc-tile__num" aria-hidden="true"><?php echo esc_html( $num ); ?></span>
					<h3 class="svc-tile__title"><?php echo esc_html( $title ); ?></h3>
					<p class="svc-tile__blurb"><?php echo esc_html( wp_trim_words( $summary, 18, '…' ) ); ?></p>
					<span class="svc-tile__cta" aria-hidden="true">
						<?php esc_html_e( 'Learn more', 'showtime-pools' ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
					</span>
				</a>
			<?php $i++; endforeach; ?>
			</div>

			<div class="svc-carousel__nav">
				<button type="button" class="svc-carousel__btn" data-carousel-prev aria-label="<?php esc_attr_e( 'Previous services', 'showtime-pools' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 19l-7-7 7-7"/></svg>
				</button>
				<button type="button" class="svc-carousel__btn" data-carousel-next aria-label="<?php esc_attr_e( 'Next services', 'showtime-pools' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
				</button>
			</div>
		</div>

		<a class="btn btn--ghost btn--lg btn--pill services-index__all" href="<?php echo esc_url( home_url( '/services/' ) ); ?>">
			<?php esc_html_e( 'Browse all services', 'showtime-pools' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
		</a>
	</div>
</section>

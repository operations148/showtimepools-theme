<?php
/**
 * Template Name: Service Areas Hub
 *
 * /service-areas/ — lists all 6 neighborhoods with cross-links.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$areas = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();
?>
<main id="primary" class="site-main interior-page">

	<?php $areas_hero = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_4', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $areas_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $areas_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Service Areas', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Where we work', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Pool service near you in Los Angeles.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'Pool service, pool cleaning, and pool repair across six West Valley and Westside neighborhoods. The same in-house tech every week — Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills. Custom construction and remodel work runs LA County-wide.', 'showtime-pools' ); ?>
				</p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="areas-hub__grid">
				<?php foreach ( $areas as $area ) :
					$slug    = (string) $area['slug'];
					$img_url = function_exists( 'showtime_image' ) ? showtime_image( 'area_' . $slug, 800 ) : '';
				?>
					<a class="area-card area-card--lg" href="<?php echo esc_url( home_url( '/service-areas/' . $slug . '/' ) ); ?>" style="--_area-grad: <?php echo esc_attr( $area['gradient'] ); ?>">
						<?php if ( $img_url ) : ?>
							<img class="area-card__img" src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" decoding="async">
						<?php endif; ?>
						<div class="area-card__overlay" aria-hidden="true"></div>
						<div class="area-card__content">
							<span class="area-card__pill"><?php echo esc_html( (string) $area['pool_count'] ); ?> <?php esc_html_e( 'pools', 'showtime-pools' ); ?></span>
							<h3 class="area-card__title"><?php echo esc_html( (string) $area['name'] ); ?></h3>
							<p class="area-card__sub"><?php echo esc_html( (string) $area['tag'] ); ?></p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="areas-hub__outside">
				<h2><?php esc_html_e( 'Outside the route?', 'showtime-pools' ); ?></h2>
				<p>
					<?php esc_html_e( 'New construction, full remodels, and inspections are available across LA County — Hancock Park, Pacific Palisades, Calabasas, Burbank, Glendale, Pasadena, Toluca Lake, Northridge, Granada Hills, and beyond. Weekly service is geographically restricted to keep the same-tech promise.', 'showtime-pools' ); ?>
				</p>
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Ask about your address', 'showtime-pools' ); ?></a>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

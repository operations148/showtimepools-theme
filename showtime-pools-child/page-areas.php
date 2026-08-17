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

// The canonical 14-location card set (see inc/service-areas.php): the nine
// neighborhoods with a published area page, then the locations that only have
// a project page, each linking there rather than to an unpublished route.
$area_cards = function_exists( 'showtime_service_area_cards' ) ? showtime_service_area_cards() : array();

// ── Native WP fields (edit via WP Admin → Pages → Service Areas → Update) ──
$pid = get_the_ID();

$hero_h1      = get_the_title();
$hero_eyebrow = (string) get_post_meta( $pid, 'hero_eyebrow', true );
$hero_lead    = (string) get_post_meta( $pid, 'hero_lead',    true );
$outside_h2   = (string) get_post_meta( $pid, 'outside_h2',   true );
$outside_body = (string) get_post_meta( $pid, 'outside_body', true );

if ( '' === $hero_eyebrow ) { $hero_eyebrow = 'Where we work'; }
// Default lead. Names the weekly-route neighborhoods as exactly that, then
// covers the rest without implying they are on the same weekly routes — the
// grid below now spans every location we have completed work in.
if ( '' === $hero_lead )    { $hero_lead    = 'Pool service, pool cleaning, and pool repair across Los Angeles and the San Fernando Valley. Our weekly routes run with the same in-house tech every week in Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, Woodland Hills, West Hollywood, Bel Air, and Calabasas. Cleaning, repairs, equipment work, remodels, and custom construction are available across the areas below and LA County-wide.'; }
if ( '' === $outside_h2 )   { $outside_h2   = 'Outside the route?'; }
if ( '' === $outside_body ) { $outside_body = 'New construction, full remodels, and inspections are available across LA County: Hancock Park, Pacific Palisades, Burbank, Glendale, Pasadena, Toluca Lake, Northridge, Granada Hills, and beyond. Weekly service is geographically restricted to keep the same-tech promise.'; }
?>
<main id="primary" class="site-main interior-page">

	<?php $areas_hero = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_4', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $areas_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $areas_hero ); ?>" <?php echo showtime_hero_srcset_attr( 'lifestyle_4' ); ?> alt="<?php esc_attr_e( 'Showtime Pools serving Los Angeles and the San Fernando Valley', 'showtime-pools' ); ?>" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Service Areas', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="areas-hub__grid">
				<?php foreach ( $area_cards as $area_card ) : ?>
					<a class="area-card area-card--lg" href="<?php echo esc_url( (string) $area_card['url'] ); ?>" style="--_area-grad: <?php echo esc_attr( (string) $area_card['gradient'] ); ?>">
						<?php if ( '' !== (string) $area_card['image'] ) : ?>
							<img class="area-card__img" src="<?php echo esc_url( (string) $area_card['image'] ); ?>" alt="<?php echo esc_attr( (string) $area_card['alt'] ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
						<div class="area-card__overlay" aria-hidden="true"></div>
						<div class="area-card__content">
							<h3 class="area-card__title"><?php echo esc_html( (string) $area_card['name'] ); ?></h3>
							<p class="area-card__sub"><?php echo esc_html( (string) $area_card['sub'] ); ?></p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="areas-hub__outside">
				<h2><?php echo esc_html( $outside_h2 ); ?></h2>
				<p><?php echo esc_html( $outside_body ); ?></p>
				<div class="cluster cluster--center">
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Ask about your address', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost btn--lg" href="https://area.showtimepools.com/" target="_blank" rel="noopener"><?php esc_html_e( 'More areas', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

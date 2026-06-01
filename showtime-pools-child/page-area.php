<?php
/**
 * Template Name: Service Area
 *
 * Single neighborhood landing page. Resolves the area slug from post meta or
 * post slug, hydrates from the Areas registry, renders neighborhood-scoped
 * content + LocalBusiness areaServed schema.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$slug = (string) get_post_meta( get_the_ID(), '_showtime_area_slug', true );
if ( '' === $slug ) {
	$slug = get_post_field( 'post_name', get_the_ID() );
}

$area = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::get( $slug ) : null;

if ( ! $area ) {
	echo '<main id="primary" class="site-main interior-page"><div class="container" style="padding:6rem 0">';
	echo '<h1>' . esc_html( get_the_title() ) . '</h1>';
	echo '<p>' . esc_html__( 'This service area page is not yet configured.', 'showtime-pools' ) . '</p>';
	echo '</div></main>';
	get_footer();
	return;
}

$name        = (string) $area['name'];
$pool_count  = (string) $area['pool_count'];
$tag         = (string) $area['tag'];
// SEO H1 / intro take precedence over the natural lead when the registry
// provides them — keyword-led wording for organic search. Falls back to
// the original `lead` paragraph so older area entries still render.
$seo_h1      = (string) ( $area['seo_h1']    ?? '' );
$seo_intro   = (string) ( $area['seo_intro'] ?? '' );
$lead        = '' !== $seo_intro ? $seo_intro : (string) $area['lead'];

// ── Native WP overrides (edit via WP Admin → Pages → [area] → Update) ───────
$pid = get_the_ID();
$_pm = static fn( string $k ) => (string) get_post_meta( $pid, $k, true );

if ( '' !== $_pm( 'area_h1' ) )   { $seo_h1 = $_pm( 'area_h1' ); }
if ( '' !== $_pm( 'area_lead' ) ) { $lead   = $_pm( 'area_lead' ); }
$area_what_common = $_pm( 'area_what_common' );
$area_what_do     = $_pm( 'area_what_do' );
$chars       = (array)  ( $area['characteristics'] ?? array() );
$jobs        = (array)  ( $area['common_jobs'] ?? array() );
$streets     = (array)  ( $area['sample_streets'] ?? array() );
$gradient    = (string) ( $area['gradient'] ?? 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' );
$lat         = (float)  ( $area['lat'] ?? 0 );
$lng         = (float)  ( $area['lng'] ?? 0 );

$schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'Service',
	'@id'         => trailingslashit( get_permalink() ) . '#area-service',
	'name'        => sprintf( /* translators: %s: neighborhood */ __( 'Pool service in %s', 'showtime-pools' ), $name ),
	'provider'    => array( '@id' => home_url( '/#localbusiness' ) ),
	'areaServed'  => array(
		'@type' => 'Place',
		'name'  => $name . ', Los Angeles, CA',
		'geo'   => $lat && $lng ? array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => $lat,
			'longitude' => $lng,
		) : null,
	),
	'description' => $lead,
	'url'         => get_permalink(),
);
?>
<main id="primary" class="site-main interior-page">

	<?php $area_hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'area_' . $slug, 1600 ) : ''; ?>
	<section class="area-hero" data-reveal style="--_area-grad: <?php echo esc_attr( $gradient ); ?>">
		<?php if ( $area_hero_img ) : ?>
			<img class="area-hero__photo" src="<?php echo esc_url( $area_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="area-hero__bg" aria-hidden="true">
			<svg viewBox="0 0 800 400" preserveAspectRatio="none" fill="none">
				<g stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none" stroke-linecap="round">
					<path d="M0 250 Q150 220 300 250 T600 250 T900 250"/>
					<path d="M0 290 Q150 260 300 290 T600 290 T900 290"/>
					<path d="M0 330 Q150 300 300 330 T600 330 T900 330"/>
				</g>
			</svg>
		</div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>"><?php esc_html_e( 'Service Areas', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( $name ); ?></span>
			</nav>
			<div class="area-hero__inner">
				<span class="area-hero__pill"><?php echo esc_html( $pool_count ); ?> <?php esc_html_e( 'pools', 'showtime-pools' ); ?> · <?php echo esc_html( $tag ); ?></span>
				<h1 class="area-hero__title balance">
					<?php if ( '' !== $seo_h1 ) {
						echo esc_html( $seo_h1 );
					} else {
						/* translators: %s: neighborhood name */
						printf( esc_html__( 'Pool service in %s.', 'showtime-pools' ), esc_html( $name ) );
					} ?>
				</h1>
				<p class="area-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a quote', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/services/weekly-pool-maintenance/' ) ); ?>"><?php esc_html_e( 'Weekly service →', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="area-detail__grid">
				<div>
					<span class="eyebrow"><?php esc_html_e( 'Local conditions', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $area_what_common ? $area_what_common : sprintf( __( 'What %s pools have in common.', 'showtime-pools' ), $name ) ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $chars as $c ) : ?>
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
								<span><?php echo esc_html( (string) $c ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div>
					<span class="eyebrow"><?php esc_html_e( 'Common jobs in this area', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $area_what_do ? $area_what_do : __( 'What we do here most.', 'showtime-pools' ) ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $jobs as $j ) : ?>
							<li>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
								<span><?php echo esc_html( (string) $j ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<?php if ( ! empty( $streets ) ) : ?>
				<aside class="area-detail__streets">
					<span class="eyebrow"><?php esc_html_e( 'Recent streets we serviced', 'showtime-pools' ); ?></span>
					<ul class="tag-list">
						<?php foreach ( $streets as $s ) : ?>
							<li><span class="tag"><?php echo esc_html( (string) $s ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</aside>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/section-08-reviews' ); ?>

</main>
<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php get_footer();

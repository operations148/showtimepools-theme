<?php
/**
 * Template Name: Services Hub
 *
 * /services/ — full 12-service hub. Magazine-style cards grouped into
 * "Core" (first 6) and "Outdoor & Custom" (last 6). Service registry is
 * the single source of truth.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
// Core covers day-to-day services (repairs, maintenance, cleaning, equipment,
// inspections, automation, tile cleaning). Outdoor covers larger build /
// remodel / hardscape projects. Split scales to whatever the registry holds
// so adding a 13th+ service does not silently fall off the hub.
$core_count = 7;
$core       = array_slice( $services, 0, $core_count );
$outdoor    = array_slice( $services, $core_count );

// ── Native WP fields (edit via WP Admin → Pages → Services → Update) ─────
$pid = get_the_ID();

$hero_h1      = get_the_title();
$hero_eyebrow = (string) get_post_meta( $pid, 'hero_eyebrow', true );
$hero_lead    = (string) get_post_meta( $pid, 'hero_lead',    true );

$core_eyebrow = (string) get_post_meta( $pid, 'core_eyebrow', true );
$core_h2      = (string) get_post_meta( $pid, 'core_h2',      true );
$core_lead    = (string) get_post_meta( $pid, 'core_lead',    true );

$outdoor_eyebrow = (string) get_post_meta( $pid, 'outdoor_eyebrow', true );
$outdoor_h2      = (string) get_post_meta( $pid, 'outdoor_h2',      true );
$outdoor_lead    = (string) get_post_meta( $pid, 'outdoor_lead',    true );

// Fallbacks — used when the meta field is empty.
if ( '' === $hero_eyebrow ) { $hero_eyebrow = 'Twelve services, one team'; }
if ( '' === $hero_lead )    { $hero_lead    = 'Pool repair, pool cleaning service, pool remodeling, pool installation, equipment upgrades, inspections, spa work, and outdoor living — handled by one supervised crew across Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills.'; }

if ( '' === $core_eyebrow ) { $core_eyebrow = 'Core services'; }
if ( '' === $core_h2 )      { $core_h2      = 'Repairs, service, remodels, equipment, inspections, automation.'; }
if ( '' === $core_lead )    { $core_lead    = 'The day-to-day of pool ownership. Every one of these is on the route or in the shop right now.'; }

if ( '' === $outdoor_eyebrow ) { $outdoor_eyebrow = 'Outdoor living & custom'; }
if ( '' === $outdoor_h2 )      { $outdoor_h2      = 'New construction, spas, finishes, hardscape, kitchens, fire & water features.'; }
if ( '' === $outdoor_lead )    { $outdoor_lead    = 'The bigger projects. Same crew, same standards, with engineered structure and permitting handled in-house.'; }
?>
<main id="primary" class="site-main interior-page">

	<?php $services_hero = function_exists( 'showtime_image' ) ? showtime_image( 'project_1', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $services_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $services_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a Free Quote', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Book Assessment', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $core_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $core_h2 ); ?></h2>
				<p class="int-section__lead"><?php echo esc_html( $core_lead ); ?></p>
			</header>

			<div class="services-hub__grid">
				<?php foreach ( $core as $svc ) : ?>
					<a class="svc-hub-card" href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>">
						<h3 class="svc-hub-card__title"><?php echo esc_html( (string) $svc['title'] ); ?></h3>
						<p class="svc-hub-card__summary"><?php echo esc_html( (string) ( $svc['summary'] ?? '' ) ); ?></p>
						<dl class="svc-hub-card__meta">
							<?php if ( ! empty( $svc['default_price'] ) ) : ?>
								<div><dt><?php esc_html_e( 'Price', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $svc['default_price'] ); ?></dd></div>
							<?php endif; ?>
							<?php if ( ! empty( $svc['default_turnaround'] ) ) : ?>
								<div><dt><?php esc_html_e( 'Timeline', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $svc['default_turnaround'] ); ?></dd></div>
							<?php endif; ?>
						</dl>
						<span class="svc-hub-card__cta">
							<?php esc_html_e( 'See the details', 'showtime-pools' ); ?>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $outdoor_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $outdoor_h2 ); ?></h2>
				<p class="int-section__lead"><?php echo esc_html( $outdoor_lead ); ?></p>
			</header>

			<div class="services-hub__grid">
				<?php foreach ( $outdoor as $svc ) : ?>
					<a class="svc-hub-card" href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>">
						<h3 class="svc-hub-card__title"><?php echo esc_html( (string) $svc['title'] ); ?></h3>
						<p class="svc-hub-card__summary"><?php echo esc_html( (string) ( $svc['summary'] ?? '' ) ); ?></p>
						<dl class="svc-hub-card__meta">
							<?php if ( ! empty( $svc['default_price'] ) ) : ?>
								<div><dt><?php esc_html_e( 'Price', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $svc['default_price'] ); ?></dd></div>
							<?php endif; ?>
							<?php if ( ! empty( $svc['default_turnaround'] ) ) : ?>
								<div><dt><?php esc_html_e( 'Timeline', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $svc['default_turnaround'] ); ?></dd></div>
							<?php endif; ?>
						</dl>
						<span class="svc-hub-card__cta">
							<?php esc_html_e( 'See the details', 'showtime-pools' ); ?>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/section-why-us' ); ?>
	<?php get_template_part( 'template-parts/home/section-08-reviews' ); ?>

</main>
<?php get_footer();

<?php
/**
 * Template Name: Inspections Hub
 *
 * /pool-inspections/ — Showtime Pools Mechanics sub-brand. Charcoal + amber.
 * Lists the 3 inspection types from the Inspections registry.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$types = class_exists( '\\Showtime\\Inspections' ) ? \Showtime\Inspections::all() : array();
?>
<main id="primary" class="site-main interior-page interior-page--mechanics">

	<?php $insp_hero = function_exists( 'showtime_image' ) ? showtime_image( 'inspections_bg', 1920 ) : ''; ?>
	<section class="int-hero int-hero--mechanics int-hero--photo" data-reveal>
		<?php if ( $insp_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $insp_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Inspections', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="mechanics__brand">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/>
					</svg>
					<?php esc_html_e( 'Showtime Pools Mechanics', 'showtime-pools' ); ?>
				</span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Independent pool inspections.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'Pre-purchase, seasonal, and equipment-only inspections. Firewalled from the construction line so the report goes to you, not to a sales pipeline. We will tell you to walk away from a pool when walking away is the right call.', 'showtime-pools' ); ?>
				</p>
				<div class="cluster">
					<a class="btn btn--inspections btn--lg" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Schedule an inspection', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/pool-inspections/pre-purchase-inspection/' ) ); ?>"><?php esc_html_e( 'Pre-purchase details →', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'Three flavors', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Pick the inspection that fits the moment.', 'showtime-pools' ); ?></h2>
			</header>

			<div class="insp-grid">
				<?php foreach ( $types as $t ) : ?>
					<a class="insp-card" href="<?php echo esc_url( home_url( '/pool-inspections/' . $t['slug'] . '/' ) ); ?>">
						<header>
							<span class="insp-card__price"><?php echo esc_html( (string) $t['price'] ); ?></span>
							<h3 class="insp-card__title"><?php echo esc_html( (string) $t['name'] ); ?></h3>
							<p class="insp-card__short"><?php echo esc_html( (string) $t['short'] ); ?></p>
						</header>
						<dl class="insp-card__meta">
							<div><dt><?php esc_html_e( 'Duration', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $t['duration'] ); ?></dd></div>
							<div><dt><?php esc_html_e( 'Turnaround', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $t['turnaround'] ); ?></dd></div>
						</dl>
						<span class="insp-card__cta"><?php esc_html_e( 'See what is included →', 'showtime-pools' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'Why "Mechanics"', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'A separate line for inspections, by design.', 'showtime-pools' ); ?></h2>
			</header>
			<div class="prose-2col">
				<p>
					<?php esc_html_e( 'Most pool inspections are done by the same companies that want to win the resulting repair contract. That conflict shapes the report. We split inspections into a separate line called Showtime Pools Mechanics so the report comes from a different P&L than the construction quote.', 'showtime-pools' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'In practice this means we will document every fault, recommend repairs honestly, and tell you to walk away from a pool when walking away is the right call. Steve has personally killed deals worth tens of thousands of dollars in construction work because the pool was past saving. Independence is the whole point.', 'showtime-pools' ); ?>
				</p>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

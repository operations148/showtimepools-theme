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

// ── Native WP fields (edit via WP Admin → Pages → Pool Inspections → Update) ─
$pid = get_the_ID();

$hero_h1      = get_the_title();
$hero_lead    = (string) get_post_meta( $pid, 'hero_lead',     true );
$types_eyebrow = (string) get_post_meta( $pid, 'types_eyebrow', true );
$types_h2     = (string) get_post_meta( $pid, 'types_h2',      true );
$why_eyebrow  = (string) get_post_meta( $pid, 'why_eyebrow',  true );
$why_h2       = (string) get_post_meta( $pid, 'why_h2',       true );
$why_para1    = (string) get_post_meta( $pid, 'why_para1',    true );
$why_para2    = (string) get_post_meta( $pid, 'why_para2',    true );

if ( '' === $hero_lead )     { $hero_lead     = 'Pre-purchase, seasonal, and equipment-only inspections. Firewalled from the construction line so the report goes to you, not to a sales pipeline. We will tell you to walk away from a pool when walking away is the right call.'; }
if ( '' === $types_eyebrow ) { $types_eyebrow = 'Three flavors'; }
if ( '' === $types_h2 )      { $types_h2      = 'Pick the inspection that fits the moment.'; }
if ( '' === $why_eyebrow )   { $why_eyebrow   = 'Why "Mechanics"'; }
if ( '' === $why_h2 )        { $why_h2        = 'A separate line for inspections, by design.'; }
if ( '' === $why_para1 )     { $why_para1     = 'Most pool inspections are done by the same companies that want to win the resulting repair contract. That conflict shapes the report. We split inspections into a separate line called Showtime Pools Mechanics so the report comes from a different P&L than the construction quote.'; }
if ( '' === $why_para2 )     { $why_para2     = 'In practice this means we will document every fault, recommend repairs honestly, and tell you to walk away from a pool when walking away is the right call. Steve has personally killed deals worth tens of thousands of dollars in construction work because the pool was past saving. Independence is the whole point.'; }
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
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
				<div class="cluster">
					<a class="btn btn--inspections btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Schedule an inspection', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/pool-inspections/pre-purchase-inspection/' ) ); ?>"><?php esc_html_e( 'Pre-purchase details →', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $types_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $types_h2 ); ?></h2>
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
				<span class="eyebrow"><?php echo esc_html( $why_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $why_h2 ); ?></h2>
			</header>
			<div class="prose-2col">
				<p><?php echo esc_html( $why_para1 ); ?></p>
				<p><?php echo esc_html( $why_para2 ); ?></p>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

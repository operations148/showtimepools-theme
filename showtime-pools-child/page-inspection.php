<?php
/**
 * Template Name: Inspection Detail
 *
 * Single inspection-type landing page. Sub-brand visual treatment.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$slug = (string) get_post_meta( get_the_ID(), '_showtime_inspection_slug', true );
if ( '' === $slug ) {
	$slug = get_post_field( 'post_name', get_the_ID() );
}

$insp = class_exists( '\\Showtime\\Inspections' ) ? \Showtime\Inspections::get( $slug ) : null;

if ( ! $insp ) {
	echo '<main id="primary" class="site-main interior-page"><div class="container" style="padding:6rem 0">';
	echo '<h1>' . esc_html( get_the_title() ) . '</h1>';
	echo '<p>' . esc_html__( 'This inspection page is not yet configured.', 'showtime-pools' ) . '</p>';
	echo '</div></main>';
	get_footer();
	return;
}

$name         = (string) $insp['name'];
$short        = (string) $insp['short'];
$price        = (string) $insp['price'];
$turnaround   = (string) $insp['turnaround'];
$duration     = (string) $insp['duration'];
$lead         = (string) $insp['lead'];

// ── Native WP overrides (edit via WP Admin → Pages → [inspection] → Update) ─
$pid = get_the_ID();
$_pm = static fn( string $k ) => (string) get_post_meta( $pid, $k, true );

if ( '' !== $_pm( 'insp_h1' ) )   { $name = $_pm( 'insp_h1' ); }
if ( '' !== $_pm( 'insp_lead' ) ) { $lead = $_pm( 'insp_lead' ); }
$insp_who_h2     = $_pm( 'insp_who_h2' );
$insp_what_h2    = $_pm( 'insp_what_h2' );
$insp_process_h2 = $_pm( 'insp_process_h2' );
$insp_faq_h2     = $_pm( 'insp_faq_h2' );
$who_for      = (array)  ( $insp['who_for'] ?? array() );
$deliverables = (array)  ( $insp['deliverables'] ?? array() );
$process      = (array)  ( $insp['process'] ?? array() );
$faqs         = (array)  ( $insp['faqs'] ?? array() );

$phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$tel   = preg_replace( '/[^0-9+]/', '', $phone );

$service_schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'Service',
	'@id'         => trailingslashit( get_permalink() ) . '#service',
	'name'        => $name,
	'description' => $lead,
	'serviceType' => 'Pool Inspection',
	'provider'    => array( '@id' => home_url( '/#organization' ) ),
	'offers'      => array(
		'@type'         => 'Offer',
		'description'   => $price,
		'priceCurrency' => 'USD',
		'availability'  => 'https://schema.org/InStock',
		'url'           => get_permalink(),
	),
	'url'         => get_permalink(),
);

$faq_schema = ! empty( $faqs ) ? array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'@id'        => trailingslashit( get_permalink() ) . '#faqs',
	'mainEntity' => array_map(
		static fn( $f ) => array(
			'@type'          => 'Question',
			'name'           => (string) ( $f['q'] ?? '' ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => (string) ( $f['a'] ?? '' ) ),
		),
		$faqs
	),
) : null;
?>
<main id="primary" class="site-main interior-page interior-page--mechanics">

	<section class="int-hero int-hero--mechanics" data-reveal>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/pool-inspections/' ) ); ?>"><?php esc_html_e( 'Inspections', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( $name ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="mechanics__brand"><?php echo esc_html( $short ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $name ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $lead ); ?></p>

				<div class="insp-detail__pair">
					<div><span class="insp-detail__label"><?php esc_html_e( 'Investment', 'showtime-pools' ); ?></span><span class="insp-detail__value"><?php echo esc_html( $price ); ?></span></div>
					<div><span class="insp-detail__label"><?php esc_html_e( 'Time on-site', 'showtime-pools' ); ?></span><span class="insp-detail__value"><?php echo esc_html( $duration ); ?></span></div>
					<div><span class="insp-detail__label"><?php esc_html_e( 'Report', 'showtime-pools' ); ?></span><span class="insp-detail__value"><?php echo esc_html( $turnaround ); ?></span></div>
				</div>

				<div class="cluster">
					<a class="btn btn--inspections btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>"><?php esc_html_e( 'Book this inspection', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( 'tel:' . $tel ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'showtime-pools' ), $phone ) ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<div class="prose-2col">
				<div>
					<span class="eyebrow"><?php esc_html_e( 'Who this is for', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $insp_who_h2 ? $insp_who_h2 : __( 'Right inspection, right moment.', 'showtime-pools' ) ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $who_for as $w ) : ?>
							<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><span><?php echo esc_html( (string) $w ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div>
					<span class="eyebrow"><?php esc_html_e( 'What you get', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $insp_what_h2 ? $insp_what_h2 : __( 'Every inspection includes the following.', 'showtime-pools' ) ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $deliverables as $d ) : ?>
							<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><span><?php echo esc_html( (string) $d ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $process ) ) : ?>
		<section class="int-section" data-reveal>
			<div class="container">
				<header class="int-section__head">
					<span class="eyebrow"><?php esc_html_e( 'How it works', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $insp_process_h2 ? $insp_process_h2 : __( 'Four steps from booking to written report.', 'showtime-pools' ) ); ?></h2>
				</header>
				<ol class="step-grid">
					<?php foreach ( $process as $p ) : ?>
						<li class="step-grid__step">
							<span class="step-grid__num"><?php echo esc_html( (string) $p['n'] ); ?></span>
							<h3><?php echo esc_html( (string) $p['title'] ); ?></h3>
							<p><?php echo esc_html( (string) $p['body'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $faqs ) ) : ?>
		<section class="int-section int-section--cream" data-reveal>
			<div class="container" style="max-width:var(--container-narrow)">
				<header class="int-section__head">
					<span class="eyebrow"><?php esc_html_e( 'FAQ', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( '' !== $insp_faq_h2 ? $insp_faq_h2 : __( 'Common questions about this inspection.', 'showtime-pools' ) ); ?></h2>
				</header>
				<div class="faq__list">
					<?php foreach ( $faqs as $i => $f ) : ?>
						<details class="faq__item"<?php echo 0 === $i ? ' open' : ''; ?>>
							<summary class="faq__q">
								<span><?php echo esc_html( (string) $f['q'] ); ?></span>
								<svg class="faq__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
							</summary>
							<div class="faq__a"><?php echo wp_kses_post( wpautop( (string) $f['a'] ) ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>


</main>
<script type="application/ld+json"><?php echo wp_json_encode( $service_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php if ( $faq_schema ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endif; ?>
<?php get_footer();

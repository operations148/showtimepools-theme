<?php
/**
 * Hero — left-aligned, asymmetric, Brikly-aligned. Full-bleed photo behind
 * the dark gradient, copy stack on the left:
 *
 *   - eyebrow chip with pulsing aqua dot
 *   - 2-line H1, weight contrast (bold + medium accent), no italic
 *   - lead paragraph
 *   - dual CTA: primary aqua + ghost-on-dark
 *   - 3-stat strip: years / pools / rating (proof at the fold)
 *   - locale row
 *
 * Trust pillars overlap card lives in section-02-trust-bar.php and bridges
 * onto this hero's bottom edge via negative margin.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// ACF Page Copy → progressive enhancement: every string here is editable
// from WP admin (Site Content → Page Copy → Hero block). PHP fallbacks
// keep the page hard-locked if ACF is ever deactivated.
$opt = function_exists( 'get_field' ) ? 'option' : false;

$pc_title    = $opt ? (string) get_field( 'hero_title', $opt ) : '';
$pc_accent   = $opt ? (string) get_field( 'hero_accent_line', $opt ) : '';
$pc_lead     = $opt ? (string) get_field( 'hero_lead', $opt ) : '';
$pc_cta1_lbl = $opt ? (string) get_field( 'hero_cta_label', $opt ) : '';
$pc_cta1_url = $opt ? (string) get_field( 'hero_cta_url', $opt ) : '';
$pc_cta2_lbl = $opt ? (string) get_field( 'hero_secondary_label', $opt ) : '';
$pc_cta2_url = $opt ? (string) get_field( 'hero_secondary_url', $opt ) : '';
$pc_alt      = $opt ? (string) get_field( 'hero_image_alt', $opt ) : '';
$pc_img      = $opt ? get_field( 'hero_image', $opt ) : null;

$hero_title  = '' !== $pc_title  ? $pc_title  : __( 'Stop juggling contractors.', 'showtime-pools' );
$hero_accent = '' !== $pc_accent ? $pc_accent : __( 'One team handles it all.', 'showtime-pools' );
$hero_lead   = '' !== $pc_lead   ? $pc_lead   : __( 'Repairs, weekly service, remodels, and new equipment, end-to-end across Los Angeles. Direct from the people who actually do the work, with itemized written quotes inside one business day.', 'showtime-pools' );
$cta1_label  = '' !== $pc_cta1_lbl ? $pc_cta1_lbl : __( 'Get a Free Quote', 'showtime-pools' );
$cta1_url    = '' !== $pc_cta1_url ? $pc_cta1_url : home_url( '/quote/' );
$cta2_label  = '' !== $pc_cta2_lbl ? $pc_cta2_lbl : __( 'See our work', 'showtime-pools' );
$cta2_url    = '' !== $pc_cta2_url ? $pc_cta2_url : home_url( '/projects/' );

// Per-page ACF image override beats bundled photo beats CDN.
if ( is_array( $pc_img ) && ! empty( $pc_img['url'] ) ) {
	$hero_url = $pc_img['sizes']['large'] ?? $pc_img['url'];
	$hero_sm  = $pc_img['sizes']['medium_large'] ?? $hero_url;
} else {
	$hero_url = function_exists( 'showtime_image' ) ? showtime_image( 'hero', 1920 ) : '';
	$hero_sm  = function_exists( 'showtime_image' ) ? showtime_image( 'hero', 1024 ) : $hero_url;
}

// Trust numerals — sourced from Page Copy (cslb/insurance) so the schema,
// the about page, the hero, and the trust-bar all read from one place.
$cslb_num   = $opt ? (string) get_field( 'cslb_license_number', $opt ) : '';
$insurance  = $opt ? (string) get_field( 'insurance_amount', $opt ) : '';
$cslb_num   = '' !== $cslb_num  ? $cslb_num  : '985241';
$insurance  = '' !== $insurance ? $insurance : '$2M';
?>
<section class="home-hero" data-reveal>
	<picture class="home-hero__photo">
		<source media="(min-width:961px)" srcset="<?php echo esc_url( $hero_url ); ?>">
		<img src="<?php echo esc_url( $hero_sm ); ?>" alt="<?php echo esc_attr( $pc_alt ); ?>" fetchpriority="high" decoding="async">
	</picture>

	<div class="home-hero__pattern" aria-hidden="true"></div>
	<div class="home-hero__overlay" aria-hidden="true"></div>

	<div class="container">
		<div class="home-hero__inner">

		<span class="home-hero__chip">
			<span class="home-hero__chip-dot" aria-hidden="true"></span>
			<strong><?php esc_html_e( 'Showtime Pools', 'showtime-pools' ); ?></strong>
			<span aria-hidden="true">·</span>
			<?php esc_html_e( 'Sherman Oaks, Los Angeles', 'showtime-pools' ); ?>
		</span>

		<h1 class="home-hero__title">
			<span class="home-hero__title-line"><?php echo esc_html( $hero_title ); ?></span>
			<span class="home-hero__title-accent"><?php echo esc_html( $hero_accent ); ?></span>
		</h1>

		<p class="home-hero__lead">
			<?php echo esc_html( $hero_lead ); ?>
		</p>

		<div class="cluster home-hero__ctas">
			<a class="btn btn--primary btn--lg home-hero__cta-primary" href="<?php echo esc_url( $cta1_url ); ?>">
				<?php echo esc_html( $cta1_label ); ?>
			</a>
			<a class="btn btn--ghost-on-dark btn--lg home-hero__cta-secondary" href="<?php echo esc_url( $cta2_url ); ?>">
				<?php echo esc_html( $cta2_label ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</div>

		<dl class="home-hero__stats" aria-label="<?php esc_attr_e( 'Track record', 'showtime-pools' ); ?>">
			<div class="home-hero__stat">
				<dt><?php esc_html_e( 'CSLB Licensed', 'showtime-pools' ); ?></dt>
				<dd>#<?php echo esc_html( $cslb_num ); ?></dd>
			</div>
			<div class="home-hero__stat">
				<dt><?php esc_html_e( 'Subcontractors', 'showtime-pools' ); ?></dt>
				<dd>0<span aria-hidden="true"> / W-2 crew</span></dd>
			</div>
			<div class="home-hero__stat">
				<dt><?php esc_html_e( 'Insurance', 'showtime-pools' ); ?></dt>
				<dd><?php echo esc_html( $insurance ); ?><span aria-hidden="true"> liability</span></dd>
			</div>
		</dl>

		<p class="home-hero__locale">
			<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>
			</svg>
			<?php esc_html_e( 'Sherman Oaks · Encino · Beverly Hills · Studio City · Tarzana · Woodland Hills', 'showtime-pools' ); ?>
		</p>
		</div><!-- /.home-hero__inner -->
	</div><!-- /.container -->

	<svg class="home-hero__curve" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
		<!-- Curve goes UP at center (smile shape): cream paints the corners
		     so the hero photo bulges down in the middle, creating a soft
		     rounded-bottom hero edge that flows into the next section. -->
		<path d="M0,80 L0,0 Q720,80 1440,0 L1440,80 Z" fill="var(--c-bg)"/>
	</svg>
</section>

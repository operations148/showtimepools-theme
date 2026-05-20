<?php
/**
 * Hero — Poolax-aligned 2-column layout (Phase G rebuild).
 *
 * Light background, copy LEFT, photo card RIGHT inside a soft blue
 * circle backdrop, downward sweeping curve at the bottom that flows
 * into the About section below.
 *
 * No license/subcontractor/insurance trio in the hero (Phase G removed
 * those at user's request — they live in the LocalBusiness JSON-LD and
 * the small footer copyright). No locale row in the hero either — the
 * service areas live in the dedicated /service-areas/ hub + footer.
 *
 * Every label is editable from Site Content → Page Copy → Hero block.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

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

// CRO + SEO defaults — H1 is tight, keyword-led, no filler. Drop the
// ", done by one team." tail per user (no search intent value). The
// service surface lives in the eyebrow ("POOL CLEANING & SERVICES")
// and in the blue accent line that runs the four pillars.
$hero_title  = '' !== $pc_title  ? $pc_title  : __( 'Pool service in Los Angeles.', 'showtime-pools' );
$hero_accent = '' !== $pc_accent ? $pc_accent : __( 'Repairs, cleaning, remodels, equipment.', 'showtime-pools' );
$hero_lead   = '' !== $pc_lead   ? $pc_lead   : __( 'Showtime Pools handles every part of your pool — weekly service, repairs, resurfacing, equipment swaps, and new construction — with one in-house crew. Itemized written quote inside one business day.', 'showtime-pools' );
$cta1_label  = '' !== $pc_cta1_lbl ? $pc_cta1_lbl : __( 'Get a Free Quote', 'showtime-pools' );
$cta1_url    = '' !== $pc_cta1_url ? $pc_cta1_url : home_url( '/quote/' );
$cta2_label  = '' !== $pc_cta2_lbl ? $pc_cta2_lbl : __( 'See our work', 'showtime-pools' );
$cta2_url    = '' !== $pc_cta2_url ? $pc_cta2_url : home_url( '/projects/' );

// Per-page ACF image override beats bundled photo beats CDN.
if ( is_array( $pc_img ) && ! empty( $pc_img['url'] ) ) {
	$hero_url = $pc_img['sizes']['large'] ?? $pc_img['url'];
	$hero_sm  = $pc_img['sizes']['medium_large'] ?? $hero_url;
} else {
	$hero_url = function_exists( 'showtime_image' ) ? showtime_image( 'hero', 1400 ) : '';
	$hero_sm  = function_exists( 'showtime_image' ) ? showtime_image( 'hero', 960 ) : $hero_url;
}

$eyebrow_text = ( '' !== ( $opt ? (string) get_field( 'hero_eyebrow', $opt ) : '' ) )
	? (string) get_field( 'hero_eyebrow', $opt )
	: __( 'POOL CLEANING & SERVICES', 'showtime-pools' );
?>
<section class="home-hero home-hero--poolax" data-reveal>
	<div class="home-hero__bg" aria-hidden="true"></div>
	<div class="home-hero__grain" aria-hidden="true"></div>

	<div class="container">
		<div class="home-hero__grid">

			<div class="home-hero__copy">
				<span class="home-hero__eyebrow">
					<span class="home-hero__chip-dot" aria-hidden="true"></span>
					<?php echo esc_html( $eyebrow_text ); ?>
					<span class="home-hero__eyebrow-line" aria-hidden="true"></span>
				</span>

				<h1 class="home-hero__title balance">
					<span class="home-hero__title-line"><?php echo esc_html( $hero_title ); ?></span>
					<span class="home-hero__title-accent"><?php echo esc_html( $hero_accent ); ?></span>
				</h1>

				<p class="home-hero__lead">
					<?php echo esc_html( $hero_lead ); ?>
				</p>

				<div class="cluster home-hero__ctas">
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $cta1_url ); ?>">
						<?php echo esc_html( $cta1_label ); ?>
					</a>
					<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( $cta2_url ); ?>">
						<?php echo esc_html( $cta2_label ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
					</a>
				</div>
			</div>

			<div class="home-hero__visual" aria-hidden="true">
				<span class="home-hero__circle"></span>

				<div class="home-hero__badge" aria-hidden="true">
					<svg viewBox="0 0 200 200">
						<defs>
							<path id="home-hero-badge-arc" d="M 100 100 m -82 0 a 82 82 0 1 1 164 0 a 82 82 0 1 1 -164 0"></path>
						</defs>
						<text font-size="11" font-weight="700" letter-spacing="3.5" fill="currentColor">
							<textPath href="#home-hero-badge-arc">POOL CLEANING · POOL REPAIR · REMODELS · INSPECTIONS · </textPath>
						</text>
					</svg>
				</div>

				<span class="home-hero__dot home-hero__dot--top" aria-hidden="true"></span>
				<span class="home-hero__dot home-hero__dot--bot" aria-hidden="true"></span>
				<div class="home-hero__photo">
					<picture>
						<source media="(min-width:961px)" srcset="<?php echo esc_url( $hero_url ); ?>">
						<img src="<?php echo esc_url( $hero_sm ); ?>" alt="<?php echo esc_attr( $pc_alt ); ?>" fetchpriority="high" decoding="async">
					</picture>
				</div>
			</div>

		</div>
	</div>

	<div class="home-hero__scroll" aria-hidden="true">
		<svg viewBox="0 0 100 100">
			<defs>
				<path id="home-hero-scroll-arc" d="M 50 50 m -34 0 a 34 34 0 1 1 68 0 a 34 34 0 1 1 -68 0"></path>
			</defs>
			<text font-size="10" font-weight="600" letter-spacing="3.5" fill="currentColor">
				<textPath href="#home-hero-scroll-arc">SCROLL · SCROLL · SCROLL · SCROLL · </textPath>
			</text>
			<path d="M50 38 L50 60 M44 54 L50 60 L56 54" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</div>
</section>

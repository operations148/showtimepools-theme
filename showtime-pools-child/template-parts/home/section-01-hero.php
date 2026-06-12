<?php
/**
 * Hero — full-bleed photo with dark overlay, white text laid over (Poolco
 * reference, Phase K rebuild). No side-by-side photo card, no rotating
 * badge, no scroll indicator. One primary CTA, one secondary text link.
 *
 * Copy comes from Site Content → Page Copy → Hero block (ACF).
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

$hero_title  = '' !== $pc_title  ? $pc_title  : __( 'Pool service in Los Angeles.', 'showtime-pools' );
$hero_accent = '' !== $pc_accent ? $pc_accent : __( 'Repairs, cleaning, remodels, equipment.', 'showtime-pools' );
$hero_lead   = '' !== $pc_lead   ? $pc_lead   : __( 'Expert pool repair, cleaning, remodeling, and equipment installation for Los Angeles homes.', 'showtime-pools' );
$cta1_label  = '' !== $pc_cta1_lbl ? $pc_cta1_lbl : __( 'Book an Appointment', 'showtime-pools' );
$cta1_url    = '' !== $pc_cta1_url ? $pc_cta1_url : showtime_booking_url();
$cta2_label  = '' !== $pc_cta2_lbl ? $pc_cta2_lbl : __( 'See our work', 'showtime-pools' );
$cta2_url    = '' !== $pc_cta2_url ? $pc_cta2_url : home_url( '/projects/' );

// Resolution shared with the LCP preload hook in inc/performance.php.
$hero_pair = function_exists( 'showtime_front_hero_urls' ) ? showtime_front_hero_urls() : array( 'desktop' => '', 'mobile' => '' );
$hero_url  = $hero_pair['desktop'];
$hero_sm   = $hero_pair['mobile'];

$eyebrow_text = ( '' !== ( $opt ? (string) get_field( 'hero_eyebrow', $opt ) : '' ) )
	? (string) get_field( 'hero_eyebrow', $opt )
	: __( 'YOUR POOL, OUR PRIORITY', 'showtime-pools' );
?>
<section class="home-hero home-hero--immersive" data-reveal>
	<picture class="home-hero__bgphoto">
		<source media="(min-width:961px)" srcset="<?php echo esc_url( $hero_url ); ?>">
		<img src="<?php echo esc_url( $hero_sm ); ?>" alt="<?php echo esc_attr( $pc_alt ); ?>" fetchpriority="high" decoding="async">
	</picture>
	<div class="home-hero__veil" aria-hidden="true"></div>

	<div class="container">
		<div class="home-hero__inner">
			<span class="home-hero__eyebrow">
				<?php echo esc_html( $eyebrow_text ); ?>
				<span class="home-hero__eyebrow-line" aria-hidden="true"></span>
			</span>

			<h1 class="home-hero__title balance">
				<?php echo esc_html( $hero_title ); ?>
			</h1>

			<p class="home-hero__lead">
				<?php echo esc_html( $hero_lead ); ?>
			</p>

			<div class="home-hero__ctas">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $cta1_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $cta1_label ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
				</a>
				<a class="home-hero__link" href="<?php echo esc_url( $cta2_url ); ?>">
					<?php echo esc_html( $cta2_label ); ?>
				</a>
			</div>
		</div>
	</div>

</section>

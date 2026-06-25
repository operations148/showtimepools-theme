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

// Responsive hero image (src + srcset + sizes), shared with the LCP preload in
// inc/performance.php so the preloaded candidate matches what paints.
$hero_img = function_exists( 'showtime_front_hero_image' )
	? showtime_front_hero_image()
	: array( 'src' => '', 'srcset' => '', 'sizes' => '100vw', 'width' => 1920, 'height' => 1080 );
$hero_url = $hero_img['src'];

$eyebrow_text = ( '' !== ( $opt ? (string) get_field( 'hero_eyebrow', $opt ) : '' ) )
	? (string) get_field( 'hero_eyebrow', $opt )
	: __( 'YOUR POOL, OUR PRIORITY', 'showtime-pools' );
?>
<?php
// Optional background video (Site Content → Homepage → Hero video URL). The
// poster is the hero_poster slot, which defaults to the hero still, so the LCP
// preload in inc/performance.php stays valid. Mobile shows the poster image
// only (CSS hides the video < 961px) so no video bytes load on phones.
$hero_video = (string) get_option( 'showtime_hero_video_url', '' );
$hero_poster = function_exists( 'showtime_image' ) ? showtime_image( 'hero_poster', 1920 ) : $hero_url;
?>
<section class="home-hero home-hero--immersive" data-reveal>
	<?php if ( '' !== $hero_video ) : ?>
		<video class="home-hero__bgvideo" autoplay muted loop playsinline preload="none" poster="<?php echo esc_url( $hero_poster ); ?>">
			<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
		</video>
		<img class="home-hero__bgphoto home-hero__bgphoto--poster" src="<?php echo esc_url( $hero_poster ); ?>" alt="<?php echo esc_attr( $pc_alt ); ?>" loading="eager" fetchpriority="high" decoding="async">
	<?php else : ?>
		<picture class="home-hero__bgphoto">
			<img
				src="<?php echo esc_url( $hero_img['src'] ); ?>"
				<?php if ( '' !== $hero_img['srcset'] ) : ?>srcset="<?php echo esc_attr( $hero_img['srcset'] ); ?>" sizes="<?php echo esc_attr( $hero_img['sizes'] ); ?>"<?php endif; ?>
				width="<?php echo (int) $hero_img['width']; ?>" height="<?php echo (int) $hero_img['height']; ?>"
				alt="<?php echo esc_attr( $pc_alt ); ?>" loading="eager" fetchpriority="high" decoding="async">
		</picture>
	<?php endif; ?>
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
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $cta1_url ); ?>">
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

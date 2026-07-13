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

// Phone — same Customizer-bridged filter used sitewide (header, footer,
// home-about), so it stays in sync everywhere it is edited from.
$phone     = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$phone_tel = preg_replace( '/[^0-9+]/', '', $phone );

// Slim trust row under the phone line. Short forms of claims already made
// (and already approved) elsewhere on the site — home-about bullets and the
// service registry — not new claims. Filterable so copy can change without
// touching this template.
$hero_trust_items = apply_filters(
	'showtime/hero_trust_strip',
	array(
		array( 'icon' => 'crew',   'label' => __( 'In-House W-2 Crew', 'showtime-pools' ) ),
		array( 'icon' => 'brand',  'label' => __( 'Pentair + Jandy Authorized', 'showtime-pools' ) ),
		array( 'icon' => 'pebble', 'label' => __( 'PebbleTec Certified', 'showtime-pools' ) ),
		array( 'icon' => 'clock',  'label' => __( 'Same-Day Service Available', 'showtime-pools' ) ),
	)
);
$hero_trust_icons = array(
	'crew'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
	'brand'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
	'pebble' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 10.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" fill="currentColor" stroke="none"/><path d="M15.5 15a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" fill="currentColor" stroke="none"/><path d="M13 9.2a.8.8 0 1 0 0-1.6.8.8 0 0 0 0 1.6Z" fill="currentColor" stroke="none"/></svg>',
	'clock'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>',
);
?>
<?php
// Background video. In code-first edit mode the bundled asset always plays; in
// WordPress mode it reads the Hero video URL setting (empty = no video, hero
// falls back to the still image). The poster is the hero_poster slot, which
// defaults to the hero still, so the LCP preload in inc/performance.php stays
// valid. Mobile shows the poster image only (CSS hides the video < 961px) so
// no video bytes load on phones.
$hero_video = ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST )
	? SHOWTIME_CHILD_URI . '/assets/img/Showtimehero.mp4'
	: (string) get_option( 'showtime_hero_video_url', '' );
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

			<a class="home-hero__phone-cta" href="tel:<?php echo esc_attr( $phone_tel ); ?>">
				<span class="home-hero__phone-cta-icon" aria-hidden="true">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
				</span>
				<span><?php echo esc_html( sprintf( /* translators: %s: phone number */ __( 'Call/Text %s', 'showtime-pools' ), $phone ) ); ?></span>
			</a>
		</div>

		<?php if ( ! empty( $hero_trust_items ) ) : ?>
			<ul class="home-hero__trust" role="list">
				<?php foreach ( $hero_trust_items as $item ) :
					$icon_key = (string) ( $item['icon'] ?? '' );
					$label    = (string) ( $item['label'] ?? '' );
					if ( '' === $label ) { continue; }
				?>
					<li class="home-hero__trust-item">
						<span class="home-hero__trust-icon" aria-hidden="true"><?php echo $hero_trust_icons[ $icon_key ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?></span>
						<span><?php echo esc_html( $label ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="home-hero__estimator">
			<?php get_template_part( 'template-parts/home/section-estimator-strip' ); ?>
		</div>
	</div>

</section>

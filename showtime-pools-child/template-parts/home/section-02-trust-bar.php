<?php
/**
 * Poolax-style "About" section (Phase G) + stats strip below.
 *
 * Replaces the Phase E trust-pillars (license/insurance/quality body
 * copy) with a brand-storytelling About block:
 *   - LEFT: two stacked photos with a circular "years experience" badge
 *           bridging them
 *   - RIGHT: eyebrow, H2, lead, 6-bullet differentiator grid, primary
 *           CTA, click-to-call phone w/ icon
 *
 * Below the About block, the 4-up stats strip stays (Phase E) — it has
 * a subtle "CSLB licensed & insured" badge but no prominent license/
 * insurance display (those were removed per user feedback).
 *
 * Every label is ACF-editable from Site Content → Page Copy → Homepage About.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$opt = function_exists( 'get_field' ) ? 'option' : false;

// About block content — ACF first, sensible defaults second.
$about_eyebrow  = $opt ? (string) get_field( 'home_about_eyebrow', $opt ) : '';
$about_title    = $opt ? (string) get_field( 'home_about_title', $opt ) : '';
$about_lead     = $opt ? (string) get_field( 'home_about_lead', $opt ) : '';
$about_years    = $opt ? (string) get_field( 'home_about_years', $opt ) : '';
$about_cta_lbl  = $opt ? (string) get_field( 'home_about_cta_label', $opt ) : '';
$about_cta_url  = $opt ? (string) get_field( 'home_about_cta_url', $opt ) : '';
$about_bullets  = $opt ? get_field( 'home_about_bullets', $opt ) : null;

$about_eyebrow = '' !== $about_eyebrow ? $about_eyebrow : __( 'About Showtime Pools', 'showtime-pools' );
$about_title   = '' !== $about_title   ? $about_title   : __( 'The pool service company Los Angeles homeowners actually call back.', 'showtime-pools' );
$about_lead    = '' !== $about_lead    ? $about_lead    : __( 'Pool repair, pool cleaning service, pool remodeling, equipment installation, and new construction, handled end-to-end by the same supervised crew. The technician who quotes your job is on-site when the work happens.', 'showtime-pools' );
$about_years   = '' !== $about_years   ? $about_years   : '23';
$about_cta_lbl = '' !== $about_cta_lbl ? $about_cta_lbl : __( 'More about us', 'showtime-pools' );
$about_cta_url = '' !== $about_cta_url ? $about_cta_url : home_url( '/about/' );

$bullets_default = array(
	array( 'text' => __( 'In-house pool service crew · same tech weekly', 'showtime-pools' ) ),
	array( 'text' => __( 'Same pool tech every weekly visit', 'showtime-pools' ) ),
	array( 'text' => __( 'Pool repair, remodel & cleaning under one roof', 'showtime-pools' ) ),
	array( 'text' => __( 'Pentair + Jandy authorized installation', 'showtime-pools' ) ),
	array( 'text' => __( 'PebbleTec certified pool resurfacing', 'showtime-pools' ) ),
	array( 'text' => __( 'Same-day emergency pool service', 'showtime-pools' ) ),
);
$bullets = is_array( $about_bullets ) && ! empty( $about_bullets ) ? $about_bullets : $bullets_default;

// Phone (Customizer-bridged).
$phone     = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$phone_tel = preg_replace( '/[^0-9+]/', '', $phone );

// Stats strip — 4-up proof markers. License/insurance numbers removed
// site-wide per user. The third tile is intentionally "Since 2003 ·
// Sherman Oaks based" rather than a star rating so the trust signal
// never goes stale and never misrepresents the live GBP data. Live
// rating + review count are surfaced via the Google Reviews widget.

$stats = apply_filters( 'showtime/home_stats_strip', array(
	array( 'icon' => 'pool',     'num' => '1,824+',      'label' => __( 'Pools serviced',   'showtime-pools' ) ),
	array( 'icon' => 'calendar', 'num' => $about_years,  'label' => __( 'Years on the route', 'showtime-pools' ) ),
	array( 'icon' => 'pin',      'num' => '2003',        'label' => __( 'Serving Sherman Oaks', 'showtime-pools' ) ),
	array( 'icon' => 'shield',   'num' => '100%',        'label' => __( 'Supervised · Steve on every job', 'showtime-pools' ) ),
) );

$stat_icons = array(
	'pool'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 18c2.5 0 2.5-1.5 5-1.5s2.5 1.5 5 1.5 2.5-1.5 5-1.5 2.5 1.5 5 1.5"/><path d="M2 12c2.5 0 2.5-1.5 5-1.5s2.5 1.5 5 1.5 2.5-1.5 5-1.5 2.5 1.5 5 1.5"/><path d="M2 6c2.5 0 2.5-1.5 5-1.5s2.5 1.5 5 1.5 2.5-1.5 5-1.5 2.5 1.5 5 1.5"/></svg>',
	'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>',
	'pin'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-7.5 7-13a7 7 0 1 0-14 0c0 5.5 7 13 7 13Z"/><circle cx="12" cy="9" r="2.5"/></svg>',
	'shield'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
);

// Bundled About-section photos — picked for visual contrast.
// Both pull from slots whose source files DO NOT contain the "fish"
// pool-cleaner head that ships on Showtime Pools 47/48 (Phase J fix).
//   - Top:    about_hero (Showtime Pools 50) — deep water finished pool
//   - Bottom: service_weekly-pool-maintenance — cleaning tech in action
$about_img_top = function_exists( 'showtime_image' ) ? showtime_image( 'about_hero', 960 ) : '';
$about_img_bot = function_exists( 'showtime_image' ) ? showtime_image( 'service_weekly-pool-maintenance', 960 ) : '';
?>
<section class="home-about" data-reveal>
	<div class="container">
		<div class="home-about__grid">

			<div class="home-about__media">
				<div class="home-about__photo home-about__photo--top">
					<?php if ( $about_img_top ) : ?>
						<img src="<?php echo esc_url( $about_img_top ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
				</div>
				<div class="home-about__badge" aria-hidden="true">
					<strong><?php echo esc_html( $about_years ); ?></strong>
					<span><?php esc_html_e( 'Years', 'showtime-pools' ); ?><br><?php esc_html_e( 'Experience', 'showtime-pools' ); ?></span>
				</div>
				<div class="home-about__photo home-about__photo--bot">
					<?php if ( $about_img_bot ) : ?>
						<img src="<?php echo esc_url( $about_img_bot ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
				</div>
			</div>

			<div class="home-about__copy">
				<span class="eyebrow home-about__eyebrow"><?php echo esc_html( $about_eyebrow ); ?></span>
				<h2 class="home-about__title balance"><?php echo esc_html( $about_title ); ?></h2>
				<p class="home-about__lead"><?php echo esc_html( $about_lead ); ?></p>

				<ul class="home-about__bullets" role="list">
					<?php foreach ( $bullets as $b ) :
						$text = is_array( $b ) ? (string) ( $b['text'] ?? '' ) : (string) $b;
						if ( '' === $text ) { continue; }
					?>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
							<span><?php echo esc_html( $text ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="home-about__foot">
					<a class="btn btn--primary" href="<?php echo esc_url( $about_cta_url ); ?>"><?php echo esc_html( $about_cta_lbl ); ?></a>

					<a class="home-about__phone" href="tel:<?php echo esc_attr( $phone_tel ); ?>">
						<span class="home-about__phone-icon" aria-hidden="true">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
						</span>
						<span class="home-about__phone-copy">
							<small><?php esc_html_e( 'Have a question? Call us', 'showtime-pools' ); ?></small>
							<strong><?php echo esc_html( $phone ); ?></strong>
						</span>
					</a>
				</div>
			</div>

		</div>
	</div>
</section>

<section class="home-stats" data-reveal>
	<div class="container">
		<div class="home-stats__grid" data-stagger>
			<?php foreach ( $stats as $s ) :
				$icon_key = (string) ( $s['icon'] ?? '' );
				$icon_svg = $stat_icons[ $icon_key ] ?? $stat_icons['star'];
			?>
				<div class="home-stats__cell">
					<span class="home-stats__badge" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?></span>
					<dl class="home-stats__text">
						<dt class="home-stats__num" data-count="<?php echo esc_attr( (string) ( $s['num'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $s['num'] ?? '' ) ); ?></dt>
						<dd class="home-stats__label"><?php echo esc_html( (string) ( $s['label'] ?? '' ) ); ?></dd>
					</dl>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

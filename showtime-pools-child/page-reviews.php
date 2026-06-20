<?php
/**
 * Template Name: Reviews
 *
 * /reviews/ — renders the live Google Reviews widget. Real customer reviews
 * are pulled from the configured widget plugin (default: Trustindex
 * `[trustindex no-registration=google]`) so the rating and count stay live
 * and authentic. No hardcoded testimonials.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ── Native WP fields (edit via WP Admin → Pages → Reviews → Update) ─────────
$pid          = get_the_ID();
$hero_h1      = get_the_title();
$hero_eyebrow = (string) get_post_meta( $pid, 'hero_eyebrow', true );
$hero_lead    = (string) get_post_meta( $pid, 'hero_lead',    true );

if ( '' === $hero_eyebrow ) { $hero_eyebrow = 'Live from Google'; }
if ( '' === $hero_lead )    { $hero_lead    = 'Live from our Google Business Profile — every review you see is published directly by the customer, with no editing on our side.'; }
?>
<main id="primary" class="site-main interior-page">

	<?php $reviews_hero = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_2', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $reviews_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $reviews_hero ); ?>" <?php echo showtime_hero_srcset_attr( 'lifestyle_2' ); ?> alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Reviews', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead">
					<?php echo esc_html( $hero_lead ); ?>
				</p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="reviews-widget">
				<?php echo showtime_render_reviews_widget(); ?>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

<?php
/**
 * About split — editorial 50/50. Founder voice, ghost numeral, signature.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Homepage About section photo. Its own slot (Site Images → "Homepage About
// section photo") so it is independent from the /about/ page background.
$img = function_exists( 'showtime_image' ) ? showtime_image( 'about_split', 1200 ) : '';
?>
<section class="about-split" data-reveal>
	<div class="container">
		<div class="about-split__grid">

			<figure class="about-split__media">
				<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" decoding="async">
				<?php endif; ?>
				<figcaption class="about-split__caption">
					<span><?php esc_html_e( 'Sherman Oaks · since 2003', 'showtime-pools' ); ?></span>
				</figcaption>
				<blockquote class="about-split__pullquote" aria-hidden="true">
					&ldquo;<?php esc_html_e( 'One supervised crew. Steve walks every site.', 'showtime-pools' ); ?>&rdquo;
				</blockquote>
			</figure>

			<div class="about-split__copy">
				<span class="about-split__ghost-num" aria-hidden="true">01</span>

				<span class="eyebrow about-split__eyebrow">
					<em>01</em> &mdash; <?php esc_html_e( 'About Showtime Pools', 'showtime-pools' ); ?>
				</span>

				<h2 class="about-split__title balance">
					<?php esc_html_e( 'Complete pool care, ', 'showtime-pools' ); ?>
					<em><?php esc_html_e( 'start to finish.', 'showtime-pools' ); ?></em>
				</h2>

				<p class="about-split__lead">
					<?php esc_html_e( 'Showtime Pools designs, builds, and transforms pools and outdoor spaces that elevate the way you live. Based in Los Angeles, we specialize in custom construction, remodeling, equipment upgrades, repairs, and luxury outdoor living.', 'showtime-pools' ); ?>
				</p>
				<p class="about-split__body">
					<?php esc_html_e( 'Twenty-three years of hands-on craft. A reputation built on quality, transparency, and reliability — every project treated like our own backyard. No shortcuts. Only results that stand the test of time.', 'showtime-pools' ); ?>
				</p>

				<div class="about-split__sig" aria-hidden="true">
					<span class="about-split__sig-name">Steve Adams</span>
					<span class="about-split__sig-role"><?php esc_html_e( 'Founder · Showtime Pools', 'showtime-pools' ); ?></span>
				</div>

				<div class="cluster about-split__ctas">
					<a class="btn btn--primary btn--lg btn--pill" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
						<?php esc_html_e( 'About the team', 'showtime-pools' ); ?>
					</a>
					<a class="btn btn--link" href="<?php echo esc_url( home_url( '/the-founder/' ) ); ?>">
						<?php esc_html_e( 'Meet the founder →', 'showtime-pools' ); ?>
					</a>
				</div>
			</div>

		</div>
	</div>
</section>

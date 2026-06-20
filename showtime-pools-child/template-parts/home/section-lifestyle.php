<?php
/**
 * Lifestyle gallery — magazine-grid of 5 photos. Sets the "what owning a
 * Showtime pool feels like" tone before we drop into the services list.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'showtime_image' ) ) { return; }
?>
<section class="lifestyle" data-reveal>
	<div class="container">
		<header class="lifestyle__header">
			<span class="eyebrow"><em>03</em> &mdash; <?php esc_html_e( 'The Lifestyle', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'What we are really selling. Time in the water.', 'showtime-pools' ); ?></h2>
			<p class="lead"><?php esc_html_e( 'Equipment specs and chemistry numbers matter. But what you actually buy is Sundays in your backyard, kids who learn to swim where they live, and a deck that looks better at 5pm than it did at noon. We design and service for that.', 'showtime-pools' ); ?></p>
		</header>

		<div class="lifestyle__grid">
			<figure class="lifestyle__cell lifestyle__cell--main">
				<img src="<?php echo esc_url( showtime_image( 'lifestyle_main', 1200 ) ); ?>" alt="" loading="lazy" decoding="async" width="800" height="800">
				<figcaption>
					<span class="lifestyle__cap-label"><?php esc_html_e( 'Encino · Sunday afternoon', 'showtime-pools' ); ?></span>
					<span class="lifestyle__cap-text"><?php esc_html_e( '2024 new build. Quartz finish, sheer descent, automation.', 'showtime-pools' ); ?></span>
				</figcaption>
			</figure>

			<figure class="lifestyle__cell">
				<img src="<?php echo esc_url( showtime_image( 'lifestyle_1', 800 ) ); ?>" alt="" loading="lazy" decoding="async" width="800" height="800">
				<figcaption>
					<span class="lifestyle__cap-label"><?php esc_html_e( 'Sherman Oaks', 'showtime-pools' ); ?></span>
					<span class="lifestyle__cap-text"><?php esc_html_e( 'Glass mosaic step accent.', 'showtime-pools' ); ?></span>
				</figcaption>
			</figure>

			<figure class="lifestyle__cell">
				<img src="<?php echo esc_url( showtime_image( 'lifestyle_2', 800 ) ); ?>" alt="" loading="lazy" decoding="async" width="800" height="800">
				<figcaption>
					<span class="lifestyle__cap-label"><?php esc_html_e( 'Tuesday route', 'showtime-pools' ); ?></span>
					<span class="lifestyle__cap-text"><?php esc_html_e( 'Same tech, same pool, same time.', 'showtime-pools' ); ?></span>
				</figcaption>
			</figure>

			<figure class="lifestyle__cell">
				<img src="<?php echo esc_url( showtime_image( 'lifestyle_3', 800 ) ); ?>" alt="" loading="lazy" decoding="async" width="800" height="800">
				<figcaption>
					<span class="lifestyle__cap-label"><?php esc_html_e( 'Beverly Hills', 'showtime-pools' ); ?></span>
					<span class="lifestyle__cap-text"><?php esc_html_e( 'Travertine deck refresh, 2025.', 'showtime-pools' ); ?></span>
				</figcaption>
			</figure>

			<figure class="lifestyle__cell">
				<img src="<?php echo esc_url( showtime_image( 'lifestyle_4', 800 ) ); ?>" alt="" loading="lazy" decoding="async" width="800" height="800">
				<figcaption>
					<span class="lifestyle__cap-label"><?php esc_html_e( 'Studio City', 'showtime-pools' ); ?></span>
					<span class="lifestyle__cap-text"><?php esc_html_e( 'Hillside rebuild, bonding compliant.', 'showtime-pools' ); ?></span>
				</figcaption>
			</figure>
		</div>
	</div>
</section>

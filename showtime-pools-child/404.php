<?php
/**
 * 404 — branded.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main interior-page">
	<section class="not-found section section--brand" data-reveal>
		<div class="cta-banner__pattern" aria-hidden="true" style="position:absolute;inset:0"></div>
		<div class="container" style="position:relative">
			<div class="not-found__inner">
				<span class="not-found__num">404</span>
				<h1 class="not-found__title balance"><?php esc_html_e( 'Pool not found.', 'showtime-pools' ); ?></h1>
				<p class="not-found__lead">
					<?php esc_html_e( 'Either the page moved or the link was wrong. Easier to start fresh: try the homepage, browse services, or send us a message and we will route you.', 'showtime-pools' ); ?>
				</p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go home', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Browse services', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Send a message', 'showtime-pools' ); ?></a>
				</div>

				<div class="not-found__shortcuts">
					<h2><?php esc_html_e( 'Or try one of these.', 'showtime-pools' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/services/weekly-pool-maintenance/' ) ); ?>"><?php esc_html_e( 'Weekly maintenance', 'showtime-pools' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/services/custom-pool-design-construction/' ) ); ?>"><?php esc_html_e( 'New construction', 'showtime-pools' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/services/pool-remodeling-resurfacing/' ) ); ?>"><?php esc_html_e( 'Remodel', 'showtime-pools' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/pool-inspections/' ) ); ?>"><?php esc_html_e( 'Pool inspections', 'showtime-pools' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>"><?php esc_html_e( 'Service areas', 'showtime-pools' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'showtime-pools' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>
</main>
<?php get_footer();

<?php
/**
 * Index — WordPress fallback template.
 *
 * WordPress requires this file to exist. All real page templates live in
 * page-*.php, single.php, archive.php, front-page.php etc. This file
 * only fires for edge cases not covered by a specific template.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main interior-page">
	<div class="container" style="padding: var(--sp-12, 4rem) 0">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="post-prose"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<h1><?php esc_html_e( 'Nothing found.', 'showtime-pools' ); ?></h1>
			<p><?php esc_html_e( 'Try going back to the homepage.', 'showtime-pools' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'showtime-pools' ); ?></a>
		<?php endif; ?>
	</div>
</main>
<?php get_footer();

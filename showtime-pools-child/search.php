<?php
/**
 * Search results template.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main interior-page">
	<section class="int-hero int-hero--brand" data-reveal>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Search', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Results for: %s', 'showtime-pools' ),
						'<em>' . esc_html( get_search_query() ) . '</em>'
					);
					?>
				</h1>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="blog-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<article class="blog-card">
							<a class="blog-card__link" href="<?php the_permalink(); ?>">
								<div class="blog-card__body">
									<h2 class="blog-card__title"><?php the_title(); ?></h2>
									<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
								</div>
							</a>
						</article>
					<?php endwhile; ?>
				</div>
				<div class="blog-pagination" style="margin-top:var(--sp-10,2.5rem)">
					<?php the_posts_pagination(); ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No results found. Try a different search term.', 'showtime-pools' ); ?></p>
				<?php get_search_form(); ?>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer();

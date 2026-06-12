<?php
/**
 * Category / tag / date archive — single template for all post archives.
 *
 * Mirrors the /blog/ hub's grid + sidebar layout. The archive header
 * adapts to the queried object (category name, tag name, or "Posts by
 * <author>"). Uses standard WP pagination at the foot.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$queried = get_queried_object();

$title = '';
$lead  = '';
$slot  = 'blog_default';
$category_slot_map = array(
	'pool-trends'      => 'blog_trends',
	'maintenance-tips' => 'blog_tips',
	'equipment-guides' => 'blog_equipment',
);

if ( is_category() && $queried instanceof WP_Term ) {
	$title = $queried->name;
	$lead  = (string) $queried->description;
	$slot  = $category_slot_map[ $queried->slug ] ?? 'blog_default';
} elseif ( is_tag() && $queried instanceof WP_Term ) {
	$title = $queried->name;
	$lead  = (string) $queried->description;
} elseif ( is_author() && $queried instanceof WP_User ) {
	$title = sprintf( /* translators: %s display name */ __( 'Articles by %s', 'showtime-pools' ), $queried->display_name );
} else {
	$title = __( 'Articles', 'showtime-pools' );
}

$archive_hero_img = function_exists( 'showtime_image' ) ? showtime_image( $slot, 1920 ) : '';
?>
<main id="primary" class="site-main blog-hub">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $archive_hero_img ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $archive_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( $title ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Category', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $title ); ?></h1>
				<?php if ( '' !== $lead ) : ?>
					<p class="int-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="blog-feed" data-reveal>
		<div class="container">
			<div class="blog-feed__inner">

				<div class="blog-feed__main">
					<?php if ( have_posts() ) : ?>
						<div class="blog-grid">
							<?php while ( have_posts() ) : the_post();
								$pid = get_the_ID();
								$slug2 = (string) get_post_meta( $pid, '_showtime_image_slot', true );
								if ( '' === $slug2 ) {
									$cats = get_the_category( $pid );
									$slug2 = isset( $cats[0] ) && isset( $category_slot_map[ $cats[0]->slug ] ) ? $category_slot_map[ $cats[0]->slug ] : 'blog_default';
								}
								$post_img = has_post_thumbnail( $pid )
									? (string) get_the_post_thumbnail_url( $pid, 'large' )
									: ( function_exists( 'showtime_image' ) ? showtime_image( $slug2, 1024 ) : '' );
								$primary_cat = get_the_category( $pid );
								$primary_cat = $primary_cat[0] ?? null;
							?>
								<article class="blog-card">
									<a class="blog-card__link" href="<?php the_permalink(); ?>">
										<div class="blog-card__media">
											<?php if ( $post_img ) : ?>
												<img src="<?php echo esc_url( $post_img ); ?>" alt="" loading="lazy" decoding="async">
											<?php endif; ?>
											<?php if ( $primary_cat ) : ?>
												<span class="blog-card__pill"><?php echo esc_html( $primary_cat->name ); ?></span>
											<?php endif; ?>
										</div>
										<div class="blog-card__body">
											<time class="blog-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
											<h3 class="blog-card__title"><?php the_title(); ?></h3>
											<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
											<span class="blog-card__read"><?php esc_html_e( 'Read article', 'showtime-pools' ); ?> <span aria-hidden="true">→</span></span>
										</div>
									</a>
								</article>
							<?php endwhile; ?>
						</div>

						<nav class="blog-pagination" aria-label="<?php esc_attr_e( 'Articles pagination', 'showtime-pools' ); ?>">
							<?php
							echo paginate_links(
								array(
									'prev_text' => '← ' . __( 'Previous', 'showtime-pools' ),
									'next_text' => __( 'Next', 'showtime-pools' ) . ' →',
								)
							);
							?>
						</nav>
					<?php else : ?>
						<p class="blog-empty"><?php esc_html_e( 'No articles in this category yet.', 'showtime-pools' ); ?></p>
					<?php endif; ?>
				</div>

				<aside class="blog-feed__side">
					<?php
					$cats = get_categories(
						array( 'orderby' => 'name', 'hide_empty' => false, 'exclude' => array( 1 ) )
					);
					if ( ! empty( $cats ) ) : ?>
						<section class="blog-side blog-side--cats">
							<h3 class="blog-side__title"><?php esc_html_e( 'Categories', 'showtime-pools' ); ?></h3>
							<ul class="blog-side__list">
								<?php foreach ( $cats as $c ) : ?>
									<li>
										<a href="<?php echo esc_url( get_category_link( $c ) ); ?>">
											<?php echo esc_html( $c->name ); ?>
											<span class="blog-side__count">(<?php echo (int) $c->count; ?>)</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<section class="blog-side blog-side--cta">
						<h3 class="blog-side__title"><?php esc_html_e( 'Need help with a pool?', 'showtime-pools' ); ?></h3>
						<p><?php esc_html_e( 'Steve or a senior tech replies within one business day.', 'showtime-pools' ); ?></p>
						<a class="btn btn--primary" href="<?php echo esc_url( showtime_booking_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Book an Appointment', 'showtime-pools' ); ?></a>
					</section>
				</aside>
			</div>
		</div>
	</section>

</main>
<?php
get_footer();

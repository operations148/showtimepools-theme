<?php
/**
 * Template Name: Blog Hub
 *
 * /blog/ — content hub. Pinch A Penny-style editorial IA: hero with
 * eyebrow + H1 + lead, 3-category strip with photo + count, featured
 * post + grid of recent posts, sidebar with categories + recent post
 * list. Single posts live at /<post-slug>/ (WP default), Article
 * JSON-LD in single.php.
 *
 * Editors add new posts in WP admin → Posts; categories under Posts →
 * Categories. Three seed categories ship by default (Pool Trends,
 * Maintenance Tips, Equipment Guides).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ── Native WP fields (edit via WP Admin → Pages → Blog → Update) ────────────
$pid              = get_the_ID();
$hero_h1          = get_the_title();
$hero_eyebrow     = (string) get_post_meta( $pid, 'hero_eyebrow',     true );
$hero_lead        = (string) get_post_meta( $pid, 'hero_lead',        true );
$cats_h2          = (string) get_post_meta( $pid, 'cats_h2',          true );
$feed_h2          = (string) get_post_meta( $pid, 'feed_h2',          true );
$sidebar_cta_title = (string) get_post_meta( $pid, 'sidebar_cta_title', true );
$sidebar_cta_body  = (string) get_post_meta( $pid, 'sidebar_cta_body',  true );

if ( '' === $hero_eyebrow )      { $hero_eyebrow      = 'Pool insights · From the crew'; }
if ( '' === $hero_lead )         { $hero_lead         = 'Practical pool knowledge from Steve and the crew. Real water, real equipment, real LA backyards.'; }
if ( '' === $cats_h2 )           { $cats_h2           = 'Three topics, written by people who do this every day.'; }
if ( '' === $feed_h2 )           { $feed_h2           = 'What we are writing about now.'; }
if ( '' === $sidebar_cta_title ) { $sidebar_cta_title = 'Ready to talk to a real human?'; }
if ( '' === $sidebar_cta_body )  { $sidebar_cta_body  = 'Get a free quote on a repair, remodel, or weekly service.'; }

// All non-Uncategorized post categories.
$categories = get_categories(
	array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
		'exclude'    => array( 1 ),
	)
);

// Latest 9 posts.
$q = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 9,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	)
);

$blog_hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'blog_default', 1920 ) : '';

// Map seeded category slugs → bundled hero slots.
$category_slot_map = array(
	'pool-trends'      => 'blog_trends',
	'maintenance-tips' => 'blog_tips',
	'equipment-guides' => 'blog_equipment',
);
?>
<main id="primary" class="site-main blog-hub">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $blog_hero_img ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $blog_hero_img ); ?>" <?php echo showtime_hero_srcset_attr( 'blog_default' ); ?> alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Blog', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $categories ) ) : ?>
		<section class="blog-categories" data-reveal>
			<div class="container">
				<header class="blog-categories__head">
					<span class="eyebrow"><?php esc_html_e( 'Browse by topic', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php echo esc_html( $cats_h2 ); ?></h2>
				</header>
				<div class="blog-categories__grid">
					<?php foreach ( $categories as $cat ) :
						$slot = $category_slot_map[ $cat->slug ] ?? 'blog_default';
						$cat_img = function_exists( 'showtime_image' ) ? showtime_image( $slot, 1024 ) : '';
					?>
						<a class="blog-cat-card" href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
							<div class="blog-cat-card__media">
								<?php if ( $cat_img ) : ?>
									<img src="<?php echo esc_url( $cat_img ); ?>" alt="" loading="lazy" decoding="async">
								<?php endif; ?>
							</div>
							<div class="blog-cat-card__body">
								<h3 class="blog-cat-card__title"><?php echo esc_html( $cat->name ); ?></h3>
								<?php if ( ! empty( $cat->description ) ) : ?>
									<p class="blog-cat-card__desc"><?php echo esc_html( $cat->description ); ?></p>
								<?php endif; ?>
								<span class="blog-cat-card__count">
									<?php
									printf(
										/* translators: %d: number of posts in this category */
										esc_html( _n( '%d article', '%d articles', (int) $cat->count, 'showtime-pools' ) ),
										(int) $cat->count
									);
									?>
									<span aria-hidden="true"> →</span>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="blog-feed" data-reveal>
		<div class="container">
			<div class="blog-feed__inner">

				<div class="blog-feed__main">
					<header class="blog-feed__head">
						<span class="eyebrow"><?php esc_html_e( 'Latest articles', 'showtime-pools' ); ?></span>
						<h2 class="balance"><?php echo esc_html( $feed_h2 ); ?></h2>
					</header>

					<?php if ( $q->have_posts() ) : ?>
						<div class="blog-grid">
							<?php $first = true; while ( $q->have_posts() ) : $q->the_post();
								$pid  = get_the_ID();
								$slot = (string) get_post_meta( $pid, '_showtime_image_slot', true );
								if ( '' === $slot ) {
									$cats = get_the_category( $pid );
									$slot = isset( $cats[0] ) && isset( $category_slot_map[ $cats[0]->slug ] ) ? $category_slot_map[ $cats[0]->slug ] : 'blog_default';
								}
								$post_img = has_post_thumbnail( $pid )
									? (string) get_the_post_thumbnail_url( $pid, 'large' )
									: ( function_exists( 'showtime_image' ) ? showtime_image( $slot, 1024 ) : '' );
								$cls = $first ? 'blog-card blog-card--feature' : 'blog-card';
								$first = false;
								$primary_cat = get_the_category( $pid );
								$primary_cat = $primary_cat[0] ?? null;
							?>
								<article class="<?php echo esc_attr( $cls ); ?>">
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
							<?php endwhile; wp_reset_postdata(); ?>
						</div>
					<?php else : ?>
						<p class="blog-empty"><?php esc_html_e( 'Articles coming soon. The crew is writing.', 'showtime-pools' ); ?></p>
					<?php endif; ?>
				</div>

				<aside class="blog-feed__side">

					<?php if ( ! empty( $categories ) ) : ?>
						<section class="blog-side blog-side--cats">
							<h3 class="blog-side__title"><?php esc_html_e( 'Categories', 'showtime-pools' ); ?></h3>
							<ul class="blog-side__list">
								<?php foreach ( $categories as $cat ) : ?>
									<li>
										<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
											<?php echo esc_html( $cat->name ); ?>
											<span class="blog-side__count">(<?php echo (int) $cat->count; ?>)</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<section class="blog-side">
						<h3 class="blog-side__title"><?php esc_html_e( 'Recent articles', 'showtime-pools' ); ?></h3>
						<?php
						$recent = new WP_Query(
							array(
								'post_type'      => 'post',
								'posts_per_page' => 5,
								'post_status'    => 'publish',
								'no_found_rows'  => true,
							)
						);
						?>
						<ul class="blog-side__list">
							<?php while ( $recent->have_posts() ) : $recent->the_post(); ?>
								<li>
									<a href="<?php the_permalink(); ?>">
										<strong><?php the_title(); ?></strong>
										<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									</a>
								</li>
							<?php endwhile; wp_reset_postdata(); ?>
						</ul>
					</section>

					<section class="blog-side blog-side--cta">
						<h3 class="blog-side__title"><?php echo esc_html( $sidebar_cta_title ); ?></h3>
						<p><?php echo esc_html( $sidebar_cta_body ); ?></p>
						<a class="btn btn--primary" href="<?php echo esc_url( showtime_booking_url() ); ?>"><?php esc_html_e( 'Book an Appointment', 'showtime-pools' ); ?></a>
					</section>

				</aside>

			</div>
		</div>
	</section>

</main>
<?php
get_footer();

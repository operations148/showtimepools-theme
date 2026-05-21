<?php
/**
 * Single post — long-form article view with hero, sticky TOC sidebar,
 * Article JSON-LD, related posts, share row, and crew bio footer.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid  = get_the_ID();
	$cats = get_the_category( $pid );
	$primary_cat = $cats[0] ?? null;

	$category_slot_map = array(
		'pool-trends'      => 'blog_trends',
		'maintenance-tips' => 'blog_tips',
		'equipment-guides' => 'blog_equipment',
	);

	$slot = (string) get_post_meta( $pid, '_showtime_image_slot', true );
	if ( '' === $slot ) {
		$slot = $primary_cat && isset( $category_slot_map[ $primary_cat->slug ] ) ? $category_slot_map[ $primary_cat->slug ] : 'blog_default';
	}
	$hero_img = has_post_thumbnail( $pid )
		? (string) get_the_post_thumbnail_url( $pid, 'full' )
		: ( function_exists( 'showtime_image' ) ? showtime_image( $slot, 1920 ) : '' );
	$hero_img_large = $hero_img;

	// Article JSON-LD.
	$opt = function_exists( 'get_field' ) ? 'option' : false;
	$author_name = $opt ? (string) get_field( 'founder_name', $opt ) : '';
	$author_name = '' !== $author_name ? $author_name : ( get_the_author() ?: 'Showtime Pools' );

	$article_schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $pid ),
		),
		'headline'         => get_the_title( $pid ),
		'description'      => wp_strip_all_tags( get_the_excerpt( $pid ) ),
		'image'            => $hero_img,
		'datePublished'    => get_the_date( 'c', $pid ),
		'dateModified'     => get_the_modified_date( 'c', $pid ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => $author_name,
			'url'   => home_url( '/the-founder/' ),
		),
		'publisher'        => array(
			'@id' => home_url( '/#localbusiness' ),
		),
		'articleSection'   => $primary_cat ? $primary_cat->name : 'Pool Insights',
		'keywords'         => wp_get_post_tags( $pid, array( 'fields' => 'names' ) ),
	);

	// Related posts (same category, not this post).
	$related = array();
	if ( $primary_cat ) {
		$r = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'post__not_in'   => array( $pid ),
				'category__in'   => array( (int) $primary_cat->term_id ),
				'no_found_rows'  => true,
			)
		);
		while ( $r->have_posts() ) {
			$r->the_post();
			$rpid = get_the_ID();
			$r_slot = (string) get_post_meta( $rpid, '_showtime_image_slot', true );
			if ( '' === $r_slot ) {
				$r_cats = get_the_category( $rpid );
				$r_slot = isset( $r_cats[0] ) && isset( $category_slot_map[ $r_cats[0]->slug ] ) ? $category_slot_map[ $r_cats[0]->slug ] : 'blog_default';
			}
			$r_img = has_post_thumbnail( $rpid )
				? (string) get_the_post_thumbnail_url( $rpid, 'large' )
				: ( function_exists( 'showtime_image' ) ? showtime_image( $r_slot, 800 ) : '' );
			$related[] = array(
				'title'  => get_the_title(),
				'href'   => get_permalink(),
				'img'    => $r_img,
				'date'   => get_the_date(),
				'pill'   => $primary_cat->name,
			);
		}
		wp_reset_postdata();
	}

	// Breadcrumb JSON-LD.
	$crumb_schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => home_url( '/blog/' ) ),
		),
	);
	if ( $primary_cat ) {
		$crumb_schema['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => $primary_cat->name,
			'item'     => get_category_link( $primary_cat ),
		);
		$crumb_schema['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 4,
			'name'     => get_the_title( $pid ),
			'item'     => get_permalink( $pid ),
		);
	} else {
		$crumb_schema['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title( $pid ),
			'item'     => get_permalink( $pid ),
		);
	}
?>
<main id="primary" class="site-main blog-single">

	<article class="post-article">

		<header class="post-hero">
			<?php if ( $hero_img ) : ?>
				<img class="post-hero__photo" src="<?php echo esc_url( $hero_img_large ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
			<?php endif; ?>
			<div class="post-hero__overlay" aria-hidden="true"></div>
			<div class="container">
				<nav class="breadcrumbs post-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
					<span class="breadcrumbs__sep">/</span>
					<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'showtime-pools' ); ?></a>
					<?php if ( $primary_cat ) : ?>
						<span class="breadcrumbs__sep">/</span>
						<a href="<?php echo esc_url( get_category_link( $primary_cat ) ); ?>"><?php echo esc_html( $primary_cat->name ); ?></a>
					<?php endif; ?>
				</nav>
				<div class="post-hero__inner">
					<?php if ( $primary_cat ) : ?>
						<span class="post-hero__pill"><?php echo esc_html( $primary_cat->name ); ?></span>
					<?php endif; ?>
					<h1 class="post-hero__title balance"><?php the_title(); ?></h1>
					<p class="post-hero__meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span aria-hidden="true"> · </span>
						<?php
						printf(
							/* translators: %s author name */
							esc_html__( 'By %s', 'showtime-pools' ),
							esc_html( $author_name )
						);
						?>
					</p>
				</div>
			</div>
		</header>

		<div class="post-body">
			<div class="container">
				<div class="post-body__grid">

					<div class="post-body__main">
						<?php if ( has_excerpt() ) : ?>
							<p class="post-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="post-prose">
							<?php the_content(); ?>
						</div>

						<div class="post-share">
							<strong><?php esc_html_e( 'Share this article', 'showtime-pools' ); ?></strong>
							<a href="https://twitter.com/intent/tweet?url=<?php echo esc_attr( rawurlencode( get_permalink() ) ); ?>&text=<?php echo esc_attr( rawurlencode( get_the_title() ) ); ?>" target="_blank" rel="noopener">Twitter / X</a>
							<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">Facebook</a>
							<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">LinkedIn</a>
						</div>
					</div>

					<aside class="post-body__side">
						<div class="post-toc" data-toc>
							<h3 class="post-toc__title"><?php esc_html_e( 'On this page', 'showtime-pools' ); ?></h3>
							<ol class="post-toc__list" data-toc-list></ol>
						</div>

						<section class="blog-side blog-side--cta">
							<h3 class="blog-side__title"><?php esc_html_e( 'Talk to a real human.', 'showtime-pools' ); ?></h3>
							<p><?php esc_html_e( 'Free quote in one business day from Steve or a senior tech.', 'showtime-pools' ); ?></p>
							<a class="btn btn--primary" href="<?php echo esc_url( SHOWTIME_BOOKING_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a free quote', 'showtime-pools' ); ?></a>
						</section>
					</aside>
				</div>
			</div>
		</div>

	</article>

	<?php if ( ! empty( $related ) ) : ?>
		<section class="post-related" data-reveal>
			<div class="container">
				<header class="post-related__head">
					<span class="eyebrow"><?php esc_html_e( 'Keep reading', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php esc_html_e( 'Related articles', 'showtime-pools' ); ?></h2>
				</header>
				<div class="blog-grid">
					<?php foreach ( $related as $r ) : ?>
						<article class="blog-card">
							<a class="blog-card__link" href="<?php echo esc_url( $r['href'] ); ?>">
								<div class="blog-card__media">
									<?php if ( $r['img'] ) : ?>
										<img src="<?php echo esc_url( $r['img'] ); ?>" alt="" loading="lazy" decoding="async">
									<?php endif; ?>
									<span class="blog-card__pill"><?php echo esc_html( $r['pill'] ); ?></span>
								</div>
								<div class="blog-card__body">
									<time class="blog-card__date"><?php echo esc_html( $r['date'] ); ?></time>
									<h3 class="blog-card__title"><?php echo esc_html( $r['title'] ); ?></h3>
								</div>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

</main>

<script type="application/ld+json"><?php echo wp_json_encode( $article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode( $crumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endwhile;

get_footer();

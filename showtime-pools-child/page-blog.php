<?php
/**
 * Template Name: Blog Hub
 *
 * /blog/ — Blog landing page. Pulls latest posts when the WP `post` type
 * has content; falls back to a substantive "what we are writing about"
 * preview so the page is never empty.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_main', 1600 ) : '';

$posts = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 9,
		'post_status'    => 'publish',
	)
);
?>
<main id="primary" class="site-main interior-page">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $hero_img ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Blog', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Blog Insights', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Pool care, equipment, and design notes from the field.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'Real lessons from 1,800+ Sherman Oaks, Encino, Beverly Hills, and West Valley pools. Equipment recommendations we actually stand behind. Chemistry that works in LA water.', 'showtime-pools' ); ?>
				</p>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $posts ) ) : ?>

		<section class="int-section" data-reveal>
			<div class="container">
				<div class="blog-grid">
					<?php foreach ( $posts as $p ) : setup_postdata( $p ); ?>
						<article class="blog-card">
							<?php $thumb = get_the_post_thumbnail_url( $p->ID, 'large' ); ?>
							<?php if ( $thumb ) : ?>
								<a class="blog-card__media" href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>">
									<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>" loading="lazy">
								</a>
							<?php endif; ?>
							<div class="blog-card__body">
								<div class="blog-card__meta">
									<time datetime="<?php echo esc_attr( get_the_date( 'c', $p->ID ) ); ?>"><?php echo esc_html( get_the_date( '', $p->ID ) ); ?></time>
									<span aria-hidden="true">·</span>
									<span><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 4, '' ) ); ?></span>
								</div>
								<h2 class="blog-card__title"><a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></a></h2>
								<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 30 ) ); ?></p>
								<a class="blog-card__cta" href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>"><?php esc_html_e( 'Read article →', 'showtime-pools' ); ?></a>
							</div>
						</article>
					<?php endforeach; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>

	<?php else : ?>

		<section class="int-section" data-reveal>
			<div class="container">
				<header class="int-section__head">
					<span class="eyebrow"><?php esc_html_e( 'In the pipeline', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php esc_html_e( 'Articles dropping over the next few weeks.', 'showtime-pools' ); ?></h2>
					<p class="int-section__lead"><?php esc_html_e( 'Subscribe at the bottom of any page and we will send the first post when it goes live. No drip campaigns, just the article.', 'showtime-pools' ); ?></p>
				</header>

				<div class="blog-grid">
					<?php
					$preview = array(
						array(
							'cat'   => 'Equipment',
							'title' => 'Variable-Speed Pumps in LA: When the LADWP Rebate Pays Back the Upgrade',
							'body'  => 'A breakdown of what the LADWP rebate actually covers, what it does not, and the real-world payback math on a Pentair IntelliFlo3 in a 14×28 backyard pool in Sherman Oaks.',
							'img'   => 'project_3',
						),
						array(
							'cat'   => 'Chemistry',
							'title' => 'Why LA Water Calcium Scaling Wrecks Plaster Faster Than You Think',
							'body'  => 'LADWP water clocks 280-320 ppm calcium hardness. Untreated, that is the #1 reason a five-year plaster reads like a ten-year plaster. The 30-day balance plan we run on every new finish.',
							'img'   => 'project_4',
						),
						array(
							'cat'   => 'Inspections',
							'title' => 'Pre-Purchase Pool Inspection: Five Things General Home Inspectors Always Miss',
							'body'  => 'Bonding grid continuity. VGB drain compliance. Heater chimney clearance. Pad-to-pool plumbing layout. Pump runtime hours. None of these get five minutes from a generalist; we spend ninety on each.',
							'img'   => 'project_6',
						),
						array(
							'cat'   => 'Remodel',
							'title' => 'PebbleTec vs Quartz vs White Plaster: Honest Cost-per-Year Math',
							'body'  => 'White plaster lasts 7-12 years. Quartz lasts 12-18. PebbleTec runs 20+. With a 5-year warranty in the picture, the cost-per-year picture flips by a lot. The full breakdown with current LA pricing.',
							'img'   => 'project_5',
						),
						array(
							'cat'   => 'Service',
							'title' => 'Same-Tech-Every-Week: Why Tight Geography Beats National Pool Chains',
							'body'  => 'Why we restrict our weekly route to six neighborhoods on purpose. The math on why same-tech-every-week beats five-techs-a-year, even when the chain quotes you 25 percent less.',
							'img'   => 'project_7',
						),
						array(
							'cat'   => 'Construction',
							'title' => 'Hillside Pool Builds in Studio City: Pier Engineering, Bonding, Permits',
							'body'  => 'Studio City hillside lots are pier-supported. Bonding grids on older builds are non-existent. The permit path through LA County for a hillside pool, with timelines, costs, and what derails most builds.',
							'img'   => 'project_8',
						),
					);
					foreach ( $preview as $p ) :
						$img = function_exists( 'showtime_image' ) ? showtime_image( $p['img'], 800 ) : '';
					?>
						<article class="blog-card">
							<?php if ( $img ) : ?>
								<div class="blog-card__media">
									<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy">
								</div>
							<?php endif; ?>
							<div class="blog-card__body">
								<div class="blog-card__meta">
									<span class="blog-card__cat"><?php echo esc_html( $p['cat'] ); ?></span>
									<span aria-hidden="true">·</span>
									<span><?php esc_html_e( 'Coming soon', 'showtime-pools' ); ?></span>
								</div>
								<h2 class="blog-card__title"><?php echo esc_html( $p['title'] ); ?></h2>
								<p class="blog-card__excerpt"><?php echo esc_html( $p['body'] ); ?></p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

	<?php endif; ?>


</main>
<?php get_footer();

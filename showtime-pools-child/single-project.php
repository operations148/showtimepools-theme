<?php
/**
 * Single Project — portfolio template for the `project` CPT.
 *
 * Standalone (does NOT extend single.php / the blog template). Sections:
 *   1. Contained hero band with overlaid breadcrumb + neighborhood pill + H1 + lead
 *   2. Meta strip (Neighborhood · Finish · Scope · Duration · Investment)
 *   3. Long-form body (Gutenberg content)
 *   4. Before / After comparison (if ACF images set)
 *   5. Gallery grid (if ACF gallery populated)
 *   6. Client quote (if ACF quote set)
 *   7. Related projects — 3 cards, same neighborhood preferred
 *   8. Footer CTA banner (already lives in footer.php)
 *
 * Schema: emits CreativeWork + BreadcrumbList JSON-LD. NO Article schema.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid = get_the_ID();

	// Pull every project field. ACF JSON auto-sync requires an admin
	// visit to register the field group, so until then `get_field()`
	// returns null for unregistered keys. The post meta written by the
	// seeder lives at the same keys, so fall back to `get_post_meta()`
	// for the scalar fields. Image / repeater fields stay ACF-only —
	// they're only meaningful once the user has uploaded media via WP
	// admin (which by definition means ACF is loaded).
	$proj_field = static function ( string $key, int $pid ) {
		$v = function_exists( 'get_field' ) ? get_field( $key, $pid ) : null;
		if ( null === $v || '' === $v ) {
			$v = get_post_meta( $pid, $key, true );
		}
		return $v;
	};

	$neighborhood = (string) $proj_field( 'neighborhood', $pid );
	$finish       = (string) $proj_field( 'finish', $pid );
	$scope        = (string) $proj_field( 'scope', $pid );
	$value_label  = (string) $proj_field( 'value_label', $pid );
	$duration     = (string) $proj_field( 'duration_label', $pid );
	$completion   = (string) $proj_field( 'completion_date', $pid );
	$client_quote = (string) $proj_field( 'client_quote', $pid );
	$client_name  = (string) $proj_field( 'client_name', $pid );
	$before_img   = function_exists( 'get_field' ) ? get_field( 'before_image', $pid ) : null;
	$after_img    = function_exists( 'get_field' ) ? get_field( 'after_image', $pid ) : null;
	$gallery      = function_exists( 'get_field' ) ? get_field( 'gallery', $pid ) : null;

	// Resolve hero image: featured image → ACF after_image → bundled project_<order> photo.
	$slot      = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $pid );
	$hero_img  = '';
	if ( has_post_thumbnail( $pid ) ) {
		$hero_img = (string) get_the_post_thumbnail_url( $pid, 'full' );
	} elseif ( is_array( $after_img ) && ! empty( $after_img['url'] ) ) {
		$hero_img = (string) ( $after_img['sizes']['large'] ?? $after_img['url'] );
	} elseif ( function_exists( 'showtime_image' ) ) {
		$hero_img = showtime_image( $slot, 1920 );
	}

	// JSON-LD: CreativeWork + BreadcrumbList. No Article schema.
	$creative_work = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'CreativeWork',
		'@id'             => get_permalink( $pid ) . '#project',
		'name'            => get_the_title( $pid ),
		'headline'        => get_the_title( $pid ),
		'description'     => wp_strip_all_tags( get_the_excerpt( $pid ) ),
		'image'           => $hero_img,
		'datePublished'   => get_the_date( 'c', $pid ),
		'dateModified'    => get_the_modified_date( 'c', $pid ),
		'creator'         => array( '@id' => home_url( '/#localbusiness' ) ),
		'publisher'       => array( '@id' => home_url( '/#localbusiness' ) ),
		'locationCreated' => array(
			'@type' => 'Place',
			'name'  => '' !== $neighborhood ? $neighborhood . ', Los Angeles' : 'Los Angeles',
		),
		'about'           => array(
			'@type' => 'Thing',
			'name'  => '' !== $scope ? $scope : 'Pool construction',
		),
		'url'             => get_permalink( $pid ),
	);

	$breadcrumb = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => home_url( '/' ) ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Projects', 'item' => home_url( '/projects/' ) ),
			array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $pid ), 'item' => get_permalink( $pid ) ),
		),
	);

	// Related projects — prefer same neighborhood, else 3 most recent siblings.
	$related = array();
	$base_args = array(
		'post_type'      => 'project',
		'posts_per_page' => 3,
		'post__not_in'   => array( $pid ),
		'orderby'        => 'rand',
		'no_found_rows'  => true,
	);
	if ( '' !== $neighborhood ) {
		$nb_args = $base_args + array(
			'meta_query' => array(
				array( 'key' => 'neighborhood', 'value' => $neighborhood, 'compare' => '=' ),
			),
		);
		$rq = new WP_Query( $nb_args );
		if ( $rq->have_posts() ) {
			while ( $rq->have_posts() ) {
				$rq->the_post();
				$related[] = get_the_ID();
			}
			wp_reset_postdata();
		}
	}
	if ( count( $related ) < 3 ) {
		$fill = 3 - count( $related );
		$f_args = $base_args;
		$f_args['posts_per_page'] = $fill;
		$f_args['post__not_in']   = array_merge( $f_args['post__not_in'], $related );
		$rq = new WP_Query( $f_args );
		while ( $rq->have_posts() ) {
			$rq->the_post();
			$related[] = get_the_ID();
		}
		wp_reset_postdata();
	}
?>
<main id="primary" class="site-main proj-single">

	<section class="proj-single__hero proj-single__hero--poolax" data-reveal>
		<div class="proj-single__hero-bg" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs proj-single__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
			</nav>
			<div class="proj-single__hero-grid">
				<div class="proj-single__hero-text">
					<?php if ( '' !== $neighborhood ) : ?>
						<span class="proj-single__pill"><?php echo esc_html( $neighborhood ); ?></span>
					<?php endif; ?>
					<h1 class="proj-single__title balance"><?php the_title(); ?></h1>
					<?php $lede = wp_strip_all_tags( get_the_excerpt( $pid ) );
					if ( '' !== $lede ) : ?>
						<p class="proj-single__lede"><?php echo esc_html( $lede ); ?></p>
					<?php endif; ?>
				</div>
				<div class="proj-single__hero-visual" aria-hidden="true">
					<?php if ( $hero_img ) : ?>
						<div class="proj-single__hero-photo proj-single__hero-photo--blob">
							<img src="<?php echo esc_url( $hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	$meta_rows = array(
		array( 'k' => __( 'Neighborhood', 'showtime-pools' ), 'v' => $neighborhood ),
		array( 'k' => __( 'Finish', 'showtime-pools' ),       'v' => $finish ),
		array( 'k' => __( 'Scope', 'showtime-pools' ),        'v' => $scope ),
		array( 'k' => __( 'Duration', 'showtime-pools' ),     'v' => $duration ),
		array( 'k' => __( 'Investment', 'showtime-pools' ),   'v' => $value_label ),
		array( 'k' => __( 'Completed', 'showtime-pools' ),    'v' => $completion ),
	);
	$meta_rows = array_filter( $meta_rows, static fn( $r ) => '' !== $r['v'] );
	if ( ! empty( $meta_rows ) ) :
	?>
	<section class="proj-single__meta" data-reveal>
		<div class="container">
			<dl class="proj-meta-strip">
				<?php foreach ( $meta_rows as $row ) : ?>
					<div class="proj-meta-strip__cell">
						<dt><?php echo esc_html( $row['k'] ); ?></dt>
						<dd><?php echo esc_html( $row['v'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>
	<?php endif; ?>

	<?php $content = apply_filters( 'the_content', get_the_content() );
	if ( '' !== trim( wp_strip_all_tags( $content ) ) ) : ?>
		<section class="proj-single__body" data-reveal>
			<div class="container">
				<div class="proj-single__prose post-prose">
					<?php echo $content; // already filtered ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Before / After comparison.
	$has_before = is_array( $before_img ) && ! empty( $before_img['url'] );
	$has_after  = is_array( $after_img )  && ! empty( $after_img['url'] );
	if ( $has_before || $has_after ) :
	?>
		<section class="proj-single__compare" data-reveal>
			<div class="container">
				<header class="proj-single__head">
					<span class="eyebrow"><?php esc_html_e( 'Before / After', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php esc_html_e( 'The transformation.', 'showtime-pools' ); ?></h2>
				</header>
				<div class="proj-compare-grid<?php echo ( $has_before && $has_after ) ? '' : ' proj-compare-grid--single'; ?>">
					<?php if ( $has_before ) : ?>
						<figure class="proj-compare-grid__item">
							<img src="<?php echo esc_url( $before_img['sizes']['large'] ?? $before_img['url'] ); ?>" alt="<?php echo esc_attr( get_the_title() . ' — before' ); ?>" loading="lazy" decoding="async">
							<figcaption><?php esc_html_e( 'Before', 'showtime-pools' ); ?></figcaption>
						</figure>
					<?php endif; ?>
					<?php if ( $has_after ) : ?>
						<figure class="proj-compare-grid__item">
							<img src="<?php echo esc_url( $after_img['sizes']['large'] ?? $after_img['url'] ); ?>" alt="<?php echo esc_attr( get_the_title() . ' — after' ); ?>" loading="lazy" decoding="async">
							<figcaption><?php esc_html_e( 'After', 'showtime-pools' ); ?></figcaption>
						</figure>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( is_array( $gallery ) && ! empty( $gallery ) ) : ?>
		<section class="proj-single__gallery" data-reveal>
			<div class="container">
				<header class="proj-single__head">
					<span class="eyebrow"><?php esc_html_e( 'Gallery', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php esc_html_e( 'More from this project.', 'showtime-pools' ); ?></h2>
				</header>
				<div class="proj-gallery-grid">
					<?php foreach ( $gallery as $g ) :
						$g_url = (string) ( $g['sizes']['large'] ?? $g['url'] ?? '' );
						$g_full = (string) ( $g['url'] ?? $g_url );
						$g_alt = (string) ( $g['alt'] ?? '' );
						if ( '' === $g_url ) { continue; }
					?>
						<a class="proj-gallery-grid__item" href="<?php echo esc_url( $g_full ); ?>" target="_blank" rel="noopener">
							<img src="<?php echo esc_url( $g_url ); ?>" alt="<?php echo esc_attr( $g_alt ); ?>" loading="lazy" decoding="async">
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== $client_quote ) : ?>
		<section class="proj-single__quote int-section--cream" data-reveal>
			<div class="container">
				<figure class="pull-quote">
					<svg class="pull-quote__mark" width="56" height="56" viewBox="0 0 48 48" fill="none" aria-hidden="true">
						<path d="M14 14h10v10c0 5.523-4.477 10-10 10V30c2.761 0 5-2.239 5-5h-5V14zM30 14h10v10c0 5.523-4.477 10-10 10V30c2.761 0 5-2.239 5-5h-5V14z" fill="currentColor"/>
					</svg>
					<blockquote><?php echo esc_html( $client_quote ); ?></blockquote>
					<?php if ( '' !== $client_name ) : ?>
						<figcaption><?php echo esc_html( $client_name ); ?></figcaption>
					<?php elseif ( '' !== $neighborhood ) : ?>
						<figcaption><?php echo esc_html( sprintf( /* translators: %s neighborhood */ __( 'Homeowner · %s', 'showtime-pools' ), $neighborhood ) ); ?></figcaption>
					<?php endif; ?>
				</figure>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $related ) ) : ?>
		<section class="proj-single__related" data-reveal>
			<div class="container">
				<header class="proj-single__head">
					<span class="eyebrow"><?php esc_html_e( 'More projects', 'showtime-pools' ); ?></span>
					<h2 class="balance">
						<?php
						if ( '' !== $neighborhood ) {
							/* translators: %s neighborhood name */
							printf( esc_html__( 'More work in %s and across LA.', 'showtime-pools' ), esc_html( $neighborhood ) );
						} else {
							esc_html_e( 'More work across LA.', 'showtime-pools' );
						}
						?>
					</h2>
				</header>
				<div class="featured-projects__grid">
					<?php
					$gradients = array(
						'linear-gradient(135deg,#1F2F3A,#5C8A9E)',
						'linear-gradient(135deg,#314A58,#88A4B6)',
						'linear-gradient(135deg,#3F6072,#B0C5D2)',
					);
					$i = 0;
					foreach ( $related as $rid ) :
						$r_slot   = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $rid );
						$r_image  = has_post_thumbnail( $rid )
							? (string) get_the_post_thumbnail_url( $rid, 'large' )
							: ( function_exists( 'showtime_image' ) ? showtime_image( $r_slot, 1024 ) : '' );
						$r_neigh  = function_exists( 'get_field' ) ? (string) get_field( 'neighborhood', $rid ) : '';
						$r_scope  = function_exists( 'get_field' ) ? (string) get_field( 'scope', $rid ) : '';
						$r_finish = function_exists( 'get_field' ) ? (string) get_field( 'finish', $rid ) : '';
						$gr       = $gradients[ $i++ % count( $gradients ) ];
					?>
						<a class="proj-card" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
							<div class="proj-card__media" style="background:<?php echo esc_attr( $gr ); ?>">
								<?php if ( $r_image ) : ?>
									<img class="proj-card__media-img" src="<?php echo esc_url( $r_image ); ?>" alt="" loading="lazy" decoding="async">
								<?php endif; ?>
								<?php if ( $r_neigh ) : ?>
									<span class="proj-card__neighborhood"><?php echo esc_html( $r_neigh ); ?></span>
								<?php endif; ?>
							</div>
							<div class="proj-card__body">
								<h3 class="proj-card__title"><?php echo esc_html( get_the_title( $rid ) ); ?></h3>
								<dl class="proj-card__meta">
									<?php if ( $r_scope ) : ?><div><dt><?php esc_html_e( 'Scope', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $r_scope ); ?></dd></div><?php endif; ?>
									<?php if ( $r_finish ) : ?><div><dt><?php esc_html_e( 'Finish', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $r_finish ); ?></dd></div><?php endif; ?>
								</dl>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

</main>

<script type="application/ld+json"><?php echo wp_json_encode( $creative_work, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<script type="application/ld+json"><?php echo wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endwhile;

get_footer();

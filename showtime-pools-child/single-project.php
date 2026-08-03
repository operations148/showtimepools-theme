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

	// ── Code-managed project data ────────────────────────────────────────
	// showtime_project_data() is THE resolver. For a managed project it returns
	// the registry entry and WordPress post meta is never consulted, so a stale
	// seeded value cannot leak onto the page. Unmanaged/legacy projects keep the
	// historical post-meta path in the else branch.
	$proj = function_exists( 'showtime_project_data' ) ? showtime_project_data( $pid ) : null;

	if ( null !== $proj ) {
		$neighborhood = $proj['neighborhood'];
		$finish       = $proj['finish'];
		$scope        = $proj['scope'];
		$completion   = $proj['completion_date'];
		$client_quote = $proj['client_quote'];
		$client_name  = '';
		$proj_title   = '' !== $proj['title'] ? $proj['title'] : get_the_title( $pid );
		$proj_lede    = $proj['excerpt'];
		$hero_img     = $proj['hero_image'];
		$hero_alt     = $proj['hero_alt'];
		$meta_rows    = showtime_project_meta_rows( $proj );
		$gallery      = null;
	} else {
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
		$completion   = (string) $proj_field( 'completion_date', $pid );
		$client_quote = (string) $proj_field( 'client_quote', $pid );
		$client_name  = (string) $proj_field( 'client_name', $pid );
		$proj_title   = get_the_title( $pid );
		$proj_lede    = wp_strip_all_tags( get_the_excerpt( $pid ) );
		$hero_alt     = '';
		$gallery      = function_exists( 'get_field' ) ? get_field( 'gallery', $pid ) : null;

		$slot     = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $pid );
		$hero_img = has_post_thumbnail( $pid )
			? (string) get_the_post_thumbnail_url( $pid, 'full' )
			: ( function_exists( 'showtime_image' ) ? showtime_image( $slot, 1920 ) : '' );

		$meta_rows = array_values(
			array_filter(
				array(
					array( 'k' => __( 'Neighborhood', 'showtime-pools' ), 'v' => $neighborhood ),
					array( 'k' => __( 'Finish', 'showtime-pools' ),       'v' => $finish ),
					array( 'k' => __( 'Scope', 'showtime-pools' ),        'v' => $scope ),
					array( 'k' => __( 'Completed', 'showtime-pools' ),    'v' => $completion ),
				),
				static function ( $r ) {
					return '' !== trim( (string) $r['v'] );
				}
			)
		);
	}

	// JSON-LD: CreativeWork + BreadcrumbList. No Article schema.
	$creative_work = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'CreativeWork',
		'@id'             => get_permalink( $pid ) . '#project',
		'name'            => $proj_title,
		'headline'        => $proj_title,
		'description'     => $proj_lede,
		'image'           => $hero_img,
		'datePublished'   => get_the_date( 'c', $pid ),
		'dateModified'    => get_the_modified_date( 'c', $pid ),
		'creator'         => array( '@id' => home_url( '/#organization' ) ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
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
			array( '@type' => 'ListItem', 'position' => 3, 'name' => $proj_title, 'item' => get_permalink( $pid ) ),
		),
	);

	// Related projects — prefer same neighborhood, else 3 most recent siblings.
	// Unmanaged/legacy posts are excluded from both queries below: their
	// `neighborhood` post meta (written once by the one-time seeder) can still
	// match a managed project's own leftover meta row, which would otherwise
	// surface unverified copy as a "related project". See
	// showtime_unmanaged_project_ids().
	$unmanaged = function_exists( 'showtime_unmanaged_project_ids' ) ? showtime_unmanaged_project_ids() : array();
	$related   = array();
	$base_args = array(
		'post_type'      => 'project',
		'posts_per_page' => 3,
		'post__not_in'   => array_merge( array( $pid ), $unmanaged ),
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

	<section class="proj-single__hero" data-reveal>
		<?php if ( $hero_img ) : ?>
			<img class="proj-single__hero-photo" src="<?php echo esc_url( $hero_img ); ?>" alt="<?php echo esc_attr( '' !== $hero_alt ? $hero_alt : $proj_title ); ?>" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="container">
			<nav class="breadcrumbs proj-single__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( $proj_title ); ?></span>
			</nav>
			<div class="proj-single__hero-inner">
				<?php if ( '' !== $neighborhood ) : ?>
					<span class="proj-single__pill"><?php echo esc_html( $neighborhood ); ?></span>
				<?php endif; ?>
				<h1 class="proj-single__title balance"><?php echo esc_html( $proj_title ); ?></h1>
				<?php if ( '' !== $proj_lede ) : ?>
					<p class="proj-single__lede"><?php echo esc_html( $proj_lede ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	// $meta_rows is built above by showtime_project_meta_rows() for managed
	// projects (fixed labels: Neighborhood, Finish, Scope, Typical timeline,
	// Typical investment, Completed) and by the legacy branch otherwise. Blank
	// optional values are already dropped, so an empty set hides the strip.
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
	// Before / After comparison. Returns null unless BOTH photographs and the
	// written copy resolve, so a partial record renders nothing at all.
	$compare = function_exists( 'showtime_project_compare' ) ? showtime_project_compare( $pid ) : null;
	if ( null !== $compare ) :
		$cmp_id = 'proj-compare-' . $pid;
		$facts  = array_filter(
			array(
				__( 'Before', 'showtime-pools' )         => $compare['before_condition'],
				__( 'Work completed', 'showtime-pools' ) => $compare['work_completed'],
				__( 'Result', 'showtime-pools' )         => $compare['completed_result'],
			),
			static fn( $v ) => '' !== $v
		);
	?>
		<section class="proj-compare" data-reveal aria-labelledby="<?php echo esc_attr( $cmp_id ); ?>-h">
			<div class="container">
				<header class="proj-compare__head">
					<span class="eyebrow proj-compare__eyebrow"><?php echo esc_html( $compare['eyebrow'] ); ?></span>
					<h2 id="<?php echo esc_attr( $cmp_id ); ?>-h" class="balance"><?php echo esc_html( $compare['heading'] ); ?></h2>
					<p class="proj-compare__summary"><?php echo esc_html( $compare['summary'] ); ?></p>
				</header>

				<?php
				// Both photographs ship in the initial HTML, side by side. JS may
				// later promote this to an overlay slider; without JS it stays a
				// perfectly usable labelled pair.
				$media_class = 'proj-compare__media' . ( $compare['sliderable'] ? ' proj-compare__media--sliderable' : '' );
				?>
				<div class="<?php echo esc_attr( $media_class ); ?>"
					id="<?php echo esc_attr( $cmp_id ); ?>"
					<?php if ( $compare['sliderable'] ) : ?>data-proj-compare<?php endif; ?>>
					<?php
					foreach ( array( 'before', 'after' ) as $side ) :
						$img   = $compare[ $side ];
						$label = 'before' === $side
							? __( 'Before', 'showtime-pools' )
							: __( 'After', 'showtime-pools' );
					?>
						<figure class="proj-compare__frame proj-compare__frame--<?php echo esc_attr( $side ); ?>">
							<picture>
								<img
									src="<?php echo esc_url( $img['url'] ); ?>"
									<?php if ( '' !== $img['srcset'] ) : ?>
									srcset="<?php echo esc_attr( $img['srcset'] ); ?>"
									sizes="<?php echo esc_attr( $img['sizes'] ); ?>"
									<?php endif; ?>
									width="<?php echo esc_attr( (string) $img['width'] ); ?>"
									height="<?php echo esc_attr( (string) $img['height'] ); ?>"
									alt="<?php echo esc_attr( $img['alt'] ); ?>"
									loading="lazy"
									decoding="async">
							</picture>
							<figcaption class="proj-compare__label"><?php echo esc_html( $label ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( ! empty( $facts ) ) : ?>
					<dl class="proj-compare__facts">
						<?php foreach ( $facts as $k => $v ) : ?>
							<div class="proj-compare__fact">
								<dt><?php echo esc_html( $k ); ?></dt>
								<dd><?php echo esc_html( $v ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( ! empty( $compare['links'] ) ) : ?>
					<p class="proj-compare__links">
						<?php
						$primary = $compare['links']['primary'] ?? null;
						$second  = $compare['links']['secondary'] ?? null;
						$area    = $compare['links']['area'] ?? null;

						if ( $primary ) {
							printf(
								/* translators: %s: linked service name, e.g. "pool remodeling & resurfacing services" */
								esc_html__( 'This project was delivered by our %s', 'showtime-pools' ),
								'<a href="' . esc_url( $primary['url'] ) . '">' . esc_html( $primary['label'] ) . '</a>'
							);
							if ( $second ) {
								printf(
									/* translators: %s: linked secondary service name */
									esc_html__( ', with %s', 'showtime-pools' ),
									'<a href="' . esc_url( $second['url'] ) . '">' . esc_html( $second['label'] ) . '</a>'
								);
							}
							echo '. ';
						}
						if ( $area ) {
							printf(
								/* translators: %s: linked service-area name, e.g. "pool services in Sherman Oaks" */
								esc_html__( 'See all %s.', 'showtime-pools' ),
								'<a href="' . esc_url( $area['url'] ) . '">' . esc_html( $area['label'] ) . '</a>'
							);
						}
						?>
					</p>
				<?php endif; ?>
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
						// Same resolver as every other surface. The old code called
						// get_field() with no fallback, so with ACF absent these
						// were always empty and related cards rendered blank.
						$r_data   = function_exists( 'showtime_project_data' ) ? showtime_project_data( $rid ) : null;
						$r_slot   = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $rid );
						$r_title  = $r_data['title'] ?? get_the_title( $rid );
						$r_neigh  = $r_data['neighborhood'] ?? '';
						$r_scope  = $r_data['scope'] ?? '';
						$r_finish = $r_data['finish'] ?? '';
						$r_alt    = $r_data['hero_alt'] ?? '';
						$r_image  = $r_data['hero_image'] ?? '';
						if ( '' === $r_image ) {
							$r_image = has_post_thumbnail( $rid )
								? (string) get_the_post_thumbnail_url( $rid, 'large' )
								: ( function_exists( 'showtime_image' ) ? showtime_image( $r_slot, 1024 ) : '' );
						}
						$gr       = $gradients[ $i++ % count( $gradients ) ];
					?>
						<a class="proj-card" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
							<div class="proj-card__media" style="background:<?php echo esc_attr( $gr ); ?>">
								<?php if ( $r_image ) : ?>
									<img class="proj-card__media-img" src="<?php echo esc_url( $r_image ); ?>" alt="<?php echo esc_attr( $r_alt ); ?>" loading="lazy" decoding="async">
								<?php endif; ?>
								<?php if ( $r_neigh ) : ?>
									<span class="proj-card__neighborhood"><?php echo esc_html( $r_neigh ); ?></span>
								<?php endif; ?>
							</div>
							<div class="proj-card__body">
								<h3 class="proj-card__title"><?php echo esc_html( $r_title ); ?></h3>
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

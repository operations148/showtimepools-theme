<?php
/**
 * Template Name: Projects
 *
 * /projects/ placeholder. Phase 2A replaces this with the full Mapbox map.
 * Until then we render a substantial gallery so the page does not feel empty.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

// The archive is driven by the CODE REGISTRY, not by WP_Query.
//
// showtime_project_slides() reads showtime-pools-core/includes/data/projects.php
// through the shared resolver and returns the managed projects, in registry
// order, chunked into slides of six (14 projects -> 6 / 6 / 2). Three things
// follow from that, all of them load-bearing:
//
//   1. Slide grouping is deterministic. A WP_Query ordered by menu_order would
//      put newly created routing shells (menu_order 0) AHEAD of the six
//      verified projects (menu_order 1-6), silently reshuffling the slides.
//   2. Every card renders even before `wp showtime projects sync --apply` has
//      created its routing post — showtime_project_permalink() falls back to
//      the canonical /projects/{slug}/ path.
//   3. Legacy unmanaged seed rows are excluded by construction, because the
//      resolver returns null for them. No post__not_in needed here.
//
// All 14 cards are emitted server-side. JavaScript only paginates them.
$slides = function_exists( 'showtime_project_slides' ) ? showtime_project_slides( 6 ) : array();

// Gradients cycle so the grid stays visually rhythmic behind each card photo,
// and give placeholder cards (which have no photo yet) a branded surface.
$gradients = array(
	'linear-gradient(135deg,#1F2F3A,#5C8A9E)',
	'linear-gradient(135deg,#314A58,#88A4B6)',
	'linear-gradient(135deg,#3F6072,#B0C5D2)',
	'linear-gradient(135deg,#0A0A0A,#4D7589)',
	'linear-gradient(135deg,#1F1F1F,#6E94A9)',
);
$slide_count = count( $slides );

// ── Native WP fields (edit via WP Admin → Pages → Projects → Update) ────────
$pid          = get_the_ID();
$hero_h1      = get_the_title();
$hero_eyebrow = (string) get_post_meta( $pid, 'hero_eyebrow', true );
$hero_lead    = (string) get_post_meta( $pid, 'hero_lead',    true );

if ( '' === $hero_eyebrow ) { $hero_eyebrow = 'Recent work'; }
if ( '' === $hero_lead )    { $hero_lead    = 'A full interactive map with photos, scope, and verified review per pin is rolling out. Until then, here are recent projects from across the route.'; }
?>
<main id="primary" class="site-main interior-page">

	<?php $projects_hero = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_main', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $projects_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $projects_hero ); ?>" <?php echo showtime_hero_srcset_attr( 'lifestyle_main' ); ?> alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $hero_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>"><?php esc_html_e( 'Start your project', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<?php
			// Progressive enhancement contract:
			//   - No JS  -> `.proj-slider` has no `is-enhanced` class, so the CSS
			//               stacks every slide vertically as a readable grid and
			//               hides the nav. All 14 cards are visible and linked.
			//   - With JS -> slider.js adds `is-enhanced`, which switches the
			//               track to a horizontal transform and reveals the nav.
			// The cards themselves are identical in both states — JS never
			// creates, fetches or injects a card.
			if ( ! empty( $slides ) ) :
			?>
			<div class="proj-slider"
				data-proj-slider
				role="region"
				aria-roledescription="carousel"
				aria-label="<?php esc_attr_e( 'Showtime Pools projects', 'showtime-pools' ); ?>">
				<div class="proj-slider__viewport" data-proj-slider-viewport>
					<div class="proj-slider__track" data-proj-slider-track>
						<?php foreach ( $slides as $s_index => $slide ) :
							$slide_no = $s_index + 1;
							// A trailing partial slide is centred rather than
							// padded with empty cards, so the cards keep the exact
							// width they have on a full slide.
							$is_partial = count( $slide ) < 3;
						?>
							<div class="proj-slider__slide"
								role="group"
								aria-roledescription="slide"
								aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total slides */ __( 'Slide %1$d of %2$d', 'showtime-pools' ), $slide_no, $slide_count ) ); ?>"
								data-proj-slider-slide="<?php echo esc_attr( (string) $s_index ); ?>">
								<div class="featured-projects__grid proj-slider__grid<?php echo $is_partial ? ' proj-slider__grid--center' : ''; ?>">
									<?php foreach ( $slide as $c_index => $p ) :
										$href        = (string) ( $p['href'] ?? '' );
										$tag         = '' === $href ? 'article' : 'a';
										$placeholder = ! empty( $p['is_coming_soon'] );
										$gradient    = $gradients[ ( $s_index * 6 + $c_index ) % count( $gradients ) ];
									?>
										<<?php echo esc_html( $tag ); ?> class="proj-card<?php echo $placeholder ? ' proj-card--placeholder' : ''; ?>"<?php if ( '' !== $href ) : ?> href="<?php echo esc_url( $href ); ?>"<?php endif; ?>>
											<div class="proj-card__media<?php echo $placeholder ? ' proj-card__media--placeholder' : ''; ?>" style="background:<?php echo esc_attr( $gradient ); ?>">
												<?php if ( ! empty( $p['image'] ) ) : ?>
													<img class="proj-card__media-img" src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( (string) ( $p['image_alt'] ?? '' ) ); ?>" loading="lazy" decoding="async">
												<?php elseif ( $placeholder ) : ?>
													<?php
													// CSS-only branded placeholder. Never a stock photo,
													// an external image, or another project's photograph.
													?>
													<span class="proj-card__coming" aria-hidden="true">
														<svg class="proj-card__coming-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M3 7h3l2-2h8l2 2h3v12H3z"/><circle cx="12" cy="13" r="3.5"/></svg>
														<span class="proj-card__coming-text"><?php esc_html_e( 'Project photos coming soon', 'showtime-pools' ); ?></span>
													</span>
												<?php endif; ?>
												<?php if ( ! empty( $p['neighborhood'] ) ) : ?>
													<span class="proj-card__neighborhood"><?php echo esc_html( $p['neighborhood'] ); ?></span>
												<?php endif; ?>
												<?php if ( $placeholder ) : ?>
													<span class="proj-card__status"><?php esc_html_e( 'Coming Soon', 'showtime-pools' ); ?></span>
												<?php endif; ?>
											</div>
											<div class="proj-card__body">
												<h3 class="proj-card__title"><?php echo esc_html( (string) $p['title'] ); ?></h3>
												<dl class="proj-card__meta">
													<?php if ( ! empty( $p['scope'] ) ) : ?><div><dt><?php esc_html_e( 'Scope', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $p['scope'] ); ?></dd></div><?php endif; ?>
													<?php if ( ! empty( $p['finish'] ) ) : ?><div><dt><?php esc_html_e( 'Finish', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $p['finish'] ); ?></dd></div><?php endif; ?>
													<?php if ( ! empty( $p['duration'] ) ) : ?><div><dt><?php esc_html_e( 'Typical timeline', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $p['duration'] ); ?></dd></div><?php endif; ?>
													<?php if ( ! empty( $p['value'] ) ) : ?><div><dt><?php esc_html_e( 'Typical investment', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( (string) $p['value'] ); ?></dd></div><?php endif; ?>
												</dl>
											</div>
										</<?php echo esc_html( $tag ); ?>>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( $slide_count > 1 ) : ?>
					<div class="proj-slider__nav" data-proj-slider-nav hidden>
						<button type="button" class="proj-slider__arrow proj-slider__arrow--prev" data-proj-slider-prev aria-label="<?php esc_attr_e( 'Previous projects', 'showtime-pools' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 19l-7-7 7-7"/></svg>
						</button>
						<div class="proj-slider__dots" role="group" aria-label="<?php esc_attr_e( 'Choose a project slide', 'showtime-pools' ); ?>">
							<?php for ( $d = 0; $d < $slide_count; $d++ ) : ?>
								<button type="button"
									class="proj-slider__dot<?php echo 0 === $d ? ' is-active' : ''; ?>"
									data-proj-slider-dot="<?php echo esc_attr( (string) $d ); ?>"
									<?php echo 0 === $d ? 'aria-current="true"' : ''; ?>
									aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total slides */ __( 'Slide %1$d of %2$d', 'showtime-pools' ), $d + 1, $slide_count ) ); ?>">
									<span class="proj-slider__dot-hit"></span>
								</button>
							<?php endfor; ?>
						</div>
						<button type="button" class="proj-slider__arrow proj-slider__arrow--next" data-proj-slider-next aria-label="<?php esc_attr_e( 'Next projects', 'showtime-pools' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
						</button>
					</div>
					<p class="visually-hidden" aria-live="polite" data-proj-slider-status></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>


	<?php get_template_part( 'template-parts/home/section-08-reviews' ); ?>

</main>
<?php get_footer();

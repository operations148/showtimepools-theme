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

// /projects/ now queries the live Project CPT. Seeder writes 8 demo
// projects on first activation; admin can add more (or replace) freely.
// Gradients cycle so the grid stays visually rhythmic.
$gradients = array(
	'linear-gradient(135deg,#1F2F3A,#5C8A9E)',
	'linear-gradient(135deg,#314A58,#88A4B6)',
	'linear-gradient(135deg,#3F6072,#B0C5D2)',
	'linear-gradient(135deg,#0A0A0A,#4D7589)',
	'linear-gradient(135deg,#1F1F1F,#6E94A9)',
);

$projects = array();
if ( post_type_exists( 'project' ) ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'project',
			'posts_per_page' => 24,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$i = 0;
	while ( $q->have_posts() ) :
		$q->the_post();
		$pid  = get_the_ID();
		$slot = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $pid );
		$image = '';
		if ( has_post_thumbnail( $pid ) ) {
			$image = (string) get_the_post_thumbnail_url( $pid, 'large' );
		} elseif ( function_exists( 'showtime_image' ) ) {
			$image = showtime_image( $slot, 1024 );
		}
		$pm = static function ( string $k, int $id ): string {
			$v = function_exists( 'get_field' ) ? get_field( $k, $id ) : null;
			if ( null === $v || '' === $v ) { $v = get_post_meta( $id, $k, true ); }
			return (string) $v;
		};
		$projects[] = array(
			'image'        => $image,
			'title'        => get_the_title(),
			'href'         => get_permalink(),
			'neighborhood' => $pm( 'neighborhood', (int) $pid ),
			'scope'        => $pm( 'scope', (int) $pid ),
			'finish'       => $pm( 'finish', (int) $pid ),
			'duration'     => $pm( 'duration_label', (int) $pid ),
			'value'        => $pm( 'value_label', (int) $pid ),
			'gradient'     => $gradients[ $i++ % count( $gradients ) ],
		);
	endwhile;
	wp_reset_postdata();
}

// Soft fallback so the page never renders empty if the seeder hasn't run.
if ( empty( $projects ) ) {
	$projects = apply_filters(
		'showtime/projects_grid',
		array(
			array( 'image' => function_exists('showtime_image') ? showtime_image('project_1', 1024) : '', 'title' => 'Sherman Oaks mid-century remodel', 'href' => home_url('/projects/'), 'neighborhood' => 'Sherman Oaks',  'scope' => 'Resurface · Tile · Coping · Equipment', 'finish' => 'PebbleTec Cool Blue', 'duration' => '12 days', 'value' => '$28k',  'gradient' => $gradients[0] ),
			array( 'image' => function_exists('showtime_image') ? showtime_image('project_2', 1024) : '', 'title' => 'Encino estate new construction',   'href' => home_url('/projects/'), 'neighborhood' => 'Encino',        'scope' => 'New build · Hardscape · Fire features', 'finish' => 'PebbleTec Aqua White', 'duration' => '10 weeks', 'value' => '$142k', 'gradient' => $gradients[1] ),
			array( 'image' => function_exists('showtime_image') ? showtime_image('project_3', 1024) : '', 'title' => 'Studio City equipment overhaul',   'href' => home_url('/projects/'), 'neighborhood' => 'Studio City',   'scope' => 'Automation · Pump · Salt · Heater',     'finish' => 'Equipment only', 'duration' => '3 days', 'value' => '$8.6k', 'gradient' => $gradients[2] ),
		)
	);
}
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
			<img class="int-hero__photo" src="<?php echo esc_url( $projects_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
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
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Start your project', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="featured-projects__grid">
				<?php foreach ( $projects as $p ) :
					$href = (string) ( $p['href'] ?? '#' );
					$tag  = '#' === $href ? 'article' : 'a';
				?>
					<<?php echo esc_html( $tag ); ?> class="proj-card"<?php if ( '#' !== $href ) : ?> href="<?php echo esc_url( $href ); ?>"<?php endif; ?>>
						<div class="proj-card__media" style="background:<?php echo esc_attr( $p['gradient'] ); ?>">
							<?php if ( ! empty( $p['image'] ) ) : ?>
								<img class="proj-card__media-img" src="<?php echo esc_url( $p['image'] ); ?>" alt="" loading="lazy" decoding="async">
							<?php endif; ?>
							<?php if ( ! empty( $p['neighborhood'] ) ) : ?>
								<span class="proj-card__neighborhood"><?php echo esc_html( $p['neighborhood'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="proj-card__body">
							<h3 class="proj-card__title"><?php echo esc_html( $p['title'] ); ?></h3>
							<dl class="proj-card__meta">
								<?php if ( ! empty( $p['scope'] ) ) : ?><div><dt><?php esc_html_e( 'Scope', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['scope'] ); ?></dd></div><?php endif; ?>
								<?php if ( ! empty( $p['finish'] ) ) : ?><div><dt><?php esc_html_e( 'Finish', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['finish'] ); ?></dd></div><?php endif; ?>
								<?php if ( ! empty( $p['duration'] ) ) : ?><div><dt><?php esc_html_e( 'Duration', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['duration'] ); ?></dd></div><?php endif; ?>
								<?php if ( ! empty( $p['value'] ) ) : ?><div><dt><?php esc_html_e( 'Investment', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['value'] ); ?></dd></div><?php endif; ?>
							</dl>
						</div>
					</<?php echo esc_html( $tag ); ?>>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/section-08-reviews' ); ?>

</main>
<?php get_footer();

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

$projects = apply_filters(
	'showtime/projects_grid',
	array(
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_1', 800) : '', 'title' => 'Sherman Oaks ranch — full remodel',           'neighborhood' => 'Sherman Oaks',  'scope' => 'Replaster · Tile · PebbleTec · Equipment swap',         'finish' => 'PebbleTec Midnight Blue',  'duration' => '4 weeks',  'value' => '$28,400',  'gradient' => 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_2', 800) : '', 'title' => 'Encino estate — new construction',            'neighborhood' => 'Encino',        'scope' => 'New gunite · Spa · Sheer descent · Automation',          'finish' => 'Quartz · Travertine coping','duration' => '11 weeks', 'value' => '$142,000', 'gradient' => 'linear-gradient(135deg,#314A58,#88A4B6)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_3', 800) : '', 'title' => 'Studio City modern — equipment + automation', 'neighborhood' => 'Studio City',   'scope' => 'Pentair IntelliCenter · VS pump · Salt cell',            'finish' => 'Existing pebble retained',  'duration' => '4 days',   'value' => '$8,650',   'gradient' => 'linear-gradient(135deg,#3F6072,#B0C5D2)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_4', 800) : '', 'title' => 'Beverly Hills 1962 rebuild',                  'neighborhood' => 'Beverly Hills', 'scope' => 'Partial gunite · Tile · Coping · Pebble',                'finish' => 'PebbleSheen Caribbean',     'duration' => '7 weeks',  'value' => '$54,200',  'gradient' => 'linear-gradient(135deg,#0A0A0A,#4D7589)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_5', 800) : '', 'title' => 'Tarzana 1980s remodel',                       'neighborhood' => 'Tarzana',       'scope' => 'Replaster · Equipment swap · Heater · Salt cell',        'finish' => 'White plaster + glass tile','duration' => '3 weeks',  'value' => '$19,800',  'gradient' => 'linear-gradient(135deg,#1F1F1F,#6E94A9)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_6', 800) : '', 'title' => 'Woodland Hills heater + salt cell',           'neighborhood' => 'Woodland Hills','scope' => 'Raypak 406A · Pentair IC40 · LADWP rebate paperwork',    'finish' => 'Existing finish retained',  'duration' => '2 days',   'value' => '$4,950',   'gradient' => 'linear-gradient(135deg,#314A58,#5C8A9E)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_7', 800) : '', 'title' => 'Sherman Oaks tile + coping refresh',          'neighborhood' => 'Sherman Oaks',  'scope' => 'Waterline tile · Travertine coping · Caulk replacement', 'finish' => 'Glass mosaic step accent',  'duration' => '10 days',  'value' => '$11,200',  'gradient' => 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_8', 800) : '', 'title' => 'Studio City hillside bonding retrofit',       'neighborhood' => 'Studio City',   'scope' => 'Bonding grid · Electrical permit · Inspection',          'finish' => 'Code compliance only',      'duration' => '6 days',   'value' => '$7,400',   'gradient' => 'linear-gradient(135deg,#3F6072,#B0C5D2)' ),
		array( 'image' => function_exists('showtime_image') ? showtime_image('project_9', 800) : '', 'title' => 'Encino spa add-on',                           'neighborhood' => 'Encino',        'scope' => 'Raised spa · Spillway · Heater · Plumbing',              'finish' => 'Travertine deck match',     'duration' => '5 weeks',  'value' => '$38,900',  'gradient' => 'linear-gradient(135deg,#314A58,#88A4B6)' ),
	)
);
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
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Recent work', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Every pool we built. Every street we worked.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'A full interactive map with photos, scope, and verified review per pin is rolling out. Until then, here are recent projects from across the route.', 'showtime-pools' ); ?>
				</p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Start your project', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="featured-projects__grid">
				<?php foreach ( $projects as $p ) : ?>
					<article class="proj-card">
						<div class="proj-card__media" style="background:<?php echo esc_attr( $p['gradient'] ); ?>">
							<?php if ( ! empty( $p['image'] ) ) : ?>
								<img class="proj-card__media-img" src="<?php echo esc_url( $p['image'] ); ?>" alt="" loading="lazy" decoding="async">
							<?php endif; ?>
							<span class="proj-card__neighborhood"><?php echo esc_html( $p['neighborhood'] ); ?></span>
						</div>
						<div class="proj-card__body">
							<h3 class="proj-card__title"><?php echo esc_html( $p['title'] ); ?></h3>
							<dl class="proj-card__meta">
								<div><dt><?php esc_html_e( 'Scope', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['scope'] ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Finish', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['finish'] ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Duration', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['duration'] ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Investment', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['value'] ); ?></dd></div>
							</dl>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/section-08-reviews' ); ?>

</main>
<?php get_footer();

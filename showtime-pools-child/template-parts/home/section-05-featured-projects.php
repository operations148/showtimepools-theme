<?php
/**
 * Featured projects — 3 magazine-style project cards. Real Project CPT
 * data lands in Phase 2A; until then we render curated placeholders so
 * the homepage feels populated from day one.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$projects = array();

if ( post_type_exists( 'project' ) ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'project',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			$projects[] = array(
				'title'        => get_the_title(),
				'href'         => get_permalink(),
				'neighborhood' => function_exists( 'get_field' ) ? (string) get_field( 'neighborhood' ) : '',
				'scope'        => function_exists( 'get_field' ) ? (string) get_field( 'scope' ) : '',
				'finish'       => function_exists( 'get_field' ) ? (string) get_field( 'finish' ) : '',
				'duration'     => function_exists( 'get_field' ) ? (string) get_field( 'duration' ) : '',
				'value'        => function_exists( 'get_field' ) ? (string) get_field( 'value' ) : '',
				'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
			);
		}
		wp_reset_postdata();
	}
}

if ( empty( $projects ) ) {
	$img = function_exists( 'showtime_image' ) ? 'showtime_image' : null;
	$projects = apply_filters(
		'showtime/home_featured_projects',
		array(
			array(
				'title'        => __( 'Sherman Oaks ranch — full remodel', 'showtime-pools' ),
				'neighborhood' => __( 'Sherman Oaks', 'showtime-pools' ),
				'scope'        => __( 'Replaster · Tile · Pebble · Equipment swap', 'showtime-pools' ),
				'duration'     => __( '4 weeks', 'showtime-pools' ),
				'value'        => __( '$28,400', 'showtime-pools' ),
				'finish'       => __( 'PebbleTec Midnight Blue', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_1', 800 ) : '',
				'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
				'href'         => home_url( '/projects/' ),
			),
			array(
				'title'        => __( 'Encino estate — new construction', 'showtime-pools' ),
				'neighborhood' => __( 'Encino', 'showtime-pools' ),
				'scope'        => __( 'New gunite · Spa · Sheer descent · Automation', 'showtime-pools' ),
				'duration'     => __( '11 weeks', 'showtime-pools' ),
				'value'        => __( '$142,000', 'showtime-pools' ),
				'finish'       => __( 'Quartz · Travertine coping', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_2', 800 ) : '',
				'gradient'     => 'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
				'href'         => home_url( '/projects/' ),
			),
			array(
				'title'        => __( 'Studio City modern — equipment + automation', 'showtime-pools' ),
				'neighborhood' => __( 'Studio City', 'showtime-pools' ),
				'scope'        => __( 'Pentair IntelliCenter · VS pump · Salt cell', 'showtime-pools' ),
				'duration'     => __( '4 days', 'showtime-pools' ),
				'value'        => __( '$8,650', 'showtime-pools' ),
				'finish'       => __( 'Existing pebble retained', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_3', 800 ) : '',
				'gradient'     => 'linear-gradient(135deg,#3F6072 0%,#6E94A9 100%)',
				'href'         => home_url( '/projects/' ),
			),
		)
	);
}
?>
<section class="featured-projects" data-reveal>
	<div class="container">
		<header class="featured-projects__header">
			<div>
				<span class="eyebrow"><em>04</em> &mdash; <?php esc_html_e( 'Recent Work', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Three projects, three streets, one crew.', 'showtime-pools' ); ?></h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">
				<?php esc_html_e( 'See the full project log', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>

		<div class="featured-projects__grid">
			<?php foreach ( $projects as $p ) : ?>
				<a class="proj-card" href="<?php echo esc_url( $p['href'] ); ?>">
					<div class="proj-card__media" style="background:<?php echo esc_attr( $p['gradient'] ?? 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ); ?>">
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
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

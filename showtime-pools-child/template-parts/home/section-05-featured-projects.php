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
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			$pid = get_the_ID();
			$slot = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $pid );
			$image = '';
			$image_alt = '';
			if ( has_post_thumbnail( $pid ) ) {
				$image = (string) get_the_post_thumbnail_url( $pid, 'large' );
				// Priority 1: the attachment's own alt metadata from the Media Library.
				$image_alt = (string) get_post_meta( get_post_thumbnail_id( $pid ), '_wp_attachment_image_alt', true );
			} elseif ( function_exists( 'showtime_image' ) ) {
				$image = showtime_image( $slot, 1024 );
			}
			$pm = static function ( string $k, int $id ): string {
				$v = function_exists( 'get_field' ) ? get_field( $k, $id ) : null;
				if ( null === $v || '' === $v ) { $v = get_post_meta( $id, $k, true ); }
				return (string) $v;
			};
			$nb = $pm( 'neighborhood', (int) $pid );
			// Alt priority: real attachment alt → contextual "…in {neighborhood}"
			// → project title. Describes the photo without repeating the card's
			// visible <h3> title verbatim, and never invents details.
			if ( '' === $image_alt ) {
				$image_alt = '' !== $nb
					? sprintf( /* translators: %s: neighborhood */ __( 'Completed pool project in %s', 'showtime-pools' ), $nb )
					: get_the_title();
			}
			$projects[] = array(
				'title'        => get_the_title(),
				'href'         => get_permalink(),
				'neighborhood' => $nb,
				'scope'        => $pm( 'scope', (int) $pid ),
				'finish'       => $pm( 'finish', (int) $pid ),
				'duration'     => $pm( 'duration_label', (int) $pid ),
				'value'        => $pm( 'value_label', (int) $pid ),
				'image'        => $image,
				'image_alt'    => $image_alt,
				'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
			);
		}
		wp_reset_postdata();
	}
}

if ( empty( $projects ) ) {
	// Soft fallback when the CPT hasn't been seeded yet — shows 3 stylized
	// cards pointing to /projects/ instead of dead air.
	$img = function_exists( 'showtime_image' );
	$projects = apply_filters(
		'showtime/home_featured_projects',
		array(
			array(
				'title'        => __( 'Sherman Oaks mid-century remodel', 'showtime-pools' ),
				'neighborhood' => __( 'Sherman Oaks', 'showtime-pools' ),
				'scope'        => __( 'Resurface · Tile · Coping · Equipment', 'showtime-pools' ),
				'duration'     => __( '12 days', 'showtime-pools' ),
				'value'        => __( '$28k', 'showtime-pools' ),
				'finish'       => __( 'PebbleTec Cool Blue', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_1', 1024 ) : '',
				'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
				'href'         => home_url( '/projects/' ),
			),
			array(
				'title'        => __( 'Encino estate new construction', 'showtime-pools' ),
				'neighborhood' => __( 'Encino', 'showtime-pools' ),
				'scope'        => __( 'New build · Hardscape · Fire features', 'showtime-pools' ),
				'duration'     => __( '10 weeks', 'showtime-pools' ),
				'value'        => __( '$142k', 'showtime-pools' ),
				'finish'       => __( 'PebbleTec Aqua White', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_2', 1024 ) : '',
				'gradient'     => 'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
				'href'         => home_url( '/projects/' ),
			),
			array(
				'title'        => __( 'Studio City equipment overhaul', 'showtime-pools' ),
				'neighborhood' => __( 'Studio City', 'showtime-pools' ),
				'scope'        => __( 'Automation · Pump · Salt · Heater', 'showtime-pools' ),
				'duration'     => __( '3 days', 'showtime-pools' ),
				'value'        => __( '$8.6k', 'showtime-pools' ),
				'finish'       => __( 'Equipment only', 'showtime-pools' ),
				'image'        => $img ? showtime_image( 'project_3', 1024 ) : '',
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
				<span class="eyebrow"><?php esc_html_e( 'Recent Work', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Three projects, three streets, one crew.', 'showtime-pools' ); ?></h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">
				<?php esc_html_e( 'See the full project log', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>

		<div class="featured-projects__grid" data-stagger>
			<?php foreach ( $projects as $p ) : ?>
				<a class="proj-card" href="<?php echo esc_url( $p['href'] ); ?>">
					<div class="proj-card__media" style="background:<?php echo esc_attr( $p['gradient'] ?? 'linear-gradient(135deg,#1F2F3A,#5C8A9E)' ); ?>">
						<?php
						if ( ! empty( $p['image'] ) ) :
							// Informative project photo — resolved alt, or a contextual
							// fallback for the curated cards that don't carry one.
							$proj_alt = (string) ( $p['image_alt'] ?? '' );
							if ( '' === $proj_alt ) {
								$proj_alt = '' !== (string) ( $p['neighborhood'] ?? '' )
									? sprintf( /* translators: %s: neighborhood */ __( 'Completed pool project in %s', 'showtime-pools' ), (string) $p['neighborhood'] )
									: (string) ( $p['title'] ?? '' );
							}
							?>
							<img class="proj-card__media-img" src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $proj_alt ); ?>" loading="lazy" decoding="async" width="1024" height="768">
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

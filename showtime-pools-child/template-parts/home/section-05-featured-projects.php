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
	// Unmanaged/legacy posts are excluded at the query level — never featured
	// here regardless of menu_order. See showtime_unmanaged_project_ids().
	$unmanaged = function_exists( 'showtime_unmanaged_project_ids' ) ? showtime_unmanaged_project_ids() : array();
	$q = new WP_Query(
		array(
			'post_type'      => 'project',
			'posts_per_page' => 3,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'post__not_in'   => $unmanaged,
		)
	);
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			$pid = get_the_ID();

			// Same resolver as the archive, single page and related cards.
			$d = function_exists( 'showtime_project_data' ) ? showtime_project_data( $pid ) : null;

			if ( null !== $d ) {
				$projects[] = array(
					'title'        => $d['title'],
					'href'         => get_permalink(),
					'neighborhood' => $d['neighborhood'],
					'scope'        => $d['scope'],
					'finish'       => $d['finish'],
					'duration'     => $d['timeline'],
					'value'        => $d['investment'],
					'image'        => $d['hero_image'],
					'image_alt'    => $d['hero_alt'],
					'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
				);
				continue;
			}

			// Legacy/unmanaged project — historical post-meta path.
			$slot      = apply_filters( 'showtime/image/slot_for_project', 'project_1', (int) $pid );
			$image     = '';
			$image_alt = '';
			if ( has_post_thumbnail( $pid ) ) {
				$image     = (string) get_the_post_thumbnail_url( $pid, 'large' );
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
				'duration'     => '',
				'value'        => '',
				'image'        => $image,
				'image_alt'    => $image_alt,
				'gradient'     => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
			);
		}
		wp_reset_postdata();
	}
}

if ( empty( $projects ) ) {
	// Registry-driven fallback when no routing posts exist yet. The previous
	// hardcoded array published retired claims (PebbleTec, "$28k", "$142k",
	// "New build · Hardscape · Fire features") whenever the query came back
	// empty — removed entirely.
	if ( class_exists( 'Showtime\Projects' ) && function_exists( 'showtime_project_data' ) ) {
		$grads = array(
			'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
			'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
			'linear-gradient(135deg,#3F6072 0%,#6E94A9 100%)',
		);
		$n = 0;
		foreach ( \Showtime\Projects::all() as $entry ) {
			if ( empty( $entry['managed'] ) || $n >= 3 ) {
				continue;
			}
			$d = showtime_project_data( (string) $entry['slug'] );
			if ( null === $d ) {
				continue;
			}
			$projects[] = array(
				'title'        => $d['title'],
				'neighborhood' => $d['neighborhood'],
				'scope'        => $d['scope'],
				'duration'     => $d['timeline'],
				'value'        => $d['investment'],
				'finish'       => $d['finish'],
				'image'        => $d['hero_image'],
				'image_alt'    => $d['hero_alt'],
				'gradient'     => $grads[ $n % 3 ],
				'href'         => home_url( '/projects/' . $d['slug'] . '/' ),
			);
			$n++;
		}
	}
	$projects = apply_filters( 'showtime/home_featured_projects', $projects );
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
							<?php if ( ! empty( $p['duration'] ) ) : ?><div><dt><?php esc_html_e( 'Typical timeline', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['duration'] ); ?></dd></div><?php endif; ?>
							<?php if ( ! empty( $p['value'] ) ) : ?><div><dt><?php esc_html_e( 'Typical investment', 'showtime-pools' ); ?></dt><dd><?php echo esc_html( $p['value'] ); ?></dd></div><?php endif; ?>
						</dl>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

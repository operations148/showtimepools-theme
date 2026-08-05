<?php
/**
 * Featured projects — 3 magazine-style project cards. Real Project CPT
 * data lands in Phase 2A; until then we render curated placeholders so
 * the homepage feels populated from day one.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// The homepage strip stays CURATED and is derived from the code registry, not
// from WP_Query.
//
// This is deliberate. Routing shells created by `wp showtime projects sync`
// carry menu_order 0, while the six original verified projects carry
// menu_order 1-6. A query ordered by menu_order would therefore promote the
// newest routing posts to the front of the homepage the moment they became
// indexable, silently replacing the featured set. Reading the registry in file
// order makes the selection deterministic and independent of the database.
//
// Only fully verified projects are eligible here: anything still flagged
// `is_coming_soon` is skipped, so a placeholder can never be featured.
$projects = array();

if ( function_exists( 'showtime_project_cards' ) ) {
	$grads = array(
		'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
		'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
		'linear-gradient(135deg,#3F6072 0%,#6E94A9 100%)',
	);
	$n = 0;
	foreach ( showtime_project_cards() as $card ) {
		if ( $n >= 3 ) {
			break;
		}
		if ( ! empty( $card['is_coming_soon'] ) ) {
			continue;
		}
		$card['gradient'] = $grads[ $n % 3 ];
		$projects[]       = $card;
		$n++;
	}
}

$projects = apply_filters( 'showtime/home_featured_projects', $projects );
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

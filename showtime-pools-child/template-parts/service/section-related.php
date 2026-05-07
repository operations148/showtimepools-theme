<?php
/**
 * Related services + Phase 2A project hook.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx     = $GLOBALS['showtime_service_ctx'] ?? array();
$related = (array) ( $ctx['related'] ?? array() );
$slug    = (string) ( $ctx['slug'] ?? '' );

if ( empty( $related ) ) {
	return;
}
?>
<section class="svc-related section" data-reveal>
	<div class="container stack stack--lg">
		<header class="stack stack--sm">
			<span class="eyebrow"><?php esc_html_e( 'Related services', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'One team, every part of pool ownership.', 'showtime-pools' ); ?></h2>
		</header>

		<div class="svc-related__grid">
			<?php foreach ( $related as $svc ) : ?>
				<a class="svc-related__card" href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>">
					<h3 class="svc-related__title"><?php echo esc_html( (string) ( $svc['title'] ?? '' ) ); ?></h3>
					<p class="svc-related__blurb"><?php echo esc_html( (string) ( $svc['summary'] ?? '' ) ); ?></p>
					<span class="svc-related__cta"><?php esc_html_e( 'Learn more →', 'showtime-pools' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php
		/**
		 * Hook for Phase 2A: Project CPT plugs in a "Recent projects in this
		 * category" strip below the related services grid.
		 *
		 * @param string $slug Current service slug.
		 */
		do_action( 'showtime/service_related_projects', $slug );
		?>
	</div>
</section>

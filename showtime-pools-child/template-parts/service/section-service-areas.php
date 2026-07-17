<?php
/**
 * Relevant service areas — links this service to the specific published
 * service-area pages where it's genuinely offered (curated per service in the
 * registry, not every city on every service). Anchors are descriptive
 * ("Pool repair in Sherman Oaks"), never repeated "near me" text.
 *
 * $ctx['areas_relevant'] is a resolved list of ['slug','name'] built in
 * page-service.php from the registry's aeo_areas slugs. Renders nothing when
 * a service has no curated areas.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx    = $GLOBALS['showtime_service_ctx'] ?? array();
$areas  = array_values( array_filter(
	(array) ( $ctx['areas_relevant'] ?? array() ),
	static fn( $a ) => is_array( $a ) && '' !== (string) ( $a['slug'] ?? '' ) && '' !== (string) ( $a['name'] ?? '' )
) );

if ( empty( $areas ) ) {
	return;
}

$anchor = (string) ( $ctx['area_anchor'] ?? __( 'Pool service', 'showtime-pools' ) );
?>
<section class="svc-areas section section--surface" data-reveal>
	<div class="container stack stack--lg">
		<header class="svc-areas__head">
			<span class="eyebrow"><?php esc_html_e( 'Where we offer this', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'Service areas for this work', 'showtime-pools' ); ?></h2>
		</header>

		<ul class="svc-areas__list" role="list">
			<?php foreach ( $areas as $area ) :
				$name = (string) $area['name'];
				$url  = home_url( '/service-areas/' . (string) $area['slug'] . '/' );
			?>
				<li>
					<a class="svc-areas__link" href="<?php echo esc_url( $url ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-7.5 7-13a7 7 0 1 0-14 0c0 5.5 7 13 7 13Z"/><circle cx="12" cy="9" r="2.5"/></svg>
						<span><?php echo esc_html( sprintf( /* translators: 1: service anchor, 2: neighborhood */ _x( '%1$s in %2$s', 'service area link', 'showtime-pools' ), $anchor, $name ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

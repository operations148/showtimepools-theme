<?php
/**
 * Footer legal bar — copyright, license, social, legal links.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$year      = current_time( 'Y' );
$socials_raw = apply_filters(
	'showtime/business/socials',
	array(
		'instagram' => 'https://www.instagram.com/showtimepools',
		'facebook'  => 'https://www.facebook.com/showtimepools',
		'youtube'   => 'https://www.youtube.com/@showtimepools',
		'google'    => 'https://g.page/showtimepools',
	)
);

// Normalize: the Customizer bridge yields list-of-dicts [{label,url}, …]
// while the legacy fallback above is dict-with-keys {instagram:url, …}.
// Templates expect $socials as key => url so they can pick an SVG by key.
$socials = array();
foreach ( (array) $socials_raw as $k => $v ) {
	if ( is_array( $v ) ) {
		$label = strtolower( (string) ( $v['label'] ?? '' ) );
		$url   = (string) ( $v['url'] ?? '' );
		if ( '' !== $label && '' !== $url ) { $socials[ $label ] = $url; }
	} else {
		$socials[ (string) $k ] = (string) $v;
	}
}
?>
<div class="footer-legal">
	<div class="container footer-legal__inner">
		<p class="footer-legal__copy">
			&copy; <?php echo esc_html( (string) $year ); ?> Showtime Pools.
			<?php esc_html_e( 'All rights reserved.', 'showtime-pools' ); ?>
		</p>

		<ul class="footer-legal__links">
			<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'showtime-pools' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'showtime-pools' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>"><?php esc_html_e( 'Sitemap', 'showtime-pools' ); ?></a></li>
		</ul>

		<ul class="footer-legal__socials" aria-label="<?php esc_attr_e( 'Social profiles', 'showtime-pools' ); ?>">
			<?php foreach ( $socials as $key => $url ) : ?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer me" aria-label="<?php echo esc_attr( ucfirst( $key ) ); ?>">
						<?php
						switch ( $key ) {
							case 'instagram':
								echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>';
								break;
							case 'facebook':
								echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9V14.9H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>';
								break;
							case 'youtube':
								echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 12s0-3.6-.5-5.3a2.7 2.7 0 0 0-1.9-1.9C18.9 4.3 12 4.3 12 4.3s-6.9 0-8.6.5a2.7 2.7 0 0 0-1.9 1.9C1 8.4 1 12 1 12s0 3.6.5 5.3a2.7 2.7 0 0 0 1.9 1.9c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.7 2.7 0 0 0 1.9-1.9c.5-1.7.5-5.3.5-5.3zM9.8 15.4V8.6l5.8 3.4-5.8 3.4z"/></svg>';
								break;
							case 'google':
								echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 11v3h5a5 5 0 1 1-1.5-5.4l2.4-2.4A8.5 8.5 0 1 0 21 13H12z"/></svg>';
								break;
							default:
								echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>';
						}
						?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

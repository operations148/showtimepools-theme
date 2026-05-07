<?php
/**
 * Site branding — three-tier resolution:
 *   1. WP custom-logo set via Customizer → Site Identity → Logo (preferred for prod).
 *   2. /assets/img/logo.svg or logo.png shipped in the theme bundle.
 *   3. Wordmark fallback (DM Sans Showtime + aqua Pools).
 *
 * The fallback only renders when neither 1 nor 2 is present, so the
 * moment a logo lands the wordmark gets out of the way.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$logo_uri = '';
foreach ( array( 'svg', 'webp', 'png', 'jpg' ) as $ext ) {
	$path = SHOWTIME_CHILD_DIR . "/assets/img/logo.{$ext}";
	if ( file_exists( $path ) ) {
		$logo_uri = SHOWTIME_CHILD_URI . "/assets/img/logo.{$ext}";
		break;
	}
}

$site_name = (string) get_bloginfo( 'name' );
?>
<a class="site-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( sprintf( __( '%s — home', 'showtime-pools' ), $site_name ) ); ?>">
	<?php if ( has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php elseif ( $logo_uri ) : ?>
		<img class="site-branding__logo" src="<?php echo esc_url( $logo_uri ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" width="220" height="48" decoding="async">
	<?php else : ?>
		<span class="site-branding__wordmark">
			<span class="site-branding__main">Showtime<span class="site-branding__accent">Pools</span></span>
		</span>
		<span class="visually-hidden"><?php echo esc_html( $site_name ); ?></span>
	<?php endif; ?>
</a>

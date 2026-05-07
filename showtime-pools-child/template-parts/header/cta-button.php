<?php
/**
 * Header CTA button. Defaults to /quote/, override via filter.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$cta_url   = apply_filters( 'showtime/header_cta_url', home_url( '/quote/' ) );
$cta_label = apply_filters( 'showtime/header_cta_label', __( 'Get a Free Quote', 'showtime-pools' ) );
?>
<a class="btn btn--secondary site-header__cta" href="<?php echo esc_url( $cta_url ); ?>">
	<?php echo esc_html( $cta_label ); ?>
</a>

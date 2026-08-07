<?php
/**
 * Footer brand row — the official Showtime Pools logo on the left, beside the
 * large outlined "SHOWTIME POOLS" watermark, between the nav grid and the
 * legal bar.
 *
 * The logo is the SAME official asset the header resolves (custom logo first,
 * then the bundled assets/img/logo.* file, in the same extension order), so
 * the two can never drift apart. It is not recoloured, filtered, redrawn or
 * duplicated — the artwork has a transparent background and bright white
 * lettering, which is why it reads correctly on the navy footer as-is.
 *
 * The outlined wordmark stays decorative: aria-hidden now sits on the text
 * span itself rather than the wrapper, so it still produces no duplicate
 * screen-reader output while the logo link inside the row remains reachable
 * and focusable. (aria-hidden on the wrapper would have hidden a focusable
 * link from assistive technology — an accessibility failure.)
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$fw_logo    = '';
$fw_width   = 0;
$fw_height  = 0;

// 1. Customizer logo, when one is set (same source the header prefers).
$fw_custom = (int) get_theme_mod( 'custom_logo' );
if ( $fw_custom > 0 ) {
	$fw_src = wp_get_attachment_image_src( $fw_custom, 'full' );
	if ( is_array( $fw_src ) && ! empty( $fw_src[0] ) ) {
		$fw_logo   = (string) $fw_src[0];
		$fw_width  = (int) $fw_src[1];
		$fw_height = (int) $fw_src[2];
	}
}

// 2. Bundled theme logo, same extension order as template-parts/header/site-branding.php.
if ( '' === $fw_logo ) {
	foreach ( array( 'svg', 'webp', 'png', 'jpg' ) as $fw_ext ) {
		$fw_path = SHOWTIME_CHILD_DIR . "/assets/img/logo.{$fw_ext}";
		if ( ! file_exists( $fw_path ) ) {
			continue;
		}
		$fw_logo = SHOWTIME_CHILD_URI . "/assets/img/logo.{$fw_ext}";
		// Intrinsic dimensions are read from the file, never guessed, so the
		// browser reserves the right box and the row cannot shift on load.
		$fw_size = @getimagesize( $fw_path );
		if ( is_array( $fw_size ) ) {
			$fw_width  = (int) $fw_size[0];
			$fw_height = (int) $fw_size[1];
		}
		break;
	}
}
?>
<div class="footer-wordmark">
	<div class="container footer-wordmark__row">

		<?php if ( '' !== $fw_logo && $fw_width > 0 && $fw_height > 0 ) : ?>
			<a class="footer-brandmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img
					class="footer-brandmark__img"
					src="<?php echo esc_url( $fw_logo ); ?>"
					alt="Showtime Pools"
					width="<?php echo esc_attr( (string) $fw_width ); ?>"
					height="<?php echo esc_attr( (string) $fw_height ); ?>"
					loading="lazy"
					decoding="async">
			</a>
		<?php endif; ?>

		<span class="footer-wordmark__text" aria-hidden="true">Showtime Pools</span>

	</div>
</div>

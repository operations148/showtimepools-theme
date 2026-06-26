<?php
/**
 * Homepage location map — full-width Google Maps embed of the main (Sherman
 * Oaks) office, rendered as the final section above the footer CTA. Replaces
 * the tiny footer map. The NAP comes from the same Offices source (Site
 * Content → Offices & hours) so the address is never retyped.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$offices_default = array(
	array( 'label' => __( 'Sherman Oaks (Main)', 'showtime-pools' ), 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
);
$offices = function_exists( 'showtime_acf_rows' )
	? showtime_acf_rows( 'offices', $offices_default )
	: $offices_default;
$offices = apply_filters( 'showtime/business/offices', $offices );

$office   = $offices[0] ?? $offices_default[0];
$o_label  = (string) ( $office['label']  ?? '' );
$o_street = (string) ( $office['street'] ?? '' );
$o_city   = (string) ( $office['city']   ?? '' );

$map_q = trim( $o_street . ' ' . $o_city );
if ( '' === $map_q ) {
	return; // No address resolved — render nothing rather than a broken embed.
}
$map_url = 'https://www.google.com/maps?q=' . rawurlencode( $map_q ) . '&output=embed';

$phone = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$tel   = preg_replace( '/[^0-9+]/', '', $phone );
?>
<section class="home-map section section--cream" data-reveal aria-labelledby="home-map-title">
	<div class="container">
		<header class="home-map__head">
			<span class="eyebrow"><em>11</em> &mdash; <?php esc_html_e( 'Find us', 'showtime-pools' ); ?></span>
			<h2 id="home-map-title" class="balance"><?php esc_html_e( 'Headquartered on Ventura Boulevard, Sherman Oaks.', 'showtime-pools' ); ?></h2>
			<p class="home-map__addr">
				<?php echo esc_html( trim( $o_street . ', ' . $o_city, ', ' ) ); ?>
				<span class="home-map__sep" aria-hidden="true">·</span>
				<a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $phone ); ?></a>
			</p>
		</header>
		<div class="home-map__embed">
			<iframe
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
				allowfullscreen
				title="<?php echo esc_attr( sprintf( __( '%s location map', 'showtime-pools' ), '' !== $o_label ? $o_label : 'Showtime Pools' ) ); ?>"
				src="<?php echo esc_url( $map_url ); ?>"></iframe>
		</div>
	</div>
</section>

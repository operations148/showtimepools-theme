<?php
/**
 * Main footer — offices, hours, and a quick-links column mirroring the
 * primary nav. Social icons live in footer-legal.php's bottom bar only
 * (no duplicate text-label social list here).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
// Verified from showtimepoolservice.com (the existing live GHL site this
// fresh build replaces). Override via the showtime/business/email filter
// once a primary @showtimepools.com mailbox is provisioned.
$email = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );

// PHP defaults — match the legacy hardcoded arrays so the site renders
// correctly when ACF is inactive or no rows have been entered yet.
$offices_default = array(
	array( 'label' => __( 'Sherman Oaks (Main)', 'showtime-pools' ), 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
	array( 'label' => __( 'Century City', 'showtime-pools' ),         'street' => '1925 Century Park East, Suite 1700', 'city' => 'Los Angeles, CA 90067' ),
);
$offices = apply_filters( 'showtime/business/offices', showtime_acf_rows( 'offices', $offices_default ) );

// Hours: ACF stores as repeater rows [{day, time}], legacy was day=>time map.
// Normalize to legacy shape so the dl renderer below stays unchanged.
$hours_default_map = array(
	__( 'Mon-Sat', 'showtime-pools' ) => __( '8:00 AM - 5:00 PM', 'showtime-pools' ),
	__( 'Sunday', 'showtime-pools' )  => __( 'Emergencies by appointment', 'showtime-pools' ),
);
$hours_rows = function_exists( 'get_field' ) ? get_field( 'hours_rows', 'option' ) : null;
if ( is_array( $hours_rows ) && ! empty( $hours_rows ) ) {
	$hours = array();
	foreach ( $hours_rows as $row ) {
		$d = (string) ( $row['day'] ?? '' );
		$t = (string) ( $row['time'] ?? '' );
		if ( '' !== $d ) {
			$hours[ $d ] = $t;
		}
	}
} else {
	$hours = $hours_default_map;
}
$hours = apply_filters( 'showtime/business/hours', $hours );

// Quick links — mirrors the primary nav exactly (same labels, same URLs)
// instead of the old three-column service/area mega-list.
$quick_links = array(
	array( 'label' => __( 'About', 'showtime-pools' ),         'url' => home_url( '/about/' ) ),
	array( 'label' => __( 'Services', 'showtime-pools' ),      'url' => home_url( '/services/' ) ),
	array( 'label' => __( 'Projects', 'showtime-pools' ),      'url' => home_url( '/projects/' ) ),
	array( 'label' => __( 'Service Areas', 'showtime-pools' ), 'url' => home_url( '/service-areas/' ) ),
	array( 'label' => __( 'Get Quote', 'showtime-pools' ),     'url' => home_url( '/contact/' ) ),
	array( 'label' => __( 'Shop', 'showtime-pools' ),          'url' => home_url( '/shop/' ) ),
);

?>
<div class="footer-main">
	<div class="container">
		<div class="footer-main__top">
			<div class="footer-main__brand">
				<p class="footer-main__tagline">
					<?php esc_html_e( 'Stop juggling contractors. One team handles repairs, weekly service, remodels, and new equipment across Los Angeles.', 'showtime-pools' ); ?>
				</p>

				<address class="footer-main__contact">
					<a class="footer-main__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					<?php if ( '' !== $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Send us a message →', 'showtime-pools' ); ?></a>
					<?php endif; ?>
				</address>
			</div>

			<div class="footer-main__col">
				<h3 class="footer-main__title"><?php esc_html_e( 'Quick Links', 'showtime-pools' ); ?></h3>
				<ul class="footer-main__list">
					<?php foreach ( $quick_links as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-main__col">
				<h3 class="footer-main__title"><?php esc_html_e( 'Offices', 'showtime-pools' ); ?></h3>
				<ul class="footer-main__list footer-main__list--offices">
					<?php foreach ( $offices as $o ) : ?>
						<li>
							<strong><?php echo esc_html( $o['label'] ); ?></strong>
							<span><?php echo esc_html( $o['street'] ); ?></span>
							<span><?php echo esc_html( $o['city'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<h3 class="footer-main__title" style="margin-top:var(--sp-7)"><?php esc_html_e( 'Hours', 'showtime-pools' ); ?></h3>
				<dl class="footer-main__hours">
					<?php foreach ( $hours as $day => $time ) : ?>
						<div><dt><?php echo esc_html( $day ); ?></dt><dd><?php echo esc_html( $time ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			</div>
		</div>
	</div>
</div>

<?php
/**
 * Main footer — 3 offices, real socials, real services + about links.
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
	array( 'label' => __( 'Beverly Hills', 'showtime-pools' ),        'street' => '9461 Charleville Blvd. #1902', 'city' => 'Beverly Hills, CA 90212' ),
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

$socials = apply_filters(
	'showtime/business/socials',
	array(
		array( 'label' => 'Facebook',  'url' => 'https://facebook.com/share/18DZy64EfX/' ),
		array( 'label' => 'Instagram', 'url' => 'https://instagram.com/showtime_pools' ),
		array( 'label' => 'Google',    'url' => 'https://share.google/ltdNPoJBWevHTvrzq' ),
		array( 'label' => 'LinkedIn',  'url' => 'https://linkedin.com/in/showtimepoolssocal/' ),
		array( 'label' => 'TikTok',    'url' => 'https://tiktok.com/@showtimepools' ),
		array( 'label' => 'YouTube',   'url' => 'https://youtube.com/channel/UC3Dw1LtPvuX1JSGT7_KLntw' ),
	)
);

$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
$services_top6 = array_slice( $services, 0, 6 );
$services_rest = array_slice( $services, 6, 6 );

// Area pages from the registry so new neighborhoods appear automatically.
$footer_areas = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::all() : array();

// Footer logo: CMS override (Site Images → Footer logo) else the bundled logo
// file. Same resolution order as the header branding so the two stay in sync.
// Code-first edit mode skips the CMS override and uses the bundled logo.
$footer_logo = '';
if ( ! ( defined( 'SHOWTIME_CODE_FIRST' ) && SHOWTIME_CODE_FIRST ) ) {
	$flo = get_option( 'showtime_img_footer_logo', '' );
	if ( '' !== (string) $flo ) {
		$footer_logo = is_numeric( $flo ) ? (string) wp_get_attachment_url( (int) $flo ) : (string) $flo;
	}
}
if ( '' === $footer_logo ) {
	foreach ( array( 'svg', 'webp', 'png' ) as $ext ) {
		if ( file_exists( SHOWTIME_CHILD_DIR . "/assets/img/logo.{$ext}" ) ) {
			$footer_logo = SHOWTIME_CHILD_URI . "/assets/img/logo.{$ext}";
			break;
		}
	}
}
?>
<div class="footer-main">
	<div class="container">
		<div class="footer-main__top">
			<div class="footer-main__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding site-branding--footer" rel="home" aria-label="<?php esc_attr_e( 'Showtime Pools home', 'showtime-pools' ); ?>">
					<?php if ( '' !== $footer_logo ) : ?>
						<img class="footer-main__logo" src="<?php echo esc_url( $footer_logo ); ?>" alt="<?php esc_attr_e( 'Showtime Pools', 'showtime-pools' ); ?>" width="60" height="60" loading="lazy" decoding="async">
					<?php endif; ?>
					<span class="footer-main__wordmark">Showtime<em>Pools</em></span>
				</a>
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

				<ul class="footer-main__socials" role="list">
					<?php foreach ( $socials as $s ) : ?>
						<li><a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s['label'] ); ?>"><?php echo esc_html( $s['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-main__col">
				<h3 class="footer-main__title"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></h3>
				<ul class="footer-main__list">
					<?php foreach ( $services_top6 as $svc ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>"><?php echo esc_html( (string) $svc['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-main__col">
				<h3 class="footer-main__title"><?php esc_html_e( 'More Services', 'showtime-pools' ); ?></h3>
				<ul class="footer-main__list">
					<?php foreach ( $services_rest as $svc ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>"><?php echo esc_html( (string) $svc['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( ! empty( $footer_areas ) ) : ?>
				<div class="footer-main__col">
					<h3 class="footer-main__title"><?php esc_html_e( 'Service Areas', 'showtime-pools' ); ?></h3>
					<ul class="footer-main__list">
						<?php foreach ( $footer_areas as $fa ) : ?>
							<?php if ( empty( $fa['slug'] ) || empty( $fa['name'] ) ) { continue; } ?>
							<li><a href="<?php echo esc_url( home_url( '/service-areas/' . $fa['slug'] . '/' ) ); ?>"><?php echo esc_html( (string) $fa['name'] ); ?></a></li>
						<?php endforeach; ?>
						<li><a href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>"><?php esc_html_e( 'All service areas', 'showtime-pools' ); ?></a></li>
					</ul>
				</div>
			<?php endif; ?>

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

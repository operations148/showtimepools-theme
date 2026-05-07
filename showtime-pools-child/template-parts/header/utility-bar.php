<?php
/**
 * Utility bar — phone, hours, "book now" — sits above primary nav.
 * Hidden on mobile to save vertical space; phone surfaces in the drawer.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$phone   = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$hours   = apply_filters( 'showtime/business/hours_short', __( 'Mon-Sat 8am-5pm', 'showtime-pools' ) );
$book    = apply_filters( 'showtime/business/book_url', home_url( '/book/' ) );
?>
<div class="utility-bar" aria-label="<?php esc_attr_e( 'Contact and hours', 'showtime-pools' ); ?>">
	<div class="container utility-bar__inner">
		<div class="utility-bar__left">
			<a class="utility-bar__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
				<?php echo esc_html( $phone ); ?>
			</a>
			<span class="utility-bar__hours" aria-hidden="true">·</span>
			<span class="utility-bar__hours"><?php echo esc_html( $hours ); ?></span>
		</div>
		<div class="utility-bar__right">
			<a class="utility-bar__book" href="<?php echo esc_url( $book ); ?>">
				<?php esc_html_e( 'Book a service →', 'showtime-pools' ); ?>
			</a>
		</div>
	</div>
</div>

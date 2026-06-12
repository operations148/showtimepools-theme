<?php
/**
 * Site header. We override Astra's default header in full so that the
 * site has a single, opinionated, predictable nav. Skip link first for
 * accessibility, then the sticky main bar.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link visually-hidden" href="#primary"><?php esc_html_e( 'Skip to content', 'showtime-pools' ); ?></a>

<header
	id="masthead"
	class="site-header js-site-header"
	data-transparent="<?php echo is_front_page() ? 'true' : 'false'; ?>"
>
	<div class="site-header__bar">
		<div class="container site-header__inner">

			<?php get_template_part( 'template-parts/header/site-branding' ); ?>

			<?php get_template_part( 'template-parts/header/primary-nav' ); ?>

			<div class="site-header__actions">
				<?php
				$header_phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
				$header_tel   = preg_replace( '/[^0-9+]/', '', $header_phone );
				?>
				<a class="site-header__phone" href="tel:<?php echo esc_attr( $header_tel ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Call %s', 'showtime-pools' ), $header_phone ) ); ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
					<span><?php echo esc_html( $header_phone ); ?></span>
				</a>

				<a class="btn site-header__cta" href="<?php echo esc_url( showtime_booking_url() ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Book an Appointment', 'showtime-pools' ); ?>
				</a>

				<button
					type="button"
					class="site-header__menu-toggle js-mobile-toggle"
					aria-controls="mobile-drawer"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Open menu', 'showtime-pools' ); ?>"
				>
					<span class="site-header__menu-toggle-bar" aria-hidden="true"></span>
					<span class="site-header__menu-toggle-bar" aria-hidden="true"></span>
					<span class="site-header__menu-toggle-bar" aria-hidden="true"></span>
				</button>
			</div>

		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/header/mobile-drawer' ); ?>

<?php
/**
 * Mobile drawer — full-height side panel. Driven by `data-mobile-open`
 * attribute on <body>, toggled by header.js. CSS drives the animation.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
?>
<div
	id="mobile-drawer"
	class="mobile-drawer"
	role="dialog"
	aria-modal="true"
	aria-labelledby="mobile-drawer-title"
	hidden
>
	<div class="mobile-drawer__panel">
		<div class="mobile-drawer__head">
			<h2 id="mobile-drawer-title" class="visually-hidden"><?php esc_html_e( 'Menu', 'showtime-pools' ); ?></h2>
			<a class="site-branding site-branding--mobile" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( has_custom_logo() ) { the_custom_logo(); } else { ?>
					<span style="font-family:var(--ff-display);font-weight:700;font-size:var(--fs-xl);color:var(--c-ink-900);letter-spacing:-0.02em">Showtime Pools</span>
				<?php } ?>
			</a>
			<button type="button" class="mobile-drawer__close js-mobile-toggle" aria-label="<?php esc_attr_e( 'Close menu', 'showtime-pools' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M6 18L18 6"/></svg>
			</button>
		</div>

		<nav class="mobile-drawer__nav" aria-label="<?php esc_attr_e( 'Mobile primary', 'showtime-pools' ); ?>">
			<?php
			if ( has_nav_menu( 'mobile' ) || has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
						'container'      => false,
						'menu_class'     => 'mobile-drawer__list',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			} else {
				?>
				<ul class="mobile-drawer__list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a></li>
					<li class="mobile-drawer__has-sub">
						<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'showtime-pools' ); ?></a>
						<ul class="mobile-drawer__sublist">
							<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Us', 'showtime-pools' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/the-founder/' ) ); ?>"><?php esc_html_e( 'The Founder', 'showtime-pools' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog Insights', 'showtime-pools' ); ?></a></li>
						</ul>
					</li>
					<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'showtime-pools' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</nav>

		<div class="mobile-drawer__cta-stack">
			<a class="btn btn--lg" style="width:100%" href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Get a Free Quote', 'showtime-pools' ); ?></a>
			<a class="btn btn--ghost btn--lg" style="width:100%" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
		</div>
	</div>
	<div class="mobile-drawer__backdrop js-mobile-toggle" aria-hidden="true"></div>
</div>

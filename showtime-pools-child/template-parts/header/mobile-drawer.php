<?php
/**
 * Mobile drawer — full-screen editorial takeover. Replaces the previous
 * right-side panel pattern. Single dark surface (--c-ink) covering the full
 * viewport with large display nav, active-page indicator, and pinned CTA
 * stack at the bottom.
 *
 * Visibility is driven entirely by CSS (opacity + translate) gated on
 * `.is-open` on this container OR `aria-expanded="true"` on any
 * `.js-mobile-toggle` button. The JS owner lives in assets/js/main.js
 * and only flips state — never touches inline styles.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$phone     = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$phone_tel = preg_replace( '/[^0-9+]/', '', $phone );
$quote_url = esc_url( SHOWTIME_BOOKING_URL );
?>
<div
	id="mobile-drawer"
	class="mobile-drawer"
	role="dialog"
	aria-modal="true"
	aria-labelledby="mobile-drawer-title"
	aria-hidden="true"
>
	<h2 id="mobile-drawer-title" class="visually-hidden"><?php esc_html_e( 'Site menu', 'showtime-pools' ); ?></h2>

	<div class="mobile-drawer__shell">

		<div class="mobile-drawer__head">
			<span class="mobile-drawer__eyebrow"><?php esc_html_e( 'Menu', 'showtime-pools' ); ?></span>
			<button type="button" class="mobile-drawer__close js-mobile-toggle" aria-label="<?php esc_attr_e( 'Close menu', 'showtime-pools' ); ?>">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
					<path stroke-linecap="round" d="M6 6l12 12M6 18L18 6"/>
				</svg>
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
					<li class="mobile-drawer__has-sub menu-item-has-children">
						<details class="mobile-drawer__sub">
							<summary>
								<span><?php esc_html_e( 'About', 'showtime-pools' ); ?></span>
								<svg class="mobile-drawer__sub-chev" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
							</summary>
							<ul class="mobile-drawer__sublist sub-menu">
								<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Us', 'showtime-pools' ); ?></a></li>
								<li><a href="<?php echo esc_url( home_url( '/the-founder/' ) ); ?>"><?php esc_html_e( 'The Founder', 'showtime-pools' ); ?></a></li>
								<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog Insights', 'showtime-pools' ); ?></a></li>
							</ul>
						</details>
					</li>
					<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></a></li>
					<li><a href="https://area.showtimepools.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Location', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'showtime-pools' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'showtime-pools' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</nav>

		<div class="mobile-drawer__foot">
			<a class="mobile-drawer__cta-quote" href="<?php echo $quote_url; ?>">
				<span><?php esc_html_e( 'Get a Free Quote', 'showtime-pools' ); ?></span>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>

			<a class="mobile-drawer__cta-call" href="tel:<?php echo esc_attr( $phone_tel ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Call %s', 'showtime-pools' ), $phone ) ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
				<span><?php echo esc_html( $phone ); ?></span>
			</a>

			<p class="mobile-drawer__signoff">
				<span class="mobile-drawer__signoff-mark">Showtime Pools</span>
				<span class="mobile-drawer__signoff-sep">·</span>
				<span class="mobile-drawer__signoff-loc"><?php esc_html_e( 'Los Angeles', 'showtime-pools' ); ?></span>
			</p>
		</div>

	</div>
</div>

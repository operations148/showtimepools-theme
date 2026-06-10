<?php
/**
 * Primary navigation — desktop. 5-item menu per Denz's brief:
 * Home / About (dropdown) / Services (mega menu of 12) / Contact / Shop.
 *
 * If a `primary` menu is assigned in WP Menus, that wins. Otherwise we
 * render this fallback that matches the canonical site IA.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Use WP menu if assigned to 'primary' location OR if any menu exists at all.
// This means the user never has to manually assign a menu to a location —
// any menu they create in WP Admin → Menus will be picked up automatically.
$has_primary  = has_nav_menu( 'primary' );
$all_menus    = wp_get_nav_menus();
$has_any_menu = ! empty( $all_menus );

$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();
?>
<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'showtime-pools' ); ?>">
	<?php if ( $has_primary || $has_any_menu ) : ?>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				// If no menu assigned to 'primary', fall back to the first available menu.
				'menu'           => $has_primary ? 0 : (int) $all_menus[0]->term_id,
				'container'      => false,
				'menu_class'     => 'primary-nav__list',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
		?>
	<?php else : ?>
		<ul class="primary-nav__list">
			<li class="primary-nav__item">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
			</li>

			<li class="primary-nav__item primary-nav__item--has-mega">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'showtime-pools' ); ?></a>
				<div class="primary-nav__mega primary-nav__mega--narrow" role="menu">
					<div class="primary-nav__mega-grid primary-nav__mega-grid--single">
						<a class="primary-nav__mega-item" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><span><?php esc_html_e( 'About Us', 'showtime-pools' ); ?></span><small><?php esc_html_e( 'Who we are, what we do', 'showtime-pools' ); ?></small></a>
						<a class="primary-nav__mega-item" href="<?php echo esc_url( home_url( '/the-founder/' ) ); ?>"><span><?php esc_html_e( 'The Founder', 'showtime-pools' ); ?></span><small><?php esc_html_e( "Steve Adams' story", 'showtime-pools' ); ?></small></a>
						<a class="primary-nav__mega-item" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><span><?php esc_html_e( 'Blog', 'showtime-pools' ); ?></span><small><?php esc_html_e( 'Pool care, equipment, design', 'showtime-pools' ); ?></small></a>
					</div>
				</div>
			</li>

			<li class="primary-nav__item primary-nav__item--has-mega">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></a>
				<div class="primary-nav__mega" role="menu">
					<div class="primary-nav__mega-grid primary-nav__mega-grid--3">
						<?php foreach ( $services as $svc ) : ?>
							<a class="primary-nav__mega-item" href="<?php echo esc_url( home_url( '/services/' . $svc['slug'] . '/' ) ); ?>">
								<span><?php echo esc_html( (string) $svc['title'] ); ?></span>
								<small><?php echo esc_html( wp_trim_words( (string) ( $svc['summary'] ?? '' ), 9, '…' ) ); ?></small>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</li>

			<li class="primary-nav__item">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>"><?php esc_html_e( 'Projects', 'showtime-pools' ); ?></a>
			</li>

			<li class="primary-nav__item">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/service-areas/' ) ); ?>"><?php esc_html_e( 'Service Areas', 'showtime-pools' ); ?></a>
			</li>

			<li class="primary-nav__item">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'showtime-pools' ); ?></a>
			</li>

			<li class="primary-nav__item">
				<a class="primary-nav__link" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'showtime-pools' ); ?></a>
			</li>
		</ul>
	<?php endif; ?>
</nav>

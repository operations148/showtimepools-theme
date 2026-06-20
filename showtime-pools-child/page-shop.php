<?php
/**
 * Template Name: Shop
 *
 * /shop/ — Coming Soon placeholder for the upcoming retail line. Substantive
 * preview of categories + newsletter signup so the page never reads empty.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_2', 1600 ) : '';
?>
<main id="primary" class="site-main interior-page">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $hero_img ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $hero_img ); ?>" <?php echo showtime_hero_srcset_attr( 'lifestyle_2' ); ?> alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Shop', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Coming soon', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'A shop for what we put in our own pools.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'Curated kits, replacement parts, and authorized Pentair and Jandy equipment. Same brands and same parts our own crew installs every day. Launch is staged for later this year.', 'showtime-pools' ); ?>
				</p>
				<div class="cluster">
					<a class="btn btn--invert btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Need a part now? Email us', 'showtime-pools' ); ?></a>
					<a class="btn btn--ghost-on-dark btn--lg" href="<?php echo esc_url( 'tel:+13238252099' ); ?>"><?php esc_html_e( 'Or call (323) 825-2099', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'What is launching', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Five categories. Real parts, not knock-offs.', 'showtime-pools' ); ?></h2>
				<p class="int-section__lead"><?php esc_html_e( 'We sell what we install. Authentic Pentair, Jandy, Hayward, and PebbleTec stock. Same factory warranty pass-through as a full equipment install.', 'showtime-pools' ); ?></p>
			</header>

			<div class="shop-grid">
				<?php
				$categories = array(
					array( 'title' => 'Pump & Filter Parts',     'body' => 'Motors, impellers, capacitors, baskets, lids, gaskets, multi-port valve seals, DE filter grids, cartridges. For Pentair IntelliFlo, Jandy Stealth, Hayward Super Pump, Sta-Rite.' ),
					array( 'title' => 'Heater Parts',            'body' => 'Igniters, thermistors, gas valves, heat exchangers, flow switches, pressure switches, thermostats, control boards. Raypak 406A, Pentair MasterTemp, Jandy JXi.' ),
					array( 'title' => 'Salt System Cells & Boards', 'body' => 'Pentair IC40 cells, Jandy AquaPure cells, Hayward TCELL15, replacement control boards, cell tester strips, salt log tracking sheets.' ),
					array( 'title' => 'Chemistry Care Kits',     'body' => 'Curated 30-day, 90-day, and seasonal chemistry kits. Pucks, granular shock, calcium hardness, CYA, phosphate remover, salt. Quality you cannot get from the big-box pool aisle.' ),
					array( 'title' => 'Tools & Test Strips',     'body' => 'Hayward HotSpring brushes, leaf skimmers, vacuum heads, test kits with fresh reagents, professional photometers. Same gear our techs carry on the truck.' ),
					array( 'title' => 'PebbleTec Care Kit',      'body' => 'Maintenance kits for fresh-plaster owners. 10-day care brushes, balance reagents, written 12-month care plan to protect your finish warranty.' ),
				);
				foreach ( $categories as $c ) :
				?>
					<article class="shop-card">
						<h3 class="shop-card__title"><?php echo esc_html( $c['title'] ); ?></h3>
						<p class="shop-card__body"><?php echo esc_html( $c['body'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container" style="max-width:var(--container-narrow)">
			<div class="shop-notify">
				<header class="int-section__head" style="margin-bottom:var(--sp-6)">
					<span class="eyebrow"><?php esc_html_e( 'Tell us when it launches', 'showtime-pools' ); ?></span>
					<h2 class="balance"><?php esc_html_e( 'Drop your email. We will send one note when the shop opens.', 'showtime-pools' ); ?></h2>
				</header>

				<form class="shop-notify__form" id="showtime-notify-form" novalidate>
					<input type="hidden" name="loaded_at" value="<?php echo esc_attr( (string) time() ); ?>">
					<div class="contact-form__hp" aria-hidden="true">
						<label>Leave empty<input type="text" name="hp_url" tabindex="-1" autocomplete="off"></label>
					</div>
					<div class="shop-notify__row">
						<input class="form-input" type="email" name="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'showtime-pools' ); ?>" aria-label="<?php esc_attr_e( 'Email address', 'showtime-pools' ); ?>">
						<input type="hidden" name="name" value="Shop Notify">
						<input type="hidden" name="phone" value="0000000000">
						<input type="hidden" name="service" value="shop-launch">
						<input type="hidden" name="message" value="Notify me when the Showtime Pools shop launches.">
						<input type="hidden" name="consent" value="1">
						<button type="submit" class="btn btn--primary btn--lg" data-default-label="<?php esc_attr_e( 'Notify me', 'showtime-pools' ); ?>">
							<?php esc_html_e( 'Notify me', 'showtime-pools' ); ?>
						</button>
					</div>
					<p class="shop-notify__hint"><?php esc_html_e( 'One email when the shop launches. No spam, no marketing list.', 'showtime-pools' ); ?></p>
					<div class="contact-form__alert" data-status="success" hidden role="status"></div>
					<div class="contact-form__alert contact-form__alert--err" data-status="error" hidden role="alert"></div>
				</form>
			</div>
		</div>
	</section>


</main>
<?php get_footer();

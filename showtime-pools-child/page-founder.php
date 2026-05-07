<?php
/**
 * Template Name: The Founder
 *
 * /the-founder/ — Steve Adams story page. Mirrors /about/ rhythm:
 *   1. Text-only hero (eyebrow + H1 + lead, no photo background)
 *   2. Story section using .about-story pattern (portrait beside prose)
 *   3. Contact list
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$founder_img = function_exists( 'showtime_image' ) ? showtime_image( 'founder', 1200 ) : '';
$hero_bg     = function_exists( 'showtime_image' ) ? showtime_image( 'about_hero', 1920 ) : '';

$person_schema = array(
	'@context'      => 'https://schema.org',
	'@type'         => 'Person',
	'@id'           => home_url( '/the-founder/#person' ),
	'name'          => 'Steve Adams',
	'jobTitle'      => 'Founder & CEO',
	'worksFor'      => array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#localbusiness' ),
		'name'  => 'Showtime Pools',
	),
	'description'   => 'Founder of Showtime Pools (Sherman Oaks, LA). Personally walks every quote and pulls every permit at the LA County and Sherman Oaks Building & Safety counter.',
	'address'       => array(
		'@type'           => 'PostalAddress',
		'addressLocality' => 'Sherman Oaks',
		'addressRegion'   => 'CA',
		'addressCountry'  => 'US',
	),
	'image'         => $founder_img,
	'sameAs'        => array(
		'https://linkedin.com/in/showtimepoolssocal/',
	),
	'knowsAbout'    => array(
		'Pool construction',
		'Pool remodeling',
		'Pool plaster and PebbleTec finishes',
		'Pool equipment installation',
		'Pool inspections',
		'CSLB C-53 contractor compliance',
	),
);
?>
<main id="primary" class="site-main interior-page">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $hero_bg ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $hero_bg ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'The Founder', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'The Founder', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'A pool company, run like a small shop.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'Showtime Pools is owner-operated. The shop on Ventura Boulevard. The crew is W-2. The phone is a real phone, answered by a real person.', 'showtime-pools' ); ?>
				</p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="about-story">
				<aside class="about-story__photo">
					<div class="about-story__photo-frame">
						<?php if ( $founder_img ) : ?>
							<img src="<?php echo esc_url( $founder_img ); ?>" alt="<?php esc_attr_e( 'Steve Adams, Founder and CEO of Showtime Pools', 'showtime-pools' ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
					</div>
					<figcaption><?php esc_html_e( 'Steve Adams · Founder & CEO', 'showtime-pools' ); ?></figcaption>
				</aside>

				<div class="about-story__copy">
					<span class="eyebrow"><?php esc_html_e( 'Steve Adams', 'showtime-pools' ); ?></span>
					<h2><?php esc_html_e( 'Founder, CEO, on every quote.', 'showtime-pools' ); ?></h2>
					<p><?php esc_html_e( 'Steve started Showtime Pools with one truck and a handful of weekly customers in Sherman Oaks. The first decade was just service: drive the route, balance the chemistry, fix what breaks, send a photo report before leaving the driveway. Customers asked when we would start doing remodels. Steve said no for years.', 'showtime-pools' ); ?></p>
					<p><?php esc_html_e( 'When we finally added construction, it was because the same handful of customers kept asking. We built a pool for one. Then a remodel for another. Word got around. Today the construction line and the service line are both staffed by W-2 crew, both supervised by Steve, both working off the same standards. Same shop on Ventura Boulevard. Same trucks. Same number.', 'showtime-pools' ); ?></p>
					<p><?php esc_html_e( 'Quotes are written and itemized. Permits are pulled in person. The person who walks your site is on the job when the work happens. When the inspection says walk away from a deal, that is what the inspection says, even when it costs us a six-figure construction quote. Independence is the whole point.', 'showtime-pools' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container" style="max-width:var(--container-narrow)">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'Where to find Steve', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'The phone, the email, the shop.', 'showtime-pools' ); ?></h2>
			</header>
			<ul class="founder-contact-list">
				<li><strong><?php esc_html_e( 'Phone', 'showtime-pools' ); ?>:</strong> <a href="tel:+13238252099">(323) 825-2099</a></li>
				<li><strong><?php esc_html_e( 'Email', 'showtime-pools' ); ?>:</strong> <a href="mailto:operations@showtimepoolmechanics.com">operations@showtimepoolmechanics.com</a></li>
				<li><strong><?php esc_html_e( 'Sherman Oaks shop', 'showtime-pools' ); ?>:</strong> 15301 Ventura Blvd., Sherman Oaks, CA 91403</li>
				<li><strong><?php esc_html_e( 'LinkedIn', 'showtime-pools' ); ?>:</strong> <a href="https://linkedin.com/in/showtimepoolssocal/" target="_blank" rel="noopener">@showtimepoolssocal</a></li>
			</ul>
		</div>
	</section>

</main>
<script type="application/ld+json"><?php echo wp_json_encode( $person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php get_footer();

<?php
/**
 * Template Name: The Founder
 *
 * /the-founder/ — Steve Adams story page. Every label, every block, the
 * portrait, the pull quote, and the contact list is editable from
 * Site Content → Page Copy → Founder page. PHP fallbacks below preserve
 * the original copy when ACF fields are empty.
 *
 * Structure:
 *   1. Hero          (eyebrow + H1 + lead, full-bleed photo)
 *   2. Story         (portrait + 3-paragraph default OR ACF story blocks)
 *   3. Pull quote    (oversized quote + attribution, off-white surface)
 *   4. Values strip  (3 promises pulled from About value_cards or defaults)
 *   5. Contact list  (phone/email/shop/social — dynamic from Customizer + ACF)
 *   6. CTA banner    (Call Steve + Get a Quote)
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ── Native WP fields (edit via WP Admin → Pages → The Founder → Update) ─────
$page_id = (int) get_queried_object_id();
$opt = function_exists( 'get_field' ) ? 'option' : false;
$_pm = static fn( string $k ) => (string) get_post_meta( $page_id, $k, true );

// Priority: post meta → ACF option → WP native → PHP fallback.
$f_name    = $_pm( 'founder_name' )  ?: __( 'Steve Adams', 'showtime-pools' );
$f_title   = $_pm( 'founder_title' ) ?: __( 'Founder & CEO', 'showtime-pools' );
$f_eyebrow = $_pm( 'founder_eyebrow' ) ?: __( 'The Founder', 'showtime-pools' );

// H1: post meta → WP page title → PHP fallback
$f_h1 = $_pm( 'founder_h1' );
if ( '' === $f_h1 && $page_id ) {
	$wp_title = trim( get_the_title( $page_id ) );
	if ( '' !== $wp_title && __( 'The Founder', 'showtime-pools' ) !== $wp_title ) {
		$f_h1 = $wp_title;
	}
}
$f_h1 = '' !== $f_h1 ? $f_h1 : __( 'A pool company, run like a small shop.', 'showtime-pools' );

// Lead: post meta → WP excerpt → PHP fallback
$f_lead = $_pm( 'founder_lead' );
if ( '' === $f_lead && $page_id ) {
	$excerpt = get_the_excerpt( $page_id );
	if ( '' !== $excerpt ) { $f_lead = $excerpt; }
}
$f_lead = '' !== $f_lead ? $f_lead : __( 'Showtime Pools is owner-operated. The shop on Ventura Boulevard. The crew is W-2. The phone is a real phone, answered by a real person.', 'showtime-pools' );

// Bio: WP page content (Gutenberg editor)
$f_blocks   = null; // ACF story blocks not used without Pro
$use_wp_bio = (bool) $page_id;
$wp_bio     = '';
if ( $use_wp_bio ) {
	$raw = get_post_field( 'post_content', $page_id );
	if ( '' !== trim( $raw ) ) { $wp_bio = apply_filters( 'the_content', $raw ); }
}

// Quote: post meta → PHP fallback
$f_quote = $_pm( 'founder_quote' ) ?: __( 'If my name is on the warranty, my crew earns it on every job.', 'showtime-pools' );
$f_qattr = $_pm( 'founder_quote_attr' ) ?: $f_name . ', ' . $f_title;

// Section headings
$founder_story_h2   = $_pm( 'founder_story_h2' )   ?: __( 'Founder, CEO, on every quote.', 'showtime-pools' );
$founder_promises_eyebrow = $_pm( 'founder_promises_eyebrow' ) ?: __( 'What you can expect', 'showtime-pools' );
$founder_promises_h2      = $_pm( 'founder_promises_h2' )      ?: __( 'Three promises Steve signs his name on.', 'showtime-pools' );
$founder_contact_eyebrow  = $_pm( 'founder_contact_eyebrow' )  ?: __( 'Where to find Steve', 'showtime-pools' );
$founder_contact_h2       = $_pm( 'founder_contact_h2' )       ?: __( 'The phone, the email, the shop.', 'showtime-pools' );

// Portrait: ACF → WP Featured Image → Site Images slot → CDN fallback
$f_portrait = $opt ? get_field( 'founder_portrait', $opt ) : null;
if ( is_array( $f_portrait ) && ! empty( $f_portrait['url'] ) ) {
	$portrait_url = $f_portrait['sizes']['large'] ?? $f_portrait['url'];
} elseif ( $page_id && has_post_thumbnail( $page_id ) ) {
	// WP Admin → Pages → The Founder → Featured Image
	$portrait_url = (string) get_the_post_thumbnail_url( $page_id, 'large' );
} else {
	// Showtime Pools → Site Images → Founder portrait (or bundled/CDN)
	$portrait_url = function_exists( 'showtime_image' ) ? showtime_image( 'founder', 1200 ) : '';
}

$hero_bg = function_exists( 'showtime_image' ) ? showtime_image( 'about_hero', 1920 ) : '';

// Sitewide contact data.
$phone = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$email = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );

// Offices + socials from same sources as footer + contact page.
$offices = function_exists( 'showtime_acf_rows' ) ? showtime_acf_rows( 'offices', array(
	array( 'label' => 'Sherman Oaks (Main)', 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
) ) : array();
$shop = $offices[0] ?? array( 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' );

$socials = (array) apply_filters( 'showtime/business/socials', array() );
$linkedin = '';
foreach ( $socials as $s ) {
	$label = strtolower( (string) ( is_array( $s ) ? ( $s['label'] ?? '' ) : '' ) );
	$url   = (string) ( is_array( $s ) ? ( $s['url'] ?? '' ) : $s );
	if ( $label === 'linkedin' ) { $linkedin = $url; break; }
}

// Values strip — reuse About value cards if defined, else fall back to 3 lines.
$values_strip = array();
if ( function_exists( 'showtime_acf_rows' ) ) {
	$values_strip = showtime_acf_rows( 'about_value_cards', array() );
}
if ( empty( $values_strip ) ) {
	$values_strip = array(
		array( 'title' => __( 'On every quote', 'showtime-pools' ),         'body' => __( 'I walk every site and sign every itemized estimate. The person who quotes the job is on-site when the work happens.', 'showtime-pools' ) ),
		array( 'title' => __( 'Permits in person', 'showtime-pools' ),       'body' => __( 'I pull every permit in person at LA County and Sherman Oaks Building & Safety. No expediters, no surprises.', 'showtime-pools' ) ),
		array( 'title' => __( 'Independent inspections', 'showtime-pools' ), 'body' => __( 'When the inspection says walk away, that is what we say, even when it costs us a six-figure construction quote.', 'showtime-pools' ) ),
	);
}
$values_strip = array_slice( $values_strip, 0, 3 );

// Person JSON-LD — anchored to the LocalBusiness node.
$person_schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'Person',
	'@id'         => home_url( '/the-founder/#person' ),
	'name'        => $f_name,
	'jobTitle'    => $f_title,
	'worksFor'    => array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
	),
	'description' => wp_strip_all_tags( $f_lead ),
	'address'     => array(
		'@type'           => 'PostalAddress',
		'addressLocality' => 'Sherman Oaks',
		'addressRegion'   => 'CA',
		'addressCountry'  => 'US',
	),
	'image'       => $portrait_url,
	'sameAs'      => $linkedin ? array( $linkedin ) : array(),
	'knowsAbout'  => array(
		'Pool construction',
		'Pool remodeling',
		'Pool plaster and PebbleTec finishes',
		'Pool equipment installation',
		'Pool inspections',
		'California pool construction code compliance',
	),
);
?>
<main id="primary" class="site-main interior-page founder-page">

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
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $f_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $f_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $f_lead ); ?></p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="about-story">
				<aside class="about-story__photo">
					<div class="about-story__photo-frame">
						<?php if ( $portrait_url ) : ?>
							<img src="<?php echo esc_url( $portrait_url ); ?>" alt="<?php echo esc_attr( $f_name . ', ' . $f_title ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
					</div>
					<figcaption><?php echo esc_html( $f_name . ' · ' . $f_title ); ?></figcaption>
				</aside>

				<div class="about-story__copy">
					<span class="eyebrow"><?php echo esc_html( $f_name ); ?></span>
					<h2><?php echo esc_html( $founder_story_h2 ); ?></h2>

					<?php if ( is_array( $f_blocks ) && ! empty( $f_blocks ) ) : ?>
						<?php foreach ( $f_blocks as $block ) :
							$heading = (string) ( $block['heading'] ?? '' );
							$body    = (string) ( $block['body'] ?? '' );
							if ( '' === $heading && '' === $body ) { continue; }
						?>
							<?php if ( '' !== $heading ) : ?>
								<h3><?php echo esc_html( $heading ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $body ) : ?>
								<?php echo wp_kses_post( wpautop( $body ) ); ?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php elseif ( '' !== $wp_bio ) : ?>
						<?php
						// WP page content from the Gutenberg/Classic editor.
						// Edit: WP Admin → Pages → The Founder → Page Content.
						echo wp_kses_post( $wp_bio );
						?>
					<?php else : ?>
						<p><?php esc_html_e( 'Steve started Showtime Pools with one truck and a handful of weekly customers in Sherman Oaks. The first decade was just service: drive the route, balance the chemistry, fix what breaks, send a photo report before leaving the driveway. Customers asked when we would start doing remodels. Steve said no for years.', 'showtime-pools' ); ?></p>
						<p><?php esc_html_e( 'When we finally added construction, it was because the same handful of customers kept asking. We built a pool for one. Then a remodel for another. Word got around. Today the construction line and the service line are both staffed by W-2 crew, both supervised by Steve, both working off the same standards. Same shop on Ventura Boulevard. Same trucks. Same number.', 'showtime-pools' ); ?></p>
						<p><?php esc_html_e( 'Quotes are written and itemized. Permits are pulled in person. The person who walks your site is on the job when the work happens. When the inspection says walk away from a deal, that is what the inspection says, even when it costs us a six-figure construction quote. Independence is the whole point.', 'showtime-pools' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream founder-quote-section" data-reveal>
		<div class="container">
			<figure class="founder-quote">
				<svg class="founder-quote__mark" width="56" height="56" viewBox="0 0 48 48" fill="none" aria-hidden="true">
					<path d="M14 14h10v10c0 5.523-4.477 10-10 10V30c2.761 0 5-2.239 5-5h-5V14zM30 14h10v10c0 5.523-4.477 10-10 10V30c2.761 0 5-2.239 5-5h-5V14z" fill="currentColor"/>
				</svg>
				<blockquote><?php echo esc_html( $f_quote ); ?></blockquote>
				<figcaption><?php echo esc_html( $f_qattr ); ?></figcaption>
			</figure>
		</div>
	</section>

	<?php if ( ! empty( $values_strip ) ) : ?>
		<section class="int-section founder-promises" data-reveal>
			<div class="container">
				<header class="int-section__head">
					<span class="eyebrow"><?php echo esc_html( $founder_promises_eyebrow ); ?></span>
					<h2 class="balance"><?php echo esc_html( $founder_promises_h2 ); ?></h2>
				</header>
				<div class="founder-promises__grid">
					<?php $n = 0; foreach ( $values_strip as $v ) : $n++; ?>
						<article class="founder-promise">
							<span class="founder-promise__num"><?php echo esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3><?php echo esc_html( (string) ( $v['title'] ?? '' ) ); ?></h3>
							<p><?php echo esc_html( (string) ( $v['body'] ?? '' ) ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container" style="max-width:var(--container-narrow)">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $founder_contact_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $founder_contact_h2 ); ?></h2>
			</header>
			<?php $tel = preg_replace( '/[^0-9+]/', '', $phone ); ?>
			<ul class="founder-contact-list">
				<li><strong><?php esc_html_e( 'Phone', 'showtime-pools' ); ?>:</strong> <a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $phone ); ?></a></li>
				<?php if ( '' !== $email ) : ?>
					<li><strong><?php esc_html_e( 'Email', 'showtime-pools' ); ?>:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
				<?php endif; ?>
				<li>
					<strong><?php esc_html_e( 'Sherman Oaks shop', 'showtime-pools' ); ?>:</strong>
					<?php echo esc_html( (string) ( $shop['street'] ?? '' ) . ', ' . (string) ( $shop['city'] ?? '' ) ); ?>
				</li>
				<?php if ( '' !== $linkedin ) : ?>
					<li><strong><?php esc_html_e( 'LinkedIn', 'showtime-pools' ); ?>:</strong> <a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener">@showtimepoolssocal</a></li>
				<?php endif; ?>
			</ul>

			<div class="cluster" style="margin-top:var(--sp-7)">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a free quote', 'showtime-pools' ); ?></a>
				<a class="btn btn--ghost btn--lg" href="tel:<?php echo esc_attr( $tel ); ?>"><?php esc_html_e( 'Call Steve direct', 'showtime-pools' ); ?> · <?php echo esc_html( $phone ); ?></a>
			</div>
		</div>
	</section>

</main>
<script type="application/ld+json"><?php echo wp_json_encode( $person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php
get_footer();

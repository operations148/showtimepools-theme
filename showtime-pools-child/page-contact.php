<?php
/**
 * Template Name: Contact
 *
 * /contact/ page. Native HTML form posts to /wp-json/showtime/v1/contact,
 * which forwards to GHL via the central Ghl integration. No FluentForms
 * dependency on this template — but if Steve later swaps the form for a
 * FluentForms shortcode, the FF→GHL bridge handles it identically.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$services = class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::all() : array();

// ── Native WP overrides (edit via WP Admin → Pages → Contact → Update) ──────
$pid  = get_the_ID();
$_pm  = static fn( string $k ) => (string) get_post_meta( $pid, $k, true );

// Priority: post meta → ACF option → PHP fallback.
$opt = function_exists( 'get_field' ) ? 'option' : false;

$c_eyebrow = $_pm( 'contact_eyebrow' ) ?: ( $opt ? (string) get_field( 'contact_eyebrow', $opt ) : '' );
$c_title   = $_pm( 'contact_h1' )      ?: ( $opt ? (string) get_field( 'contact_title', $opt ) : '' );
$c_lead    = $_pm( 'contact_lead' )    ?: ( $opt ? (string) get_field( 'contact_lead', $opt ) : '' );
$c_ftitle  = $_pm( 'contact_form_title' ) ?: ( $opt ? (string) get_field( 'contact_form_title', $opt ) : '' );
$c_fbody   = $opt ? (string) get_field( 'contact_form_body', $opt ) : '';

$c_eyebrow = '' !== $c_eyebrow ? $c_eyebrow : __( 'Talk to us', 'showtime-pools' );
$c_title   = '' !== $c_title   ? $c_title   : __( 'Talk to a real human at Showtime Pools.', 'showtime-pools' );
$c_lead    = '' !== $c_lead    ? $c_lead    : __( 'Send us a note and Steve or a senior tech replies within one business day. Same-day for active service customers.', 'showtime-pools' );
$c_ftitle  = '' !== $c_ftitle  ? $c_ftitle  : __( 'Tell us about your pool.', 'showtime-pools' );

$contact_sidebar_h2        = $_pm( 'contact_sidebar_h2' );
$contact_existing_customer = $_pm( 'contact_existing_customer' );
$contact_existing_body     = $_pm( 'contact_existing_body' );

// Phone + email come from Customizer (Showtime Brand panel) via filter bridge.
$phone = (string) apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$email = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );

// Offices come from the one canonical source (functions.php): Sherman Oaks +
// Century City. Beverly Hills is a service area, not an office — it must not
// appear in this list. ACF `offices` rows still override in wp-admin.
$offices = function_exists( 'showtime_offices' )
	? showtime_offices()
	: array(
		array( 'label' => __( 'Sherman Oaks (Main)', 'showtime-pools' ), 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
		array( 'label' => __( 'Century City', 'showtime-pools' ),         'street' => '1925 Century Park East, Suite 1700', 'city' => 'Los Angeles, CA 90067' ),
	);

$hours_default_map = array(
	__( 'Mon-Sat', 'showtime-pools' ) => __( '8:00 AM - 5:00 PM', 'showtime-pools' ),
	__( 'Sunday', 'showtime-pools' )  => __( 'By appointment for emergencies', 'showtime-pools' ),
);
$hours_rows = $opt ? get_field( 'hours_rows', $opt ) : null;
if ( is_array( $hours_rows ) && ! empty( $hours_rows ) ) {
	$hours = array();
	foreach ( $hours_rows as $row ) {
		$d = (string) ( $row['day'] ?? '' );
		$t = (string) ( $row['time'] ?? '' );
		if ( '' !== $d ) { $hours[ $d ] = $t; }
	}
} else {
	$hours = $hours_default_map;
}

// Use the first office for the map embed (defaults to Sherman Oaks).
$map_office = $offices[0] ?? $offices_default[0];
$map_query = trim( ( (string) ( $map_office['street'] ?? '' ) ) . ' ' . ( (string) ( $map_office['city'] ?? '' ) ) );
$map_url   = 'https://www.google.com/maps?q=' . rawurlencode( $map_query ) . '&output=embed';

// ── GHL contact form (replaces the broken native form) ──────────────────────
// URL is a CMS field (Showtime → Settings → "GHL Contact Form URL"); falls back
// to the bundled default. UTM is appended so n8n still attributes the source.
// Filterable so the URL/UTM can change in one place.
$contact_form_base = trim( (string) get_option( 'showtime_ghl_contact_url', '' ) );
if ( '' === $contact_form_base && defined( 'SHOWTIME_CONTACT_FORM_URL' ) ) {
	$contact_form_base = SHOWTIME_CONTACT_FORM_URL;
}
$contact_form_url = (string) apply_filters(
	'showtime/contact/form_url',
	add_query_arg(
		array(
			'utm_source'  => (string) get_option( 'showtime_utm_source', 'website' ),
			'utm_medium'  => (string) get_option( 'showtime_utm_medium', 'organic' ),
			'utm_content' => 'contact_form',
		),
		$contact_form_base
	)
);
// form_embed.js auto-sizes the iframe whose id matches the bare form id.
$contact_embed_id = '';
if ( preg_match( '#/widget/form/([A-Za-z0-9]+)#', $contact_form_url, $cm ) ) {
	$contact_embed_id = $cm[1];
}
?>
<main id="primary" class="site-main contact-page">

	<?php $contact_hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_3', 1920 ) : ''; ?>
	<section class="contact-hero section section--brand contact-hero--photo" data-reveal>
		<?php if ( $contact_hero_img ) : ?>
			<img class="contact-hero__photo" src="<?php echo esc_url( $contact_hero_img ); ?>" <?php echo showtime_hero_srcset_attr( 'lifestyle_3' ); ?> alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="contact-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs contact-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Contact', 'showtime-pools' ); ?></span>
			</nav>
			<div class="contact-hero__inner stack stack--md">
				<span class="eyebrow contact-hero__eyebrow"><?php echo esc_html( $c_eyebrow ); ?></span>
				<h1 class="contact-hero__title balance"><?php echo esc_html( $c_title ); ?></h1>
				<p class="contact-hero__lead"><?php echo esc_html( $c_lead ); ?></p>
			</div>
		</div>
	</section>

	<section class="contact-grid section" data-reveal>
		<div class="container">
			<div class="contact-grid__inner">

				<div class="contact-form-wrap">
					<header class="stack stack--sm" style="margin-bottom:var(--sp-6)">
						<span class="eyebrow"><?php esc_html_e( 'Send a message', 'showtime-pools' ); ?></span>
						<h2><?php echo esc_html( $c_ftitle ); ?></h2>
						<?php if ( '' !== $c_fbody ) : ?>
							<p><?php echo esc_html( $c_fbody ); ?></p>
						<?php endif; ?>
					</header>

					<?php // GHL form embed — replaces the broken native form. Transparent,
					      // no card, fills the form column; internal styling is owned by the
					      // GHL form builder. form_embed.js (enqueued for this template) sizes
					      // the iframe to its content. ?>
					<div class="contact-embed">
						<iframe
							src="<?php echo esc_url( $contact_form_url ); ?>"
							<?php if ( '' !== $contact_embed_id ) : ?>id="<?php echo esc_attr( $contact_embed_id ); ?>"<?php endif; ?>
							class="contact-embed__iframe"
							scrolling="no"
							referrerpolicy="no-referrer-when-downgrade"
							title="<?php esc_attr_e( 'Contact Showtime Pools', 'showtime-pools' ); ?>"
						></iframe>
						<p class="contact-embed__alt">
							<?php
							/* translators: %s: anchor link */
							printf(
								esc_html__( 'Form not loading? %s.', 'showtime-pools' ),
								'<a href="' . esc_url( $contact_form_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open it in a new tab', 'showtime-pools' ) . '</a>'
							);
							?>
						</p>
					</div>
				</div>

				<aside class="contact-info">
					<div class="contact-info__card">
						<h2 class="contact-info__title"><?php echo esc_html( '' !== $contact_sidebar_h2 ? $contact_sidebar_h2 : __( 'Or reach us directly', 'showtime-pools' ) ); ?></h2>
						<ul class="contact-info__list" role="list">
							<?php $tel = preg_replace( '/[^0-9+]/', '', $phone ); ?>
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Call', 'showtime-pools' ); ?></span>
								<a href="tel:<?php echo esc_attr( $tel ); ?>" class="contact-info__value"><?php echo esc_html( $phone ); ?></a>
							</li>
							<?php if ( '' !== $email ) : ?>
								<li>
									<span class="contact-info__label"><?php esc_html_e( 'Email', 'showtime-pools' ); ?></span>
									<a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-info__value"><?php echo esc_html( $email ); ?></a>
								</li>
							<?php endif; ?>
							<?php foreach ( $offices as $o ) :
								$label  = (string) ( $o['label']  ?? '' );
								$street = (string) ( $o['street'] ?? '' );
								$city   = (string) ( $o['city']   ?? '' );
								if ( '' === $label && '' === $street ) { continue; }
							?>
								<li>
									<span class="contact-info__label"><?php echo esc_html( $label ); ?></span>
									<span class="contact-info__value"><?php echo esc_html( $street ); ?><br><?php echo esc_html( $city ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="contact-info__hours">
							<h3 class="contact-info__sub"><?php esc_html_e( 'Hours', 'showtime-pools' ); ?></h3>
							<dl class="contact-info__dl">
								<?php foreach ( $hours as $day => $time ) : ?>
									<dt><?php echo esc_html( $day ); ?></dt><dd><?php echo esc_html( $time ); ?></dd>
								<?php endforeach; ?>
							</dl>
						</div>

						<div class="contact-info__map" aria-hidden="true">
							<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
								src="<?php echo esc_url( $map_url ); ?>"
								title="<?php esc_attr_e( 'Showtime Pools location map', 'showtime-pools' ); ?>"
							></iframe>
						</div>
					</div>

					<div class="contact-info__alt">
						<p><strong><?php echo esc_html( '' !== $contact_existing_customer ? $contact_existing_customer : __( 'Already a service customer?', 'showtime-pools' ) ); ?></strong></p>
						<p><?php echo esc_html( '' !== $contact_existing_body ? $contact_existing_body : __( 'Text the same number you used at sign-up; same-day priority is reserved for the route schedule.', 'showtime-pools' ) ); ?></p>
					</div>
				</aside>

			</div>
		</div>
	</section>


</main>
<?php
get_footer();

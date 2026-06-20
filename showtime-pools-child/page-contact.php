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

// Offices + hours come from Site Content → Offices & hours ACF repeaters.
$offices_default = array(
	array( 'label' => __( 'Sherman Oaks (Main)', 'showtime-pools' ), 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
	array( 'label' => __( 'Century City', 'showtime-pools' ),         'street' => '1925 Century Park East, Suite 1700', 'city' => 'Los Angeles, CA 90067' ),
	array( 'label' => __( 'Beverly Hills', 'showtime-pools' ),        'street' => '9461 Charleville Blvd. #1902', 'city' => 'Beverly Hills, CA 90212' ),
);
$offices = function_exists( 'showtime_acf_rows' )
	? showtime_acf_rows( 'offices', $offices_default )
	: $offices_default;
$offices = apply_filters( 'showtime/business/offices', $offices );

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
?>
<main id="primary" class="site-main contact-page">

	<?php $contact_hero_img = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_3', 1920 ) : ''; ?>
	<section class="contact-hero section section--brand contact-hero--photo" data-reveal>
		<?php if ( $contact_hero_img ) : ?>
			<img class="contact-hero__photo" src="<?php echo esc_url( $contact_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
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

					<form class="contact-form" id="showtime-contact-form" novalidate>
						<input type="hidden" name="loaded_at" value="<?php echo esc_attr( (string) time() ); ?>">
						<div class="contact-form__hp" aria-hidden="true">
							<label>Leave this field empty<input type="text" name="hp_url" tabindex="-1" autocomplete="off"></label>
						</div>

						<?php
						// UTM attribution defaults (Showtime Pools → Site Content → Homepage).
						// contact.js overrides any of these from real ?utm_… in the visitor's
						// URL before submit; the REST controller forwards them to GHL → n8n.
						$utm_fields = array(
							'utm_source'   => 'website',
							'utm_medium'   => 'organic',
							'utm_campaign' => 'site_form',
							'utm_content'  => 'contact_form',
						);
						foreach ( $utm_fields as $utm_key => $utm_default ) :
							$utm_val = (string) get_option( 'showtime_' . $utm_key, $utm_default );
							?>
							<input type="hidden" name="<?php echo esc_attr( $utm_key ); ?>" value="<?php echo esc_attr( $utm_val ); ?>" data-utm="<?php echo esc_attr( $utm_key ); ?>">
						<?php endforeach; ?>

						<div class="contact-form__row">
							<div class="form-field">
								<label class="form-label" for="cf-name"><?php esc_html_e( 'Name', 'showtime-pools' ); ?> <span class="required">*</span></label>
								<input class="form-input" type="text" id="cf-name" name="name" autocomplete="name" required>
								<span class="form-error" data-field="name" hidden></span>
							</div>
							<div class="form-field">
								<label class="form-label" for="cf-phone"><?php esc_html_e( 'Phone', 'showtime-pools' ); ?> <span class="required">*</span></label>
								<input class="form-input" type="tel" id="cf-phone" name="phone" autocomplete="tel" required>
								<span class="form-error" data-field="phone" hidden></span>
							</div>
						</div>

						<div class="form-field">
							<label class="form-label" for="cf-email"><?php esc_html_e( 'Email', 'showtime-pools' ); ?> <span class="required">*</span></label>
							<input class="form-input" type="email" id="cf-email" name="email" autocomplete="email" required>
							<span class="form-error" data-field="email" hidden></span>
						</div>

						<div class="form-field">
							<label class="form-label" for="cf-service"><?php esc_html_e( "What's this about?", 'showtime-pools' ); ?></label>
							<select class="form-select" id="cf-service" name="service">
								<option value=""><?php esc_html_e( 'Choose a service (optional)', 'showtime-pools' ); ?></option>
								<?php foreach ( $services as $svc ) : ?>
									<option value="<?php echo esc_attr( (string) $svc['slug'] ); ?>"><?php echo esc_html( (string) $svc['title'] ); ?></option>
								<?php endforeach; ?>
								<option value="other"><?php esc_html_e( 'Something else', 'showtime-pools' ); ?></option>
							</select>
						</div>

						<div class="form-field">
							<label class="form-label" for="cf-message"><?php esc_html_e( 'Message', 'showtime-pools' ); ?> <span class="required">*</span></label>
							<textarea class="form-textarea" id="cf-message" name="message" rows="5" required minlength="10" placeholder="<?php esc_attr_e( 'Pool size, what you need, anything that helps us prep before we call back…', 'showtime-pools' ); ?>"></textarea>
							<span class="form-error" data-field="message" hidden></span>
						</div>

						<label class="contact-form__consent">
							<input type="checkbox" name="consent" value="1" checked>
							<span><?php esc_html_e( "It's OK to contact me by SMS or call about my message. Unsubscribe any time.", 'showtime-pools' ); ?></span>
						</label>

						<?php if ( class_exists( '\\Showtime\\Security\\Turnstile' ) && \Showtime\Security\Turnstile::is_configured() ) : ?>
							<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( \Showtime\Security\Turnstile::site_key() ); ?>"></div>
							<span class="form-error" data-field="turnstile" hidden></span>
						<?php endif; ?>

						<div class="cluster" style="align-items:center">
							<button type="submit" class="btn btn--primary btn--lg" data-default-label="<?php esc_attr_e( 'Send message', 'showtime-pools' ); ?>">
								<?php esc_html_e( 'Send message', 'showtime-pools' ); ?>
							</button>
							<span class="contact-form__hint">
								<?php esc_html_e( 'Reply within 1 business day.', 'showtime-pools' ); ?>
							</span>
						</div>

						<div class="contact-form__alert" data-status="success" hidden role="status"></div>
						<div class="contact-form__alert contact-form__alert--err" data-status="error" hidden role="alert"></div>
					</form>
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
						<p><?php echo esc_html( '' !== $contact_existing_body ? $contact_existing_body : __( 'Text the same number you used at sign-up — same-day priority is reserved for the route schedule.', 'showtime-pools' ) ); ?></p>
					</div>
				</aside>

			</div>
		</div>
	</section>


</main>
<?php
get_footer();

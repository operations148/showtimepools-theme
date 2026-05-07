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
				<span class="eyebrow contact-hero__eyebrow"><?php esc_html_e( 'Talk to us', 'showtime-pools' ); ?></span>
				<h1 class="contact-hero__title balance"><?php esc_html_e( 'Talk to a real human at Showtime Pools.', 'showtime-pools' ); ?></h1>
				<p class="contact-hero__lead"><?php esc_html_e( 'Send us a note and Steve or a senior tech replies within one business day. Same-day for active service customers.', 'showtime-pools' ); ?></p>
			</div>
		</div>
	</section>

	<section class="contact-grid section" data-reveal>
		<div class="container">
			<div class="contact-grid__inner">

				<div class="contact-form-wrap">
					<header class="stack stack--sm" style="margin-bottom:var(--sp-6)">
						<span class="eyebrow"><?php esc_html_e( 'Send a message', 'showtime-pools' ); ?></span>
						<h2><?php esc_html_e( 'Tell us about your pool.', 'showtime-pools' ); ?></h2>
					</header>

					<form class="contact-form" id="showtime-contact-form" novalidate>
						<input type="hidden" name="loaded_at" value="<?php echo esc_attr( (string) time() ); ?>">
						<div class="contact-form__hp" aria-hidden="true">
							<label>Leave this field empty<input type="text" name="hp_url" tabindex="-1" autocomplete="off"></label>
						</div>

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
						<h2 class="contact-info__title"><?php esc_html_e( 'Or reach us directly', 'showtime-pools' ); ?></h2>
						<ul class="contact-info__list" role="list">
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Call', 'showtime-pools' ); ?></span>
								<a href="tel:+13238252099" class="contact-info__value">(323) 825-2099</a>
							</li>
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Email', 'showtime-pools' ); ?></span>
								<a href="mailto:operations@showtimepoolmechanics.com" class="contact-info__value">operations@showtimepoolmechanics.com</a>
							</li>
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Sherman Oaks (Main)', 'showtime-pools' ); ?></span>
								<span class="contact-info__value">15301 Ventura Blvd.<br>Sherman Oaks, CA 91403</span>
							</li>
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Century City', 'showtime-pools' ); ?></span>
								<span class="contact-info__value">1925 Century Park East, Suite 1700<br>Los Angeles, CA 90067</span>
							</li>
							<li>
								<span class="contact-info__label"><?php esc_html_e( 'Beverly Hills', 'showtime-pools' ); ?></span>
								<span class="contact-info__value">9461 Charleville Blvd. #1902<br>Beverly Hills, CA 90212</span>
							</li>
						</ul>

						<div class="contact-info__hours">
							<h3 class="contact-info__sub"><?php esc_html_e( 'Hours', 'showtime-pools' ); ?></h3>
							<dl class="contact-info__dl">
								<dt><?php esc_html_e( 'Mon-Sat', 'showtime-pools' ); ?></dt><dd>8:00a &mdash; 5:00p</dd>
								<dt><?php esc_html_e( 'Sunday', 'showtime-pools' ); ?></dt><dd><?php esc_html_e( 'By appointment for emergencies', 'showtime-pools' ); ?></dd>
							</dl>
						</div>

						<div class="contact-info__map" aria-hidden="true">
							<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
								src="https://www.google.com/maps?q=15303+Ventura+Blvd+Sherman+Oaks+CA+91403&output=embed"
								title="<?php esc_attr_e( 'Showtime Pools location map', 'showtime-pools' ); ?>"
							></iframe>
						</div>
					</div>

					<div class="contact-info__alt">
						<p><strong><?php esc_html_e( 'Already a service customer?', 'showtime-pools' ); ?></strong></p>
						<p><?php esc_html_e( 'Text the same number you used at sign-up — same-day priority is reserved for the route schedule.', 'showtime-pools' ); ?></p>
					</div>
				</aside>

			</div>
		</div>
	</section>


</main>
<?php
get_footer();

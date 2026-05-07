<?php
/**
 * Template Name: Legal Page
 *
 * Used for /privacy/ and /terms/. Renders the WP page content body inside
 * a long-form layout with sticky table-of-contents on desktop.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$slug = get_post_field( 'post_name', get_the_ID() );

$copy = array(
	'privacy' => array(
		'eyebrow' => __( 'Last updated 2026-05-06', 'showtime-pools' ),
		'lead'    => __( 'Plain-English summary of what we collect, why, and how we treat it. The full text follows.', 'showtime-pools' ),
		'body'    => '<h2>What we collect</h2><p>Three things: contact info you give us via the contact form (name, phone, email, message), the page URL you came from, and your IP address. The IP is logged for spam protection only — never sold, never used for advertising.</p><h2>Why we collect it</h2><p>So that Steve or a senior tech can call you back about your message. Period. We do not run ad retargeting on this site, we do not sell or share your information with third parties, and we do not use your data for marketing analytics.</p><h2>Where it goes</h2><p>Form submissions are forwarded to GoHighLevel (our internal CRM) for follow-up. GHL is hosted in the United States and is bound by industry-standard data-protection terms. Nothing leaves the GHL boundary unless you ask us to send it (e.g. you forward us a contractor reference).</p><h2>Cookies</h2><p>We use a session cookie for honeypot/spam protection on the contact form, and standard WordPress login cookies if you have a staff account. We do not run third-party analytics cookies, advertising cookies, or social-tracking pixels.</p><h2>Your rights</h2><p>You can ask us to delete your record at any time. Email <a href="mailto:operations@showtimepoolmechanics.com">operations@showtimepoolmechanics.com</a> with the subject "Delete my data" and we will purge your contact record from GHL and our backups within 30 days. California residents have additional rights under CCPA — same process, same email.</p><h2>Children</h2><p>This site is for adult business inquiries about pool services. We do not knowingly collect data from anyone under 16.</p><h2>Updates</h2><p>If we ever change this policy, the "last updated" date at the top of the page changes. Material changes get an email notice to anyone with an active service contract.</p><h2>Questions</h2><p>Email <a href="mailto:operations@showtimepoolmechanics.com">operations@showtimepoolmechanics.com</a> or call (323) 825-2099. Steve usually answers.</p>',
	),
	'terms' => array(
		'eyebrow' => __( 'Last updated 2026-05-06', 'showtime-pools' ),
		'lead'    => __( 'The terms that govern using this website. Service contracts have their own terms in the signed contract.', 'showtime-pools' ),
		'body'    => '<h2>Use of this site</h2><p>This website is informational. Browsing the site, reading service pages, or filling out the contact form does not create a contract for pool work. A signed proposal does.</p><h2>Quotes and proposals</h2><p>Quotes and itemized proposals are valid for 30 days from the date issued, unless otherwise stated in writing on the quote. Pricing on website pages is directional — your written quote is the binding number.</p><h2>Service contracts</h2><p>Weekly service is month-to-month. You can cancel any time with 7 days written notice. Construction and remodel contracts have project-specific terms in the signed contract, including milestone payments, change-order procedure, and warranty coverage.</p><h2>Warranties</h2><p>We warrant our workmanship for 2 years on construction and remodel work. PebbleTec finishes carry an additional 5-year manufacturer warranty. Equipment carries the manufacturer warranty pass-through (typically 1-3 years depending on the item). Specific terms are in your signed contract or finish-warranty document — those govern in any disagreement with this summary.</p><h2>Liability</h2><p>Our liability is limited to the cost of the work performed. We carry $2M general liability insurance and full workers compensation; we will provide a certificate of insurance to any property owner on request.</p><h2>Photography and references</h2><p>By default we ask permission before photographing a completed project for our website or social media. If you would prefer your project not be photographed at all, mention it before we start work and we will note it on the project record.</p><h2>Governing law</h2><p>These terms are governed by California law. Any dispute that cannot be resolved between us directly is subject to the exclusive jurisdiction of the courts of Los Angeles County, California.</p><h2>Contact</h2><p>Email <a href="mailto:operations@showtimepoolmechanics.com">operations@showtimepoolmechanics.com</a> or call (323) 825-2099 with any questions.</p>',
	),
);

$page_copy = $copy[ $slug ] ?? array(
	'eyebrow' => '',
	'lead'    => '',
	'body'    => apply_filters( 'the_content', get_the_content() ),
);
?>
<main id="primary" class="site-main interior-page">
	<section class="int-hero int-hero--brand int-hero--compact" data-reveal>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $page_copy['eyebrow'] ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( get_the_title() ); ?></h1>
				<?php if ( ! empty( $page_copy['lead'] ) ) : ?>
					<p class="int-hero__lead"><?php echo esc_html( $page_copy['lead'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container" style="max-width:var(--container-narrow)">
			<article class="legal-prose">
				<?php echo wp_kses_post( $page_copy['body'] ); ?>
			</article>
		</div>
	</section>

</main>
<?php get_footer();

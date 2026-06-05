<?php
/**
 * Template Name: Affiliate
 *
 * /affiliate/ — Showtime Pools referral-partner recruitment landing page.
 * Recruits realtors, property managers, HOAs, home inspectors, and existing
 * customers to refer pool-service work for recurring commission.
 *
 * Every field is editable in WP Admin → Pages → Affiliate Program → Update
 * (native meta box, see inc/meta-fields.php). Defaults live in the seeder
 * (page-defaults.php) and are mirrored by the `?:` fallbacks below.
 *
 * The signup form posts to /wp-json/showtime/v1/affiliate (AffiliateController)
 * which forwards to GHL via Ghl::forward(). See assets/js/affiliate.js.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ─── Content sources (edit via WP Admin → Pages → Affiliate Program) ──────────
$pid = get_the_ID();
$_pm = static fn( string $k ) => (string) get_post_meta( $pid, $k, true );

// Hero.
$aff_hero_eyebrow = $_pm( 'affiliate_hero_eyebrow' ) ?: __( 'Now accepting referral partners', 'showtime-pools' );
$aff_h1           = $_pm( 'affiliate_h1' )           ?: __( 'Refer pool owners. Earn recurring commission every month.', 'showtime-pools' );
$aff_hero_lead    = $_pm( 'affiliate_hero_lead' )    ?: __( 'Join the Showtime Pools Partner Program. Every homeowner, property manager, or HOA you send our way earns you commission for as long as they stay on service — no chasing, no cold-selling, no cap.', 'showtime-pools' );
$aff_hero_cta     = $_pm( 'affiliate_hero_cta' )     ?: __( 'Become a Partner', 'showtime-pools' );

// Trust strip (skip empties).
$aff_trust = array_filter( array(
	$_pm( 'affiliate_trust1' ) ?: __( 'Paid monthly', 'showtime-pools' ),
	$_pm( 'affiliate_trust2' ) ?: __( 'No cap on referrals', 'showtime-pools' ),
	$_pm( 'affiliate_trust3' ) ?: __( 'Real-time tracking', 'showtime-pools' ),
	$_pm( 'affiliate_trust4' ) ?: __( 'Free to join', 'showtime-pools' ),
) );

// Benefits.
$aff_ben_eyebrow = $_pm( 'affiliate_benefits_eyebrow' ) ?: __( 'Why partner with us', 'showtime-pools' );
$aff_ben_h2      = $_pm( 'affiliate_benefits_h2' )      ?: __( 'Build a referral income that pays you every month.', 'showtime-pools' );
$aff_ben_lead    = $_pm( 'affiliate_benefits_lead' )    ?: __( 'Earn predictable monthly commission from every customer you refer — without one-time payouts or constant selling. A scalable income stream that compounds as your referrals stay on service.', 'showtime-pools' );

// Benefit cards — title/body editable, icon is fixed presentation.
$aff_benefits = array(
	array(
		'icon'  => 'coins',
		'title' => $_pm( 'affiliate_benefit1_title' ) ?: __( 'Recurring Commission', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_benefit1_body' )  ?: __( 'Earn commission every month your referral stays on a maintenance plan. Steady income that grows with your book of referrals.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'chart',
		'title' => $_pm( 'affiliate_benefit2_title' ) ?: __( 'Real-Time Tracking', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_benefit2_body' )  ?: __( 'See every referral, conversion, and payout in your partner dashboard. Always know exactly what you have earned.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'infinity',
		'title' => $_pm( 'affiliate_benefit3_title' ) ?: __( 'No Cap on Earnings', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_benefit3_body' )  ?: __( 'Refer one pool owner or fifty — there is no limit. Scale your income as fast as you can send qualified leads our way.', 'showtime-pools' ),
	),
	array(
		'icon'  => 'rocket',
		'title' => $_pm( 'affiliate_benefit4_title' ) ?: __( 'Fast Onboarding', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_benefit4_body' )  ?: __( 'Apply in minutes and get your referral link the same day. We handle the quote, the work, and the warranty.', 'showtime-pools' ),
	),
);

// Process.
$aff_proc_eyebrow = $_pm( 'affiliate_process_eyebrow' ) ?: __( 'Process', 'showtime-pools' );
$aff_proc_h2      = $_pm( 'affiliate_process_h2' )      ?: __( 'From referral to recurring income — here is how it works.', 'showtime-pools' );
$aff_proc_lead    = $_pm( 'affiliate_process_lead' )    ?: __( 'Three simple steps. Sign up, share, and get paid every month your referral stays with us.', 'showtime-pools' );

$aff_steps = array(
	array(
		'title' => $_pm( 'affiliate_step1_title' ) ?: __( 'Apply Free', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_step1_body' )  ?: __( 'Fill out the form below. We review and send your unique referral link the same day. No cost to join.', 'showtime-pools' ),
	),
	array(
		'title' => $_pm( 'affiliate_step2_title' ) ?: __( 'Refer Pool Owners', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_step2_body' )  ?: __( 'Send homeowners, property managers, and HOAs our way through your link — or just have them mention your name.', 'showtime-pools' ),
	),
	array(
		'title' => $_pm( 'affiliate_step3_title' ) ?: __( 'Earn Every Month', 'showtime-pools' ),
		'body'  => $_pm( 'affiliate_step3_body' )  ?: __( 'Get paid for as long as your referral stays on service. True recurring income from work we deliver.', 'showtime-pools' ),
	),
);

// FAQ (up to 5; skip empties).
$aff_faq_eyebrow = $_pm( 'affiliate_faq_eyebrow' ) ?: __( 'Questions', 'showtime-pools' );
$aff_faq_h2      = $_pm( 'affiliate_faq_h2' )      ?: __( 'Everything partners ask before they join.', 'showtime-pools' );
$aff_faq_defaults = array(
	array( 'q' => __( 'How much can I earn?', 'showtime-pools' ),                 'a' => __( 'You earn recurring commission every month your referral stays on a Showtime Pools maintenance plan, plus a share of qualifying repair and remodel work. We confirm your exact rate when you join.', 'showtime-pools' ) ),
	array( 'q' => __( 'Who makes a good referral?', 'showtime-pools' ),           'a' => __( 'Any pool owner in our service area — homeowners, property managers, HOAs, and home inspectors all refer to us. If they own or manage a pool in the LA area, they qualify.', 'showtime-pools' ) ),
	array( 'q' => __( 'When and how do I get paid?', 'showtime-pools' ),          'a' => __( 'Commissions are paid monthly once your referral is active and billed. You can track every referral and payout in your partner dashboard.', 'showtime-pools' ) ),
	array( 'q' => __( 'Does it cost anything to join?', 'showtime-pools' ),       'a' => __( 'No. The program is free to join. You apply, we approve, and you start referring the same day.', 'showtime-pools' ) ),
	array( 'q' => __( 'Do I need a website or audience?', 'showtime-pools' ),     'a' => __( 'No. A referral link helps, but plenty of partners simply introduce us by name. We just need a way to credit the referral to you.', 'showtime-pools' ) ),
);
$aff_faq = array();
for ( $i = 1; $i <= 5; $i++ ) {
	$q = $_pm( "affiliate_faq{$i}_q" ) ?: ( $aff_faq_defaults[ $i - 1 ]['q'] ?? '' );
	$a = $_pm( "affiliate_faq{$i}_a" ) ?: ( $aff_faq_defaults[ $i - 1 ]['a'] ?? '' );
	if ( '' !== $q && '' !== $a ) {
		$aff_faq[] = array( 'q' => $q, 'a' => $a );
	}
}

// Form.
$aff_form_eyebrow = $_pm( 'affiliate_form_eyebrow' ) ?: __( 'Apply now', 'showtime-pools' );
$aff_form_h2      = $_pm( 'affiliate_form_h2' )      ?: __( 'Join the Partner Program', 'showtime-pools' );
$aff_form_lead    = $_pm( 'affiliate_form_lead' )    ?: __( 'Tell us how you plan to refer pool owners and we will get your partner link set up.', 'showtime-pools' );
$aff_submit_label = $_pm( 'affiliate_submit_label' ) ?: __( 'Activate My Partner Account', 'showtime-pools' );
$aff_consent_text = $_pm( 'affiliate_consent_text' ) ?: __( 'I agree to the partner program terms and the commission payout policy.', 'showtime-pools' );

// Promote options — one per line in admin; fall back to pool-relevant defaults.
$aff_promote_raw = $_pm( 'affiliate_promote_options' );
if ( '' === $aff_promote_raw ) {
	$aff_promote_raw = implode( "\n", array(
		__( 'Realtor / real estate', 'showtime-pools' ),
		__( 'Property manager', 'showtime-pools' ),
		__( 'HOA / community', 'showtime-pools' ),
		__( 'Home inspector', 'showtime-pools' ),
		__( 'Existing customer', 'showtime-pools' ),
		__( 'Social media', 'showtime-pools' ),
		__( 'Other', 'showtime-pools' ),
	) );
}
$aff_promote_options = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $aff_promote_raw ) ) ) );

// Inline SVG icons for benefit cards.
$aff_icon = static function ( string $name ): string {
	$icons = array(
		'coins'    => '<circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>',
		'chart'    => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
		'infinity' => '<path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 0 0 0-8c-2 0-4 1.33-6 4Z"/>',
		'rocket'   => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91 0z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
	);
	return $icons[ $name ] ?? $icons['coins'];
};
?>
<main id="primary" class="site-main interior-page affiliate-page">

	<section class="int-hero int-hero--brand" data-reveal>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Partner Program', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php echo esc_html( $aff_hero_eyebrow ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $aff_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $aff_hero_lead ); ?></p>
				<div class="affiliate-hero__cta">
					<a class="btn btn--primary btn--lg" href="#affiliate-apply"><?php echo esc_html( $aff_hero_cta ); ?></a>
				</div>
				<?php if ( $aff_trust ) : ?>
					<ul class="affiliate-trust" role="list">
						<?php foreach ( $aff_trust as $chip ) : ?>
							<li class="affiliate-trust__item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
								<span><?php echo esc_html( $chip ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $aff_ben_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $aff_ben_h2 ); ?></h2>
				<p class="int-section__lead"><?php echo esc_html( $aff_ben_lead ); ?></p>
			</header>
			<div class="affiliate-benefits">
				<?php foreach ( $aff_benefits as $b ) :
					if ( '' === $b['title'] && '' === $b['body'] ) { continue; }
				?>
					<article class="affiliate-benefit">
						<span class="affiliate-benefit__icon" aria-hidden="true">
							<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $aff_icon( (string) $b['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG path literals. ?></svg>
						</span>
						<h3 class="affiliate-benefit__title"><?php echo esc_html( $b['title'] ); ?></h3>
						<p class="affiliate-benefit__body"><?php echo esc_html( $b['body'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $aff_proc_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $aff_proc_h2 ); ?></h2>
				<p class="int-section__lead"><?php echo esc_html( $aff_proc_lead ); ?></p>
			</header>
			<ol class="affiliate-process" role="list">
				<?php $sn = 0; foreach ( $aff_steps as $s ) :
					$sn++;
					if ( '' === $s['title'] && '' === $s['body'] ) { continue; }
				?>
					<li class="affiliate-step">
						<span class="affiliate-step__num"><?php echo esc_html( str_pad( (string) $sn, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3 class="affiliate-step__title"><?php echo esc_html( $s['title'] ); ?></h3>
						<p class="affiliate-step__body"><?php echo esc_html( $s['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php if ( $aff_faq ) : ?>
	<section class="int-section" data-reveal>
		<div class="container container--narrow">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $aff_faq_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $aff_faq_h2 ); ?></h2>
			</header>
			<div class="affiliate-faq">
				<?php foreach ( $aff_faq as $f ) : ?>
					<details class="affiliate-faq__item">
						<summary class="affiliate-faq__q"><?php echo esc_html( $f['q'] ); ?></summary>
						<div class="affiliate-faq__a"><p><?php echo esc_html( $f['a'] ); ?></p></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section id="affiliate-apply" class="int-section int-section--cream affiliate-apply" data-reveal>
		<div class="container container--narrow">
			<header class="int-section__head">
				<span class="eyebrow"><?php echo esc_html( $aff_form_eyebrow ); ?></span>
				<h2 class="balance"><?php echo esc_html( $aff_form_h2 ); ?></h2>
				<p class="int-section__lead"><?php echo esc_html( $aff_form_lead ); ?></p>
			</header>

			<form class="affiliate-form" id="showtime-affiliate-form" novalidate>
				<input type="hidden" name="loaded_at" value="<?php echo esc_attr( (string) time() ); ?>">
				<div class="affiliate-form__hp" aria-hidden="true">
					<label>Leave this field empty<input type="text" name="hp_url" tabindex="-1" autocomplete="off"></label>
				</div>

				<div class="form-field">
					<label class="form-label" for="aff-name"><?php esc_html_e( 'Full Name', 'showtime-pools' ); ?> <span class="required">*</span></label>
					<input class="form-input" type="text" id="aff-name" name="full_name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Enter your full name', 'showtime-pools' ); ?>">
					<span class="form-error" data-field="full_name" hidden></span>
				</div>

				<div class="affiliate-form__row">
					<div class="form-field">
						<label class="form-label" for="aff-phone"><?php esc_html_e( 'Phone', 'showtime-pools' ); ?> <span class="required">*</span></label>
						<input class="form-input" type="tel" id="aff-phone" name="phone" autocomplete="tel" required placeholder="+1 (555) 000-0000">
						<span class="form-error" data-field="phone" hidden></span>
					</div>
					<div class="form-field">
						<label class="form-label" for="aff-email"><?php esc_html_e( 'Email', 'showtime-pools' ); ?> <span class="required">*</span></label>
						<input class="form-input" type="email" id="aff-email" name="email" autocomplete="email" required placeholder="your@email.com">
						<span class="form-error" data-field="email" hidden></span>
					</div>
				</div>

				<div class="form-field">
					<label class="form-label" for="aff-website"><?php esc_html_e( 'Website / Social Media URL', 'showtime-pools' ); ?></label>
					<input class="form-input" type="url" id="aff-website" name="website" autocomplete="url" placeholder="https://yoursite.com or IG/FB">
					<span class="form-error" data-field="website" hidden></span>
				</div>

				<?php if ( $aff_promote_options ) : ?>
				<fieldset class="form-field affiliate-form__promote">
					<legend class="form-label"><?php esc_html_e( 'How will you refer pool owners?', 'showtime-pools' ); ?> <span class="required">*</span></legend>
					<div class="affiliate-form__checks">
						<?php foreach ( $aff_promote_options as $opt ) : ?>
							<label class="affiliate-form__check">
								<input type="checkbox" name="promote[]" value="<?php echo esc_attr( $opt ); ?>">
								<span><?php echo esc_html( $opt ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<span class="form-error" data-field="promote" hidden></span>
				</fieldset>
				<?php endif; ?>

				<label class="affiliate-form__consent">
					<input type="checkbox" name="consent" value="1" required>
					<span><?php echo esc_html( $aff_consent_text ); ?></span>
				</label>
				<span class="form-error" data-field="consent" hidden></span>

				<div class="cluster" style="align-items:center">
					<button type="submit" class="btn btn--primary btn--lg" data-default-label="<?php echo esc_attr( $aff_submit_label ); ?>">
						<?php echo esc_html( $aff_submit_label ); ?>
					</button>
				</div>

				<div class="affiliate-form__alert" data-status="success" hidden role="status"></div>
				<div class="affiliate-form__alert affiliate-form__alert--err" data-status="error" hidden role="alert"></div>
			</form>
		</div>
	</section>

</main>
<?php get_footer();

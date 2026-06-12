<?php
/**
 * Template Name: GHL Iframe Page
 *
 * Generic iframe page used by /quote/ and /book/. The page's
 * `_showtime_iframe_kind` post meta (set by the seeder) maps to one of two
 * wp_options keys for the GHL URL. When the URL isn't set yet, we render a
 * polished 3-step expectations layout + contact card so the page reads as
 * deliberate even pre-launch — never as broken.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$kind = (string) get_post_meta( get_the_ID(), '_showtime_iframe_kind', true );
if ( '' === $kind ) {
	$slug = get_post_field( 'post_name', get_the_ID() );
	$kind = in_array( $slug, array( 'quote', 'book' ), true ) ? $slug : 'quote';
}

$phone = apply_filters( 'showtime/business/phone', '(323) 825-2099' );
$tel   = preg_replace( '/[^0-9+]/', '', $phone );
// Verified mailbox from showtimepoolservice.com. Override via filter.
$email = (string) apply_filters( 'showtime/business/email', 'operations@showtimepoolmechanics.com' );

$config = array(
	'quote' => array(
		'option'  => 'showtime_ghl_quote_url',
		'eyebrow' => __( 'Free quote', 'showtime-pools' ),
		'title'   => __( 'Tell us about your project.', 'showtime-pools' ),
		'lead'    => __( 'A few quick questions and Steve will get back to you with an itemized quote inside 48 hours.', 'showtime-pools' ),
		'hero'    => 'lifestyle_main',
		'steps'   => array(
			array( 'n' => '01', 'title' => __( 'Tell us about the pool', 'showtime-pools' ),  'body' => __( 'Address, basic dimensions if you know them, and what you want done. Photos help but are not required.', 'showtime-pools' ) ),
			array( 'n' => '02', 'title' => __( 'Free site visit', 'showtime-pools' ),         'body' => __( 'Steve walks the property within 2 to 3 business days. We measure, photograph the equipment pad, and listen.', 'showtime-pools' ) ),
			array( 'n' => '03', 'title' => __( 'Itemized PDF inside 48 hours', 'showtime-pools' ), 'body' => __( 'Line-item pricing, materials, timeline, and warranty terms. No verbal estimates. No surprise upcharges.', 'showtime-pools' ) ),
		),
	),
	'book' => array(
		'option'  => 'showtime_ghl_book_url',
		'eyebrow' => __( 'Book an appointment', 'showtime-pools' ),
		'title'   => __( 'Pick a time that works.', 'showtime-pools' ),
		'lead'    => __( 'Repairs, weekly service, remodel quotes, or inspections: choose a time on the calendar and Steve confirms the appointment the same business day.', 'showtime-pools' ),
		'hero'    => 'inspections_bg',
		'steps'   => array(
			array( 'n' => '01', 'title' => __( 'Tell us what you need', 'showtime-pools' ),  'body' => __( 'A repair visit, weekly service, a remodel quote, or an inspection. Pick the appointment type that fits, we will confirm the details.', 'showtime-pools' ) ),
			array( 'n' => '02', 'title' => __( 'Lock a time on the calendar', 'showtime-pools' ),'body' => __( 'Most appointments happen within 3 business days. Same-day slots available for urgent repairs and active escrow timelines.', 'showtime-pools' ) ),
			array( 'n' => '03', 'title' => __( 'We show up prepared', 'showtime-pools' ),'body' => __( 'Confirmation and reminder included, and the right tech arrives with your notes in hand. Written follow-up after every visit.', 'showtime-pools' ) ),
		),
	),
);

$cfg     = $config[ $kind ] ?? $config['quote'];

// ── Native WP overrides (edit via WP Admin → Pages → Quote/Book → Update) ───
$pid = get_the_ID();
$_ov = static function ( string $key, string $fallback ) use ( $pid ): string {
	$v = (string) get_post_meta( $pid, $key, true );
	return '' !== $v ? $v : $fallback;
};
$cfg['eyebrow'] = $_ov( 'iframe_eyebrow', $cfg['eyebrow'] );
$cfg['title']   = $_ov( 'iframe_title',   $cfg['title'] );
$cfg['lead']    = $_ov( 'iframe_lead',    $cfg['lead'] );
foreach ( array( 0, 1, 2 ) as $si ) {
	$n = $si + 1;
	$cfg['steps'][ $si ]['title'] = $_ov( "iframe_step{$n}_title", $cfg['steps'][ $si ]['title'] ?? '' );
	$cfg['steps'][ $si ]['body']  = $_ov( "iframe_step{$n}_body",  $cfg['steps'][ $si ]['body']  ?? '' );
}

$src_url = (string) get_option( $cfg['option'], '' );
if ( '' === $src_url && 'book' === $kind && defined( 'SHOWTIME_BOOKING_URL' ) ) {
	// On-domain booking: /book/ hosts the bundled GHL booking widget by
	// default so every CTA lands here without an admin-entered URL. The
	// showtime_ghl_book_url option still overrides when set.
	$src_url = SHOWTIME_BOOKING_URL;
}
$has_url = ( '' !== $src_url );
$hero_img = function_exists( 'showtime_image' ) ? showtime_image( $cfg['hero'], 1920 ) : '';
?>
<main id="primary" class="site-main iframe-page">
	<section class="iframe-hero section section--brand iframe-hero--photo" data-reveal>
		<?php if ( $hero_img ) : ?>
			<img class="iframe-hero__photo" src="<?php echo esc_url( $hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="iframe-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs iframe-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
			</nav>
			<div class="iframe-hero__inner stack stack--md">
				<span class="eyebrow iframe-hero__eyebrow"><?php echo esc_html( $cfg['eyebrow'] ); ?></span>
				<h1 class="iframe-hero__title balance"><?php echo esc_html( $cfg['title'] ); ?></h1>
				<p class="iframe-hero__lead"><?php echo esc_html( $cfg['lead'] ); ?></p>
			</div>
		</div>
	</section>

	<?php if ( $has_url ) : ?>
		<section class="iframe-frame section" data-reveal>
			<div class="container">
				<div class="iframe-frame__wrap">
					<iframe
						src="<?php echo esc_url( $src_url ); ?>"
						class="iframe-frame__iframe"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						title="<?php echo esc_attr( get_the_title() ); ?>"
					></iframe>
				</div>
				<p class="iframe-frame__alt">
					<?php
					/* translators: %s: anchor link */
					printf(
						esc_html__( 'Form not loading? %s.', 'showtime-pools' ),
						'<a href="' . esc_url( $src_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open in a new tab', 'showtime-pools' ) . '</a>'
					);
					?>
				</p>
			</div>
		</section>
	<?php else : ?>

		<section class="iframe-steps section" data-reveal>
			<div class="container">
				<header class="int-section__head">
					<span class="eyebrow"><?php esc_html_e( 'How it works', 'showtime-pools' ); ?></span>
					<h2 class="balance">
						<?php
						echo $kind === 'book'
							? esc_html__( 'Three steps from "interested" to "report in hand."', 'showtime-pools' )
							: esc_html__( 'Three steps from "interested" to "itemized quote."', 'showtime-pools' );
						?>
					</h2>
				</header>
				<ol class="step-grid">
					<?php foreach ( $cfg['steps'] as $step ) : ?>
						<li class="step-grid__step">
							<span class="step-grid__num"><?php echo esc_html( $step['n'] ); ?></span>
							<h3><?php echo esc_html( $step['title'] ); ?></h3>
							<p><?php echo esc_html( $step['body'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>

		<section class="iframe-fallback section section--cream" data-reveal>
			<div class="container">
				<div class="iframe-fallback__card">
					<header class="int-section__head" style="text-align:center;margin-bottom:var(--sp-6)">
						<span class="eyebrow"><?php esc_html_e( 'Start now', 'showtime-pools' ); ?></span>
						<h2 class="balance">
							<?php
							echo $kind === 'book'
								? esc_html__( 'Three ways to lock a time.', 'showtime-pools' )
								: esc_html__( 'Three ways to start your quote.', 'showtime-pools' );
							?>
						</h2>
						<p class="int-section__lead">
							<?php
							echo $kind === 'book'
								? esc_html__( 'The online booking calendar is being prepared. Until it is live, the routes below all reach the same desk.', 'showtime-pools' )
								: esc_html__( 'The online quote intake is being prepared. Until it is live, the routes below all reach the same desk.', 'showtime-pools' );
							?>
						</p>
					</header>

					<div class="iframe-fallback__routes iframe-fallback__routes--<?php echo $email ? '3' : '2'; ?>">
						<a class="iframe-fallback__route" href="<?php echo esc_url( 'tel:' . $tel ); ?>">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
							<span class="iframe-fallback__route-label"><?php esc_html_e( 'Call', 'showtime-pools' ); ?></span>
							<span class="iframe-fallback__route-value"><?php echo esc_html( $phone ); ?></span>
							<small><?php esc_html_e( 'Mon-Sat 8a-5p · Steve usually answers', 'showtime-pools' ); ?></small>
						</a>

						<?php if ( '' !== $email ) : ?>
							<a class="iframe-fallback__route" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
								<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg>
								<span class="iframe-fallback__route-label"><?php esc_html_e( 'Email', 'showtime-pools' ); ?></span>
								<span class="iframe-fallback__route-value"><?php echo esc_html( $email ); ?></span>
								<small><?php esc_html_e( 'Reply within 1 business day', 'showtime-pools' ); ?></small>
							</a>
						<?php endif; ?>

						<a class="iframe-fallback__route" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
							<span class="iframe-fallback__route-label"><?php esc_html_e( 'Contact form', 'showtime-pools' ); ?></span>
							<span class="iframe-fallback__route-value"><?php esc_html_e( 'Send a message', 'showtime-pools' ); ?></span>
							<small><?php esc_html_e( 'Itemized reply within 1 business day', 'showtime-pools' ); ?></small>
						</a>
					</div>
				</div>
			</div>
		</section>

	<?php endif; ?>
</main>
<?php
get_footer();

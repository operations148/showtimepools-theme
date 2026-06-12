<?php
/**
 * Service hero — eyebrow, H1, summary, dual CTA, brand pattern.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx = $GLOBALS['showtime_service_ctx'] ?? array();
$title    = $ctx['title']   ?? get_the_title();
$summary  = $ctx['summary'] ?? '';
$slug     = (string) ( $ctx['slug'] ?? '' );

// SEO H1 takes precedence over the natural title — keyword-led wording
// for organic search. Nav, breadcrumb, footer, related cards keep the
// natural title via $ctx['title'].
$h1   = '' !== (string) ( $ctx['seo_h1'] ?? '' )    ? (string) $ctx['seo_h1']    : $title;
// SEO intro (keyword + geo + brand) wins the lead slot when present;
// otherwise the original short summary stays.
$lead = '' !== (string) ( $ctx['seo_intro'] ?? '' ) ? (string) $ctx['seo_intro'] : (string) $summary;

// Per-service hero photo from bundled set: /assets/img/service_<slug>.{webp,jpg}
$svc_hero_img = '';
if ( $slug && function_exists( 'showtime_image' ) ) {
	$svc_hero_img = showtime_image( 'service_' . $slug, 1920 );
}
?>
<section class="svc-hero section section--brand svc-hero--photo" data-reveal>
	<?php if ( $svc_hero_img ) : ?>
		<img class="svc-hero__photo" src="<?php echo esc_url( $svc_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
	<?php endif; ?>
	<div class="svc-hero__pattern" aria-hidden="true"></div>
	<div class="container">
		<nav class="breadcrumbs svc-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
			<span class="breadcrumbs__sep">/</span>
			<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'showtime-pools' ); ?></a>
			<span class="breadcrumbs__sep">/</span>
			<span aria-current="page"><?php echo esc_html( $title ); ?></span>
		</nav>

		<div class="svc-hero__inner stack stack--lg">
			<span class="eyebrow svc-hero__eyebrow"><?php esc_html_e( 'Showtime Pools service', 'showtime-pools' ); ?></span>
			<h1 class="svc-hero__title balance"><?php echo esc_html( $h1 ); ?></h1>
			<?php if ( $lead ) : ?>
				<p class="svc-hero__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<div class="cluster">
				<a class="btn btn--invert btn--lg" href="<?php echo esc_url( showtime_booking_url() ); ?>">
					<?php esc_html_e( 'Request a Quote', 'showtime-pools' ); ?>
				</a>
				<a class="btn btn--ghost btn--lg svc-hero__btn-ghost" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">
					<?php esc_html_e( 'See Recent Projects', 'showtime-pools' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

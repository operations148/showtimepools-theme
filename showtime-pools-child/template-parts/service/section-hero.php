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
?>
<section class="svc-hero section section--brand" data-reveal>
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
			<h1 class="svc-hero__title balance"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $summary ) : ?>
				<p class="svc-hero__lead"><?php echo esc_html( $summary ); ?></p>
			<?php endif; ?>
			<div class="cluster">
				<a class="btn btn--invert btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php esc_html_e( 'Request a Quote', 'showtime-pools' ); ?>
				</a>
				<a class="btn btn--ghost btn--lg svc-hero__btn-ghost" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">
					<?php esc_html_e( 'See Recent Projects', 'showtime-pools' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

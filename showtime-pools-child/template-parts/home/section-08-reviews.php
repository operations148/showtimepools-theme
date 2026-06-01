<?php
/**
 * Reviews — live Google Reviews widget (compact variant for the homepage).
 *
 * Pulled directly from the configured widget plugin (Trustindex by default).
 * The Reviews page (/reviews/) renders the same widget; this section is the
 * compact variant via the .reviews-widget--compact min-height constraint so
 * the homepage scrolls cleanly while still surfacing real customer voice.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="reviews section section--cream" data-reveal>
	<div class="container">
		<header class="reviews__header">
			<div>
				<span class="eyebrow"><em>08</em> &mdash; <?php esc_html_e( 'What Customers Say', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Reviews from our Google Business Profile.', 'showtime-pools' ); ?></h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>">
				<?php esc_html_e( 'See all reviews', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>

		<div class="reviews-widget reviews-widget--compact">
			<?php echo showtime_render_reviews_widget( 'compact' ); ?>
		</div>
	</div>
</section>

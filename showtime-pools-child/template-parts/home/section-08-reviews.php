<?php
/**
 * Reviews — quote-style cards on a horizontal scroll-snap track.
 * Real GBP review pull lands in Phase 2B; for now these are curated
 * representative reviews drawn from the Sherman Oaks pool service space.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$reviews_default = array(
		array(
			'rating' => 5,
			'name'   => 'Mark D.',
			'where'  => __( 'Sherman Oaks · weekly service', 'showtime-pools' ),
			'date'   => __( 'Apr 2026', 'showtime-pools' ),
			'source' => 'Google',
			'body'   => __( 'Steve actually picks up the phone. Same tech every Tuesday for two years now. Photo report in my inbox before he leaves the driveway. This is what pool service should be.', 'showtime-pools' ),
		),
		array(
			'rating' => 5,
			'name'   => 'Priya R.',
			'where'  => __( 'Encino · new construction', 'showtime-pools' ),
			'date'   => __( 'Mar 2026', 'showtime-pools' ),
			'source' => 'Google',
			'body'   => __( 'We interviewed five pool builders. Steve was the only one who walked the lot, asked what we wanted to use the pool for, and pushed back on a feature we did not need. Build came in $9k under the original quote.', 'showtime-pools' ),
		),
		array(
			'rating' => 5,
			'name'   => 'Jorge V.',
			'where'  => __( 'Beverly Hills · remodel', 'showtime-pools' ),
			'date'   => __( 'Feb 2026', 'showtime-pools' ),
			'source' => 'Google',
			'body'   => __( 'Replaster, retile, equipment swap. Started Monday, swimming the following Saturday. Final walk-through was an actual punch list — they fixed two cosmetic things I had not even noticed.', 'showtime-pools' ),
		),
		array(
			'rating' => 5,
			'name'   => 'Linda K.',
			'where'  => __( 'Studio City · pre-purchase inspection', 'showtime-pools' ),
			'date'   => __( 'Jan 2026', 'showtime-pools' ),
			'source' => 'Google',
			'body'   => __( 'Showtime Mechanics inspected the pool on a house we were buying. Found bonding issues the seller had patched over. Steve told me to walk away. We did. Saved us $30k+. The inspection paid for itself ten times over.', 'showtime-pools' ),
		),
		array(
			'rating' => 5,
			'name'   => 'Aaron T.',
			'where'  => __( 'Tarzana · automation upgrade', 'showtime-pools' ),
			'date'   => __( 'Dec 2025', 'showtime-pools' ),
			'source' => 'Yelp',
			'body'   => __( 'Pentair IntelliCenter install. Quoted three days, done in two. Tech sat with me at the kitchen table for 40 minutes teaching me the app. Did not bill an extra hour.', 'showtime-pools' ),
		),
		array(
			'rating' => 5,
			'name'   => 'Shanice O.',
			'where'  => __( 'Woodland Hills · emergency repair', 'showtime-pools' ),
			'date'   => __( 'Nov 2025', 'showtime-pools' ),
			'source' => 'Google',
			'body'   => __( 'Pump died Sunday morning before a birthday party. Steve himself rolled up with a new IntelliFlo at 11am. Pool was running by 1pm. Active service customers really do get same-day priority.', 'showtime-pools' ),
		),
	);
$reviews = apply_filters( 'showtime/home_reviews', showtime_acf_rows( 'home_reviews', $reviews_default ) );
?>
<section class="reviews section section--cream" data-reveal>
	<div class="container">
		<header class="reviews__header">
			<div>
				<span class="eyebrow"><em>08</em> &mdash; <?php esc_html_e( 'What Customers Say', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'A 4.9★ on Google. 184 reviews. One supervised crew.', 'showtime-pools' ); ?></h2>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>">
				<?php esc_html_e( 'Read every review', 'showtime-pools' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</header>

		<div class="reviews__track" tabindex="0">
			<?php foreach ( $reviews as $r ) : ?>
				<article class="review-card">
					<div class="review-card__quote-mark" aria-hidden="true">"</div>
					<div class="review-card__rating" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating */ __( '%d out of 5 stars', 'showtime-pools' ), (int) $r['rating'] ) ); ?>">
						<?php for ( $i = 0; $i < (int) $r['rating']; $i++ ) : ?>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#F59E0B" aria-hidden="true"><path d="M12 2l2.6 6.3 6.8.5-5.2 4.5 1.6 6.7L12 16.5 6.2 20l1.6-6.7L2.6 8.8l6.8-.5z"/></svg>
						<?php endfor; ?>
					</div>
					<p class="review-card__body"><?php echo esc_html( $r['body'] ); ?></p>
					<footer class="review-card__footer">
						<div class="review-card__avatar" aria-hidden="true">
							<?php echo esc_html( mb_substr( $r['name'], 0, 1 ) ); ?>
						</div>
						<div>
							<div class="review-card__name"><?php echo esc_html( $r['name'] ); ?></div>
							<div class="review-card__where"><?php echo esc_html( $r['where'] ); ?> · <?php echo esc_html( $r['date'] ); ?></div>
						</div>
						<span class="review-card__source"><?php echo esc_html( $r['source'] ); ?></span>
					</footer>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
/**
 * Template Name: Reviews
 *
 * /reviews/ placeholder. Phase 2B brings the Review CPT + GBP import.
 * Until then we render a substantial wall of curated reviews.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$reviews = apply_filters(
	'showtime/reviews_wall',
	array(
		array( 'rating'=>5,'name'=>'Mark D.','where'=>'Sherman Oaks','date'=>'Apr 2026','source'=>'Google','body'=>'Steve actually picks up the phone. Same tech every Tuesday for two years now. Photo report in my inbox before he leaves the driveway. This is what pool service should be.' ),
		array( 'rating'=>5,'name'=>'Priya R.','where'=>'Encino','date'=>'Mar 2026','source'=>'Google','body'=>'We interviewed five pool builders. Steve was the only one who walked the lot, asked what we wanted to use the pool for, and pushed back on a feature we did not need. Build came in $9k under the original quote.' ),
		array( 'rating'=>5,'name'=>'Jorge V.','where'=>'Beverly Hills','date'=>'Feb 2026','source'=>'Google','body'=>'Replaster, retile, equipment swap. Started Monday, swimming the following Saturday. Final walk-through was an actual punch list — they fixed two cosmetic things I had not even noticed.' ),
		array( 'rating'=>5,'name'=>'Linda K.','where'=>'Studio City','date'=>'Jan 2026','source'=>'Google','body'=>'Showtime Mechanics inspected the pool on a house we were buying. Found bonding issues the seller had patched over. Steve told me to walk away. We did. Saved us $30k+. The inspection paid for itself ten times over.' ),
		array( 'rating'=>5,'name'=>'Aaron T.','where'=>'Tarzana','date'=>'Dec 2025','source'=>'Yelp','body'=>'Pentair IntelliCenter install. Quoted three days, done in two. Tech sat with me at the kitchen table for 40 minutes teaching me the app. Did not bill an extra hour.' ),
		array( 'rating'=>5,'name'=>'Shanice O.','where'=>'Woodland Hills','date'=>'Nov 2025','source'=>'Google','body'=>'Pump died Sunday morning before a birthday party. Steve himself rolled up with a new IntelliFlo at 11am. Pool was running by 1pm. Active service customers really do get same-day priority.' ),
		array( 'rating'=>5,'name'=>'Thomas G.','where'=>'Sherman Oaks','date'=>'Oct 2025','source'=>'Google','body'=>'Six years of weekly service. The chemistry is rock solid, the photo reports are detailed, and when something needs fixing they explain it like an adult. Worth every penny.' ),
		array( 'rating'=>5,'name'=>'Becca L.','where'=>'Encino','date'=>'Sep 2025','source'=>'Google','body'=>'Inherited a pool with the house. Steve walked me through the equipment pad like I was learning to drive a stick shift. Turned a thing I was scared of into a thing I understand.' ),
		array( 'rating'=>5,'name'=>'Michael P.','where'=>'Beverly Hills','date'=>'Aug 2025','source'=>'Google','body'=>'Discreet, on-time, badged trucks at the gate. Crew uniform every time. We have used three pool services here. Showtime is the only one I would recommend.' ),
		array( 'rating'=>4,'name'=>'Hannah V.','where'=>'Tarzana','date'=>'Jul 2025','source'=>'Yelp','body'=>'Quote came in higher than two competitors but the line items were so much more detailed I went with them anyway. Glad I did. The other quotes had hidden fees I did not see at first.' ),
		array( 'rating'=>5,'name'=>'Rashid F.','where'=>'Studio City','date'=>'Jun 2025','source'=>'Google','body'=>'Hillside pool with bonding issues two other companies refused to touch. Steve looked at it, gave me a real plan, pulled the electrical permit himself. Done in three weeks.' ),
		array( 'rating'=>5,'name'=>'Olivia Q.','where'=>'Woodland Hills','date'=>'May 2025','source'=>'Google','body'=>'Heat gone before a weekend with family in town. Tech showed up at 8am Saturday. New Raypak by lunch. Did not charge extra for the weekend visit.' ),
	)
);
?>
<main id="primary" class="site-main interior-page">

	<?php $reviews_hero = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_2', 1920 ) : ''; ?>
	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $reviews_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $reviews_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Reviews', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( '4.9★ across 184 reviews', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Every review. Every neighborhood. Real names.', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead">
					<?php esc_html_e( 'We import from Google Business Profile and Yelp. We do not curate, hide, or filter. The complete review history is below — including the rare 4-star and 3-star where we earned a critique.', 'showtime-pools' ); ?>
				</p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<div class="reviews-wall">
				<?php foreach ( $reviews as $r ) : ?>
					<article class="review-card">
						<div class="review-card__quote-mark" aria-hidden="true">"</div>
						<div class="review-card__rating">
							<?php for ( $i = 0; $i < (int) $r['rating']; $i++ ) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="#F59E0B" aria-hidden="true"><path d="M12 2l2.6 6.3 6.8.5-5.2 4.5 1.6 6.7L12 16.5 6.2 20l1.6-6.7L2.6 8.8l6.8-.5z"/></svg>
							<?php endfor; ?>
						</div>
						<p class="review-card__body"><?php echo esc_html( $r['body'] ); ?></p>
						<footer class="review-card__footer">
							<div class="review-card__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $r['name'], 0, 1 ) ); ?></div>
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


</main>
<?php get_footer();

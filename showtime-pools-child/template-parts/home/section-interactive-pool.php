<?php
/**
 * Interactive pool visualizer — a real base pool photo that swaps to a real
 * per-feature composite photo when a feature button is clicked. Each
 * composite (assets/img/add_*.webp) is the exact same pool with that one
 * feature actually added — a real edited photo, not an icon overlay.
 *
 * One feature can be previewed at a time (clicking a second feature swaps
 * to its photo instead of stacking); clicking the active feature again
 * returns to the base photo. Day/Night is a real filter transition applied
 * to whichever photo is currently showing.
 *
 * All copy + the feature list are ACF-editable from Site Content → Page
 * Copy → Interactive Pool Preview (option scope), same pattern as every
 * other homepage section.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$opt = function_exists( 'get_field' ) ? 'option' : false;

$eyebrow        = $opt ? (string) get_field( 'interactive_pool_eyebrow', $opt )   : '';
$headline       = $opt ? (string) get_field( 'interactive_pool_headline', $opt )  : '';
$lead           = $opt ? (string) get_field( 'interactive_pool_lead', $opt )      : '';
$cta_label      = $opt ? (string) get_field( 'interactive_pool_cta_label', $opt ) : '';
$cta_url        = $opt ? (string) get_field( 'interactive_pool_cta_url', $opt )   : '';
$features_field = $opt ? get_field( 'interactive_pool_features', $opt ) : null;

$eyebrow   = '' !== $eyebrow   ? $eyebrow   : __( 'Design Preview', 'showtime-pools' );
$headline  = '' !== $headline  ? $headline  : __( 'See your pool before we build it.', 'showtime-pools' );
$lead      = '' !== $lead      ? $lead      : __( 'Tap a feature to see it added to a real pool. Preview in day or night lighting, then bring your picks to your design consultation.', 'showtime-pools' );
$cta_label = '' !== $cta_label ? $cta_label : __( 'Start My Pool Design', 'showtime-pools' );
$cta_url   = '' !== $cta_url   ? $cta_url   : home_url( '/services/custom-pool-design-construction/' );

// Each 'image' is a real composite photo of the same base pool with that
// feature actually added (not an icon overlay, not a generated render).
$features_default = array(
	array( 'icon' => 'spa',    'title' => __( 'Spa', 'showtime-pools' ),                 'note' => __( 'Add a raised spa', 'showtime-pools' ),               'image' => 'add_spa' ),
	array( 'icon' => 'rock',   'title' => __( 'Rock Waterfall', 'showtime-pools' ),       'note' => __( 'Add a rock waterfall', 'showtime-pools' ),           'image' => 'add_rock' ),
	array( 'icon' => 'bar',    'title' => __( 'Swim-Up Bar', 'showtime-pools' ),          'note' => __( 'Add in-pool bar stools', 'showtime-pools' ),         'image' => 'add_bar' ),
	array( 'icon' => 'diving', 'title' => __( 'Diving Board', 'showtime-pools' ),         'note' => __( 'Add a diving board', 'showtime-pools' ),             'image' => 'add_diving' ),
	array( 'icon' => 'fire',   'title' => __( 'Fire Features', 'showtime-pools' ),        'note' => __( 'Add a fire bowl or fire pit', 'showtime-pools' ),    'image' => 'add_fire' ),
	array( 'icon' => 'sheer',  'title' => __( 'Water Sheer Descents', 'showtime-pools' ), 'note' => __( 'Add a sheer descent water wall', 'showtime-pools' ), 'image' => 'add_water' ),
	array( 'icon' => 'jets',   'title' => __( 'In-Deck Jets', 'showtime-pools' ),         'note' => __( 'Add laminar deck jets', 'showtime-pools' ),          'image' => 'add_deckjets' ),
);
$features = is_array( $features_field ) && ! empty( $features_field ) ? $features_field : $features_default;

$feature_icons = array(
	'spa'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 15c1-1 1-2 0-3s-1-2 0-3"/><path d="M10 15c1-1 1-2 0-3s-1-2 0-3"/><path d="M15 15c1-1 1-2 0-3s-1-2 0-3"/><path d="M4 19h16"/><path d="M6 19a6 6 0 0 0 12 0"/></svg>',
	'rock'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 16 4-6 3 4 3-5 4 5 4-3"/><path d="M3 20h18"/></svg>',
	'bar'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16l-8 9-8-9Z"/><path d="M12 13v7"/><path d="M8 20h8"/></svg>',
	'diving' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 13h12"/><path d="M11 9v4"/><path d="M15 13a2.5 2.5 0 1 0 5 0"/><path d="M2 19c2 0 2-1.5 4-1.5S8 19 10 19s2-1.5 4-1.5 2 1.5 4 1.5 2-1.5 4-1.5"/></svg>',
	'fire'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2s6 5.5 6 11a6 6 0 1 1-12 0C6 8.5 9 6.5 9 6.5s-.5 3 1 3.5c.8.3 2-.5 2-2C12 6 10 4 12 2Z"/></svg>',
	'sheer'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 15c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M2 19c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/></svg>',
	'jets'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7" cy="18" r="1.5"/><circle cx="12" cy="12.5" r="2"/><circle cx="17" cy="18" r="1.5"/><path d="M12 10V4"/></svg>',
);

$base_photo = function_exists( 'showtime_image' ) ? showtime_image( 'interactive_pool', 1400 ) : '';
?>
<section class="interactive-pool" data-reveal>
	<div class="container">

		<header class="interactive-pool__header">
			<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<h2 class="interactive-pool__title balance"><?php echo esc_html( $headline ); ?></h2>
			<p class="interactive-pool__lead"><?php echo esc_html( $lead ); ?></p>
		</header>

		<div class="interactive-pool__grid js-interactive-pool" data-ip-base="<?php echo esc_url( $base_photo ); ?>">

			<div class="interactive-pool__media js-ip-media">
				<?php if ( $base_photo ) : ?>
					<img class="interactive-pool__img js-ip-img" src="<?php echo esc_url( $base_photo ); ?>" alt="<?php esc_attr_e( 'Pool design preview', 'showtime-pools' ); ?>" loading="lazy" decoding="async" width="1400" height="800">
				<?php endif; ?>
				<div class="interactive-pool__veil" aria-hidden="true"></div>

				<div class="interactive-pool__glow" aria-hidden="true">
					<span class="interactive-pool__glow-dot" style="top:44%;left:11%"></span>
					<span class="interactive-pool__glow-dot" style="top:14%;left:34%"></span>
					<span class="interactive-pool__glow-dot" style="top:9%;left:64%"></span>
					<span class="interactive-pool__glow-dot" style="top:38%;left:88%"></span>
					<span class="interactive-pool__glow-dot" style="top:68%;left:78%"></span>
					<span class="interactive-pool__glow-dot" style="top:66%;left:30%"></span>
				</div>

				<span class="interactive-pool__badge js-ip-badge" aria-live="polite"></span>
			</div>

			<div class="interactive-pool__panel">
				<div class="interactive-pool__toggle js-ip-toggle" role="group" aria-label="<?php esc_attr_e( 'Preview lighting', 'showtime-pools' ); ?>">
					<button type="button" class="interactive-pool__toggle-btn is-active" data-ip-mode="day" aria-pressed="true">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
						<?php esc_html_e( 'Day', 'showtime-pools' ); ?>
					</button>
					<button type="button" class="interactive-pool__toggle-btn" data-ip-mode="night" aria-pressed="false">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
						<?php esc_html_e( 'Night', 'showtime-pools' ); ?>
					</button>
				</div>

				<h3 class="interactive-pool__panel-title"><?php esc_html_e( 'Choose Your Features', 'showtime-pools' ); ?></h3>

				<ul class="interactive-pool__feature-list js-ip-features" role="list">
					<?php foreach ( $features as $f ) :
						$icon_key  = is_array( $f ) ? (string) ( $f['icon'] ?? '' )  : '';
						$title     = is_array( $f ) ? (string) ( $f['title'] ?? '' ) : '';
						$note      = is_array( $f ) ? (string) ( $f['note'] ?? '' )  : '';
						$image_key = is_array( $f ) ? (string) ( $f['image'] ?? '' ) : '';
						$image_url = ( '' !== $image_key && function_exists( 'showtime_image' ) ) ? showtime_image( $image_key, 1400 ) : '';
						if ( '' === $title || '' === $image_url ) { continue; }
					?>
						<li>
							<button
								type="button"
								class="interactive-pool__feature js-ip-feature"
								data-ip-feature-title="<?php echo esc_attr( $title ); ?>"
								data-ip-feature-image="<?php echo esc_url( $image_url ); ?>"
								aria-pressed="false"
							>
								<span class="interactive-pool__feature-icon" aria-hidden="true"><?php echo $feature_icons[ $icon_key ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG ?></span>
								<span class="interactive-pool__feature-text">
									<strong><?php echo esc_html( $title ); ?></strong>
									<?php if ( '' !== $note ) : ?><small><?php echo esc_html( $note ); ?></small><?php endif; ?>
								</span>
								<span class="interactive-pool__feature-check" aria-hidden="true">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
								</span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		</div>

		<div class="interactive-pool__cta-wrap">
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $cta_url ); ?>">
				<?php echo esc_html( $cta_label ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		</div>

	</div>
</section>

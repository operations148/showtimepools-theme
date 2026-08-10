<?php
/**
 * Additional project gallery — a grouped, two-per-page carousel.
 *
 * Shared and data-driven: it renders because a project's registry entry
 * configures `additional_gallery`, never because of who the project is. No
 * slug appears anywhere in this file, so enabling it for another project is a
 * registry edit and nothing else.
 *
 * Nested INSIDE the "Real project" comparison section, after the
 * Before / Work completed / Result facts and before that section's closing
 * service and area links. It is deliberately not its own <section> and adds no
 * background band of its own — it continues the comparison, and its heading is
 * an <h3> under the section's <h2>.
 *
 * Pending slots render NO <img> and no src: a non-image "Coming soon" card
 * carrying no invented alt text, caption, date, material or description. A slot
 * only becomes a real figure once the registry supplies both a bundled asset
 * and real alt text — see showtime_project_gallery(), which fails the whole
 * gallery closed on any malformed slot.
 *
 * Behaviour is provided by the shared assets/js/project-slider.js markup
 * contract (grouped pages, dots, arrows, keyboard, swipe, live region, no
 * autoplay, no clones). Without JavaScript the nav stays `hidden` and the CSS
 * leaves every page stacked as a readable grid, so all slots stay visible.
 *
 * Expects:
 *   $args['pages']   array<int,array<int,array>> from showtime_project_gallery_pages()
 *   $args['base_id'] string unique id stem for this instance
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$g_pages = isset( $args['pages'] ) && is_array( $args['pages'] ) ? $args['pages'] : array();
$g_base  = isset( $args['base_id'] ) ? sanitize_html_class( (string) $args['base_id'] ) : '';

if ( empty( $g_pages ) || '' === $g_base ) {
	return;
}

$g_total = count( $g_pages );

// The "more photos coming" note is only true while a slot is still pending.
// On a fully populated gallery it would be an unsupported claim about future
// work, so it is dropped rather than shown alongside six real photographs.
$g_pending = 0;
foreach ( $g_pages as $g_p ) {
	foreach ( $g_p as $g_s ) {
		if ( 'ready' !== ( $g_s['status'] ?? '' ) ) {
			$g_pending++;
		}
	}
}
?>
<div class="proj-gallery"
	id="<?php echo esc_attr( $g_base ); ?>"
	data-proj-slider
	data-proj-slider-label="<?php esc_attr_e( 'Gallery page', 'showtime-pools' ); ?>"
	role="group"
	aria-roledescription="carousel"
	aria-labelledby="<?php echo esc_attr( $g_base ); ?>-h">

	<header class="proj-gallery__head">
		<h3 class="proj-gallery__title" id="<?php echo esc_attr( $g_base ); ?>-h">
			<?php esc_html_e( 'More Project Highlights', 'showtime-pools' ); ?>
		</h3>
		<?php if ( $g_pending > 0 ) : ?>
			<p class="proj-gallery__note">
				<?php esc_html_e( 'Additional project photos will be added soon.', 'showtime-pools' ); ?>
			</p>
		<?php endif; ?>
	</header>

	<div class="proj-gallery__viewport" data-proj-slider-viewport>
		<div class="proj-gallery__track" data-proj-slider-track>
			<?php foreach ( $g_pages as $g_i => $g_page ) : ?>
				<div class="proj-gallery__page"
					role="group"
					aria-roledescription="slide"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: page number, 2: total pages */ __( 'Gallery page %1$d of %2$d', 'showtime-pools' ), $g_i + 1, $g_total ) ); ?>"
					data-proj-slider-slide="<?php echo esc_attr( (string) $g_i ); ?>">
					<ul class="proj-gallery__grid" role="list">
						<?php foreach ( $g_page as $g_slot ) : ?>
							<li class="proj-gallery__cell">
							<?php if ( 'ready' === $g_slot['status'] ) : ?>
								<figure class="proj-gallery__card">
									<div class="proj-gallery__frame">
										<img
											src="<?php echo esc_url( $g_slot['url'] ); ?>"
											width="<?php echo esc_attr( (string) $g_slot['width'] ); ?>"
											height="<?php echo esc_attr( (string) $g_slot['height'] ); ?>"
											alt="<?php echo esc_attr( $g_slot['alt'] ); ?>"
											loading="lazy"
											decoding="async">
									</div>
									<?php if ( '' !== $g_slot['caption'] ) : ?>
										<figcaption class="proj-gallery__caption"><?php echo esc_html( $g_slot['caption'] ); ?></figcaption>
									<?php endif; ?>
								</figure>
							<?php else : ?>
								<?php
								// Deliberately NOT a <figure>/<img>: there is no image to
								// describe, so emitting one would mean an empty src or an
								// invented alt string. Semantic text only.
								?>
								<div class="proj-gallery__card proj-gallery__card--pending">
									<div class="proj-gallery__frame">
										<span class="proj-gallery__badge"><?php esc_html_e( 'Coming soon', 'showtime-pools' ); ?></span>
										<span class="proj-gallery__icon" aria-hidden="true">
											<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L5 19"/></svg>
										</span>
										<p class="proj-gallery__pending-text"><?php esc_html_e( 'Project photo coming soon', 'showtime-pools' ); ?></p>
									</div>
								</div>
							<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( $g_total > 1 ) : ?>
		<div class="proj-gallery__nav" data-proj-slider-nav hidden>
			<button type="button" class="proj-gallery__arrow proj-gallery__arrow--prev" data-proj-slider-prev
				aria-label="<?php esc_attr_e( 'Previous gallery page', 'showtime-pools' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 19l-7-7 7-7"/></svg>
			</button>

			<div class="proj-gallery__dots" role="group" aria-label="<?php esc_attr_e( 'Choose a gallery page', 'showtime-pools' ); ?>">
				<?php for ( $g_d = 0; $g_d < $g_total; $g_d++ ) : ?>
					<button type="button" class="proj-gallery__dot<?php echo 0 === $g_d ? ' is-active' : ''; ?>"
						data-proj-slider-dot="<?php echo esc_attr( (string) $g_d ); ?>"
						<?php echo 0 === $g_d ? 'aria-current="true"' : ''; ?>
						aria-label="<?php echo esc_attr( sprintf( /* translators: 1: page number, 2: total pages */ __( 'View gallery page %1$d of %2$d', 'showtime-pools' ), $g_d + 1, $g_total ) ); ?>">
						<span class="proj-gallery__dot-hit" aria-hidden="true"></span>
					</button>
				<?php endfor; ?>
			</div>

			<button type="button" class="proj-gallery__arrow proj-gallery__arrow--next" data-proj-slider-next
				aria-label="<?php esc_attr_e( 'Next gallery page', 'showtime-pools' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
			</button>
		</div>

		<p class="visually-hidden" aria-live="polite" data-proj-slider-status></p>
	<?php endif; ?>
</div>

<?php
/**
 * "Add to Preferred Sources" callout — Google's official Reader Revenue /
 * publisher.js button, wrapped in a restrained Showtime Pools card.
 *
 * ONE template part, included by page-blog.php (blog archive) and single.php
 * (individual posts), so the markup exists in exactly one place and cannot
 * drift between the two surfaces. Each surface includes it once.
 *
 * The <div google-add-preferred-source-btn> element is Google's own contract:
 * publisher.js finds it by that attribute and renders the real button into it.
 * The div is left EMPTY on purpose — nothing here imitates, restyles or wraps
 * Google's rendered control. Only the surrounding card is ours.
 *
 * The script itself is enqueued in inc/enqueue.php (handle
 * `google-swg-publisher`), conditionally and asynchronously, never here — a
 * hard-coded <script> in this partial would double-load on any page that
 * rendered it twice.
 *
 * Without JavaScript the Google div stays empty, so a plain text link to
 * Google's own preferences page is rendered alongside it. It is deliberately
 * styled as a text link, not as a button: it must never be mistaken for the
 * real control.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

// Render only where publisher.js is actually enqueued. Same predicate the
// enqueue uses, so a button can never appear without its script.
if ( function_exists( 'showtime_is_preferred_source_surface' ) && ! showtime_is_preferred_source_surface() ) {
	return;
}
?>
<aside class="preferred-source" aria-labelledby="preferred-source-title">
	<div class="preferred-source__text">
		<h2 class="preferred-source__title" id="preferred-source-title"><?php esc_html_e( 'Helpful pool advice from Showtime Pools', 'showtime-pools' ); ?></h2>
		<p class="preferred-source__body"><?php esc_html_e( 'Add Showtime Pools as a preferred source on Google.', 'showtime-pools' ); ?></p>
	</div>

	<div class="preferred-source__action">
		<?php // Google renders its own button into this element. Do not style or fill it. ?>
		<div google-add-preferred-source-btn data-theme="light" data-lang="en"></div>

		<noscript>
			<a class="preferred-source__fallback" href="https://www.google.com/preferences/source?q=showtimepools.com" target="_blank" rel="noopener nofollow">
				<?php esc_html_e( 'Set your preferred sources on Google', 'showtime-pools' ); ?>
			</a>
		</noscript>
	</div>
</aside>

<?php
/**
 * Site footer — full takeover from Blocksy parent.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;
?>

<footer id="colophon" class="site-footer">
	<?php get_template_part( 'template-parts/footer/footer-cta' ); ?>
	<?php get_template_part( 'template-parts/footer/footer-main' ); ?>
	<?php get_template_part( 'template-parts/footer/footer-legal' ); ?>
</footer>

<?php
// Sitewide JSON-LD — canonical LocalBusiness node + branch offices.
// Lives outside the visible <footer> so Rank Math's WebSite/FAQPage blocks
// don't compete for the same scroll position. All values pull from
// Customizer + ACF Site Content (see template for source matrix).
get_template_part( 'template-parts/footer/local-business-schema' );
?>

<button type="button" class="back-to-top js-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'showtime-pools' ); ?>">
	<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<path d="M12 19V5M5 12l7-7 7 7"/>
	</svg>
</button>

<?php wp_footer(); ?>
</body>
</html>

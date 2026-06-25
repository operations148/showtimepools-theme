<?php
/**
 * Homepage quick-quote form band (after Reviews).
 *
 * A light form section — deliberately NOT styled like the dark footer booking
 * CTA. The GHL <iframe> is rendered with a data-src and lazy-loaded by
 * quote-form.js when it nears the viewport, so it never affects LCP. A
 * <noscript> fallback keeps the form usable without JS.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'showtime_quote_active' ) || ! showtime_quote_active() ) {
	return;
}

$cfg = showtime_quote_config();
?>
<section class="home-quote" aria-labelledby="home-quote-title">
	<div class="container home-quote__inner">

		<div class="home-quote__intro">
			<span class="eyebrow"><?php esc_html_e( 'Fast, free, no obligation', 'showtime-pools' ); ?></span>
			<h2 id="home-quote-title" class="home-quote__title balance"><?php echo esc_html( $cfg['heading'] ); ?></h2>
			<p class="home-quote__lead"><?php echo esc_html( $cfg['subtext'] ); ?></p>
		</div>

		<div class="home-quote__form">
			<div class="home-quote__embed"
				data-quote-embed
				data-src="<?php echo esc_url( $cfg['embed_url'] ); ?>"
				data-title="<?php esc_attr_e( 'Homepage quick-quote form', 'showtime-pools' ); ?>">
				<div class="home-quote__placeholder" aria-hidden="true">
					<span class="home-quote__spinner"></span>
				</div>
				<noscript>
					<iframe src="<?php echo esc_url( $cfg['embed_url'] ); ?>" style="width:100%;min-height:560px;border:0" title="<?php esc_attr_e( 'Homepage quick-quote form', 'showtime-pools' ); ?>"></iframe>
				</noscript>
			</div>
		</div>

	</div>
</section>

<?php
/**
 * Service FAQ — `<details>` accordion. Mirrors the home FAQ pattern.
 * Schema is emitted separately by `template-parts/service/schema.php`
 * to keep markup and JSON-LD in lockstep.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx  = $GLOBALS['showtime_service_ctx'] ?? array();
$faqs = (array) ( $ctx['faqs'] ?? array() );

if ( empty( $faqs ) ) {
	return;
}
?>
<section class="svc-faq section section--surface" data-reveal>
	<div class="container stack stack--lg" style="max-width:var(--container-narrow)">
		<header class="stack stack--sm">
			<span class="eyebrow"><?php esc_html_e( 'Common questions', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'What customers ask before signing the contract.', 'showtime-pools' ); ?></h2>
		</header>

		<div class="faq__list">
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<details class="faq__item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary class="faq__q">
						<span><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></span>
						<svg class="faq__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
						</svg>
					</summary>
					<div class="faq__a"><?php echo wp_kses_post( wpautop( (string) ( $faq['a'] ?? '' ) ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

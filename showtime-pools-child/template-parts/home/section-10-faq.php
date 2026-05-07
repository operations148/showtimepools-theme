<?php
/**
 * FAQ — 5 most common, with FAQPage JSON-LD schema for SERP rich results.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$faqs = apply_filters(
	'showtime/home_faqs',
	array(
		array(
			'q' => __( 'How much does weekly pool service cost?', 'showtime-pools' ),
			'a' => __( 'Standard residential weekly service starts at $185/month for chemistry, debris, and equipment check. Larger pools, salt systems, and pools with spas are quoted individually after a free site visit.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Do you sub out the work?', 'showtime-pools' ),
			'a' => __( 'No. Steve owns and operates Showtime Pools and personally supervises every job. The crew is W-2, not subcontracted. The same tech services your pool every week.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'How long does a remodel take?', 'showtime-pools' ),
			'a' => __( 'A standard replaster + retile + equipment refresh runs 2-4 weeks depending on scope and weather. New construction is 8-14 weeks. We commit to a written timeline before work starts.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Are you licensed and insured?', 'showtime-pools' ),
			'a' => __( 'Yes. CSLB License #985241, $2M liability insurance, full workers comp coverage. Documentation provided on request.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Do you serve outside Sherman Oaks?', 'showtime-pools' ),
			'a' => __( 'We service Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills on regular weekly routes. Outside that zone, we take occasional construction and remodel projects but not weekly maintenance.', 'showtime-pools' ),
		),
	)
);

$faq_schema = array(
	'@context' => 'https://schema.org',
	'@type'    => 'FAQPage',
	'mainEntity' => array_map(
		function ( $f ) {
			return array(
				'@type'          => 'Question',
				'name'           => $f['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $f['a'],
				),
			);
		},
		$faqs
	),
);
?>
<section class="faq section section--surface" data-reveal>
	<div class="container stack stack--lg" style="max-width:var(--container-narrow)">
		<header class="stack stack--sm">
			<span class="eyebrow"><em>10</em> &mdash; <?php esc_html_e( 'Common Questions', 'showtime-pools' ); ?></span>
			<h2 class="balance"><?php esc_html_e( 'What customers ask before signing up.', 'showtime-pools' ); ?></h2>
		</header>

		<div class="faq__list">
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<details class="faq__item"<?php echo $i === 0 ? ' open' : ''; ?>>
					<summary class="faq__q">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<svg class="faq__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
					</summary>
					<div class="faq__a"><?php echo wp_kses_post( wpautop( $faq['a'] ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

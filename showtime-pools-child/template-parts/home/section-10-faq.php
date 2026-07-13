<?php
/**
 * FAQ — most common questions, with FAQPage JSON-LD schema for SERP rich results.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$faqs_default = array(
		array(
			'q' => __( 'How much does weekly pool service cost?', 'showtime-pools' ),
			'a' => __( 'Standard residential weekly service starts at $159/month for chemistry, debris, and equipment check. Commercial pools start at $350/month. Larger pools, salt systems, and pools with spas are quoted individually after a free site visit.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Do you sub out the work?', 'showtime-pools' ),
			'a' => __( 'The crew is W-2 and in-house for everything except Replaster and demo, which are handled by partner crews we work with exclusively. Steve is on-site supervising 100% of the time, whether the work is in-house or partner. The same tech services your pool every week.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'How long does a remodel take?', 'showtime-pools' ),
			'a' => __( 'A standard replaster + retile + equipment refresh runs 2-4 weeks depending on scope and weather. New construction is 8-14 weeks. We commit to a written timeline before work starts.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Who actually shows up to my house?', 'showtime-pools' ),
			'a' => __( 'The same in-house W-2 tech every visit. Photo report after each visit. The person who quotes the job is on-site when the work happens. Steve supervises every job, including the exclusive partner crews we use for Replaster and demo.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Do you serve outside Sherman Oaks?', 'showtime-pools' ),
			'a' => __( 'We service Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills on regular weekly routes. Outside that zone, we take occasional construction and remodel projects but not weekly maintenance.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Are you licensed and insured?', 'showtime-pools' ),
			'a' => __( 'Yes. Showtime Pools is CSLB licensed and carries general liability and workers comp coverage. Certificates are provided to property owners directly on request as part of the contract process.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'Do you offer emergency or same-day pool service?', 'showtime-pools' ),
			'a' => __( 'Active service customers get same-day priority for equipment failures, active leaks, and safety issues. New customers can typically get a diagnostic visit within 24 to 48 hours, sooner if the situation is actively causing damage.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'What payment methods do you accept?', 'showtime-pools' ),
			'a' => __( 'Check, all major credit cards, and ACH bank transfer. Construction and remodel projects follow a milestone payment schedule laid out in your signed contract; weekly service bills monthly.', 'showtime-pools' ),
		),
		array(
			'q' => __( 'What if I am not happy with the work?', 'showtime-pools' ),
			'a' => __( 'Tell us and we fix it. Workmanship is warrantied for 2 years on construction and remodel work, and weekly service is month-to-month specifically so we have to earn the renewal every time, not lock you in.', 'showtime-pools' ),
		),
	);
$faqs = apply_filters( 'showtime/home_faqs', showtime_acf_rows( 'home_faqs', $faqs_default ) );

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
			<span class="eyebrow"><?php esc_html_e( 'Common Questions', 'showtime-pools' ); ?></span>
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

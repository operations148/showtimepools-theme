<?php
/**
 * Service + FAQPage JSON-LD. The `provider` references the LocalBusiness
 * `@id` emitted by `template-parts/footer/local-business-schema.php`,
 * giving us a clean entity graph for Rank Math + Google.
 *
 * FAQPage schema is gated on FAQs being present (Google guidelines update).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$ctx        = $GLOBALS['showtime_service_ctx'] ?? array();
$slug       = (string) ( $ctx['slug'] ?? '' );
$title      = (string) ( $ctx['title'] ?? get_the_title() );
$summary    = (string) ( $ctx['summary'] ?? '' );
$price      = (string) ( $ctx['price'] ?? '' );
$turnaround = (string) ( $ctx['turnaround'] ?? '' );
$faqs       = (array)  ( $ctx['faqs'] ?? array() );
$problems   = (array)  ( $ctx['problems'] ?? array() );

// Fold the visible "Common problems" Q&A into the same FAQPage entity set as
// the FAQ accordion. Both use the {q,a} shape and both render visible answers
// on the page, so both are legitimately eligible. We keep a SINGLE FAQPage
// block (not a second one) to avoid duplicate/competing schema, and dedupe by
// question text so a repeated question never lands twice. This makes the
// symptom-style problem questions ("why is my pump grinding?") machine-readable
// for AI answer engines without changing anything the visitor sees.
$faq_entities = array();
$seen_q       = array();
foreach ( array_merge( $faqs, $problems ) as $qa ) {
	$q = trim( (string) ( $qa['q'] ?? '' ) );
	$a = trim( (string) ( $qa['a'] ?? '' ) );
	if ( '' === $q || '' === $a ) {
		continue;
	}
	$key = strtolower( $q );
	if ( isset( $seen_q[ $key ] ) ) {
		continue;
	}
	$seen_q[ $key ] = true;
	$faq_entities[] = array( 'q' => $q, 'a' => $a );
}

$service_schema = array(
	'@context'      => 'https://schema.org',
	'@type'         => 'Service',
	'@id'           => trailingslashit( get_permalink() ) . '#service',
	'name'          => $title,
	'description'   => $summary,
	'serviceType'   => $title,
	'provider'      => array( '@id' => home_url( '/#organization' ) ),
	'areaServed'    => array(
		array( '@type' => 'City', 'name' => 'Sherman Oaks' ),
		array( '@type' => 'City', 'name' => 'Encino' ),
		array( '@type' => 'City', 'name' => 'Beverly Hills' ),
		array( '@type' => 'City', 'name' => 'Studio City' ),
		array( '@type' => 'City', 'name' => 'Tarzana' ),
		array( '@type' => 'City', 'name' => 'Woodland Hills' ),
	),
	'url'           => get_permalink(),
);

if ( '' !== $price ) {
	$service_schema['offers'] = array(
		'@type'         => 'Offer',
		'description'   => $price,
		'priceCurrency' => 'USD',
		'availability'  => 'https://schema.org/InStock',
		'url'           => get_permalink(),
	);
}

$service_schema = apply_filters( 'showtime/schema/service', $service_schema, $ctx );
?>
<script type="application/ld+json"><?php echo wp_json_encode( $service_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

<?php if ( ! empty( $faq_entities ) ) : ?>
	<?php
	$faq_schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'@id'        => trailingslashit( get_permalink() ) . '#faqs',
		'mainEntity' => array_values(
			array_map(
				static function ( $f ) {
					return array(
						'@type'          => 'Question',
						'name'           => (string) ( $f['q'] ?? '' ),
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => (string) ( $f['a'] ?? '' ),
						),
					);
				},
				$faq_entities
			)
		),
	);
	$faq_schema = apply_filters( 'showtime/schema/service_faq', $faq_schema, $ctx );
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endif; ?>

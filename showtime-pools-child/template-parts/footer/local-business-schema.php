<?php
/**
 * LocalBusiness JSON-LD schema. Sitewide. The canonical Organization +
 * LocalBusiness node Google's Knowledge Graph anchors to. Branches are
 * declared as additional nodes via `branchOf` so each location can also
 * earn its own local pack listing.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$home_id = home_url( '/#localbusiness' );

$main_office = array(
	'@type'           => 'PostalAddress',
	'streetAddress'   => '15301 Ventura Blvd.',
	'addressLocality' => 'Sherman Oaks',
	'addressRegion'   => 'CA',
	'postalCode'      => '91403',
	'addressCountry'  => 'US',
);

$schema = apply_filters(
	'showtime/schema/local_business',
	array(
		'@context'      => 'https://schema.org',
		'@type'         => array( 'LocalBusiness', 'PoolCleaningService', 'GeneralContractor' ),
		'@id'           => $home_id,
		'name'          => 'Showtime Pools',
		'alternateName' => 'Showtime Pool Service',
		'description'   => 'Stop juggling contractors. One team handles pool repairs, weekly service, remodels, equipment installation, inspections, and outdoor living across Los Angeles.',
		'slogan'        => 'Complete Pool Care, Start to Finish.',
		'url'           => home_url( '/' ),
		'logo'          => home_url( '/wp-content/themes/showtime-pools-child/assets/images/logo.png' ),
		'image'         => home_url( '/wp-content/themes/showtime-pools-child/assets/images/storefront.jpg' ),
		'telephone'     => '+1-323-825-2099',
		'email'         => 'operations@showtimepoolmechanics.com',
		'priceRange'    => '$$-$$$',
		'address'       => $main_office,
		'geo'           => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => 34.1511,
			'longitude' => -118.4490,
		),
		'areaServed'    => array(
			array( '@type' => 'City', 'name' => 'Sherman Oaks',     'containedInPlace' => array( '@type' => 'AdministrativeArea', 'name' => 'Los Angeles County' ) ),
			array( '@type' => 'City', 'name' => 'Encino' ),
			array( '@type' => 'City', 'name' => 'Beverly Hills' ),
			array( '@type' => 'City', 'name' => 'Studio City' ),
			array( '@type' => 'City', 'name' => 'Tarzana' ),
			array( '@type' => 'City', 'name' => 'Woodland Hills' ),
			array( '@type' => 'City', 'name' => 'Los Angeles' ),
		),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
				'opens'     => '08:00',
				'closes'    => '17:00',
			),
		),
		'sameAs'        => array(
			'https://facebook.com/share/18DZy64EfX/',
			'https://instagram.com/showtime_pools',
			'https://share.google/ltdNPoJBWevHTvrzq',
			'https://linkedin.com/in/showtimepoolssocal/',
			'https://tiktok.com/@showtimepools',
			'https://youtube.com/channel/UC3Dw1LtPvuX1JSGT7_KLntw',
		),
		'aggregateRating' => array(
			'@type'       => 'AggregateRating',
			'ratingValue' => '4.9',
			'reviewCount' => '184',
			'bestRating'  => '5',
			'worstRating' => '1',
		),
		'founder' => array(
			'@type' => 'Person',
			'@id'   => home_url( '/the-founder/#person' ),
			'name'  => 'Steve Adams',
			'jobTitle' => 'Founder & CEO',
		),
		'numberOfEmployees' => array(
			'@type' => 'QuantitativeValue',
			'value' => 12,
		),
	)
);

// Branch offices — separate LocalBusiness nodes that branchOf the main entity.
$branches = apply_filters(
	'showtime/schema/local_business_branches',
	array(
		array(
			'@context'  => 'https://schema.org',
			'@type'     => 'LocalBusiness',
			'@id'       => home_url( '/#branch-century-city' ),
			'branchOf'  => array( '@id' => $home_id ),
			'name'      => 'Showtime Pools — Century City',
			'telephone' => '+1-323-825-2099',
			'address'   => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '1925 Century Park East, Suite 1700',
				'addressLocality' => 'Los Angeles',
				'addressRegion'   => 'CA',
				'postalCode'      => '90067',
				'addressCountry'  => 'US',
			),
		),
		array(
			'@context'  => 'https://schema.org',
			'@type'     => 'LocalBusiness',
			'@id'       => home_url( '/#branch-beverly-hills' ),
			'branchOf'  => array( '@id' => $home_id ),
			'name'      => 'Showtime Pools — Beverly Hills',
			'telephone' => '+1-323-825-2099',
			'address'   => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '9461 Charleville Blvd. #1902',
				'addressLocality' => 'Beverly Hills',
				'addressRegion'   => 'CA',
				'postalCode'      => '90212',
				'addressCountry'  => 'US',
			),
		),
	)
);

?>
<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php foreach ( $branches as $branch ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $branch, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endforeach; ?>

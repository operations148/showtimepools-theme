<?php
/**
 * LocalBusiness JSON-LD schema — fully dynamic.
 *
 * Every value here is sourced from Customizer (Showtime Brand panel) or
 * the ACF Site Content options pages. PHP defaults below match the
 * site's hardcoded values so the schema renders sanely even if every
 * ACF field is blank and the Customizer is untouched.
 *
 * Sources:
 *   - name / description / telephone / email / socials  ← Customizer filters
 *   - address / hours / offices                          ← ACF Offices & hours
 *   - rating / employees / founder                       ← ACF Page Copy → Trust
 *   - areaServed                                         ← Showtime\Areas registry
 *
 * Outputs:
 *   - 1× canonical LocalBusiness node (the @id Google's KG anchors to)
 *   - N× branch LocalBusiness nodes, one per additional office
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$home_id = home_url( '/#organization' );
$opt     = function_exists( 'get_field' ) ? 'option' : false;

// ---------------------------------------------------------------------------
// Helpers — keep this template self-contained.
// ---------------------------------------------------------------------------

/**
 * Parse a city string like "Sherman Oaks, CA 91403" into addressLocality,
 * addressRegion, postalCode. Falls back to a single locality if the format
 * doesn't match.
 */
$parse_city = static function ( string $city ): array {
	$out = array( 'locality' => $city, 'region' => 'CA', 'postal' => '' );
	if ( preg_match( '/^(.+?),\s*([A-Z]{2})\s+(\d{5}(?:-\d{4})?)$/', $city, $m ) ) {
		$out = array(
			'locality' => trim( $m[1] ),
			'region'   => trim( $m[2] ),
			'postal'   => trim( $m[3] ),
		);
	}
	return $out;
};

/**
 * Parse "8:00 AM - 5:00 PM" or "8:00a - 5:00p" into ['08:00','17:00'].
 * Returns [null, null] if the row can't be parsed (e.g. "By appointment").
 */
$parse_hours = static function ( string $time ): array {
	$time = strtolower( str_replace( array( '—', '–' ), '-', $time ) );
	if ( ! preg_match( '/(\d{1,2})(?::(\d{2}))?\s*(am|a|pm|p)?\s*[-]\s*(\d{1,2})(?::(\d{2}))?\s*(am|a|pm|p)?/i', $time, $m ) ) {
		return array( null, null );
	}
	$to24 = static function ( $h, $min, $mer ) {
		$h = (int) $h;
		$mer = ( $mer === 'p' || $mer === 'pm' ) ? 'pm' : ( ( $mer === 'a' || $mer === 'am' ) ? 'am' : '' );
		if ( $mer === 'pm' && $h < 12 ) { $h += 12; }
		if ( $mer === 'am' && $h === 12 ) { $h = 0; }
		return sprintf( '%02d:%02d', $h, (int) $min );
	};
	return array( $to24( $m[1], $m[2], $m[3] ), $to24( $m[4], $m[5], $m[6] ) );
};

/**
 * Map a day label (Mon-Sat, Monday, Sun, etc.) to schema.org day URIs.
 */
$days_to_schema = static function ( string $day ): array {
	$day = strtolower( trim( $day ) );
	$alias = array(
		'monday' => 'Monday',     'mon' => 'Monday',
		'tuesday' => 'Tuesday',   'tue' => 'Tuesday',  'tues' => 'Tuesday',
		'wednesday' => 'Wednesday','wed' => 'Wednesday',
		'thursday' => 'Thursday', 'thu' => 'Thursday', 'thurs' => 'Thursday',
		'friday' => 'Friday',     'fri' => 'Friday',
		'saturday' => 'Saturday', 'sat' => 'Saturday',
		'sunday' => 'Sunday',     'sun' => 'Sunday',
	);
	if ( strpos( $day, '-' ) !== false ) {
		[ $a, $b ] = array_map( 'trim', explode( '-', $day, 2 ) );
		$order = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
		$ai = array_search( $alias[ $a ] ?? '', $order, true );
		$bi = array_search( $alias[ $b ] ?? '', $order, true );
		if ( false !== $ai && false !== $bi ) {
			return array_slice( $order, $ai, $bi - $ai + 1 );
		}
	}
	return isset( $alias[ $day ] ) ? array( $alias[ $day ] ) : array();
};

// ---------------------------------------------------------------------------
// Sourced values — all editable.
// ---------------------------------------------------------------------------

// Brand name is hardcoded, never blogname: the schema name must match GBP
// NAP exactly even when the WP site title carries an environment or
// sub-brand suffix. Override via the filter if the brand ever changes.
$biz_name        = (string) apply_filters( 'showtime/business/name',      'Showtime Pools' );
$biz_phone       = (string) apply_filters( 'showtime/business/phone',     '(323) 825-2099' );
$biz_email       = (string) apply_filters( 'showtime/business/email',     'operations@showtimepoolmechanics.com' );
$biz_description = (string) apply_filters( 'showtime/business/tagline',
	'Stop juggling contractors. One team handles pool repairs, weekly service, remodels, equipment installation, inspections, and outdoor living across Los Angeles.'
);

$tel_e164 = '+1' . preg_replace( '/[^0-9]/', '', preg_replace( '/^\+1/', '', $biz_phone ) );

$socials_raw = apply_filters( 'showtime/business/socials', array() );
$sameAs = array();
foreach ( (array) $socials_raw as $s ) {
	$url = is_array( $s ) ? (string) ( $s['url'] ?? '' ) : (string) $s;
	if ( '' !== $url ) { $sameAs[] = $url; }
}
if ( empty( $sameAs ) ) {
	$sameAs = array(
		'https://facebook.com/share/18DZy64EfX/',
		'https://instagram.com/showtime_pools',
		'https://share.google/ltdNPoJBWevHTvrzq',
		'https://linkedin.com/in/showtimepoolssocal/',
		'https://tiktok.com/@showtimepools',
		'https://youtube.com/channel/UC3Dw1LtPvuX1JSGT7_KLntw',
		'https://www.yelp.com/biz/showtime-pools-reseda',
	);
}

// Offices from ACF (fallback to legacy hardcoded 3-office set).
$offices_default = array(
	array( 'label' => 'Sherman Oaks (Main)', 'street' => '15301 Ventura Blvd.', 'city' => 'Sherman Oaks, CA 91403' ),
	array( 'label' => 'Century City',         'street' => '1925 Century Park East, Suite 1700', 'city' => 'Los Angeles, CA 90067' ),
	array( 'label' => 'Beverly Hills',        'street' => '9461 Charleville Blvd. #1902', 'city' => 'Beverly Hills, CA 90212' ),
);
$offices = function_exists( 'showtime_acf_rows' )
	? showtime_acf_rows( 'offices', $offices_default )
	: $offices_default;
$offices = apply_filters( 'showtime/business/offices', $offices );

$main_office = $offices[0] ?? $offices_default[0];
$main_parsed = $parse_city( (string) ( $main_office['city'] ?? '' ) );
$main_address = array(
	'@type'           => 'PostalAddress',
	'streetAddress'   => (string) ( $main_office['street'] ?? '' ),
	'addressLocality' => $main_parsed['locality'],
	'addressRegion'   => $main_parsed['region'],
	'postalCode'      => $main_parsed['postal'],
	'addressCountry'  => 'US',
);

// Hours from ACF.
$hours_rows = $opt ? get_field( 'hours_rows', $opt ) : null;
if ( empty( $hours_rows ) ) {
	$hours_rows = array(
		array( 'day' => 'Mon-Sat', 'time' => '8:00 AM - 5:00 PM' ),
	);
}
$openingHoursSpec = array();
foreach ( (array) $hours_rows as $row ) {
	$days = $days_to_schema( (string) ( $row['day'] ?? '' ) );
	[ $opens, $closes ] = $parse_hours( (string) ( $row['time'] ?? '' ) );
	if ( empty( $days ) || ! $opens || ! $closes ) { continue; } // skip "By appointment" rows
	$openingHoursSpec[] = array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => $days,
		'opens'     => $opens,
		'closes'    => $closes,
	);
}
if ( empty( $openingHoursSpec ) ) {
	// Safety net so the schema always emits a valid block.
	$openingHoursSpec[] = array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
		'opens'     => '08:00',
		'closes'    => '17:00',
	);
}

// areaServed from the canonical Areas registry. Falls back to hardcoded list.
$areaServed = array();
if ( class_exists( '\\Showtime\\Areas' ) ) {
	foreach ( \Showtime\Areas::all() as $area ) {
		$areaServed[] = array(
			'@type' => 'City',
			'name'  => (string) ( $area['name'] ?? '' ),
			'containedInPlace' => array(
				'@type' => 'AdministrativeArea',
				'name'  => 'Los Angeles County',
			),
		);
	}
}
if ( empty( $areaServed ) ) {
	foreach ( array( 'Sherman Oaks', 'Encino', 'Beverly Hills', 'Studio City', 'Tarzana', 'Woodland Hills' ) as $city ) {
		$areaServed[] = array( '@type' => 'City', 'name' => $city );
	}
}

// Trust / credentials (license number deliberately omitted sitewide).
// Aggregate rating intentionally NOT included in the schema — Google reads
// rating data from the linked GBP listing, so hardcoding it here in JSON-LD
// would risk search-engine misrepresentation when numbers drift from GBP.
$employees    = $opt ? (int) get_field( 'number_of_employees', $opt ) : 0;
$founder_name = $opt ? (string) get_field( 'founder_name', $opt ) : '';
$founder_title= $opt ? (string) get_field( 'founder_title', $opt ) : '';

$employees    = $employees > 0      ? $employees    : 12;
$founder_name = '' !== $founder_name ? $founder_name : 'Steve Adams';
$founder_title= '' !== $founder_title ? $founder_title : 'Founder & CEO';

// Logo + storefront image — bundled in theme; absolute URLs.
$logo_url = file_exists( SHOWTIME_CHILD_DIR . '/assets/img/logo.png' )
	? SHOWTIME_CHILD_URI . '/assets/img/logo.png'
	: home_url( '/wp-content/themes/showtime-pools-child/assets/img/logo.png' );

$image_url = file_exists( SHOWTIME_CHILD_DIR . '/assets/img/hero.jpg' )
	? SHOWTIME_CHILD_URI . '/assets/img/hero.jpg'
	: $logo_url;

// ---------------------------------------------------------------------------
// Canonical LocalBusiness node.
// ---------------------------------------------------------------------------

$schema = apply_filters(
	'showtime/schema/local_business',
	array(
		'@context'        => 'https://schema.org',
		'@type'           => array( 'HomeAndConstructionBusiness', 'GeneralContractor' ),
		'@id'             => $home_id,
		'name'            => $biz_name,
		'alternateName'   => 'Showtime Pool Service',
		'description'     => $biz_description,
		'slogan'          => 'Complete Pool Care, Start to Finish.',
		'url'             => home_url( '/' ),
		'logo'            => $logo_url,
		'image'           => $image_url,
		'telephone'       => $tel_e164,
		'email'           => $biz_email,
		'priceRange'      => '$$-$$$',
		'address'         => $main_address,
		'geo'             => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => 34.1511,
			'longitude' => -118.4490,
		),
		'areaServed'              => $areaServed,
		'openingHoursSpecification' => $openingHoursSpec,
		'sameAs'                  => $sameAs,
		'founder'                 => array(
			'@type'    => 'Person',
			'@id'      => home_url( '/the-founder/#person' ),
			'name'     => $founder_name,
			'jobTitle' => $founder_title,
		),
		'numberOfEmployees'       => array(
			'@type' => 'QuantitativeValue',
			'value' => $employees,
		),
	)
);

// Branch offices — separate LocalBusiness nodes, one per additional office.
$branches = array();
$slug_for = static function ( string $label ): string {
	return sanitize_title( $label );
};
foreach ( array_slice( $offices, 1 ) as $office ) {
	$label  = (string) ( $office['label']  ?? '' );
	$street = (string) ( $office['street'] ?? '' );
	$city   = (string) ( $office['city']   ?? '' );
	if ( '' === $street ) { continue; }
	$parsed = $parse_city( $city );
	$branches[] = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'LocalBusiness',
		'@id'       => home_url( '/#branch-' . $slug_for( $label ) ),
		'branchOf'  => array( '@id' => $home_id ),
		'name'      => $biz_name . ' — ' . $label,
		'telephone' => $tel_e164,
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street,
			'addressLocality' => $parsed['locality'],
			'addressRegion'   => $parsed['region'],
			'postalCode'      => $parsed['postal'],
			'addressCountry'  => 'US',
		),
	);
}
$branches = apply_filters( 'showtime/schema/local_business_branches', $branches );

?>
<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php foreach ( $branches as $branch ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $branch, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endforeach; ?>

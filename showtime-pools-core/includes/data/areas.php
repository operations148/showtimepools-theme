<?php
/**
 * Service-area registry — 6 neighborhoods, single source of truth.
 *
 * Drives the homepage area grid, the /service-areas/ hub, the 6 area landing
 * pages, the LocalBusiness `areaServed` schema, and footer cross-links.
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'        => 'sherman-oaks',
		'name'        => 'Sherman Oaks',
		'tag'         => 'Home base · 7 days a week',
		'pool_count'  => '420+',
		'gradient'    => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
		'lat'         => 34.1511,
		'lng'         => -118.4490,
		'zip_codes'   => array( '91403', '91423', '91436' ),
		'lead'        => 'Showtime Pools is headquartered on Ventura Boulevard. Sherman Oaks pools are our home base, our test bed, and the neighborhood Steve has been pulling permits in since 2003.',
		'characteristics' => array(
			'Mix of mid-century ranch pools (1950-1970) and 2000s-built rectangles',
			'High clay content soil — engineered structural builds matter here',
			'Hard water from LADWP — calcium scaling is the #1 chemistry issue',
			'Older equipment pads frequently undersized for modern variable-speed pumps',
		),
		'common_jobs' => array(
			'Replaster + retile remodels on 25-year-old pools',
			'Pentair IntelliCenter automation upgrades',
			'Salt cell installs (older pools converting from chlorine tablets)',
			'Heater replacements (pre-2008 Raypaks reaching end of life)',
		),
		'sample_streets' => array( 'Hayvenhurst Ave', 'Greenleaf St', 'Sunnyslope Ave', 'Magnolia Blvd', 'Riverside Dr' ),
	),

	array(
		'slug'        => 'encino',
		'name'        => 'Encino',
		'tag'         => '5 days a week',
		'pool_count'  => '310+',
		'gradient'    => 'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
		'lat'         => 34.1592,
		'lng'         => -118.5012,
		'zip_codes'   => array( '91316', '91436' ),
		'lead'        => 'Encino skews larger lots and larger pools. Custom shapes, raised spas, and water features are common. We service pools from Burbank Boulevard south through the Reseda/Encino flats.',
		'characteristics' => array(
			'Larger-than-average pool footprints (18×40 and up)',
			'Higher rate of attached spas and water features',
			'Frequent automation deployments — IntelliCenter, OmniLogic, AquaLink',
			'Tile-feature requests: glass mosaics, sheer descents, fire bowls',
		),
		'common_jobs' => array(
			'Full custom new construction with engineered structural drawings',
			'Spa add-ons and conversion projects',
			'Automation retrofits on early-2000s equipment pads',
			'PebbleTec finishes (Midnight Blue and Tropical Breeze are popular)',
		),
		'sample_streets' => array( 'Louise Ave', 'Petit Ave', 'White Oak Ave', 'Hayvenhurst Pl', 'Amestoy Ave' ),
	),

	array(
		'slug'        => 'beverly-hills',
		'name'        => 'Beverly Hills',
		'tag'         => '3 days a week',
		'pool_count'  => '180+',
		'gradient'    => 'linear-gradient(135deg,#0A0A0A 0%,#4D7589 100%)',
		'lat'         => 34.0736,
		'lng'         => -118.4004,
		'zip_codes'   => array( '90210', '90211', '90212' ),
		'lead'        => 'Beverly Hills work skews high-end remodels and discreet weekly service. We work behind 90210 gates with the same level of crew uniform, badged vehicles, and same-day reporting we run everywhere.',
		'characteristics' => array(
			'Tight access — 12-ft side gates and narrow driveways are common',
			'Privacy-first crew protocols (badged vehicles, scheduled gate access)',
			'Premium finish requests: Diamond Brite, AquaQuartz, custom mosaics',
			'Older 1960s-era pool structures often need partial gunite work',
		),
		'common_jobs' => array(
			'Discreet luxury remodels behind privacy gates',
			'Tile + coping refresh with high-end natural stone',
			'Equipment pad consolidations (multiple pumps → single VS)',
			'Smart-home integration (HomeKit, Alexa, Crestron)',
		),
		'sample_streets' => array( 'Roxbury Dr', 'Bedford Dr', 'Camden Dr', 'Beverly Glen Blvd', 'Sunset Blvd' ),
	),

	array(
		'slug'        => 'studio-city',
		'name'        => 'Studio City',
		'tag'         => '5 days a week',
		'pool_count'  => '270+',
		'gradient'    => 'linear-gradient(135deg,#3F6072 0%,#B0C5D2 100%)',
		'lat'         => 34.1394,
		'lng'         => -118.3870,
		'zip_codes'   => array( '91604' ),
		'lead'        => 'Studio City is hillside pools, pools-on-decks, and pools cantilevered over the canyon. We handle structural, weekly, and emergency work in the hillside lots from Coldwater to Laurel.',
		'characteristics' => array(
			'Hillside lots — pool decks often pier-supported',
			'Older pools in the hillside often need bonding-grid retrofits',
			'Heater chimney runs frequently inadequate for modern units',
			'Travertine coping is the dominant remodel finish',
		),
		'common_jobs' => array(
			'Hillside structural assessments before any rebuild',
			'Bonding grid retrofits and electrical re-permits',
			'Equipment pad relocations (canyon-side to street-side)',
			'Salt cells + automation in mid-century rebuilds',
		),
		'sample_streets' => array( 'Laurel Canyon Blvd', 'Coldwater Canyon Ave', 'Tujunga Ave', 'Klump Ave', 'Whitsett Ave' ),
	),

	array(
		'slug'        => 'tarzana',
		'name'        => 'Tarzana',
		'tag'         => '4 days a week',
		'pool_count'  => '230+',
		'gradient'    => 'linear-gradient(135deg,#1F1F1F 0%,#6E94A9 100%)',
		'lat'         => 34.1731,
		'lng'         => -118.5526,
		'zip_codes'   => array( '91335', '91356', '91357' ),
		'lead'        => 'Tarzana pools tend to be 1970s-1990s vintage with original equipment pads still running. We do a lot of "everything at once" remodels here — tile, plaster, equipment, automation in a single contract.',
		'characteristics' => array(
			'1970s and 1980s pool stock — original copings reaching end of life',
			'Original equipment pads with single-speed pumps still common',
			'Higher rate of "remodel-the-whole-thing-at-once" projects',
			'Diatomaceous earth filters being phased out for cartridge units',
		),
		'common_jobs' => array(
			'Combined replaster + retile + equipment swap (single contract)',
			'DE-to-cartridge filter conversions',
			'Variable-speed pump installs with LADWP rebate paperwork',
			'Automation upgrades on legacy equipment',
		),
		'sample_streets' => array( 'Reseda Blvd', 'Tampa Ave', 'Wilbur Ave', 'Vanalden Ave', 'Lindley Ave' ),
	),

	array(
		'slug'        => 'woodland-hills',
		'name'        => 'Woodland Hills',
		'tag'         => '4 days a week',
		'pool_count'  => '210+',
		'gradient'    => 'linear-gradient(135deg,#314A58 0%,#5C8A9E 100%)',
		'lat'         => 34.1683,
		'lng'         => -118.6059,
		'zip_codes'   => array( '91364', '91367' ),
		'lead'        => 'Woodland Hills runs hot — literally. Sun exposure and afternoon heat make heater runtime, salt-cell wear, and chemistry stability the dominant service themes here.',
		'characteristics' => array(
			'High sun exposure — heater wear faster than valley average',
			'Salt cells reach end of life 1-2 years sooner due to bather load',
			'Higher chemistry maintenance load in summer (CYA management critical)',
			'Premium new construction pockets in Calabasas-adjacent gated communities',
		),
		'common_jobs' => array(
			'Heater replacements (Raypak 406A and 336A both common)',
			'Salt cell replacements (Hayward TCELL15, Pentair IC40)',
			'CYA management programs and stabilizer reductions',
			'New gunite construction in newer hillside developments',
		),
		'sample_streets' => array( 'Ventura Blvd', 'Topanga Canyon Blvd', 'Mulholland Dr', 'Avenida Oriente', 'De Soto Ave' ),
	),

);

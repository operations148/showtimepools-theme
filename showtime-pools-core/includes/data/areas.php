<?php
/**
 * Service-area registry — 14 neighborhoods, single source of truth.
 *
 * Drives the homepage area grid, the /service-areas/ hub, the area landing
 * pages, the LocalBusiness `areaServed` schema, and footer cross-links.
 *
 * NOTE: each area also needs a WP page (slug = area slug, parent =
 * /service-areas/, template = page-area.php, meta _showtime_area_slug).
 * The registry alone does not create the route. Because that page is a
 * database record created after deploy, a registry entry is NOT proof the URL
 * resolves: every surface that advertises an area URL must first ask
 * showtime_area_page_is_published(), so a link is only ever published once its
 * page is live. See showtime-pools-child/inc/service-areas.php.
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'        => 'sherman-oaks',
		'seo_title'   => 'Pool Service in Sherman Oaks, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Sherman Oaks (91403, 91423, 91436) since 2003. Same tech weekly, repairs, remodels, equipment. Call (323) 825-2099.',
		'name'        => 'Sherman Oaks',
		'seo_h1'      => 'Pool Service in Sherman Oaks, Los Angeles',
		'seo_intro'   => 'Pool service near you in Sherman Oaks, Los Angeles. Showtime Pools is headquartered on Ventura Boulevard. Weekly pool cleaning service, pool repair near me calls, remodels, and equipment upgrades across ZIP codes 91403, 91423, and 91436, six days a week, with Sunday reserved for emergencies.',
		'tag'         => 'Home base · 6 days a week',
		'pool_count'  => '420+',
		'gradient'    => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
		'lat'         => 34.1511,
		'lng'         => -118.4490,
		'zip_codes'   => array( '91403', '91423', '91436' ),
		'lead'        => 'Showtime Pools is headquartered on Ventura Boulevard. Sherman Oaks pools are our home base, our test bed, and the neighborhood Steve has been pulling permits in since 2003.',
		'characteristics' => array(
			'Mix of mid-century ranch pools (1950-1970) and 2000s-built rectangles',
			'High clay content soil: engineered structural builds matter here',
			'Hard water from LADWP: calcium scaling is the #1 chemistry issue',
			'Older equipment pads frequently undersized for modern variable-speed pumps',
		),
		'common_jobs' => array(
			'Replaster + retile remodels on 25-year-old pools',
			'Pentair IntelliCenter automation upgrades',
			'Salt cell installs (older pools converting from chlorine tablets)',
			'Heater replacements (pre-2008 Raypaks reaching end of life)',
		),
		'sample_streets' => array( 'Hayvenhurst Ave', 'Greenleaf St', 'Sunnyslope Ave', 'Magnolia Blvd', 'Riverside Dr' ),
		'related_services' => array( 'pool-remodeling-resurfacing', 'equipment-installation-upgrades', 'weekly-pool-maintenance' ),
	),

	array(
		'slug'        => 'encino',
		'seo_title'   => 'Pool Service in Encino, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Encino since 2003. Weekly cleaning, repairs, remodels, and custom construction across 91316 and 91436. Call (323) 825-2099.',
		'name'        => 'Encino',
		'seo_h1'      => 'Pool Service in Encino, Los Angeles',
		'seo_intro'   => 'Pool service near you in Encino, Los Angeles. Showtime Pools is the pool company Encino homeowners call for pool cleaning near me, pool repair, and custom pool construction across ZIP codes 91316 and 91436, five days a week.',
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
			'Frequent automation deployments: IntelliCenter, OmniLogic, AquaLink',
			'Tile-feature requests: glass mosaics, sheer descents, fire bowls',
		),
		'common_jobs' => array(
			'Full custom new construction with engineered structural drawings',
			'Spa add-ons and conversion projects',
			'Automation retrofits on early-2000s equipment pads',
			'PebbleTec finishes (Midnight Blue and Tropical Breeze are popular)',
		),
		'sample_streets' => array( 'Louise Ave', 'Petit Ave', 'White Oak Ave', 'Hayvenhurst Pl', 'Amestoy Ave' ),
		'related_services' => array( 'custom-pool-design-construction', 'spa-installation-renovations', 'pool-remodeling-resurfacing' ),
	),

	array(
		'slug'        => 'beverly-hills',
		'seo_title'   => 'Pool Service in Beverly Hills, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Beverly Hills since 2003. Discreet weekly cleaning, repairs, remodels, and custom construction. Call (323) 825-2099.',
		'name'        => 'Beverly Hills',
		'seo_h1'      => 'Pool Service in Beverly Hills',
		'seo_intro'   => 'Pool service near you in Beverly Hills, Los Angeles. Showtime Pools runs discreet weekly pool cleaning service and pool maintenance near me calls behind 90210, 90211, and 90212 gates: badged vehicles, scheduled named-tech crew.',
		'tag'         => '3 days a week',
		'pool_count'  => '180+',
		'gradient'    => 'linear-gradient(135deg,#0A0A0A 0%,#4D7589 100%)',
		'lat'         => 34.0736,
		'lng'         => -118.4004,
		'zip_codes'   => array( '90210', '90211', '90212' ),
		'lead'        => 'Beverly Hills work skews high-end remodels and discreet weekly service. We work behind 90210 gates with the same level of crew uniform, badged vehicles, and same-day reporting we run everywhere.',
		'characteristics' => array(
			'Tight access: 12-ft side gates and narrow driveways are common',
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
		'related_services' => array( 'pool-remodeling-resurfacing', 'tile-coping-plaster-decking', 'smart-pool-automation' ),
	),

	array(
		'slug'        => 'studio-city',
		'seo_title'   => 'Pool Service in Studio City, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Studio City since 2003. Weekly cleaning, repair, remodel, and equipment by one supervised crew. Call (323) 825-2099.',
		'name'        => 'Studio City',
		'seo_h1'      => 'Pool Service in Studio City',
		'seo_intro'   => 'Pool service near you in Studio City, Los Angeles. Showtime Pools handles hillside pool repair near me calls, emergency pool service, and pier-supported deck work across ZIP 91604: structural, weekly, and same-day from Coldwater to Laurel Canyon.',
		'tag'         => '5 days a week',
		'pool_count'  => '270+',
		'gradient'    => 'linear-gradient(135deg,#3F6072 0%,#B0C5D2 100%)',
		'lat'         => 34.1394,
		'lng'         => -118.3870,
		'zip_codes'   => array( '91604' ),
		'lead'        => 'Studio City is hillside pools, pools-on-decks, and pools cantilevered over the canyon. We handle structural, weekly, and emergency work in the hillside lots from Coldwater to Laurel.',
		'characteristics' => array(
			'Hillside lots: pool decks often pier-supported',
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
		'related_services' => array( 'pool-repairs-plumbing', 'equipment-installation-upgrades', 'smart-pool-automation' ),
	),

	array(
		'slug'        => 'tarzana',
		'seo_title'   => 'Pool Service in Tarzana, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Tarzana since 2003. Weekly cleaning, repairs, remodels, and equipment upgrades. Call (323) 825-2099.',
		'name'        => 'Tarzana',
		'seo_h1'      => 'Pool Service in Tarzana',
		'seo_intro'   => 'Pool service near you in Tarzana, Los Angeles. Showtime Pools specializes in pool remodeling, pool resurfacing, pool cleaning near me, and pool maintenance near me on 1970s-1990s vintage pools across 91335, 91356, and 91357.',
		'tag'         => '4 days a week',
		'pool_count'  => '230+',
		'gradient'    => 'linear-gradient(135deg,#1F1F1F 0%,#6E94A9 100%)',
		'lat'         => 34.1731,
		'lng'         => -118.5526,
		'zip_codes'   => array( '91335', '91356', '91357' ),
		'lead'        => 'Tarzana pools tend to be 1970s-1990s vintage with original equipment pads still running. We do a lot of "everything at once" remodels here: tile, plaster, equipment, automation in a single contract.',
		'characteristics' => array(
			'1970s and 1980s pool stock: original copings reaching end of life',
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
		'related_services' => array( 'pool-remodeling-resurfacing', 'equipment-installation-upgrades', 'tile-coping-plaster-decking' ),
	),

	array(
		'slug'        => 'woodland-hills',
		'seo_title'   => 'Pool Service in Woodland Hills | Showtime Pools',
		'seo_meta'    => 'Pool service in Woodland Hills since 2003. Weekly cleaning, repair, remodel, equipment, and hard-water calcium fixes. Call (323) 825-2099.',
		'name'        => 'Woodland Hills',
		'seo_h1'      => 'Pool Service in Woodland Hills',
		'seo_intro'   => 'Pool service near you in Woodland Hills, Los Angeles. One of the top pool companies near me for Woodland Hills homeowners; Showtime Pools handles pool maintenance near me calls, new pool construction, and heater/salt cell work across 91364 and 91367.',
		'tag'         => '4 days a week',
		'pool_count'  => '210+',
		'gradient'    => 'linear-gradient(135deg,#314A58 0%,#5C8A9E 100%)',
		'lat'         => 34.1683,
		'lng'         => -118.6059,
		'zip_codes'   => array( '91364', '91367' ),
		'lead'        => 'Woodland Hills runs hot, literally. Sun exposure and afternoon heat make heater runtime, salt-cell wear, and chemistry stability the dominant service themes here.',
		'characteristics' => array(
			'High sun exposure: heater wear faster than valley average',
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
		'related_services' => array( 'equipment-installation-upgrades', 'weekly-pool-maintenance', 'custom-pool-design-construction' ),
	),

	array(
		'slug'        => 'west-hollywood',
		'seo_title'   => 'Pool Service in West Hollywood, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in West Hollywood (90046, 90048, 90069). Weekly cleaning, leak detection, repairs, and remodels. Call (323) 825-2099.',
		'name'        => 'West Hollywood',
		'seo_h1'      => 'Pool Service in West Hollywood',
		'seo_intro'   => 'Pool service in West Hollywood, from the Sunset Strip hills down to the Melrose flats. Showtime Pools handles weekly cleaning, leak detection, repairs, and remodels across 90046, 90048, and 90069, with the same named technician on every visit and a photo report before the truck leaves.',
		'tag'         => 'Now booking weekly routes',
		// Intentionally empty until Steve confirms a real serviced-pool count;
		// templates skip the count pill when this is blank.
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#23303B 0%,#7FA8BD 100%)',
		'lat'         => 34.0900,
		'lng'         => -118.3617,
		'zip_codes'   => array( '90046', '90048', '90069' ),
		'lead'        => 'West Hollywood pools run small and work hard: courtyard plunges, hillside spas, and mid-century rectangles on tight lots. Access, discretion, and tight scheduling matter more here than anywhere else we service.',
		'characteristics' => array(
			'Compact pools and plunge pools on tight lots: small volumes shift chemistry fast',
			'Hillside properties above Sunset with long plumbing runs to remote equipment',
			'Mature ficus and palm canopy: heavy debris load and faster filter clogging',
			'1920s-1960s housing stock: original underground plumbing prone to slow leaks',
		),
		'common_jobs' => array(
			'Leak detection on older underground plumbing lines',
			'Weekly service routes for compact and courtyard pools',
			'Heater and salt cell swaps on space-constrained equipment pads',
			'Plaster-to-pebble refinishes on mid-century rectangles',
		),
		'related_services' => array( 'weekly-pool-maintenance', 'pool-leak-detection' ),
	),

	array(
		'slug'        => 'bel-air',
		'seo_title'   => 'Pool Service in Bel Air, CA | Showtime Pools',
		'seo_meta'    => 'Estate pool service in Bel Air (90077). Weekly care, remodels, automation, and equipment for large hillside pools. Call (323) 825-2099.',
		'name'        => 'Bel Air',
		'seo_h1'      => 'Pool Service in Bel Air',
		'seo_intro'   => 'Pool service in Bel Air for estate properties off Stone Canyon and Bellagio Road. Showtime Pools maintains, remodels, and re-equips large hillside pools across 90077: infinity edges, attached spas, and full automation, serviced by a badged, owner-supervised crew with gate protocols handled.',
		'tag'         => 'Estate service · now booking',
		// Intentionally empty until Steve confirms a real serviced-pool count;
		// templates skip the count pill when this is blank.
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#101820 0%,#6E8F77 100%)',
		'lat'         => 34.0944,
		'lng'         => -118.4590,
		'zip_codes'   => array( '90077' ),
		'lead'        => 'Bel Air pools are estate-scale: long plumbing runs, vanishing edges, multiple bodies of water, and equipment rooms instead of pads. The work rewards engineering discipline and a crew that documents everything.',
		'characteristics' => array(
			'Estate-scale pools with attached spas and multiple water features',
			'Hillside builds: vanishing edges, catch basins, and long hydraulic runs',
			'Dedicated equipment rooms with multi-pump systems and full automation',
			'Heavy canyon landscaping: debris management drives filtration schedules',
		),
		'common_jobs' => array(
			'Estate remodels: finish, tile, coping, and deck coordinated in one scope',
			'IntelliCenter and Crestron-integrated automation builds',
			'Vanishing-edge and catch-basin diagnostics',
			'Multi-pump equipment room consolidations to variable-speed systems',
		),
		'related_services' => array( 'pool-remodeling-resurfacing', 'smart-pool-automation' ),
	),

	array(
		'slug'        => 'calabasas',
		'seo_title'   => 'Pool Service in Calabasas, CA | Showtime Pools',
		'seo_meta'    => 'Premium pool service in Calabasas (91301, 91302, 91372). Weekly cleaning, repairs, equipment upgrades, and full remodels for luxury pools. Call (323) 825-2099.',
		'name'        => 'Calabasas',
		'seo_h1'      => 'Pool Service in Calabasas',
		'seo_intro'   => 'Pool service in Calabasas for the gated, hillside, and estate properties off Mulholland and Las Virgenes. Showtime Pools runs established weekly routes through Calabasas from our Sherman Oaks office: premium maintenance, expert repairs, equipment upgrades, and full remodels on the larger, spa-integrated pools this community is known for, across 91301, 91302, and 91372.',
		'tag'         => 'Established routes · now booking',
		// Intentionally empty until Steve confirms a real serviced-pool count;
		// templates skip the count pill when this is blank.
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#1B2A22 0%,#6E8F77 100%)',
		'lat'         => 34.1378,
		'lng'         => -118.6602,
		'zip_codes'   => array( '91301', '91302', '91372' ),
		'lead'        => 'Calabasas pools run large and complex: custom shapes, integrated spas, water features, and full automation on gated and hillside lots. The work rewards a crew with deep equipment expertise and the discipline to document every visit.',
		'characteristics' => array(
			'Larger, high-end pools frequently integrated with spas and water features',
			'Automation the norm: variable-speed pumps, salt systems, and smart controls',
			'Gated and hillside estate lots with access and scheduling protocols',
			'Premium finishes and outdoor-living environments driving remodel scope',
		),
		'common_jobs' => array(
			'Premium weekly maintenance with full chemistry management',
			'Equipment upgrades to California 2025 code (VS pumps, salt, LED, automation)',
			'Full surface + finish remodels (plaster, pebble, tile, coping, decking)',
			'Diagnostics and repairs on integrated spa, water-feature, and automation systems',
		),
		'related_services' => array( 'pool-remodeling-resurfacing', 'smart-pool-automation' ),
	),

	// ── Project-backed areas ─────────────────────────────────────────────
	// Added alongside the five service-area pages. Their WordPress child page
	// under /service-areas/ is created after deploy, so every consumer that
	// advertises an area URL routes through showtime_area_page_is_published()
	// and omits (or falls back for) a record whose page is not live yet. See
	// showtime-pools-child/inc/service-areas.php.
	//
	// TRUTH RULES, same as the project registry. These five assert only what is
	// already verified elsewhere on the site:
	//   - `characteristics` lists the service categories Showtime Pools offers,
	//     drawn from the Services registry. It is NOT a claim about local soil,
	//     housing stock or water chemistry, none of which is documented for
	//     these cities.
	//   - `common_jobs` describes the city's OWN completed project, exactly as
	//     that record already states it. No other work is implied.
	//   - `pool_count` is blank, `sample_streets` is omitted, and no `tag`,
	//     intro or heading claims a weekly route, a schedule, a response time,
	//     a years-in-city figure, a landmark or a coverage area.
	//   - `lat`/`lng` are the city's public centroid, used only for the
	//     areaServed Place in the shared Service schema. No per-city
	//     LocalBusiness node or street address is created anywhere.
	//   - `related_project` powers the project-proof block and reuses that
	//     project's own cover image and approved alt text.

	array(
		'slug'        => 'van-nuys',
		'seo_title'   => 'Pool Service in Van Nuys, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Van Nuys: cleaning, repairs, equipment replacement, remodeling and construction. See a completed Van Nuys equipment project. Call (323) 825-2099.',
		'name'        => 'Van Nuys',
		'seo_h1'      => 'Pool Service in Van Nuys',
		'seo_intro'   => 'Showtime Pools works on pools in Van Nuys. Equipment replacement is the work we have documented here: a poolside vault refitted with a new cartridge filter and fresh PVC pipework. Cleaning, repairs, remodeling and construction are all available in the area, booked through the same office that has run Showtime Pools since 2003.',
		'tag'         => 'Service available',
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#1F2F3A 0%,#5C8A9E 100%)',
		'lat'         => 34.1867,
		'lng'         => -118.4489,
		'zip_codes'   => array( '91401', '91405', '91406', '91411' ),
		'lead'        => 'Showtime Pools works on pools in Van Nuys, from equipment and plumbing replacement through to cleaning, repairs and full remodels.',
		'what_common' => 'Pool services available in Van Nuys.',
		'characteristics' => array(
			'Weekly pool maintenance and cleaning',
			'Pool repairs and plumbing',
			'Equipment installation and upgrades',
			'Pool remodeling and resurfacing',
			'Tile, coping, plaster and decking',
			'Custom pool design and construction',
		),
		'what_do'     => 'What our Van Nuys project shows.',
		'common_jobs' => array(
			'A sunken poolside equipment vault holding an older filter tank, valve assembly and pipework',
			'The filter and its associated plumbing replaced inside the existing vault',
			'A replacement cartridge filter and new white PVC pipework fitted',
			'Photographed before and after the work — see the full project below',
		),
		// Hero photograph: this city's OWN project highlight, already committed
		// and already carrying this approved alt text on the project page. Set
		// because the area_<slug> image slot has no bundled file for these five,
		// and its stock fallback would put an unrelated photo under a locational
		// alt. Areas without the key keep the slot resolver untouched.
		'hero_image'  => 'assets/img/projects/galleries/van-nuys-pool-project/van-nuys-pool-project-highlight-04.webp',
		'hero_alt'    => 'Van Nuys pool project showing a rectangular pool and attached spa with a vacuum hose in the water beside a metal fence.',
		'related_project'  => 'van-nuys-pool-project',
		'related_services' => array( 'equipment-installation-upgrades', 'pool-repairs-plumbing', 'weekly-pool-maintenance' ),
	),

	array(
		'slug'        => 'north-hollywood',
		'seo_title'   => 'Pool Service in North Hollywood, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in North Hollywood: tile cleaning, weekly maintenance, repairs, equipment and remodels. See a completed North Hollywood tile project. Call (323) 825-2099.',
		'name'        => 'North Hollywood',
		'seo_h1'      => 'Pool Service in North Hollywood',
		'seo_intro'   => 'Showtime Pools works on pools in North Hollywood. Waterline tile cleaning is the work we have documented here: pale scale deposits lifted off a decorative blue tile band until the pattern read clearly again. Maintenance, repairs, equipment work and remodels are all available in the area.',
		'tag'         => 'Service available',
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#23303B 0%,#7FA8BD 100%)',
		'lat'         => 34.1870,
		'lng'         => -118.3813,
		'zip_codes'   => array( '91601', '91605', '91606' ),
		'lead'        => 'Showtime Pools works on pools in North Hollywood, from waterline tile cleaning through to weekly maintenance, repairs and remodels.',
		'what_common' => 'Pool services available in North Hollywood.',
		'characteristics' => array(
			'Pool tile cleaning',
			'Weekly pool maintenance and cleaning',
			'Pool repairs and plumbing',
			'Equipment installation and upgrades',
			'Pool remodeling and resurfacing',
			'Tile, coping, plaster and decking',
		),
		'what_do'     => 'What our North Hollywood project shows.',
		'common_jobs' => array(
			'Waterline tile covered with heavy pale scale deposits, hiding the blue pattern beneath',
			'The waterline tile cleaned along the pool edge',
			'The existing tile retained throughout — nothing was replaced',
			'Photographed before and after the work — see the full project below',
		),
		// Hero photograph: this city's OWN project highlight, already committed
		// and already carrying this approved alt text on the project page. Set
		// because the area_<slug> image slot has no bundled file for these five,
		// and its stock fallback would put an unrelated photo under a locational
		// alt. Areas without the key keep the slot resolver untouched.
		'hero_image'  => 'assets/img/projects/galleries/north-hollywood-pool-project/north-hollywood-pool-project-highlight-01.webp',
		'hero_alt'    => 'North Hollywood pool project showing water falling from a stone-faced raised spa into the pool below beside a hedged patio.',
		'related_project'  => 'north-hollywood-pool-project',
		'related_services' => array( 'pool-tile-cleaning', 'weekly-pool-maintenance', 'pool-repairs-plumbing' ),
	),

	array(
		'slug'        => 'toluca-lake',
		'seo_title'   => 'Pool Service in Toluca Lake, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Toluca Lake: water treatment, weekly maintenance, repairs, equipment and remodels. See a completed Toluca Lake water project. Call (323) 825-2099.',
		'name'        => 'Toluca Lake',
		'seo_h1'      => 'Pool Service in Toluca Lake',
		'seo_intro'   => 'Showtime Pools works on pools in Toluca Lake. Water treatment is the work we have documented here: a pool and attached spa taken from pale, clouded water to a clear deep blue with every surface left exactly as it was. Maintenance, repairs, equipment work and remodels are all available in the area.',
		'tag'         => 'Service available',
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#1B2A22 0%,#6E8F77 100%)',
		'lat'         => 34.1506,
		'lng'         => -118.3520,
		'zip_codes'   => array( '91602' ),
		'lead'        => 'Showtime Pools works on pools in Toluca Lake, from water treatment and weekly maintenance through to repairs, equipment work and remodels.',
		'what_common' => 'Pool services available in Toluca Lake.',
		'characteristics' => array(
			'Weekly pool maintenance and cleaning',
			'Pool repairs and plumbing',
			'Equipment installation and upgrades',
			'Pool remodeling and resurfacing',
			'Tile, coping, plaster and decking',
			'Spa installation and renovation',
		),
		'what_do'     => 'What our Toluca Lake project shows.',
		'common_jobs' => array(
			'An existing pool and attached spa holding pale turquoise water',
			'The pool water treated — no structural, finish, tile, coping or decking work',
			'The same pool afterwards with the water reading deep blue',
			'Photographed before and after the work — see the full project below',
		),
		// Hero photograph: this city's OWN project highlight, already committed
		// and already carrying this approved alt text on the project page. Set
		// because the area_<slug> image slot has no bundled file for these five,
		// and its stock fallback would put an unrelated photo under a locational
		// alt. Areas without the key keep the slot resolver untouched.
		'hero_image'  => 'assets/img/projects/galleries/toluca-lake-pool-project/toluca-lake-pool-project-highlight-02.webp',
		'hero_alt'    => 'Toluca Lake pool project showing a freeform pool with a rock waterfall feature surrounded by palms and flagstone paving.',
		'related_project'  => 'toluca-lake-pool-project',
		'related_services' => array( 'weekly-pool-maintenance', 'pool-repairs-plumbing', 'spa-installation-renovations' ),
	),

	array(
		'slug'        => 'burbank',
		'seo_title'   => 'Pool Service in Burbank, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Burbank: new pool and spa construction, remodeling, weekly maintenance, repairs and equipment. See a completed Burbank build. Call (323) 825-2099.',
		'name'        => 'Burbank',
		'seo_h1'      => 'Pool Service in Burbank',
		'seo_intro'   => 'Showtime Pools works on pools in Burbank. New construction is the work we have documented here: a backyard taken from excavation through to a finished, filled pool and attached spa with its surrounding deck. Remodeling, weekly maintenance, repairs and equipment work are all available in the area.',
		'tag'         => 'Service available',
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#314A58 0%,#88A4B6 100%)',
		'lat'         => 34.1808,
		'lng'         => -118.3090,
		'zip_codes'   => array( '91501', '91502', '91504', '91505', '91506' ),
		'lead'        => 'Showtime Pools works on pools in Burbank, from new pool and spa construction through to remodels, weekly maintenance and repairs.',
		'what_common' => 'Pool services available in Burbank.',
		'characteristics' => array(
			'Custom pool design and construction',
			'Spa installation and renovation',
			'Pool remodeling and resurfacing',
			'Tile, coping, plaster and decking',
			'Weekly pool maintenance and cleaning',
			'Pool repairs and plumbing',
		),
		'what_do'     => 'What our Burbank project shows.',
		'common_jobs' => array(
			'A backyard at excavation, before the pool and spa shell were formed',
			'A new pool and attached spa built out across the space',
			'The finished pool filled, with its surrounding deck complete',
			'Photographed from excavation through to completion — see the full project below',
		),
		// Hero photograph: this city's OWN project highlight, already committed
		// and already carrying this approved alt text on the project page. Set
		// because the area_<slug> image slot has no bundled file for these five,
		// and its stock fallback would put an unrelated photo under a locational
		// alt. Areas without the key keep the slot resolver untouched.
		'hero_image'  => 'assets/img/projects/galleries/burbank-pool-project/burbank-pool-project-highlight-05.webp',
		'hero_alt'    => 'Burbank pool project showing a filled pool with stone-faced columns and a raised spa during site work.',
		'related_project'  => 'burbank-pool-project',
		'related_services' => array( 'custom-pool-design-construction', 'spa-installation-renovations', 'pool-remodeling-resurfacing' ),
	),

	array(
		'slug'        => 'brentwood',
		'seo_title'   => 'Pool Service in Brentwood, CA | Showtime Pools',
		'seo_meta'    => 'Pool service in Brentwood, Los Angeles: pool cleaning, weekly maintenance, repairs, equipment and remodels. See a completed Brentwood cleaning project. Call (323) 825-2099.',
		'name'        => 'Brentwood',
		'seo_h1'      => 'Pool Service in Brentwood',
		'seo_intro'   => 'Showtime Pools works on pools in Brentwood. Pool cleaning is the work we have documented here: a naturalistic, stone-edged pool taken from dark green water to clear. Weekly maintenance, repairs, equipment work and remodels are all available in the area.',
		'tag'         => 'Service available',
		'pool_count'  => '',
		'gradient'    => 'linear-gradient(135deg,#101820 0%,#6E8F77 100%)',
		'lat'         => 34.0520,
		'lng'         => -118.4760,
		'zip_codes'   => array( '90049' ),
		'lead'        => 'Showtime Pools works on pools in Brentwood, from pool cleaning and weekly maintenance through to repairs, equipment work and remodels.',
		'what_common' => 'Pool services available in Brentwood.',
		'characteristics' => array(
			'Weekly pool maintenance and cleaning',
			'Pool repairs and plumbing',
			'Equipment installation and upgrades',
			'Pool remodeling and resurfacing',
			'Tile, coping, plaster and decking',
			'Pool inspections and diagnostics',
		),
		'what_do'     => 'What our Brentwood project shows.',
		'common_jobs' => array(
			'A naturalistic pool edged with stacked stone, its water dark green',
			'The pool cleaned and the water cleared',
			'Clear turquoise water between the stacked-stone edges afterwards',
			'Photographed before and after the work — see the full project below',
		),
		// Hero photograph: this city's OWN project highlight, already committed
		// and already carrying this approved alt text on the project page. Set
		// because the area_<slug> image slot has no bundled file for these five,
		// and its stock fallback would put an unrelated photo under a locational
		// alt. Areas without the key keep the slot resolver untouched.
		'hero_image'  => 'assets/img/projects/galleries/brentwood-pool-project/brentwood-pool-project-highlight-01.webp',
		'hero_alt'    => 'Brentwood pool project showing a pool with clear blue water and a floating vacuum hose, with a leaf net and pole resting on the brick deck beside a stacked-boulder rock feature.',
		'related_project'  => 'brentwood-pool-project',
		'related_services' => array( 'weekly-pool-maintenance', 'pool-repairs-plumbing', 'pool-inspections-diagnostics' ),
	),

);

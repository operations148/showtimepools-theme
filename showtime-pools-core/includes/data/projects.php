<?php
/**
 * Project registry — the single authoritative source for project content.
 *
 * ARCHITECTURE
 * ------------
 * Entries carrying `managed => true` are CODE-MANAGED: every frontend surface
 * (archive cards, single page, related cards, homepage strip, before/after
 * comparison, Open Graph, CreativeWork) reads them through
 * showtime_project_data(). WordPress post meta CANNOT override a managed
 * entry — the matching `project` post exists only as a routing shell for the
 * permalink, published status, sitemap inclusion and WP_Query compatibility.
 * Edit a project here, then deploy through GitHub.
 *
 * Unmanaged entries (no `managed` key) are legacy seed rows retained so the
 * one-time seeder keeps working. They are not rendered through the resolver.
 *
 * TRUTH RULES
 * -----------
 * Copy describes only what the project photographs actually show. Absent by
 * deliberate decision, pending owner documentation: manufacturer names, finish
 * product/colour names, exact tile materials and sizes, equipment models,
 * contract prices, real durations, completion months and testimonials.
 *
 * `investment` is a RESEARCHED MARKET RANGE, never a contract value. It is
 * displayed under the fixed label "Typical investment" and must never enter
 * JSON-LD (no Product, Offer, price, priceRange, Review or AggregateRating).
 *
 * Optional fields (`completion_date`, `client_quote`) are intentionally blank:
 * the renderer omits the entire item rather than falling back to a seeded value.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Data;

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'               => 'sherman-oaks-mid-century-remodel',
		'managed'            => true,
		'title'              => 'Pool Interior Finish Project in Sherman Oaks, CA',
		'excerpt'            => 'A rectangular pool and attached spa in Sherman Oaks received a new aggregate interior finish, with the surrounding deck and coping protected during the work.',
		'neighborhood'       => 'Sherman Oaks',
		'finish'             => 'Aggregate pool finish — manufacturer and color not specified',
		'scope'              => 'Pool and spa interior finish',
		'timeline'           => '1–3 weeks',
		'investment'         => '$14,000–$30,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'sherman-oaks-mid-century-remodel-after.webp',
		'hero_alt'           => 'A Sherman Oaks pool and attached spa filled after its new interior finish was completed.',
		'before_image'       => 'sherman-oaks-mid-century-remodel-before.webp',
		'before_alt'         => 'Crew applying a new aggregate finish to a drained Sherman Oaks pool with the surrounding deck protected.',
		'after_image'        => 'sherman-oaks-mid-century-remodel-after.webp',
		'after_alt'          => 'The same Sherman Oaks pool and attached spa filled after the new interior finish was completed.',
		'comparison_heading' => 'Before and After: Pool Interior Finish Project in Sherman Oaks, CA',
		'comparison_summary' => 'In Sherman Oaks, Showtime Pools applied a new aggregate interior finish to a rectangular pool and attached spa. The crew protected the surrounding deck and coping, applied the finish throughout the shell, and returned the pool to service after filling and startup.',
		'before_condition'   => 'Pool shell prepared and masked, with the new interior finish being applied.',
		'work_completed'     => 'New aggregate interior finish applied across the pool and attached spa shell.',
		'completed_result'   => 'Refinished pool and spa, filled and returned to service.',
		'service_url'        => '/services/pool-remodeling-resurfacing/',
		'area_url'           => '/service-areas/sherman-oaks/',
		'seo_title'          => 'Pool Interior Finish Project in Sherman Oaks, CA',
		'meta_description'   => 'See before and after photos of a Sherman Oaks pool and spa that received a new aggregate interior finish, with the existing deck and coping retained.',
		'og_image'           => 'sherman-oaks-mid-century-remodel-after.webp',
	),

	array(
		'slug'               => 'encino-estate-new-build',
		'managed'            => true,
		'title'              => 'Pool Interior Finish Renewal in Encino, CA',
		'excerpt'            => 'An existing freeform pool in Encino received a new dark aggregate interior finish, with the existing coping and surrounding concrete left in place.',
		'neighborhood'       => 'Encino',
		'finish'             => 'Aggregate pool finish — manufacturer and color not specified',
		'scope'              => 'Existing-pool interior finish renewal',
		'timeline'           => '1–3 weeks',
		'investment'         => '$14,000–$30,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'encino-estate-new-build-after.webp',
		'hero_alt'           => 'An Encino freeform pool refilled after its interior-finish renewal.',
		'before_image'       => 'encino-estate-new-build-before.webp',
		'before_alt'         => 'Existing Encino freeform pool drained after application of a new dark aggregate interior finish.',
		'after_image'        => 'encino-estate-new-build-after.webp',
		'after_alt'          => 'The same Encino pool refilled after its interior-finish renewal.',
		'comparison_heading' => 'Before and After: Pool Interior Finish Renewal in Encino, CA',
		'comparison_summary' => 'In Encino, Showtime Pools renewed the interior finish of an existing freeform pool. The shell received a new dark aggregate finish while the existing coping and surrounding concrete were retained. After filling and startup, the pool returned to service with a refreshed interior.',
		'before_condition'   => 'Existing freeform pool drained, with the new interior finish applied and awaiting fill.',
		'work_completed'     => 'New aggregate interior finish applied while the existing coping and surrounding concrete were retained.',
		'completed_result'   => 'Refilled pool with a renewed interior surface.',
		'service_url'        => '/services/pool-remodeling-resurfacing/',
		'area_url'           => '/service-areas/encino/',
		'seo_title'          => 'Pool Interior Finish Renewal in Encino, CA',
		'meta_description'   => 'Before and after photos of an existing Encino freeform pool refinished with a new dark aggregate interior, with its coping and decking retained.',
		'og_image'           => 'encino-estate-new-build-after.webp',
	),

	array(
		'slug'               => 'studio-city-modern-automation',
		'managed'            => true,
		'title'              => 'Pool Control Panel Replacement in Studio City, CA',
		'excerpt'            => 'A weathered poolside control enclosure in Studio City was replaced with a new panel housing refreshed breakers, relays and organized wiring.',
		'neighborhood'       => 'Studio City',
		'finish'             => 'Not applicable',
		'scope'              => 'Pool control panel and electrical enclosure replacement',
		'timeline'           => '1–3 days',
		'investment'         => '$3,000–$8,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'studio-city-modern-automation-after.webp',
		'hero_alt'           => 'A replacement Studio City pool-control enclosure with new breakers and organized wiring.',
		'before_image'       => 'studio-city-modern-automation-before.webp',
		'before_alt'         => 'Weathered Studio City pool-control enclosure before replacement.',
		'after_image'        => 'studio-city-modern-automation-after.webp',
		'after_alt'          => 'Replacement Studio City pool-control enclosure with new breakers, relays, and organized wiring.',
		'comparison_heading' => 'Before and After: Pool Control Panel Replacement in Studio City, CA',
		'comparison_summary' => 'In Studio City, Showtime Pools replaced a weathered pool-control enclosure mounted on an exterior wall. The older controller and its corroded housing were removed, and a new enclosure was installed with refreshed breakers, relays, and organized color-coded wiring. The existing conduit runs below the unit were left in place.',
		'before_condition'   => 'Weathered control enclosure with an aging controller and wiring.',
		'work_completed'     => 'Control enclosure, breakers, relays, and associated wiring replaced and organized.',
		'completed_result'   => 'New pool-control enclosure with clean, labeled, serviceable wiring.',
		'service_url'        => '/services/equipment-installation-upgrades/',
		'area_url'           => '/service-areas/studio-city/',
		'seo_title'          => 'Pool Control Panel Replacement in Studio City, CA',
		'meta_description'   => 'Before and after photos of a Studio City pool control enclosure replaced with a new panel, refreshed breakers, relays and organized wiring.',
		'og_image'           => 'studio-city-modern-automation-after.webp',
	),

	array(
		'slug'               => 'beverly-hills-luxe-spa-renovation',
		'managed'            => true,
		'title'              => 'Spa Tile Renovation in Beverly Hills, CA',
		'excerpt'            => 'An existing in-ground spa in Beverly Hills was finished in small blue-gray mosaic tile, with the adjoining deck area renewed to match.',
		'neighborhood'       => 'Beverly Hills',
		'finish'             => 'Small-format mosaic tile — manufacturer and material not specified',
		'scope'              => 'Spa tile renovation and surrounding-surface renewal',
		'timeline'           => '1–3 weeks',
		'investment'         => '$12,000–$25,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'beverly-hills-luxe-spa-renovation-after.webp',
		'hero_alt'           => 'A Beverly Hills in-ground spa finished in small blue-gray mosaic tile.',
		'before_image'       => 'beverly-hills-luxe-spa-renovation-before.webp',
		'before_alt'         => 'Existing Beverly Hills in-ground spa before its tile renovation.',
		'after_image'        => 'beverly-hills-luxe-spa-renovation-after.webp',
		'after_alt'          => 'The same Beverly Hills spa finished in small blue-gray mosaic tile with its surrounding surface renewed.',
		'comparison_heading' => 'Before and After: Spa Tile Renovation in Beverly Hills, CA',
		'comparison_summary' => 'In Beverly Hills, Showtime Pools renovated an existing in-ground spa set alongside a pool. The previous interior finish and surrounding surface were replaced with small blue-gray mosaic tile, and the adjoining deck area was renewed to coordinate with the completed spa. The neighboring pool was left in place.',
		'before_condition'   => 'Existing spa with its previous interior finish and surrounding deck.',
		'work_completed'     => 'Spa interior and surround finished in small-format mosaic tile, with the adjoining deck area renewed.',
		'completed_result'   => 'Completed tiled spa with a coordinated surrounding surface.',
		'service_url'        => '/services/spa-installation-renovations/',
		'area_url'           => '/service-areas/beverly-hills/',
		'seo_title'          => 'Spa Tile Renovation in Beverly Hills, CA',
		'meta_description'   => 'Before and after photos of a Beverly Hills in-ground spa finished in small blue-gray mosaic tile, with the adjoining deck surface renewed.',
		'og_image'           => 'beverly-hills-luxe-spa-renovation-after.webp',
	),

	array(
		'slug'               => 'tarzana-resort-style-finish',
		'managed'            => true,
		'title'              => 'Pool Resurfacing in Tarzana, CA',
		'excerpt'            => 'A Tarzana freeform pool with a flaking interior finish was resurfaced in a new pale aggregate, with the existing waterline band and timber deck retained.',
		'neighborhood'       => 'Tarzana',
		'finish'             => 'Aggregate pool finish — manufacturer and color not specified',
		'scope'              => 'Pool interior resurfacing',
		'timeline'           => '1–3 weeks',
		'investment'         => '$14,000–$30,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'tarzana-resort-style-finish-after.webp',
		'hero_alt'           => 'A Tarzana freeform pool filled after resurfacing, with its timber deck retained.',
		'before_image'       => 'tarzana-resort-style-finish-before.webp',
		'before_alt'         => 'Tarzana freeform pool with a deteriorated interior finish beside the existing timber deck.',
		'after_image'        => 'tarzana-resort-style-finish-after.webp',
		'after_alt'          => 'The same Tarzana pool filled after resurfacing, with the timber deck retained.',
		'comparison_heading' => 'Before and After: Pool Resurfacing in Tarzana, CA',
		'comparison_summary' => 'In Tarzana, Showtime Pools resurfaced a freeform pool whose interior finish had flaked away across the floor and walls. The worn surface was prepared and replaced with a new pale aggregate finish, then the pool was refilled. The existing waterline band and timber deck remained in place throughout.',
		'before_condition'   => 'Freeform pool with a deteriorated and delaminating interior finish beside the existing timber deck.',
		'work_completed'     => 'Worn interior surface prepared and replaced with a new aggregate finish.',
		'completed_result'   => 'Pool refilled with a renewed interior while retaining the existing waterline and deck.',
		'service_url'        => '/services/pool-remodeling-resurfacing/',
		'area_url'           => '/service-areas/tarzana/',
		'seo_title'          => 'Pool Resurfacing in Tarzana, CA',
		'meta_description'   => 'Before and after photos of a Tarzana freeform pool resurfaced in a new pale aggregate finish, with its waterline band and timber deck retained.',
		'og_image'           => 'tarzana-resort-style-finish-after.webp',
	),

	array(
		'slug'               => 'woodland-hills-tile-coping-refresh',
		'managed'            => true,
		'title'              => 'Spa Tile Renovation in Woodland Hills, CA',
		'excerpt'            => 'A disused round in-ground spa in Woodland Hills was tiled from floor to rim in blue mosaic, with the cracked concrete surround replaced by a wood-look tiled surface.',
		'neighborhood'       => 'Woodland Hills',
		'finish'             => 'Small-format mosaic tile — manufacturer and material not specified',
		'scope'              => 'Spa interior tiling and surrounding-surface replacement',
		'timeline'           => '1–3 weeks',
		'investment'         => '$10,000–$25,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'woodland-hills-tile-coping-refresh-after.webp',
		'hero_alt'           => 'A round Woodland Hills spa finished in blue mosaic tile with a renewed wood-look surround.',
		'before_image'       => 'woodland-hills-tile-coping-refresh-before.webp',
		'before_alt'         => 'Drained round spa in Woodland Hills with a deteriorated interior and cracked concrete surround.',
		'after_image'        => 'woodland-hills-tile-coping-refresh-after.webp',
		'after_alt'          => 'The same Woodland Hills spa finished in blue mosaic tile with a renewed wood-look surrounding surface.',
		'comparison_heading' => 'Before and After: Spa Tile Renovation in Woodland Hills, CA',
		'comparison_summary' => 'In Woodland Hills, Showtime Pools renovated a round in-ground spa that had been out of service and left full of fallen leaves. The deteriorated interior was replaced with small blue mosaic tile from floor to rim, and the cracked surrounding concrete was replaced with a wood-look tiled surface.',
		'before_condition'   => 'Round spa drained with a deteriorated interior and cracked surrounding concrete.',
		'work_completed'     => 'Spa interior tiled from floor to rim, with the surrounding surface replaced.',
		'completed_result'   => 'Completed blue-tiled spa with a renewed wood-look surrounding surface.',
		'service_url'        => '/services/spa-installation-renovations/',
		'area_url'           => '/service-areas/woodland-hills/',
		'seo_title'          => 'Spa Tile Renovation in Woodland Hills, CA',
		'meta_description'   => 'Before and after photos of a round Woodland Hills spa tiled floor to rim in blue mosaic, with the cracked concrete surround replaced by a tiled surface.',
		'og_image'           => 'woodland-hills-tile-coping-refresh-after.webp',
	),

	// ── Placeholder projects (code-managed, NOT yet verified) ────────────
	// `is_coming_soon => true` + `status => 'coming_soon'`. These render on the
	// /projects/ archive so the service-area footprint is visible, but they
	// claim NOTHING: no scope, finish, timeline, investment, date, testimonial
	// or photograph. Every unknown field is either the literal string
	// "Coming soon" (displayed under its normal fixed label) or blank (the
	// renderer omits the row/section entirely).
	//
	// They are deliberately NOT discoverable: noindex,follow, excluded from
	// both sitemaps, excluded from the homepage strip and related-project
	// cards, and excluded from CreativeWork schema — see
	// showtime_project_ids_hidden_from_discovery().
	//
	// Promotion to a verified project is a DATA-ONLY change: fill in the real
	// copy and images below, drop `is_coming_soon`/`status`, and the existing
	// consumers pick it up. No template edit is required.
	//
	// service_url / area_url stay blank on purpose. Three of these areas do
	// have registry pages (west-hollywood, bel-air, calabasas) but asserting a
	// service or area relationship for work that has not been verified would be
	// a claim, and the comparison block that renders those links is hidden for
	// placeholders anyway.

	array(
		'slug'               => 'van-nuys-pool-project',
		'managed'            => true,
		'title'              => 'Pool Filter and Equipment Replacement in Van Nuys, CA',
		'excerpt'            => 'A Van Nuys poolside equipment vault had its filter and associated plumbing replaced, with a new cartridge filter and PVC pipework fitted in place of the older assembly.',
		'neighborhood'       => 'Van Nuys',
		'finish'             => 'Not applicable — equipment replacement',
		'scope'              => 'Pool filter and equipment plumbing replacement',
		'timeline'           => '1–2 days',
		'investment'         => '$1,200–$3,500',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'van-nuys-pool-project-after.webp',
		'hero_alt'           => 'A Van Nuys pool equipment vault with a replacement cartridge filter and new plumbing.',
		'before_image'       => 'van-nuys-pool-project-before.webp',
		'before_alt'         => 'A Van Nuys poolside equipment vault holding an older filter tank, valve assembly and pipework, with leaf debris on the floor.',
		'after_image'        => 'van-nuys-pool-project-after.webp',
		'after_alt'          => 'The same Van Nuys equipment vault after a replacement cartridge filter and new white PVC plumbing were fitted.',
		'comparison_heading' => 'Before and After: Pool Filter and Equipment Work in Van Nuys, CA',
		'comparison_summary' => 'In Van Nuys, Showtime Pools replaced the filter and associated plumbing in a sunken poolside equipment vault. The photographs show the vault before the work, with an older filter tank and valve assembly, and afterwards with a replacement cartridge filter and new PVC pipework in place.',
		'before_condition'   => 'Sunken equipment vault with an older filter tank, valve assembly and pipework.',
		'work_completed'     => 'Filter and associated plumbing replaced within the existing vault.',
		'completed_result'   => 'Replacement cartridge filter and new PVC pipework installed.',
		'service_url'        => '/services/equipment-installation-upgrades/',
		'area_url'           => '',
		'seo_title'          => 'Pool Filter and Equipment Replacement in Van Nuys, CA',
		'meta_description'   => 'Before and after photos of a Van Nuys pool equipment vault refitted with a replacement cartridge filter and new PVC plumbing.',
		'og_image'           => 'van-nuys-pool-project-after.webp',
	),

	array(
		'slug'               => 'north-hollywood-pool-project',
		'managed'            => true,
		'title'              => 'Pool Tile Cleaning in North Hollywood, CA',
		'excerpt'            => 'The waterline tile of a North Hollywood pool was cleaned, removing the pale scale deposits that had covered the decorative blue tile.',
		'neighborhood'       => 'North Hollywood',
		'finish'             => 'Existing waterline tile retained and cleaned',
		'scope'              => 'Waterline tile cleaning',
		'timeline'           => '1 day',
		'investment'         => '$400–$900',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'north-hollywood-pool-project-after.webp',
		'hero_alt'           => 'A cleaned North Hollywood pool waterline with its blue tile pattern visible.',
		'before_image'       => 'north-hollywood-pool-project-before.webp',
		'before_alt'         => 'Pool waterline tile in North Hollywood with heavy pale scale deposits obscuring the blue tile pattern.',
		'after_image'        => 'north-hollywood-pool-project-after.webp',
		'after_alt'          => 'The same North Hollywood waterline tile after cleaning, with the blue pattern visible again.',
		'comparison_heading' => 'Before and After: Pool Tile Work in North Hollywood, CA',
		'comparison_summary' => 'In North Hollywood, Showtime Pools cleaned the waterline tile of a swimming pool. The photographs show the tile before the work, with pale scale deposits covering the decorative blue tiles, and afterwards with the tile surface and pattern visible again.',
		'before_condition'   => 'Waterline tile covered with pale scale deposits.',
		'work_completed'     => 'Waterline tile cleaned along the pool edge.',
		'completed_result'   => 'Waterline tile with the blue pattern visible again.',
		'service_url'        => '/services/pool-tile-cleaning/',
		'area_url'           => '',
		'seo_title'          => 'Pool Tile Cleaning in North Hollywood, CA',
		'meta_description'   => 'Before and after photos of a North Hollywood pool waterline cleaned of pale scale deposits, with the original blue tile pattern visible again.',
		'og_image'           => 'north-hollywood-pool-project-after.webp',
	),

	array(
		'slug'               => 'toluca-lake-pool-project',
		'managed'            => true,
		'title'              => 'Pool Water Clarity Treatment in Toluca Lake, CA',
		'excerpt'            => 'A Toluca Lake pool received water treatment that changed its water appearance. The pool structure, interior finish, coping, tile and surrounding deck were all left exactly as they were.',
		'neighborhood'       => 'Toluca Lake',
		'finish'             => 'Existing pool finish retained — water treatment only',
		'scope'              => 'Pool water treatment and water appearance',
		'timeline'           => '1–2 weeks',
		'investment'         => '$300–$900',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'toluca-lake-pool-project-after.webp',
		'hero_alt'           => 'A Toluca Lake pool and attached spa with deep blue water after treatment, the existing deck and planting unchanged.',
		'before_image'       => 'toluca-lake-pool-project-before.webp',
		'before_alt'         => 'A Toluca Lake pool and attached spa with pale turquoise water before treatment.',
		'after_image'        => 'toluca-lake-pool-project-after.webp',
		'after_alt'          => 'The same Toluca Lake pool after water treatment, the water reading deep blue with the surroundings unchanged.',
		'comparison_heading' => 'Before and After: Pool Water Treatment in Toluca Lake, CA',
		'comparison_summary' => 'In Toluca Lake, Showtime Pools treated the water of an existing pool and attached spa. The two photographs show the same pool before and after treatment, with the water reading pale turquoise in the first and deep blue in the second. The pool structure, interior finish, coping, waterline tile, decking and planting are unchanged between the two images: only the water was treated.',
		'before_condition'   => 'Existing pool and attached spa with pale turquoise water.',
		'work_completed'     => 'Pool water treated. No structural, finish, tile, coping or decking work was carried out.',
		'completed_result'   => 'The same pool with deep blue water and all surrounding surfaces unchanged.',
		'service_url'        => '/services/weekly-pool-maintenance/',
		'area_url'           => '',
		'seo_title'          => 'Pool Water Clarity Treatment in Toluca Lake, CA',
		'meta_description'   => 'Before and after photos of a Toluca Lake pool after water treatment changed the water appearance, with the existing finish and deck retained.',
		'og_image'           => 'toluca-lake-pool-project-after.webp',
	),

	array(
		'slug'               => 'burbank-pool-project',
		'managed'            => true,
		'title'              => 'Pool and Spa Construction in Burbank, CA',
		'excerpt'            => 'A Burbank backyard was built out with a new pool and attached spa, photographed from excavation through to the finished, filled pool and surrounding deck.',
		'neighborhood'       => 'Burbank',
		'finish'             => 'Aggregate pool interior — manufacturer and color not specified',
		'scope'              => 'Pool and attached spa construction with surrounding deck',
		'timeline'           => '6–10 weeks',
		'investment'         => '$85,000–$150,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'burbank-pool-project-after.webp',
		'hero_alt'           => 'A completed Burbank pool and attached spa beside a finished concrete deck.',
		'before_image'       => 'burbank-pool-project-before.webp',
		'before_alt'         => 'A Burbank backyard during pool construction, with the excavated shell, form boards and coping stones being set.',
		'after_image'        => 'burbank-pool-project-after.webp',
		'after_alt'          => 'The same Burbank backyard with the completed pool and attached spa filled, beside a finished concrete deck.',
		'comparison_heading' => 'Before and After: Pool and Spa Work in Burbank, CA',
		'comparison_summary' => 'In Burbank, Showtime Pools built a pool with an attached spa. The photographs show the same backyard during construction, with the shell excavated and coping being set, and afterwards with the pool and spa filled and the surrounding deck finished.',
		'before_condition'   => 'Excavated pool shell with form boards and coping being set.',
		'work_completed'     => 'Pool and attached spa built out, with the surrounding deck finished.',
		'completed_result'   => 'Completed pool and spa, filled, beside a finished deck.',
		'service_url'        => '/services/custom-pool-design-construction/',
		'area_url'           => '',
		'seo_title'          => 'Pool and Spa Construction in Burbank, CA',
		'meta_description'   => 'Before and after photos of a Burbank pool and attached spa built from excavation through to a filled pool and finished concrete deck.',
		'og_image'           => 'burbank-pool-project-after.webp',
	),

	array(
		'slug'               => 'calabasas-pool-project',
		'managed'            => true,
		'title'              => 'Custom Pool Construction in Calabasas, CA',
		'excerpt'            => 'A custom pool alongside a modern Calabasas house was completed, photographed as a tiled but unfilled shell and again once filled with the surrounding deck poured.',
		'neighborhood'       => 'Calabasas',
		'finish'             => 'Small-format mosaic tile interior — manufacturer and material not specified',
		'scope'              => 'Custom pool construction with tiled interior and surrounding deck',
		'timeline'           => '8–14 weeks',
		'investment'         => '$100,000–$200,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'calabasas-pool-project-after.webp',
		'hero_alt'           => 'A completed custom Calabasas pool filled beside a finished deck.',
		'before_image'       => 'calabasas-pool-project-before.webp',
		'before_alt'         => 'A custom Calabasas pool under construction, tiled but unfilled, with building materials stacked around the deck area.',
		'after_image'        => 'calabasas-pool-project-after.webp',
		'after_alt'          => 'The same Calabasas pool filled, with the surrounding deck poured and the site cleared.',
		'comparison_heading' => 'Before and After: Custom Pool Work in Calabasas, CA',
		'comparison_summary' => 'In Calabasas, Showtime Pools completed a custom pool alongside a modern single-storey house. The photographs show the pool tiled but unfilled with materials still on site, and afterwards filled with the surrounding deck poured and the area cleared.',
		'before_condition'   => 'Tiled pool shell, unfilled, with materials still on site.',
		'work_completed'     => 'Pool filled and the surrounding deck poured and finished.',
		'completed_result'   => 'Completed custom pool with a finished surrounding deck.',
		'service_url'        => '/services/custom-pool-design-construction/',
		'area_url'           => '/service-areas/calabasas/',
		'seo_title'          => 'Custom Pool Construction in Calabasas, CA',
		'meta_description'   => 'Before and after photos of a custom Calabasas pool with a tiled interior, shown as an unfilled shell and again filled with its deck finished.',
		'og_image'           => 'calabasas-pool-project-after.webp',
	),

	array(
		'slug'               => 'bel-air-pool-project',
		'managed'            => true,
		'title'              => 'Swimming Pool Construction in Bel Air, CA',
		'excerpt'            => 'A rectangular swimming pool was built into an established Bel Air garden, photographed as a bare concrete shell and again once coped and filled.',
		'neighborhood'       => 'Bel Air',
		'finish'             => 'Aggregate pool interior — manufacturer and color not specified',
		'scope'              => 'Swimming pool construction with coping',
		'timeline'           => '6–10 weeks',
		'investment'         => '$85,000–$150,000',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'bel-air-pool-project-after.webp',
		'hero_alt'           => 'A completed Bel Air swimming pool with pale coping, filled and framed by the surrounding garden.',
		'before_image'       => 'bel-air-pool-project-before.webp',
		'before_alt'         => 'A Bel Air pool shell in bare grey concrete during construction, with plumbing stub-outs and bare soil around it.',
		'after_image'        => 'bel-air-pool-project-after.webp',
		'after_alt'          => 'The same Bel Air pool finished with pale coping and filled with water, framed by the existing garden.',
		'comparison_heading' => 'Before and After: Swimming Pool Work in Bel Air, CA',
		'comparison_summary' => 'In Bel Air, Showtime Pools completed a rectangular swimming pool set into an established garden. The photographs show the shell in bare concrete with plumbing stub-outs in place, and afterwards finished with pale coping and filled with water.',
		'before_condition'   => 'Bare concrete pool shell with plumbing stub-outs and surrounding soil.',
		'work_completed'     => 'Pool interior and coping finished, then filled.',
		'completed_result'   => 'Completed pool with pale coping, filled and in use.',
		'service_url'        => '/services/custom-pool-design-construction/',
		'area_url'           => '/service-areas/bel-air/',
		'seo_title'          => 'Swimming Pool Construction in Bel Air, CA',
		'meta_description'   => 'Before and after photos of a Bel Air swimming pool built into an established garden, from bare concrete shell to a coped and filled pool.',
		'og_image'           => 'bel-air-pool-project-after.webp',
	),

	array(
		// PROMOTED from placeholder to verified. Every claim below is limited to
		// what the two photographs actually show: an ageing pump and weathered PVC
		// on an existing concrete equipment pad, replaced by a new pump with a
		// digital control interface reconnected with new PVC, wall and pad left as
		// found. Deliberately ABSENT because nothing verifies them: pump brand,
		// model, horsepower, variable-speed specification, warranty, permit,
		// rebate, electrical upgrade, completion date, contract price, customer
		// name and testimonial. This is a pump replacement only — never described
		// as resurfacing, replastering, remodeling, tile, coping, construction,
		// pad reconstruction, wall refinishing or concrete replacement.
		//
		// `investment` is a RESEARCHED CALIFORNIA MARKET RANGE, not this
		// customer's invoice. It publishes under the fixed label "Typical
		// investment for similar California projects" and never enters JSON-LD.
		// Sources: HomeGuide 2026 (VS pump $800–$2,000 installed); Waterline
		// Controls, updated 13 Jan 2026 ($900–$2,500 total, install $300–$800);
		// Kirby's Pool Service, Los Angeles ($1,350–$1,650 installed incl. tax,
		// labor, programming); Dog Days Pools (plumbing modifications add
		// $150–$300; standard swap 1–3 hours). California Energy Commission
		// Title 20 §1605.3(g)6 requires replacement dedicated-purpose pool pump
		// motors to be variable-speed, which puts every California replacement in
		// the variable-speed band rather than the cheaper single-speed one.
		'slug'               => 'west-hollywood-pool-project',
		'managed'            => true,
		'title'              => 'Pool Pump Replacement in West Hollywood, CA',
		'excerpt'            => 'An ageing pool pump on a West Hollywood equipment pad was replaced and reconnected with new PVC plumbing, with the existing concrete pad and surrounding wall left in place.',
		'neighborhood'       => 'West Hollywood',
		'finish'             => 'Equipment upgrade — existing equipment pad retained',
		'scope'              => 'Pool pump replacement and PVC plumbing reconnection',
		// One service day. The research puts the physical swap at roughly 1–3
		// hours, so a single day is the conservative homeowner-facing figure for
		// the work itself. Scheduling lead time and parts procurement are
		// deliberately NOT counted as project duration — they are not work.
		'timeline'           => '1 day',
		'investment'         => '$1,300–$2,600',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'west-hollywood-pool-project-after.webp',
		'hero_alt'           => 'A new pool pump with a digital control panel installed on a West Hollywood equipment pad.',
		'before_image'       => 'west-hollywood-pool-project-before.webp',
		'before_alt'         => 'An ageing pool pump on a concrete equipment pad in West Hollywood, connected to weathered grey and white PVC plumbing against a textured terracotta wall.',
		'after_image'        => 'west-hollywood-pool-project-after.webp',
		'after_alt'          => 'The same West Hollywood equipment pad after the work, with a new pool pump and digital control panel connected to new grey PVC plumbing.',
		'comparison_heading' => 'Before and After: Pool Pump Replacement in West Hollywood, CA',
		'comparison_summary' => 'In West Hollywood, Showtime Pools replaced an ageing pool pump on an existing equipment pad. The photographs show the original pump and its weathered PVC manifold before the work, and a new pump with a digital control panel connected to new PVC plumbing afterwards, with the surrounding wall and concrete pad left as found.',
		'before_condition'   => 'Ageing pool pump on the equipment pad, connected to weathered PVC plumbing.',
		'work_completed'     => 'Pump replaced and reconnected with new PVC plumbing on the existing pad.',
		'completed_result'   => 'A new pool pump with a digital control panel running on the same equipment pad.',
		'service_url'        => '/services/equipment-installation-upgrades/',
		'area_url'           => '/service-areas/west-hollywood/',
		'seo_title'          => 'Pool Pump Replacement in West Hollywood, CA',
		'meta_description'   => 'Before and after photos of a West Hollywood pool pump replacement: an ageing pump and weathered PVC reconnected with new plumbing on the existing equipment pad.',
		'og_image'           => 'west-hollywood-pool-project-after.webp',

		// No `additional_gallery` key: this project inherits the shared default
		// (four pending slots) from showtime_project_gallery_default(), exactly
		// like the other thirteen. Supply the key here to publish real gallery
		// photographs for this project, or set it to array() to opt out.
	),

	array(
		'slug'               => 'brentwood-pool-project',
		'managed'            => true,
		'title'              => 'Pool Cleaning in Brentwood, CA',
		'excerpt'            => 'A naturalistic Brentwood pool edged with stacked stone was cleaned, taking the water from dark green to clear.',
		'neighborhood'       => 'Brentwood',
		'finish'             => 'Existing pool finish retained',
		'scope'              => 'Pool cleaning and water clearing',
		'timeline'           => '1–2 weeks',
		'investment'         => '$300–$800',
		'completion_date'    => '',
		'client_quote'       => '',
		'hero_image'         => 'brentwood-pool-project-after.webp',
		'hero_alt'           => 'A naturalistic Brentwood pool with clear turquoise water after cleaning.',
		'before_image'       => 'brentwood-pool-project-before.webp',
		'before_alt'         => 'A naturalistic Brentwood pool edged with stacked stone, its water dark green before cleaning.',
		'after_image'        => 'brentwood-pool-project-after.webp',
		'after_alt'          => 'The same Brentwood pool after cleaning, with clear turquoise water between the stacked-stone edges.',
		'comparison_heading' => 'Before and After: Pool Cleaning in Brentwood, CA',
		'comparison_summary' => 'In Brentwood, Showtime Pools cleaned a naturalistic swimming pool edged with stacked stone and planting. The photographs show the water dark green before the work and clear afterwards, with a cleaning pole resting at the pool edge.',
		'before_condition'   => 'Naturalistic stone-edged pool with dark green water.',
		'work_completed'     => 'Pool cleaned and the water cleared.',
		'completed_result'   => 'Clear turquoise water between the stacked-stone edges.',
		'service_url'        => '/services/weekly-pool-maintenance/',
		'area_url'           => '',
		'seo_title'          => 'Pool Cleaning in Brentwood, CA',
		'meta_description'   => 'Before and after photos of a naturalistic Brentwood pool cleaned from dark green water to clear water between its stacked-stone edges.',
		'og_image'           => 'brentwood-pool-project-after.webp',
	),

	// ── Legacy seed rows (NOT code-managed) ──────────────────────────────
	// Retained so the one-time seeder keeps working. No `managed` key, so
	// showtime_project_data() ignores them and the old post-meta path applies.

	array(
		'slug'             => 'sherman-oaks-outdoor-living-build',
		'title'            => 'Sherman Oaks outdoor living build',
		'excerpt'          => 'Full backyard transform: deck repour, pergola, custom BBQ island, and a linear fire pit anchoring the lounge zone.',
		'neighborhood'     => 'Sherman Oaks',
		'completion_date'  => '2025-04',
		'finish'           => 'Sandstone deck · Stainless BBQ · Linear gas fire',
		'scope'            => 'Decking · BBQ island · Fire pit · Pergola',
		'value_label'      => '$58k',
		'duration_label'   => '5 weeks',
		'client_quote'     => 'Our backyard went from a pool with grass around it to the place our kids invite their friends to every weekend.',
	),

	array(
		'slug'             => 'encino-custom-design-water-feature',
		'title'            => 'Encino custom design with water feature',
		'excerpt'          => 'Architect-led pool design with a 14-foot sheer-descent water wall, lit from behind with color-changing LEDs.',
		'neighborhood'     => 'Encino',
		'completion_date'  => '2025-03',
		'finish'           => 'PebbleTec Onyx · Sheer-descent wall · LED',
		'scope'            => 'Custom design · Water feature · Lighting',
		'value_label'      => '$168k',
		'duration_label'   => '14 weeks',
		'client_quote'     => 'The architect drew it. Showtime made it real. Two years in, not one issue with the water wall.',
	),
);

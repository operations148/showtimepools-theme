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

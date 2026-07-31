<?php
/**
 * Project seed data — 8 demonstration projects matching the 8 bundled
 * photos at /assets/img/project_{1..8}.{webp,jpg}.
 *
 * Hardcoded but treated as defaults: once the seeder writes them to the
 * `project` CPT, Steve can edit titles, copy, photos, neighborhoods, and
 * meta entirely in WP admin. New projects beyond the eight bundled are
 * created in admin like any other post.
 *
 * Each entry mirrors the ACF group_project_meta field names so the seeder
 * can copy values straight into post meta.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Data;

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'             => 'sherman-oaks-mid-century-remodel',
		'title'            => 'Sherman Oaks mid-century remodel',
		'excerpt'          => 'Full pebble resurface, new coping, refreshed coping and waterline tile, plus a Pentair IntelliFlo + IntelliCenter automation swap.',
		'neighborhood'     => 'Sherman Oaks',
		'completion_date'  => '2025-09',
		'finish'           => 'PebbleTec Cool Blue · 6×6 ceramic tile',
		'scope'            => 'Resurface · Tile · Coping · Equipment',
		'value_label'      => '$28k',
		'duration_label'   => '12 days',
		'client_quote'     => 'Looks better than the day they finished the original build. Crew showed up at 7am, every day, no excuses.',
		'compare'          => array(
			// Copy is limited to what the photographs actually show. Product
			// brands, finish colours, equipment models, prices, durations,
			// dates and testimonials are deliberately absent — none of them is
			// evidenced by the imagery or by any record in this repository.
			'heading'          => 'Before and After: Pool Interior Finish Project in Sherman Oaks, CA',
			'summary'          => 'In Sherman Oaks, Showtime Pools applied a new aggregate interior finish to a rectangular pool and attached spa. The crew protected the surrounding deck and coping, applied the finish throughout the shell, and returned the pool to service after filling and startup.',
			'before_condition' => 'Pool shell prepared and masked, with the new interior finish being applied.',
			'work_completed'   => 'New aggregate interior finish applied across the pool and attached spa shell.',
			'completed_result' => 'Refinished pool and spa, filled and returned to service.',
			'before_alt'       => 'Crew applying a new aggregate finish to a drained Sherman Oaks pool with the surrounding deck protected.',
			'after_alt'        => 'The same Sherman Oaks pool and attached spa filled after the new interior finish was completed.',
			// Code-first comparison assets, relative to
			// assets/img/projects/comparisons/. Used only when BOTH are present
			// and no WordPress image has been uploaded for the project.
			'before_asset'     => 'sherman-oaks-mid-century-remodel-before.webp',
			'after_asset'      => 'sherman-oaks-mid-century-remodel-after.webp',
			'primary_service'  => 'pool-remodeling-resurfacing',
			// No secondary service: tile/coping work is not evidenced by the
			// photographs (the coping is masked and retained in the before frame).
			'secondary_service'=> '',
			'area'             => 'sherman-oaks',
		),
	),

	array(
		'slug'             => 'encino-estate-new-build',
		'title'            => 'Encino estate new construction',
		'excerpt'          => 'New gunite pool + spa with vanishing edge, custom waterline glass tile, full hardscape, outdoor kitchen, and fire bowl.',
		'neighborhood'     => 'Encino',
		'completion_date'  => '2025-07',
		'finish'           => 'PebbleTec Aqua White · 1×1 glass mosaic',
		'scope'            => 'New build · Hardscape · Outdoor kitchen · Fire features',
		'value_label'      => '$142k',
		'duration_label'   => '10 weeks',
		'client_quote'     => 'Steve handled everything. Permits, three trades, the inspector: we never made a single phone call.',
		'compare'          => array(
			// Reclassified from "new construction" to an interior finish
			// renewal: the before frame shows an EXISTING pool with pre-existing
			// coping and decking, and no vanishing edge, outdoor kitchen or fire
			// feature appears in either frame.
			'heading'          => 'Before and After: Pool Interior Finish Renewal in Encino, CA',
			'summary'          => 'In Encino, Showtime Pools renewed the interior finish of an existing freeform pool. The shell received a new dark aggregate finish while the existing coping and surrounding concrete were retained. After filling and startup, the pool returned to service with a refreshed interior.',
			'before_condition' => 'Existing freeform pool drained, with the new interior finish applied and awaiting fill.',
			'work_completed'   => 'New aggregate interior finish applied while the existing coping and surrounding concrete were retained.',
			'completed_result' => 'Refilled pool with a renewed interior surface.',
			'before_alt'       => 'Existing Encino freeform pool drained after application of a new dark aggregate interior finish.',
			'after_alt'        => 'The same Encino pool refilled after its interior-finish renewal.',
			'before_asset'     => 'encino-estate-new-build-before.webp',
			'after_asset'      => 'encino-estate-new-build-after.webp',
			// Was custom-pool-design-construction; the photographs show a
			// resurface of an existing pool, not new construction.
			'primary_service'  => 'pool-remodeling-resurfacing',
			'secondary_service'=> '',
			'area'             => 'encino',
		),
	),

	array(
		'slug'             => 'studio-city-modern-automation',
		'title'            => 'Studio City equipment + automation overhaul',
		'excerpt'          => 'Replaced an aging pad with an IntelliCenter automation system, salt cell, variable-speed pump, and a Raypak heater swap.',
		'neighborhood'     => 'Studio City',
		'completion_date'  => '2025-11',
		'finish'           => 'Equipment only · existing pebble retained',
		'scope'            => 'Automation · Pump · Salt · Heater',
		'value_label'      => '$8.6k',
		'duration_label'   => '3 days',
		'client_quote'     => 'They actually pulled the old equipment and recycled it. The pad looks like a magazine spread now.',
		'compare'          => array(
			// Narrowed to what the frames show: a wall-mounted control enclosure
			// swap. No pump, heater, salt cell, filter or equipment pad appears
			// in either photograph, so none of those is claimed.
			'heading'          => 'Before and After: Pool Control Panel Replacement in Studio City, CA',
			'summary'          => 'In Studio City, Showtime Pools replaced a weathered pool-control enclosure mounted on an exterior wall. The older controller and its corroded housing were removed, and a new enclosure was installed with refreshed breakers, relays, and organized color-coded wiring. The existing conduit runs below the unit were left in place.',
			'before_condition' => 'Weathered control enclosure with an aging controller and wiring.',
			'work_completed'   => 'Control enclosure, breakers, relays, and associated wiring replaced and organized.',
			'completed_result' => 'New pool-control enclosure with clean, labeled, serviceable wiring.',
			'before_alt'       => 'Weathered Studio City pool-control enclosure before replacement.',
			'after_alt'        => 'Replacement Studio City pool-control enclosure with new breakers, relays, and organized wiring.',
			'before_asset'     => 'studio-city-modern-automation-before.webp',
			'after_asset'      => 'studio-city-modern-automation-after.webp',
			'primary_service'  => 'equipment-installation-upgrades',
			'secondary_service'=> 'smart-pool-automation',
			'area'             => 'studio-city',
		),
	),

	array(
		'slug'             => 'beverly-hills-luxe-spa-renovation',
		'title'            => 'Beverly Hills luxe spa renovation',
		'excerpt'          => 'Existing spa stripped, re-tiled with hand-cut Italian glass mosaic, new jets, and color-tuned LED lighting.',
		'neighborhood'     => 'Beverly Hills',
		'completion_date'  => '2025-08',
		'finish'           => 'Italian glass mosaic · LED color-loop',
		'scope'            => 'Spa renovation · Tile · Lighting',
		'value_label'      => '$22k',
		'duration_label'   => '8 days',
		'client_quote'     => 'It’s the only thing in the backyard our daughter actually compliments. That is the highest praise possible.',
		'compare'          => array(
			// Tile material/origin, jets and lighting are not determinable from a
			// photograph, so the copy describes only the visible tile work and
			// the renewed surrounding surface.
			'heading'          => 'Before and After: Spa Tile Renovation in Beverly Hills, CA',
			'summary'          => 'In Beverly Hills, Showtime Pools renovated an existing in-ground spa set alongside a pool. The previous interior finish and surrounding surface were replaced with small blue-gray mosaic tile, and the adjoining deck area was renewed to coordinate with the completed spa. The neighboring pool was left in place.',
			'before_condition' => 'Existing spa with its previous interior finish and surrounding deck.',
			'work_completed'   => 'Spa interior and surround finished in small-format mosaic tile, with the adjoining deck area renewed.',
			'completed_result' => 'Completed tiled spa with a coordinated surrounding surface.',
			'before_alt'       => 'Existing Beverly Hills in-ground spa before its tile renovation.',
			'after_alt'        => 'The same Beverly Hills spa finished in small blue-gray mosaic tile with its surrounding surface renewed.',
			'before_asset'     => 'beverly-hills-luxe-spa-renovation-before.webp',
			'after_asset'      => 'beverly-hills-luxe-spa-renovation-after.webp',
			'primary_service'  => 'spa-installation-renovations',
			'secondary_service'=> 'tile-coping-plaster-decking',
			'area'             => 'beverly-hills',
		),
	),

	array(
		'slug'             => 'tarzana-resort-style-finish',
		'title'            => 'Tarzana resort-style finish',
		'excerpt'          => 'Resurface in PebbleTec Caribbean Blue with a sunshelf addition, new bullnose coping, and travertine deck repointing.',
		'neighborhood'     => 'Tarzana',
		'completion_date'  => '2025-06',
		'finish'           => 'PebbleTec Caribbean Blue · Travertine deck',
		'scope'            => 'Resurface · Sunshelf · Coping · Decking',
		'value_label'      => '$36k',
		'duration_label'   => '14 days',
		'client_quote'     => 'Quote came back with three options. Most companies give you one. We picked the middle one with zero buyer’s remorse.',
		'compare'          => array(
			// The deck is timber, not travertine, and the waterline band and
			// shell geometry appear unchanged between frames — so no coping,
			// decking or sunshelf work is claimed.
			'heading'          => 'Before and After: Pool Resurfacing in Tarzana, CA',
			'summary'          => 'In Tarzana, Showtime Pools resurfaced a freeform pool whose interior finish had flaked away across the floor and walls. The worn surface was prepared and replaced with a new pale aggregate finish, then the pool was refilled. The existing waterline band and timber deck remained in place throughout.',
			'before_condition' => 'Freeform pool with a deteriorated and delaminating interior finish beside the existing timber deck.',
			'work_completed'   => 'Worn interior surface prepared and replaced with a new aggregate finish.',
			'completed_result' => 'Pool refilled with a renewed interior while retaining the existing waterline and deck.',
			'before_alt'       => 'Tarzana freeform pool with a deteriorated interior finish beside the existing timber deck.',
			'after_alt'        => 'The same Tarzana pool filled after resurfacing, with the timber deck retained.',
			'before_asset'     => 'tarzana-resort-style-finish-before.webp',
			'after_asset'      => 'tarzana-resort-style-finish-after.webp',
			'primary_service'  => 'pool-remodeling-resurfacing',
			// No secondary service: no tile, coping or decking work is evidenced.
			'secondary_service'=> '',
			'area'             => 'tarzana',
		),
	),

	array(
		'slug'             => 'woodland-hills-tile-coping-refresh',
		'title'            => 'Woodland Hills tile + coping refresh',
		'excerpt'          => 'Replaced 30-year-old waterline tile and coping without touching the plaster. Same pool, completely different look.',
		'neighborhood'     => 'Woodland Hills',
		'completion_date'  => '2025-05',
		'finish'           => '6×6 porcelain · Cantilever coping',
		'scope'            => 'Tile · Coping (no resurface)',
		'value_label'      => '$12.4k',
		'duration_label'   => '6 days',
		'client_quote'     => 'They told us the plaster had another five years. Saved us $20k by not selling us a resurface we didn’t need.',
		'compare'          => array(
			// Subject is a round SPA, not a pool, and the whole interior was
			// relined in small mosaic tile — the opposite of the previous
			// "no resurfacing / 6x6 porcelain waterline" claim. Tile size,
			// material and age are not determinable from the photographs.
			'heading'          => 'Before and After: Spa Tile Renovation in Woodland Hills, CA',
			'summary'          => 'In Woodland Hills, Showtime Pools renovated a round in-ground spa that had been out of service and left full of fallen leaves. The deteriorated interior was replaced with small blue mosaic tile from floor to rim, and the cracked surrounding concrete was replaced with a wood-look tiled surface.',
			'before_condition' => 'Round spa drained with a deteriorated interior and cracked surrounding concrete.',
			'work_completed'   => 'Spa interior tiled from floor to rim, with the surrounding surface replaced.',
			'completed_result' => 'Completed blue-tiled spa with a renewed wood-look surrounding surface.',
			'before_alt'       => 'Drained round spa in Woodland Hills with a deteriorated interior and cracked concrete surround.',
			'after_alt'        => 'The same Woodland Hills spa finished in blue mosaic tile with a renewed wood-look surrounding surface.',
			'before_asset'     => 'woodland-hills-tile-coping-refresh-before.webp',
			'after_asset'      => 'woodland-hills-tile-coping-refresh-after.webp',
			// Reclassified from tile/coping/decking to spa renovation.
			'primary_service'  => 'spa-installation-renovations',
			'secondary_service'=> '',
			'area'             => 'woodland-hills',
		),
	),

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

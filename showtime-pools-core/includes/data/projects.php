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
			'heading'          => 'Before and After: Pool Remodeling in Sherman Oaks, CA',
			'summary'          => 'In Sherman Oaks, Showtime Pools completed a full remodel of a mid-century pool: resurfacing, waterline tile, coping, and an equipment upgrade. The work included a PebbleTec Cool Blue pebble finish, 6×6 ceramic waterline tile, new coping, and a Pentair IntelliFlo variable-speed pump paired with an IntelliCenter automation system. The crew worked the site daily from 7am through the full build. The project took 12 days, represented an investment of $28k, and was completed in September 2025.',
			'before_condition' => 'Original mid-century surface, waterline tile, coping, and equipment pad.',
			'work_completed'   => 'Full pebble resurface, new waterline tile and coping, plus a pump and automation swap.',
			'completed_result' => 'PebbleTec Cool Blue finish with 6×6 ceramic tile and IntelliCenter-controlled equipment.',
			'before_alt'       => 'Crew applying the new pebble finish to the drained Sherman Oaks pool during the remodel.',
			'after_alt'        => 'The finished Sherman Oaks pool and spa filled with water after the remodel.',
			// Code-first comparison assets, relative to
			// assets/img/projects/comparisons/. Used only when BOTH are present
			// and no WordPress image has been uploaded for the project.
			'before_asset'     => 'sherman-oaks-mid-century-remodel-before.webp',
			'after_asset'      => 'sherman-oaks-mid-century-remodel-after.webp',
			'primary_service'  => 'pool-remodeling-resurfacing',
			'secondary_service'=> 'tile-coping-plaster-decking',
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
			'heading'          => 'Before and After: Custom Pool Construction in Encino, CA',
			'summary'          => 'In Encino, Showtime Pools built a new gunite pool and spa from an undeveloped yard. The work included a vanishing edge, custom 1×1 glass mosaic waterline tile, a PebbleTec Aqua White finish, full hardscape, an outdoor kitchen, and a fire bowl. Showtime pulled the permits and coordinated all three trades and the inspector directly. The project took 10 weeks, represented an investment of $142k, and was completed in July 2025.',
			'before_condition' => 'Undeveloped yard with no existing pool.',
			'work_completed'   => 'New gunite pool and spa, vanishing edge, hardscape, outdoor kitchen, and fire features.',
			'completed_result' => 'PebbleTec Aqua White pool and spa with glass mosaic waterline and full outdoor living build.',
			// Alt text describes only what is visible in the photograph. NOTE:
			// the summary/scope copy above still describes a new build; the
			// photographs show a resurface. Registry copy to be realigned.
			'before_alt'       => 'Drained Encino pool with fresh dark finish applied, before refilling.',
			'after_alt'        => 'The Encino pool refilled with water after the finish work.',
			'before_asset'     => 'encino-estate-new-build-before.jpg',
			'after_asset'      => 'encino-estate-new-build-after.jpg',
			'primary_service'  => 'custom-pool-design-construction',
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
			'heading'          => 'Before and After: Pool Equipment and Automation Upgrade in Studio City, CA',
			'summary'          => 'In Studio City, Showtime Pools replaced an aging equipment pad and left the existing pebble surface untouched. The work included a Pentair IntelliCenter automation system, a salt chlorine cell, a variable-speed pump, and a Raypak heater swap. The old equipment was pulled off site and recycled rather than sent to landfill. The project took 3 days, represented an investment of $8.6k, and was completed in November 2025.',
			'before_condition' => 'Aging equipment pad with end-of-life pump, heater, and controls.',
			'work_completed'   => 'Full pad rebuild: automation, variable-speed pump, salt cell, and heater replacement.',
			'completed_result' => 'IntelliCenter-automated pad running a variable-speed pump, salt cell, and Raypak heater.',
			'before_alt'       => 'Studio City pool automation control panel before the upgrade, with aged wiring and the original controller.',
			'after_alt'        => 'The rebuilt Studio City control panel after the upgrade, with new breakers and tidied wiring.',
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
			'heading'          => 'Before and After: Spa Renovation in Beverly Hills, CA',
			'summary'          => 'In Beverly Hills, Showtime Pools renovated an existing spa down to the shell. The work included hand-cut Italian glass mosaic tile, new jets, and color-tuned LED lighting on a programmable color loop. The surrounding pool was left in place, so the renovation stayed contained entirely to the spa. The project took 8 days, represented an investment of $22k, and was completed in August 2025.',
			'before_condition' => 'Existing spa stripped back to the shell before re-tiling.',
			'work_completed'   => 'Re-tiled in hand-cut Italian glass mosaic, new jets, and LED lighting installed.',
			'completed_result' => 'Italian glass mosaic spa with new jets and a color-tuned LED loop.',
			'before_alt'       => 'Beverly Hills spa before renovation, showing the plain plaster interior and original trim tile.',
			'after_alt'        => 'The finished Beverly Hills spa lined with blue glass mosaic tile.',
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
			'heading'          => 'Before and After: Pool Resurfacing in Tarzana, CA',
			'summary'          => 'In Tarzana, Showtime Pools resurfaced an existing pool and added a sunshelf. The work included a PebbleTec Caribbean Blue finish, a new sunshelf, new bullnose coping, and repointing of the existing travertine deck. The job was quoted as three costed options rather than a single take-it-or-leave-it figure. The project took 14 days, represented an investment of $36k, and was completed in June 2025.',
			'before_condition' => 'Pool due for resurfacing, no sunshelf, and travertine decking needing repointing.',
			'work_completed'   => 'Pebble resurface, sunshelf addition, new bullnose coping, and deck repointing.',
			'completed_result' => 'PebbleTec Caribbean Blue finish with a new sunshelf and repointed travertine deck.',
			// NOTE: registry copy says travertine deck; the photographs show a
			// timber deck. Alt text follows the photographs.
			'before_alt'       => 'Drained Tarzana pool with worn interior finish, beside the timber deck.',
			'after_alt'        => 'The Tarzana pool refilled after resurfacing, with its shallow sun shelf and timber deck.',
			'before_asset'     => 'tarzana-resort-style-finish-before.webp',
			'after_asset'      => 'tarzana-resort-style-finish-after.webp',
			'primary_service'  => 'pool-remodeling-resurfacing',
			'secondary_service'=> 'tile-coping-plaster-decking',
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
			'heading'          => 'Before and After: Pool Tile and Coping Upgrade in Woodland Hills, CA',
			'summary'          => 'In Woodland Hills, Showtime Pools replaced 30-year-old waterline tile and coping without touching the plaster. The work included 6×6 porcelain waterline tile and new cantilever coping. The existing plaster was inspected, judged to have roughly five years of service left, and deliberately kept rather than resurfaced. The project took 6 days, represented an investment of $12.4k, and was completed in May 2025.',
			'before_condition' => '30-year-old waterline tile and coping; existing plaster still serviceable.',
			'work_completed'   => 'Waterline tile and coping replaced. No resurfacing performed.',
			'completed_result' => '6×6 porcelain waterline tile and cantilever coping, with the original plaster retained.',
			// NOTE: registry copy describes pool waterline tile in 6x6 porcelain
			// with no resurfacing; the photographs show a round spa re-tiled in
			// glass mosaic. Alt text follows the photographs.
			'before_alt'       => 'Round Woodland Hills spa before the refresh, drained with worn surfaces and fallen leaves.',
			'after_alt'        => 'The same Woodland Hills spa after re-tiling, lined in blue mosaic tile.',
			'before_asset'     => 'woodland-hills-tile-coping-refresh-before.webp',
			'after_asset'      => 'woodland-hills-tile-coping-refresh-after.webp',
			'primary_service'  => 'tile-coping-plaster-decking',
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

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
		'client_quote'     => 'Steve handled everything. Permits, three trades, the inspector — we never made a single phone call.',
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

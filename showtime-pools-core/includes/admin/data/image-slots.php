<?php
/**
 * Image slot → bundled source file map for the seeder.
 *
 * Each entry maps a Site Images slot to a relative path inside the child
 * theme's /assets/img/ directory. The seeder reads the .jpg (universal,
 * smaller than .webp once Media Library generates its size variants).
 *
 * Slots intentionally NOT seeded:
 *   - process_bg  (no bundled file)
 *   - project_9   (no bundled file)
 *   - logo        (handled by Customizer → Site Identity)
 *
 * Resolved at seed time via SHOWTIME_CHILD_DIR (defined by the theme).
 * If the constant is missing the seeder reports 0 images and skips silently.
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Heroes & backgrounds.
	'hero'                                     => 'assets/img/hero.jpg',
	'about_hero'                               => 'assets/img/about_hero.jpg',
	'founder'                                  => 'assets/img/founder.jpg',
	'inspections_bg'                           => 'assets/img/inspections_bg.jpg',

	// Lifestyle.
	'lifestyle_main'                           => 'assets/img/lifestyle_main.jpg',
	'lifestyle_1'                              => 'assets/img/lifestyle_1.jpg',
	'lifestyle_2'                              => 'assets/img/lifestyle_2.jpg',
	'lifestyle_3'                              => 'assets/img/lifestyle_3.jpg',
	'lifestyle_4'                              => 'assets/img/lifestyle_4.jpg',

	// Service heroes (12).
	'service_pool-repairs-plumbing'            => 'assets/img/service_pool-repairs-plumbing.jpg',
	'service_weekly-pool-maintenance'          => 'assets/img/service_weekly-pool-maintenance.jpg',
	'service_pool-remodeling-resurfacing'      => 'assets/img/service_pool-remodeling-resurfacing.jpg',
	'service_equipment-installation-upgrades'  => 'assets/img/service_equipment-installation-upgrades.jpg',
	'service_pool-inspections-diagnostics'     => 'assets/img/service_pool-inspections-diagnostics.jpg',
	'service_smart-pool-automation'            => 'assets/img/service_smart-pool-automation.jpg',
	'service_custom-pool-design-construction'  => 'assets/img/service_custom-pool-design-construction.jpg',
	'service_spa-installation-renovations'     => 'assets/img/service_spa-installation-renovations.jpg',
	'service_tile-coping-plaster-decking'      => 'assets/img/service_tile-coping-plaster-decking.jpg',
	'service_outdoor-living-hardscape'         => 'assets/img/service_outdoor-living-hardscape.jpg',
	'service_outdoor-kitchens-bbq'             => 'assets/img/service_outdoor-kitchens-bbq.jpg',
	'service_fire-water-features'              => 'assets/img/service_fire-water-features.jpg',

	// Service areas (6).
	'area_sherman-oaks'                        => 'assets/img/area_sherman-oaks.jpg',
	'area_encino'                              => 'assets/img/area_encino.jpg',
	'area_beverly-hills'                       => 'assets/img/area_beverly-hills.jpg',
	'area_studio-city'                         => 'assets/img/area_studio-city.jpg',
	'area_tarzana'                             => 'assets/img/area_tarzana.jpg',
	'area_woodland-hills'                      => 'assets/img/area_woodland-hills.jpg',

	// Projects (8 of 9 — project_9 has no bundled file).
	'project_1'                                => 'assets/img/project_1.jpg',
	'project_2'                                => 'assets/img/project_2.jpg',
	'project_3'                                => 'assets/img/project_3.jpg',
	'project_4'                                => 'assets/img/project_4.jpg',
	'project_5'                                => 'assets/img/project_5.jpg',
	'project_6'                                => 'assets/img/project_6.jpg',
	'project_7'                                => 'assets/img/project_7.jpg',
	'project_8'                                => 'assets/img/project_8.jpg',

	// Blog (4).
	'blog_default'                             => 'assets/img/blog_default.jpg',
	'blog_trends'                              => 'assets/img/blog_trends.jpg',
	'blog_tips'                                => 'assets/img/blog_tips.jpg',
	'blog_equipment'                           => 'assets/img/blog_equipment.jpg',
);

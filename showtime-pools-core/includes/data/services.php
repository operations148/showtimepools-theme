<?php
/**
 * Services registry — single source of truth.
 *
 * The 12 real services from showtimepoolservice.com. Slugs are LOCKED;
 * renaming requires a 301 redirect plan at launch QA. Title and summary
 * come straight from the canonical site copy, lightly tightened where
 * the source repeated itself.
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'              => 'pool-repairs-plumbing',
		'title'             => 'Pool Repairs & Plumbing',
		'summary'           => 'Fast diagnosis and repair for leaks, pumps, heaters, filters, valves, and plumbing issues. We fix it right the first time with clear recommendations and quality parts.',
		'icon'              => 'wrench',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Diagnostic from $185',
		'default_turnaround'=> 'Same-day to 48 hours',
		'default_includes'  => array(
			'On-site diagnostic with photos and root-cause findings',
			'Pump, filter, heater, salt cell, and valve repair',
			'Underground plumbing leak detection and repair',
			'Pentair and Jandy authorized parts',
			'Written quote before any billable repair work',
			'Same-day priority for active service customers',
		),
		'default_faqs' => array(
			array( 'q' => 'Will you diagnose before you charge for the repair?', 'a' => 'Yes. The diagnostic is quoted up front. We identify the root cause first, document it with photos, and only proceed with the repair after you approve the written quote.' ),
			array( 'q' => 'Do you stock common parts on the truck?', 'a' => 'For Pentair and Jandy: pump motors, igniters, salt cells, pressure gauges, common o-rings, and capacitors. Special-order items take 2 to 7 days depending on the supplier.' ),
			array( 'q' => 'My pool keeps losing water. Can you find a leak underground?', 'a' => 'Yes. We run dye tests at the skimmer and returns, pressure-test plumbing lines, and use electronic detection where the line is unreachable. Most underground leaks are located within a single visit.' ),
		),
	),

	array(
		'slug'              => 'weekly-pool-maintenance',
		'title'             => 'Weekly Pool Maintenance',
		'summary'           => 'Reliable weekly service to keep your water clean, balanced, and swim-ready. Skimming, brushing, vacuuming, chemical testing, and equipment checks, so you never have to worry.',
		'icon'              => 'maintenance',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $185/month',
		'default_turnaround'=> 'Weekly visits',
		'default_includes'  => array(
			'Same W-2 technician on your route every week',
			'Full chemistry test and balance (chlorine, pH, alkalinity, calcium, CYA)',
			'Skim, brush, vacuum, and tile-line wipe-down',
			'Empty skimmer and pump baskets',
			'Equipment check with photo report after every visit',
			'Filter clean every 90 days included',
		),
		'default_faqs' => array(
			array( 'q' => 'What does the price include?', 'a' => 'Labor, chemistry, photo report, and 90-day filter cleans. Specialty chemicals (algaecide, phosphate remover, salt) are itemized separately and only used when test results justify it.' ),
			array( 'q' => 'Do I sign a long-term contract?', 'a' => 'No. Service is month-to-month. You can pause for vacancy or cancel any time with 7 days notice. We earn the renewal every month.' ),
			array( 'q' => 'What if you find a problem during the visit?', 'a' => 'You get a written quote within 24 hours with photos. Nothing is repaired without your written approval. Active service customers get same-day priority for urgent fixes.' ),
		),
	),

	array(
		'slug'              => 'pool-remodeling-resurfacing',
		'title'             => 'Pool Remodeling, Resurfacing & Finishes',
		'summary'           => 'Refresh your pool with resurfacing, structural upgrades, and modern finishes. Plaster, pebble, tile, coping, and decking improvements that boost comfort, longevity, and value.',
		'icon'              => 'remodel',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $18,500',
		'default_turnaround'=> '3-5 weeks',
		'default_includes'  => array(
			'Drain, prep, and bond coat on existing shell',
			'Plaster, quartz, or PebbleTec finish (your choice)',
			'Tile and coping refresh with premium options',
			'Equipment swap if pad is at end of life',
			'New main drain covers, VGB-compliant',
			'Acid wash and balanced startup chemistry',
			'5-year written finish warranty',
		),
		'default_faqs' => array(
			array( 'q' => 'How long does a typical remodel take?', 'a' => 'Standard replaster + retile + equipment refresh runs 3 to 5 weeks depending on weather and material lead time. We commit to a written schedule before draining your pool.' ),
			array( 'q' => 'Can I keep the same shape?', 'a' => 'Yes. Most remodels keep the existing shell and structure and just refresh surfaces and equipment. Reshaping requires an engineering review and is treated as new construction.' ),
			array( 'q' => 'Do you offer PebbleTec?', 'a' => 'Yes, we are a PebbleTec Certified Applicator. PebbleTec finishes carry a 5-year manufacturer warranty in addition to our 2-year workmanship warranty.' ),
		),
	),

	array(
		'slug'              => 'equipment-installation-upgrades',
		'title'             => 'Equipment Installation & Upgrades',
		'summary'           => 'Upgrade your pool with efficient pumps, filters, heaters, and salt systems, installed and configured professionally. Improve water clarity, performance, and energy savings with the right setup.',
		'icon'              => 'equipment',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $2,400',
		'default_turnaround'=> '2-7 days',
		'default_includes'  => array(
			'In-person equipment audit with photo report',
			'Pentair or Jandy authorized installation',
			'Plumbing rework if pad layout is undersized',
			'Variable-speed pump tuning to LADWP rebate spec',
			'Manufacturer warranty registration on your behalf',
			'Old equipment haul-away and proper disposal',
		),
		'default_faqs' => array(
			array( 'q' => 'Should I switch to a variable-speed pump?', 'a' => 'Yes if your current pump is single-speed and over 5 years old. Variable-speed pumps cut runtime electricity 70 to 80 percent, qualify for LADWP rebates, and pay back the upgrade in 18 to 30 months.' ),
			array( 'q' => 'How long do heaters last?', 'a' => 'Gas heaters: 8 to 12 years. Heat pumps: 12 to 15. Salt cells: 4 to 7 depending on bather load and chemistry. We carry the most common parts on the truck for in-warranty repairs.' ),
			array( 'q' => 'Do you handle the LADWP rebate paperwork?', 'a' => 'Yes. We pull the rebate forms, submit them on your behalf, and follow up until the credit lands. The rebate is yours; we never take a cut.' ),
		),
	),

	array(
		'slug'              => 'pool-inspections-diagnostics',
		'title'             => 'Pool Inspections & Diagnostics',
		'summary'           => 'Not sure what is wrong? On-site assessments with clear findings and next steps. Perfect for recurring issues, performance problems, or pre-purchase peace of mind.',
		'icon'              => 'inspection',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Flat $185 to $385',
		'default_turnaround'=> 'Same-day verbal · 24-48 hour written',
		'default_includes'  => array(
			'On-site inspection: structure, equipment, plumbing, electrical, bonding',
			'Written report within 24 to 48 hours, photos and code refs',
			'Estimated remaining life on every major component',
			'Repair quote you can shop, hire us, or both',
			'Independent line, firewalled from the construction quote',
			'Suitable for buyers, sellers, agents, lenders, and insurers',
		),
		'default_faqs' => array(
			array( 'q' => 'How is this different from a general home inspection?', 'a' => 'General home inspectors scan a pool for 5 to 10 minutes and look at gross condition. We spend 60 to 90 minutes pressure-testing, opening equipment, checking bonding continuity, and photographing structural cracks.' ),
			array( 'q' => 'Will you tell me to walk away from a pool?', 'a' => 'Yes when walking away is the right call. The inspection line is firewalled from construction. We have killed our own six-figure construction quotes when the pool was past saving.' ),
			array( 'q' => 'Will the report stand up with my lender or insurance?', 'a' => 'Yes. The report includes inspector credentials, methodology, and dated photos. Lenders, insurers, and home warranty companies accept it as third-party documentation.' ),
		),
	),

	array(
		'slug'              => 'smart-pool-automation',
		'title'             => 'Smart Pool Automation Upgrades',
		'summary'           => 'Control your pool from your phone with automation and smart system upgrades. We install, program, and optimize schedules for heating, filtration, lighting, and cleaner operation.',
		'icon'              => 'automation',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $3,200',
		'default_turnaround'=> '3-5 days',
		'default_includes'  => array(
			'Pentair IntelliCenter or Jandy AquaLink RS install',
			'Phone, tablet, and Alexa/Google Home integration',
			'Wireless control of pump, heater, lights, and water features',
			'Optional chemistry monitoring (pH and ORP) with alerts',
			'Schedule presets for spa, party, and energy-saver modes',
			'Hands-on training session with the homeowner',
		),
		'default_faqs' => array(
			array( 'q' => 'Do I need new equipment to add automation?', 'a' => 'Not always. Most modern Pentair and Jandy equipment is automation-ready out of the box. Older non-VS pumps and analog heaters may need a small retrofit kit, which we quote up front.' ),
			array( 'q' => 'What does it actually do for me?', 'a' => 'Three things: drops your electric bill (the system runs equipment only when needed), prevents bad chemistry from going unnoticed (sensors text you), and lets you run a perfect spa night without walking outside.' ),
			array( 'q' => 'Will it work if Wi-Fi is weak at the pad?', 'a' => 'We test signal at the equipment pad before quoting. If it is below threshold, we add a hardwired bridge or mesh node, included in the quote, never a surprise charge later.' ),
		),
	),

	array(
		'slug'              => 'custom-pool-design-construction',
		'title'             => 'Custom Pool Design & New Construction',
		'summary'           => 'From concept to completion. Custom pool design, planning, and full construction. Engineered structure, premium tile and coping, and equipment installed and tuned by the same team that maintains it.',
		'icon'              => 'construction',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $85,000',
		'default_turnaround'=> '8-14 weeks',
		'default_includes'  => array(
			'Site survey, soil report, and engineered structural plans',
			'Full permitting through Sherman Oaks and LA County',
			'Steel, gunite, plumbing, electrical, and bonding',
			'Tile, coping, and finish (plaster, quartz, or PebbleTec)',
			'Equipment install: pump, filter, heater, salt cell, automation',
			'Startup chemistry and a written 2-year workmanship warranty',
		),
		'default_faqs' => array(
			array( 'q' => 'How long does a new pool take?', 'a' => 'A standard 14x28 gunite pool runs 8 to 12 weeks from permit pull to startup. Larger custom builds with spas, water features, or hardscape can stretch to 14 to 16 weeks. You get a written milestone schedule before any work starts.' ),
			array( 'q' => 'Do you handle permits?', 'a' => 'Yes, in-house. We pull every permit through Sherman Oaks Building & Safety and LA County. Permit cost is itemized in your quote, no markup.' ),
			array( 'q' => 'What kind of warranty do I get?', 'a' => '2-year workmanship warranty on the build, 5-year warranty on most plaster finishes, and manufacturer warranty pass-through on all equipment.' ),
		),
	),

	array(
		'slug'              => 'spa-installation-renovations',
		'title'             => 'Spa Installation & Renovations',
		'summary'           => 'Add a new spa or upgrade an existing one for comfort and style. Raised spas, attached spas, freestanding spas, and full spa retrofits with modern jets, automation, and finishes.',
		'icon'              => 'spa',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $14,500',
		'default_turnaround'=> '3-6 weeks',
		'default_includes'  => array(
			'Engineered structural drawings for raised or attached spas',
			'Permitting through LA County and city counter',
			'Plumbing, electrical, and bonding to current code',
			'Jet selection, blower placement, and spillway tuning',
			'Heater and pump matched to spa volume',
			'Tile and coping selection coordinated with the pool',
		),
		'default_faqs' => array(
			array( 'q' => 'Can you add a spa to my existing pool?', 'a' => 'Usually yes. Attached spas use the existing pool deck and a shared equipment pad. We engineer the structural attachment and re-permit the electrical. Standalone spas are simpler still.' ),
			array( 'q' => 'How long until I can use it?', 'a' => 'Three to six weeks for a typical spa add-on. Standalone retrofits with modern jets can be done in two weeks.' ),
			array( 'q' => 'Will it match the pool finish?', 'a' => 'Yes. We coordinate plaster, tile, and coping across both spa and pool so the visual reads as one project, not a bolt-on.' ),
		),
	),

	array(
		'slug'              => 'tile-coping-plaster-decking',
		'title'             => 'Tile, Coping, Plaster & Decking',
		'summary'           => 'Detailed finish work that transforms the look and feel of your pool. Waterline tile, mosaic accents, natural stone or precast coping, and surrounding deck refresh.',
		'icon'              => 'tile',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $5,200',
		'default_turnaround'=> '1-3 weeks',
		'default_includes'  => array(
			'Chip out and replace existing waterline tile and coping',
			'Optional custom mosaic feature wall or step accents',
			'Travertine, limestone, or precast coping selection',
			'Mortar, mastic, and grout sealing for longevity',
			'Color-matched grout and clean caulk lines at deck joint',
			'Optional deck refresh with concrete, pavers, or stone',
		),
		'default_faqs' => array(
			array( 'q' => 'Do I have to drain the pool for tile work?', 'a' => 'Yes, the water level needs to drop below the work line. We coordinate the partial drain so you are not down a full pool of water for any longer than necessary.' ),
			array( 'q' => 'Glass vs porcelain tile?', 'a' => 'Glass tile delivers depth and color shift in sun, costs about 1.6x more than porcelain, and demands precise grout work. Porcelain is the workhorse: durable, cost-effective, and 95 percent of the visual impact.' ),
			array( 'q' => 'Can you match the existing tile?', 'a' => 'Sometimes. We carry samples from major manufacturers and do a free in-person color match. If your tile is older than 10 years, an exact match is unlikely; we recommend a deliberate contrast instead.' ),
		),
	),

	array(
		'slug'              => 'outdoor-living-hardscape',
		'title'             => 'Outdoor Living & Hardscape',
		'summary'           => 'Extend your backyard with functional outdoor upgrades. Hardscape, concrete, pavers, retaining walls, and structural elements that turn the pool deck into a usable, layered space.',
		'icon'              => 'hardscape',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $9,400',
		'default_turnaround'=> '2-6 weeks',
		'default_includes'  => array(
			'Layout design coordinated with the pool deck',
			'Concrete, paver, or natural stone surfaces',
			'Retaining walls, planters, and structural footings',
			'Drainage planning to keep water off the pool deck',
			'Permits when the work crosses property lines or impacts grading',
			'Coordinated finishes across pool deck and yard',
		),
		'default_faqs' => array(
			array( 'q' => 'Pavers, concrete, or natural stone?', 'a' => 'Pavers cost more up front, are infinitely patchable. Stamped concrete reads premium and is faster to install. Natural stone (travertine, flagstone) is the long-game choice. We will tell you which fits your lot, climate, and budget.' ),
			array( 'q' => 'Will the new deck work with the existing pool?', 'a' => 'Yes. We pull the deck levels, drainage, and finishes off the existing pool tile and coping line so everything reads as one project.' ),
			array( 'q' => 'Do you do the planters and walls too?', 'a' => 'Yes. Built-in planters, low retaining walls, and structural seating are routine. Engineered retaining walls over 4 feet require a separate permit, which we pull.' ),
		),
	),

	array(
		'slug'              => 'outdoor-kitchens-bbq',
		'title'             => 'Outdoor Kitchens & BBQ Areas',
		'summary'           => 'Create a complete backyard experience with built-in cooking and hosting areas. Counter, grill, fridge, sink, and run-of-show layout that works for actual entertaining.',
		'icon'              => 'kitchen',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $12,800',
		'default_turnaround'=> '3-7 weeks',
		'default_includes'  => array(
			'Layout consultation focused on flow and cleanup',
			'Concrete or block structural base',
			'Granite, quartz, or sealed concrete countertops',
			'Built-in grill, side burner, fridge, and sink installation',
			'Gas and electrical permits + utility runs',
			'Splash zone planning so the BBQ does not lose to the pool',
		),
		'default_faqs' => array(
			array( 'q' => 'What grills do you install?', 'a' => 'Lynx, Wolf, Bull, and Twin Eagles for premium builds. Blaze and Bull for value-oriented builds. We carry samples for each at the Sherman Oaks shop.' ),
			array( 'q' => 'Do I need a gas line run?', 'a' => 'For natural-gas grills, yes. We coordinate with the utility for the new line and pull the permits. For propane, the tank line is much simpler.' ),
			array( 'q' => 'How close to the pool can the kitchen go?', 'a' => 'Code minimum is 3 feet from waterline for cooking elements; we usually push to 6 to 8 feet for splash and ergonomic reasons. Final layout depends on the lot.' ),
		),
	),

	array(
		'slug'              => 'fire-water-features',
		'title'             => 'Fire Features & Water Features',
		'summary'           => 'Add standout elements that change the feel of the backyard at night. Waterfalls, scuppers, fountains, fire bowls, and fire pits engineered into the pool and deck.',
		'icon'              => 'fire',
		'accent_token'      => '--c-aqua-500',
		'default_price'     => 'Starts at $4,800',
		'default_turnaround'=> '1-4 weeks',
		'default_includes'  => array(
			'Spillways, sheer descents, and laminar deck jets',
			'Fire bowls and fire pits, gas or propane',
			'Plumbing, gas, and electrical to current code',
			'Automation integration with existing pool controls',
			'Tile, finish, and coping match for visual continuity',
			'Permits when fire features cross code thresholds',
		),
		'default_faqs' => array(
			array( 'q' => 'Can features be added to an existing pool?', 'a' => 'Most can. Spillways, scuppers, and laminar jets retrofit cleanly into existing pools. Fire bowls and fire pits are simpler still. We will tell you up front if a feature requires structural work.' ),
			array( 'q' => 'Are they hard to maintain?', 'a' => 'No. Water features are tied into the existing filtration loop. Fire bowls run on the same gas line as a BBQ. Both are turned on and off through the automation system.' ),
			array( 'q' => 'Will fire features pass HOA review?', 'a' => 'In most LA HOA neighborhoods, yes. We have submitted dozens of fire-feature plans through HOA architectural committees. We can attach the structural and gas drawings the HOA needs.' ),
		),
	),

);

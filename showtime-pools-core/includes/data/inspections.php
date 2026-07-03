<?php
/**
 * Inspection types registry — 3 types, single source of truth.
 *
 * Drives the /pool-inspections/ hub, the 3 inspection landing pages, and
 * the inspections-callout section on the homepage.
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	array(
		'slug'           => 'pre-purchase-inspection',
		'name'           => 'Pre-Purchase Inspection',
		'short'          => 'Before you close on the house',
		'price'          => 'Flat $385',
		'turnaround'     => '24-hour written report',
		'duration'       => '60-90 minutes on-site',
		'lead'           => 'You are buying a house. The pool comes with it. Before you sign, we tell you what is actually in the equipment shed, what is failing in the structure, and what the next 5 years of ownership realistically cost.',
		'who_for'        => array(
			'Homebuyers in escrow with a pool on the property',
			"Buyer's agents wanting independent equipment + structural review",
			"Sellers wanting to pre-empt the buyer's inspection findings",
		),
		'deliverables'   => array(
			'On-site inspection of structure, equipment, plumbing, electrical, and bonding',
			'Written report within 24 hours (PDF, suitable to share with agent and lender)',
			'Photos of every finding with reference to the relevant code or manufacturer spec',
			'Estimated remaining life on every major component',
			'Repair cost ranges (we will quote you for the work, but you can shop the report)',
			'A direct "would I buy this pool" answer in plain English',
		),
		'process'        => array(
			array( 'n' => '01', 'title' => 'Schedule', 'body' => 'Book within 48 hours. We coordinate access with the listing agent or seller.' ),
			array( 'n' => '02', 'title' => 'On-site',  'body' => 'Senior inspector spends 60-90 minutes walking structure, equipment, plumbing, electrical, and bonding.' ),
			array( 'n' => '03', 'title' => 'Report',   'body' => 'Written PDF in your inbox within 24 hours. Photos, code refs, and remaining-life estimates per component.' ),
			array( 'n' => '04', 'title' => 'Q&A call', 'body' => '15-minute call to walk you through findings before you reply to the seller. Free, no upsell.' ),
		),
		'faqs'           => array(
			array( 'q' => 'How is this different from a general home inspection?', 'a' => 'General home inspectors scan a pool for 5-10 minutes and look at gross condition. We spend 60-90 minutes specifically on the pool: pressure-testing, opening equipment, checking bonding continuity, photographing structural cracks. Most general inspectors miss the bonding and structural issues we catch.' ),
			array( 'q' => 'Is the report independent from the construction line?', 'a' => 'Yes. The Mechanics line is firewalled from construction. We will tell you to walk away from a pool, even if it costs us a $80k construction job, when walking away is the right call. Our reputation is worth more than any single deal.' ),
			array( 'q' => 'Will the report stand up with my lender or home warranty?', 'a' => 'Yes. The report includes inspector credentials, methodology, and dated photos. Lenders and home warranty companies accept it as third-party documentation.' ),
		),
	),

	array(
		'slug'           => 'seasonal-inspection',
		'name'           => 'Seasonal Inspection',
		'short'          => 'Spring open · winter close',
		'price'          => 'Flat $245',
		'turnaround'     => 'Same-day verbal · 48-hour written',
		'duration'       => '45 minutes on-site',
		'lead'           => 'For pools that get used hard six months a year and sit closed the other six. Spring open and winter close inspections catch the small-becomes-expensive problems before they spread.',
		'who_for'        => array(
			'Second-home owners with seasonal pool use',
			'Owners who closed the pool last fall and want a clean spring open',
			'Owners winterizing for an extended absence (2-week trip or longer)',
		),
		'deliverables'   => array(
			'Full chemistry test and balance documentation',
			'Equipment runtime check on every powered component',
			'Skimmer, main drain, and return jet flow tests',
			'Cover inspection (if winterizing) and storage prep',
			'Written punch list of any items needing attention before next season',
			'Optional: scheduled re-check at 30 days for $95',
		),
		'process'        => array(
			array( 'n' => '01', 'title' => 'Schedule', 'body' => 'Book a spring open or fall close window. We work them into the existing route.' ),
			array( 'n' => '02', 'title' => 'On-site',  'body' => '45 minutes for the open or close. Chemistry, equipment runtime, structural visual.' ),
			array( 'n' => '03', 'title' => 'Punch list', 'body' => '48-hour written punch list with priority labels (urgent / this season / next season).' ),
			array( 'n' => '04', 'title' => 'Optional re-check', 'body' => '30-day follow-up if you want eyes on it again. Same-day-flat $95.' ),
		),
		'faqs'           => array(
			array( 'q' => 'Do I need to be home?', 'a' => 'No. Most seasonal customers leave a side gate code or a key. We send a 24-hour-before reminder text and a same-day text once we are wrapping up.' ),
			array( 'q' => 'What if my pool was closed by another company?', 'a' => 'Fine, we do not require continuity to inspect. We will note any items that look incomplete from the close and flag them in the punch list, not as a sales pitch.' ),
			array( 'q' => 'Can I bundle this with weekly service?', 'a' => 'Yes. Active weekly service customers get a free seasonal inspection in spring and a $99 fall close (vs. flat $245 retail). The discount comes from already being on the route.' ),
		),
	),

	array(
		'slug'           => 'equipment-diagnostics',
		'name'           => 'Equipment Diagnostics',
		'short'          => 'Full mechanical workup',
		'price'          => 'Flat $159',
		'turnaround'     => 'Verbal on-site · written next day',
		'duration'       => '60 minutes on-site',
		'lead'           => 'Something is wrong with the equipment but you do not know what. We diagnose the actual fault, document it with photos, and give you a written quote with parts pricing, separate from any repair work.',
		'who_for'        => array(
			'Owners with a pump, heater, or salt cell behaving badly',
			'Owners who got a quote elsewhere and want a second opinion',
			'New owners trying to understand what they inherited',
		),
		'deliverables'   => array(
			'On-site diagnostic with multimeter, pressure gauge, flow meter',
			'Photos of each component with serial numbers, in-service dates, runtime hours',
			'Written report identifying the actual fault and root cause',
			'Repair quote from us: you can shop it elsewhere or hire us, no pressure',
			'$159 inspection fee credits toward repair work if you hire us within 30 days',
		),
		'process'        => array(
			array( 'n' => '01', 'title' => 'Schedule', 'body' => 'Most diagnostics happen within 48 hours. Same-day for active service customers.' ),
			array( 'n' => '02', 'title' => 'On-site',  'body' => '60 minutes with the equipment running. We test, photograph, and identify the real fault.' ),
			array( 'n' => '03', 'title' => 'Verbal',   'body' => 'You get a verbal answer before we leave. No "we will get back to you" stalling.' ),
			array( 'n' => '04', 'title' => 'Written',  'body' => 'Full written report next business day with photos, root cause, and repair quote.' ),
		),
		'faqs'           => array(
			array( 'q' => 'My pump is loud and I think it is the motor. Why pay $159?', 'a' => 'Because half the time it is not the motor. Bearings, impeller damage, and stuck check valves all sound like a bad motor. The diagnostic prevents a $1,200 unnecessary motor swap.' ),
			array( 'q' => 'Will you tell me to fix something I do not need?', 'a' => 'No. We document what is broken and what is not. The report goes to you, not to a sales pipeline. If we hire you a fix, the inspection fee credits toward the work.' ),
			array( 'q' => 'Do you diagnose every brand?', 'a' => 'Pentair, Jandy: full authorized diagnostics. Hayward: yes. Sta-Rite, Polaris, Aqualink: yes for repair-track diagnostics, retrofit recommendations when honest. Older off-brand units we will tell you up front if a diagnostic is worth the $159.' ),
		),
	),

);

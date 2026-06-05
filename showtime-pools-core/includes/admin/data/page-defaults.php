<?php
/**
 * Default text content for every page template that uses post_meta-driven copy.
 *
 * Source of truth duplicates the inline `?: __('…')` fallbacks inside each
 * template (Approach A). When templates and this file drift, the template
 * fallbacks win at render time; the seeder writes from THIS file.
 *
 * Structure:
 *   - 'templates' => single-instance templates resolved by _wp_page_template.
 *   - 'iframe_kinds' => quote vs book defaults for the multi-instance iframe template.
 *   - 'options' => sitewide wp_options (Site Content team + creds rows).
 *
 * Multi-instance area + inspection pages are not in this file. They are seeded
 * at runtime from \Showtime\Areas::all() and \Showtime\Inspections::all().
 *
 * @package ShowtimePoolsCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	'templates' => array(

		// ─── About ────────────────────────────────────────────────────────────
		'page-about.php' => array(
			'about_h1'             => 'Complete pool care, start to finish.',
			'about_hero_eyebrow'   => 'About Showtime Pools',
			'about_hero_lead'      => 'Showtime Pools designs, builds, and transforms pools and outdoor spaces that elevate the way you live. Based in Los Angeles, we are the trusted name for homeowners, property managers, and businesses across Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills.',
			'about_eyebrow'        => 'Who we are',
			'about_wwa_title'      => 'Years of hands-on experience. Built on quality, transparency, and reliability.',
			'about_wwa_body'       => '',
			'about_photo_caption'  => 'Sherman Oaks shop · Ventura Boulevard',
			'about_values_title'   => 'Five commitments. Every project, every visit.',
			'about_values_eyebrow' => 'What we believe',
			'about_team_eyebrow'   => 'The team',
			'about_team_h2'        => 'Who actually shows up to your house.',
			'about_team_lead'      => 'Four people you will meet. The same four who own your project from the first call to the final walk-through.',
			'about_creds_eyebrow'  => 'Certifications & partnerships',
			'about_creds_h2'       => 'Manufacturer-certified. Trade-trained. Accountable.',
		),

		// ─── Service Areas Hub ─────────────────────────────────────────────────
		'page-areas.php' => array(
			'hero_eyebrow' => 'Where we work',
			'hero_lead'    => 'Pool service, pool cleaning, and pool repair across six West Valley and Westside neighborhoods. The same in-house tech every week — Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills. Custom construction and remodel work runs LA County-wide.',
			'outside_h2'   => 'Outside the route?',
			'outside_body' => 'New construction, full remodels, and inspections are available across LA County — Hancock Park, Pacific Palisades, Calabasas, Burbank, Glendale, Pasadena, Toluca Lake, Northridge, Granada Hills, and beyond. Weekly service is geographically restricted to keep the same-tech promise.',
		),

		// ─── Blog Hub ──────────────────────────────────────────────────────────
		'page-blog.php' => array(
			'hero_eyebrow'      => 'Pool insights · From the crew',
			'hero_lead'         => 'Practical pool knowledge from Steve and the crew. Real water, real equipment, real LA backyards.',
			'cats_h2'           => 'Three topics, written by people who do this every day.',
			'feed_h2'           => 'What we are writing about now.',
			'sidebar_cta_title' => 'Ready to talk to a real human?',
			'sidebar_cta_body'  => 'Get a free quote on a repair, remodel, or weekly service.',
		),

		// ─── Contact ───────────────────────────────────────────────────────────
		'page-contact.php' => array(
			'contact_eyebrow'           => 'Talk to us',
			'contact_h1'                => 'Talk to a real human at Showtime Pools.',
			'contact_lead'              => 'Send us a note and Steve or a senior tech replies within one business day. Same-day for active service customers.',
			'contact_form_title'        => 'Tell us about your pool.',
			'contact_sidebar_h2'        => 'Or reach us directly',
			'contact_existing_customer' => 'Already a service customer?',
			'contact_existing_body'     => 'Text the same number you used at sign-up — same-day priority is reserved for the route schedule.',
		),

		// ─── Founder ───────────────────────────────────────────────────────────
		'page-founder.php' => array(
			'founder_name'             => 'Steve Adams',
			'founder_title'            => 'Founder & CEO',
			'founder_eyebrow'          => 'The Founder',
			'founder_h1'               => 'A pool company, run like a small shop.',
			'founder_lead'             => 'Showtime Pools is owner-operated. The shop on Ventura Boulevard. The crew is W-2. The phone is a real phone, answered by a real person.',
			'founder_quote'            => 'If my name is on the warranty, my crew earns it on every job.',
			'founder_quote_attr'       => 'Steve Adams, Founder & CEO',
			'founder_story_h2'         => 'Founder, CEO, on every quote.',
			'founder_promises_eyebrow' => 'What you can expect',
			'founder_promises_h2'      => 'Three promises Steve signs his name on.',
			'founder_contact_eyebrow'  => 'Where to find Steve',
			'founder_contact_h2'       => 'The phone, the email, the shop.',
		),

		// ─── Inspections Hub ───────────────────────────────────────────────────
		'page-inspections.php' => array(
			'hero_lead'     => 'Pre-purchase, seasonal, and equipment-only inspections. Firewalled from the construction line so the report goes to you, not to a sales pipeline. We will tell you to walk away from a pool when walking away is the right call.',
			'types_eyebrow' => 'Three flavors',
			'types_h2'      => 'Pick the inspection that fits the moment.',
			'why_eyebrow'   => 'Why "Mechanics"',
			'why_h2'        => 'A separate line for inspections, by design.',
			'why_para1'     => 'Most pool inspections are done by the same companies that want to win the resulting repair contract. That conflict shapes the report. We split inspections into a separate line called Showtime Pools Mechanics so the report comes from a different P&L than the construction quote.',
			'why_para2'     => 'In practice this means we will document every fault, recommend repairs honestly, and tell you to walk away from a pool when walking away is the right call. Steve has personally killed deals worth tens of thousands of dollars in construction work because the pool was past saving. Independence is the whole point.',
		),

		// ─── Projects ──────────────────────────────────────────────────────────
		'page-projects.php' => array(
			'hero_eyebrow' => 'Recent work',
			'hero_lead'    => 'A full interactive map with photos, scope, and verified review per pin is rolling out. Until then, here are recent projects from across the route.',
		),

		// ─── Reviews ───────────────────────────────────────────────────────────
		// Reviews come from the live Google Reviews widget shortcode — see
		// inc/reviews-widget.php. Hero copy here is neutral so it never
		// hardcodes a rating or count that could drift from the live GBP.
		'page-reviews.php' => array(
			'hero_eyebrow' => 'Live from Google',
			'hero_lead'    => 'Live from our Google Business Profile — every review you see is published directly by the customer, with no editing on our side.',
		),

		// ─── Services Hub ──────────────────────────────────────────────────────
		'page-services-hub.php' => array(
			'hero_eyebrow'    => 'Twelve services, one team',
			'hero_lead'       => 'Pool repair, pool cleaning service, pool remodeling, pool installation, equipment upgrades, inspections, spa work, and outdoor living — handled by one supervised crew across Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills.',
			'core_eyebrow'    => 'Core services',
			'core_h2'         => 'Repairs, service, remodels, equipment, inspections, automation.',
			'core_lead'       => 'The day-to-day of pool ownership. Every one of these is on the route or in the shop right now.',
			'outdoor_eyebrow' => 'Outdoor living & custom',
			'outdoor_h2'      => 'New construction, spas, finishes, hardscape, kitchens, fire & water features.',
			'outdoor_lead'    => 'The bigger projects. Same crew, same standards, with engineered structure and permitting handled in-house.',
		),

		// ─── Affiliate / Partner Program ───────────────────────────────────────
		// Commission figures are intentionally soft (no hardcoded % or $) — a
		// public number is a business promise Steve owns. Edit in WP Admin →
		// Pages → Affiliate Program. Referral-source options are one-per-line.
		'page-affiliate.php' => array(
			'affiliate_hero_eyebrow'     => 'Now accepting referral partners',
			'affiliate_h1'               => 'Refer pool owners. Earn recurring income.',
			'affiliate_hero_lead'        => 'Join the Showtime Pools Partner Program. Every homeowner, property manager, or HOA you send our way earns you commission for as long as they stay on service — no chasing, no cold-selling, no cap.',
			'affiliate_hero_cta'         => 'Become a Partner',
			'affiliate_trust1'           => 'Paid monthly',
			'affiliate_trust2'           => 'No cap on referrals',
			'affiliate_trust3'           => 'Real-time tracking',
			'affiliate_trust4'           => 'Free to join',
			'affiliate_benefits_eyebrow' => 'Why partner with us',
			'affiliate_benefits_h2'      => 'Build a referral income that pays you every month.',
			'affiliate_benefits_lead'    => 'Earn predictable monthly commission from every customer you refer — without one-time payouts or constant selling. A scalable income stream that compounds as your referrals stay on service.',
			'affiliate_benefit1_title'   => 'Recurring Commission',
			'affiliate_benefit1_body'    => 'Earn commission every month your referral stays on a maintenance plan. Steady income that grows with your book of referrals.',
			'affiliate_benefit2_title'   => 'Real-Time Tracking',
			'affiliate_benefit2_body'    => 'See every referral, conversion, and payout in your partner dashboard. Always know exactly what you have earned.',
			'affiliate_benefit3_title'   => 'No Cap on Earnings',
			'affiliate_benefit3_body'    => 'Refer one pool owner or fifty — there is no limit. Scale your income as fast as you can send qualified leads our way.',
			'affiliate_benefit4_title'   => 'Fast Onboarding',
			'affiliate_benefit4_body'    => 'Apply in minutes and get your referral link the same day. We handle the quote, the work, and the warranty.',
			'affiliate_process_eyebrow'  => 'Process',
			'affiliate_process_h2'       => 'From referral to recurring income — here is how it works.',
			'affiliate_process_lead'     => 'Three simple steps. Sign up, share, and get paid every month your referral stays with us.',
			'affiliate_step1_title'      => 'Apply Free',
			'affiliate_step1_body'       => 'Fill out the form below. We review and send your unique referral link the same day. No cost to join.',
			'affiliate_step2_title'      => 'Refer Pool Owners',
			'affiliate_step2_body'       => 'Send homeowners, property managers, and HOAs our way through your link — or just have them mention your name.',
			'affiliate_step3_title'      => 'Earn Every Month',
			'affiliate_step3_body'       => 'Get paid for as long as your referral stays on service. True recurring income from work we deliver.',
			'affiliate_faq_eyebrow'      => 'Questions',
			'affiliate_faq_h2'           => 'Everything partners ask before they join.',
			'affiliate_faq1_q'           => 'How much can I earn?',
			'affiliate_faq1_a'           => 'You earn recurring commission every month your referral stays on a Showtime Pools maintenance plan, plus a share of qualifying repair and remodel work. We confirm your exact rate when you join.',
			'affiliate_faq2_q'           => 'Who makes a good referral?',
			'affiliate_faq2_a'           => 'Any pool owner in our service area — homeowners, property managers, HOAs, and home inspectors all refer to us. If they own or manage a pool in the LA area, they qualify.',
			'affiliate_faq3_q'           => 'When and how do I get paid?',
			'affiliate_faq3_a'           => 'Commissions are paid monthly once your referral is active and billed. You can track every referral and payout in your partner dashboard.',
			'affiliate_faq4_q'           => 'Does it cost anything to join?',
			'affiliate_faq4_a'           => 'No. The program is free to join. You apply, we approve, and you start referring the same day.',
			'affiliate_faq5_q'           => 'Do I need a website or audience?',
			'affiliate_faq5_a'           => 'No. A referral link helps, but plenty of partners simply introduce us by name. We just need a way to credit the referral to you.',
			'affiliate_form_eyebrow'     => 'Apply now',
			'affiliate_form_h2'          => 'Join the Partner Program',
			'affiliate_form_lead'        => 'Tell us how you plan to refer pool owners and we will get your partner link set up.',
			'affiliate_promote_options'  => "Realtor / real estate\nProperty manager\nHOA / community\nHome inspector\nExisting customer\nSocial media\nOther",
			'affiliate_submit_label'     => 'Activate My Partner Account',
			'affiliate_consent_text'     => 'I agree to the partner program terms and the commission payout policy.',
		),
	),

	// Multi-instance template: /quote/ and /book/. Defaults selected by the
	// page's _showtime_iframe_kind post_meta (set by the page seeder).
	'iframe_kinds' => array(

		'quote' => array(
			'iframe_eyebrow'     => 'Free quote',
			'iframe_title'       => 'Tell us about your project.',
			'iframe_lead'        => 'A few quick questions and Steve will get back to you with an itemized quote inside 48 hours.',
			'iframe_step1_title' => 'Tell us about the pool',
			'iframe_step1_body'  => 'Address, basic dimensions if you know them, and what you want done. Photos help but are not required.',
			'iframe_step2_title' => 'Free site visit',
			'iframe_step2_body'  => 'Steve walks the property within 2 to 3 business days. We measure, photograph the equipment pad, and listen.',
			'iframe_step3_title' => 'Itemized PDF inside 48 hours',
			'iframe_step3_body'  => 'Line-item pricing, materials, timeline, and warranty terms. No verbal estimates. No surprise upcharges.',
		),

		'book' => array(
			'iframe_eyebrow'     => 'Book an inspection',
			'iframe_title'       => 'Pick a time that works.',
			'iframe_lead'        => 'Inspections are 60 to 90 minutes on-site. You get a written report and recommended next steps within 24 hours.',
			'iframe_step1_title' => 'Choose inspection type',
			'iframe_step1_body'  => 'Pre-purchase, seasonal, or equipment-only. We will help you pick on the call if you are not sure.',
			'iframe_step2_title' => 'Lock a time on the calendar',
			'iframe_step2_body'  => 'Most inspections happen within 3 business days. Same-day available for active escrow timelines.',
			'iframe_step3_title' => 'Written report within 24 hours',
			'iframe_step3_body'  => 'Photos, code references, and a remaining-life estimate per major component. Lender and insurer ready.',
		),
	),

	// Sitewide wp_options read via \Showtime\Admin\ContentPage. The Site Content
	// admin UI exposes the same keys; this seeder pre-fills them with the
	// defaults declared in ContentPage::team_defaults() + creds_defaults().
	'options' => array(

		// Team: 4 members, 5 fields each (name, role, initials, note, href).
		'team' => array(
			1 => array( 'name' => 'Steve Adams', 'role' => 'Founder & CEO',          'initials' => 'SA', 'note' => 'On every quote, walks every site, pulls every permit personally. The phone you call rings on his desk.',     'href' => '/the-founder/' ),
			2 => array( 'name' => 'Viktor O',    'role' => 'Repair Manager',          'initials' => 'VO', 'note' => 'Runs the repair line. Diagnoses the failure before he quotes the fix. Pentair- and Jandy-certified.',        'href' => '' ),
			3 => array( 'name' => 'Felipe A',    'role' => 'Pool Service Technician', 'initials' => 'FA', 'note' => 'Senior route tech. Same customers every week. Photo report after every visit before he leaves the driveway.', 'href' => '' ),
			4 => array( 'name' => 'George C',    'role' => 'Senior Cleaner',          'initials' => 'GC', 'note' => 'Owns chemistry and detail. Tile-line wipe-down, full chemistry balance, equipment runtime check.',           'href' => '' ),
		),

		// Credentials: 4 entries, 2 fields each (title, body).
		'creds' => array(
			1 => array( 'title' => 'Pentair Authorized Service',     'body' => 'Manufacturer warranty pass-through on IntelliFlo, IntelliCenter, MasterTemp, and IC40 salt cells.' ),
			2 => array( 'title' => 'Jandy Authorized Service',       'body' => 'AquaLink, AquaPure, JXi heater, and Stealth pump warranty pass-through.' ),
			3 => array( 'title' => 'PebbleTec Certified Applicator', 'body' => 'Five-year written finish warranty backed by PebbleTec. Annual applicator training.' ),
			4 => array( 'title' => 'California Code Compliance',     'body' => 'Every permit, bonding inspection, and electrical sign-off pulled through LA County and city counters in-house.' ),
		),
	),
);

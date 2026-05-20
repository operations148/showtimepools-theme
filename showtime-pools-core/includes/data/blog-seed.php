<?php
/**
 * Blog seed data — 3 categories + 6 demo posts for the /blog/ hub.
 *
 * Categories: Pool Trends, Maintenance Tips, Equipment Guides.
 * Each post is paraphrased original content (not copied verbatim from
 * any competitor source) and aligned to the bundled blog_* photos and
 * Showtime brand voice.
 *
 * Editors can edit/delete/replace freely — these are seeds, not locks.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Data;

defined( 'ABSPATH' ) || exit;

return array(

	'categories' => array(
		array(
			'slug'        => 'pool-trends',
			'name'        => 'Pool Trends',
			'description' => 'What homeowners are choosing in 2026 — finishes, design moves, equipment, lifestyle decisions.',
		),
		array(
			'slug'        => 'maintenance-tips',
			'name'        => 'Maintenance Tips',
			'description' => 'Real, practical pool care from the crew that does it every week across LA.',
		),
		array(
			'slug'        => 'equipment-guides',
			'name'        => 'Equipment Guides',
			'description' => 'Pumps, heaters, salt cells, automation. What to buy, what to skip, what to upgrade.',
		),
	),

	'posts' => array(

		array(
			'slug'      => '2026-pool-design-trends-los-angeles',
			'title'     => '2026 pool design trends shaping Los Angeles backyards',
			'category'  => 'pool-trends',
			'excerpt'   => 'Earth-tone pebble finishes, vanishing edges, integrated spas, and minimalist hardscape are dominating new builds across Sherman Oaks and Encino. Here is what is driving the shift.',
			'content'   => <<<HTML
<p>Across Sherman Oaks, Encino, and Beverly Hills, the pools we are quoting in 2026 look almost nothing like the pools we were quoting in 2019. Three things are driving the shift, and homeowners can plan around them without guessing.</p>

<h2>Earth-tone pebble is taking over from bright blue</h2>
<p>White plaster used to be the default. Then it was Caribbean Blue PebbleSheen. Now it is warm grey, gentle green, and onyx-toned PebbleTec finishes that read more like natural water and less like a hotel pool. The shift is partly aesthetic and partly practical — earth-tone pebble hides surface stains, holds color through aggressive sun, and pairs with natural-stone coping instead of fighting it.</p>

<h2>Vanishing edges are no longer just for hillside lots</h2>
<p>We are seeing infinity-edge designs on flat lots in the valley because homeowners want a sightline from the kitchen to the water without a coping line breaking it. The engineering changes slightly — you need a catch basin and a separate equipment loop — but the cost difference on a new build is small enough that it is becoming a default-yes question instead of a default-no.</p>

<h2>Integrated spas, not bolt-on spas</h2>
<p>The old pattern was a separate round spa next to the pool. The new pattern is a spa that shares one wall with the pool, spills into it on a timer, and uses a single equipment pad. Cheaper to run, easier to automate, and looks like one cohesive water feature instead of two.</p>

<h2>What this means if you are remodeling</h2>
<p>If you are sitting on a 1990s pool with white plaster, blue waterline tile, and a separate spa, you have three high-ROI upgrades available before you spend on anything cosmetic: switch the finish to a current PebbleTec color, replace the waterline with a porcelain or glass tile that complements the new finish, and tie the spa into the pool's plumbing so they share the same automation. Total spend lands between fifteen and thirty-five thousand dollars on a typical valley pool, and the visual change is closer to a new pool than a remodel.</p>
HTML,
			'image_slot' => 'blog_trends',
		),

		array(
			'slug'      => 'why-pebble-finishes-replacing-plaster',
			'title'     => 'Why pebble finishes are replacing plaster on California remodels',
			'category'  => 'pool-trends',
			'excerpt'   => 'Plaster gets you five to seven years if you are lucky. Pebble gets you fifteen to twenty with the right water chemistry. The math is no longer close.',
			'content'   => <<<HTML
<p>If you call us for a remodel quote and we walk out without recommending pebble, something is wrong with the pool, not the finish. Here is why pebble has effectively replaced plaster on every quote we write.</p>

<h2>Lifespan, not cost-per-square-foot</h2>
<p>White plaster runs roughly twenty percent cheaper than a basic PebbleTec finish at install time. But plaster's realistic lifespan in Southern California water is five to seven years before staining, etching, and chip-out start showing up. PebbleTec — with even mediocre water chemistry — sits at fifteen to twenty. Once you amortize the install cost across the lifespan, pebble is the cheaper finish by a wide margin.</p>

<h2>Pebble is harder to stain — but not invincible</h2>
<p>The exposed-aggregate texture hides calcium scaling and metal staining that would be glaring on plaster. That said, pebble still benefits from a salt cell that does not overshoot the pH ceiling, and from a monthly hand-test of total alkalinity. Skip both and you will end up with a finish that still lasts but does not look its best at year ten.</p>

<h2>Color choice matters more than you think</h2>
<p>Cool Blue, Aqua White, Black Onyx, and Caribbean Blue all behave differently under California sun. Black Onyx holds heat — useful for solar gain, expensive for chillers. Aqua White makes the pool look bright and tropical but shows leaves and debris within hours. Cool Blue is the safe default for valley pools because it balances visual depth with stain-hiding without being too dark.</p>

<h2>The five-year warranty is real</h2>
<p>PebbleTec backs certified applicators with a written five-year finish warranty. We are certified, which means the warranty passes through to the homeowner. Most plaster jobs come with a one-year workmanship warranty and no manufacturer backing. That alone is worth more than the price difference.</p>
HTML,
			'image_slot' => 'blog_trends',
		),

		array(
			'slug'      => 'weekly-pool-care-checklist-la-homeowners',
			'title'     => 'A weekly pool care checklist for LA homeowners',
			'category'  => 'maintenance-tips',
			'excerpt'   => 'The eight things that actually matter every week. Doing these in order takes thirty minutes and prevents almost every problem that ends in an emergency phone call.',
			'content'   => <<<HTML
<p>Most pool problems are not surprises. They are slow drifts in chemistry, clogged baskets, and missed runtime that compound over weeks. Here is the checklist our crews run on every visit — homeowners can run the same one and save themselves a service call.</p>

<h2>1. Skim the surface</h2>
<p>Before anything else, get debris off the surface with a leaf rake. The longer leaves sit on the water, the more tannins they release and the harder your sanitizer has to work.</p>

<h2>2. Empty the skimmer and pump baskets</h2>
<p>A full skimmer basket starves the pump of water. A full pump basket starves it more. Empty both. If the pump basket is more than half full at the weekly check, your skimmer's not catching enough — investigate why.</p>

<h2>3. Brush the walls and waterline</h2>
<p>Two minutes with a nylon brush prevents algae from setting up shop. Pay extra attention to the waterline tile and the shadow side of the pool where sun hits least.</p>

<h2>4. Vacuum the bottom (every other visit)</h2>
<p>If you have a robot, run it. If you have a manual vacuum head, hook it up to the dedicated suction line and walk the bottom. Skipping vacuuming for three weeks straight is how a clean-looking pool turns into an algae outbreak overnight.</p>

<h2>5. Test free chlorine, pH, total alkalinity</h2>
<p>Use a drop kit, not test strips — strips drift. Free chlorine: 2 to 4 ppm. pH: 7.4 to 7.6. Total alkalinity: 80 to 120 ppm. If any of these is out, adjust before moving on.</p>

<h2>6. Check the filter pressure</h2>
<p>Note the pressure reading on the filter gauge. If it is more than 8 PSI above the clean-filter baseline, backwash (DE/sand) or hose down the cartridges. Filtering through a clogged filter wastes electricity and water.</p>

<h2>7. Run the pump long enough</h2>
<p>Rule of thumb: total pool volume should turn over once per day. For most LA backyard pools that is six to eight hours on a single-speed pump, or two to three hours on a variable-speed at high RPM plus longer at low. Cut runtime and you will see algae within a week.</p>

<h2>8. Walk around the equipment pad</h2>
<p>Listen for unusual noises. Look for water on the ground that should not be there. Check the heater's exterior for soot or scorching. Five seconds at the pad catches problems three days before they become emergencies.</p>
HTML,
			'image_slot' => 'blog_tips',
		),

		array(
			'slug'      => 'spring-pool-opening-step-by-step',
			'title'     => 'Spring pool opening — a step-by-step the right way',
			'category'  => 'maintenance-tips',
			'excerpt'   => 'LA pools rarely close fully, but the seasonal reset still matters. Here is the eight-step opening sequence that prevents an algae bloom in week three.',
			'content'   => <<<HTML
<p>Most LA homeowners never fully close their pool. What we call a spring opening here is really a deep reset — the moment to undo a winter of low runtime and slightly relaxed chemistry. Done right, it sets the pool up for a clean summer. Done wrong, you fight algae until July.</p>

<h2>Step one — clear and clean</h2>
<p>Before anything else, get the cover off (if any), skim, brush every surface aggressively, and vacuum to waste if you have any visible sediment. Starting clean is non-negotiable.</p>

<h2>Step two — equipment inspection</h2>
<p>Open the pump and filter, check seals and O-rings, inspect the impeller through the basket port, and prime the pump before you turn it on. Run for an hour and watch for leaks at unions.</p>

<h2>Step three — full chemistry test</h2>
<p>Do not just check free chlorine. Test free chlorine, total chlorine (the gap tells you if you have combined chloramines), pH, total alkalinity, calcium hardness, cyanuric acid, and total dissolved solids if you can. The CYA test is the one most homeowners skip — it controls how much chlorine you actually need.</p>

<h2>Step four — shock to break point</h2>
<p>If combined chlorine is above 0.5 ppm, shock to ten times that level. Use cal-hypo unless you are running a salt cell, in which case use sodium hypochlorite (liquid). Run the pump 24 hours after.</p>

<h2>Step five — adjust pH and alkalinity</h2>
<p>Get total alkalinity into the 80–120 ppm window first. Adjust pH to 7.4–7.6 after. Doing it in the other order causes you to chase your own tail.</p>

<h2>Step six — clean the salt cell</h2>
<p>If you are on salt, pull the cell, inspect for calcium buildup, soak in a 1:4 muriatic-to-water acid bath until bubbling stops (usually two to ten minutes), rinse, reinstall. Skip this and you will replace a six-hundred-dollar cell two years before you needed to.</p>

<h2>Step seven — set runtime up</h2>
<p>Add at least two hours per day to your off-season runtime. Sun hits the pool harder, water heats up, organic load increases — runtime has to keep up.</p>

<h2>Step eight — retest in 48 hours</h2>
<p>Confirm everything held. If chlorine dropped fast, you probably have CYA too low (sun burning chlorine off) or organic load too high (more brushing and vacuuming needed). Adjust and move on.</p>
HTML,
			'image_slot' => 'blog_tips',
		),

		array(
			'slug'      => 'pentair-vs-jandy-salt-systems',
			'title'     => 'Choosing between Pentair and Jandy salt systems',
			'category'  => 'equipment-guides',
			'excerpt'   => 'Both make great salt cells. The choice usually comes down to what is already on your pad, your automation system, and how often you want to think about the cell.',
			'content'   => <<<HTML
<p>When a homeowner asks us to recommend a salt system, our answer almost always depends on what is already on the equipment pad. Here is how we think about it, and the trade-offs that decide the call.</p>

<h2>If you have Pentair automation, get a Pentair cell</h2>
<p>An IC40 (or IC60 for larger pools) plumbed into a Pentair IntelliCenter or EasyTouch reads salt level, cell life, and output level directly from the controller. No second app, no separate diagnostic light, no homeowner-confused mismatch between two systems. Stay in the Pentair ecosystem if you can.</p>

<h2>If you have Jandy automation, get a Jandy cell</h2>
<p>Same logic in reverse. AquaPure pairs natively with AquaLink and reports through the same iAquaLink app. Mixing brands works, but it adds a layer of inconsistency that homeowners notice the first time the salt cell shuts down and they cannot figure out which app to open.</p>

<h2>If your pad is mixed-brand or has nothing yet, lean Pentair</h2>
<p>Slightly broader installer base in Los Angeles, slightly easier to source replacement parts, slightly better reliability on the diagnostic LED. None of these are dealbreakers — but if all else is equal, Pentair wins our pick by a small margin.</p>

<h2>Cell life is roughly the same — chemistry is not</h2>
<p>Both manufacturers rate cells around 10,000 hours. Real-world lifespan depends almost entirely on whether you keep pH below 7.8 and acid-clean the cell once or twice a year. Skip those and you will see four-year cell death whether the badge says Pentair or Jandy.</p>

<h2>What we install most often</h2>
<p>Our standard salt swap quote for a valley pool is a Pentair IC40 paired with an IntelliFlo3 VS pump, plumbed into an IntelliCenter controller. Runs about $3,800 installed including the cell, three days from quote to commissioning. We are certified for warranty pass-through on both Pentair and Jandy, so either choice gets the manufacturer warranty.</p>
HTML,
			'image_slot' => 'blog_equipment',
		),

		array(
			'slug'      => 'when-to-replace-your-pool-pump',
			'title'     => 'When to replace your pool pump — five signs to act',
			'category'  => 'equipment-guides',
			'excerpt'   => 'A failing pump rarely dies in one moment. It nags you for months. Here are the five signals worth listening to before you find yourself without filtration in July.',
			'content'   => <<<HTML
<p>Pool pumps almost never fail catastrophically. They warn you. The homeowners who get caught flat-footed in the middle of summer are the ones who missed three months of small signals. Here is what to watch for.</p>

<h2>1. Louder than it used to be</h2>
<p>A new pump runs around 65 dB. If yours is now closer to a dishwasher in distress, the motor bearings are giving up. You probably have six to twelve months of runtime left.</p>

<h2>2. Tripping the breaker</h2>
<p>If the pump trips the breaker on startup or mid-cycle, the motor is drawing too much amperage. Could be a capacitor (cheap fix) or a winding short (replace the pump).</p>

<h2>3. Losing prime more often</h2>
<p>Suction-side leaks make a pump lose prime. So does a worn impeller. So does a cracked pump basket lid. If you find yourself opening the pump lid to refill it more than once a week, get it diagnosed.</p>

<h2>4. Energy bill creep</h2>
<p>Single-speed pumps are now banned in California for residential pools larger than a hot tub. If you are still running one, your replacement quote is a variable-speed pump that uses a quarter of the energy and lasts longer. Either you replace it on your terms or the next inspection forces you to.</p>

<h2>5. Visible rust or moisture at the seal</h2>
<p>Wet streaks below the pump, rust on the bolts that hold the motor to the wet end, or condensation on the motor housing — all signs the shaft seal is failing. The seal itself is a cheap part, but once it leaks, water gets into the motor and you are looking at a full replacement within weeks.</p>

<h2>What we install most often</h2>
<p>Pentair IntelliFlo3 VSF for most valley pools, Jandy ePump VS for Jandy automation pads. Both pull around 1.8–2.0 amps at low speed. Standard install is half a day for a like-for-like swap, full day if we are also rebuilding the suction-side plumbing. Variable-speed quotes typically include the LADWP rebate paperwork so you get the rebate credited in week six instead of chasing it yourself.</p>
HTML,
			'image_slot' => 'blog_equipment',
		),
	),
);

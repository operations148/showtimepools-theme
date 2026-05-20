/**
 * Photo curation + optimization pipeline.
 *
 * Reads CURATION map (source path → dest slug), runs sharp on each:
 *   - resize to 1600px wide (max), preserve aspect
 *   - export .webp @ q80
 *   - export .jpg  @ q82 (mozjpeg flavor)
 *
 * Output goes to ../showtime-pools-child/assets/img/{slot}.{webp,jpg}
 *
 * Run:  node tools/optimize-photos.mjs
 */
import sharp from "sharp";
import { existsSync, mkdirSync, statSync, readFileSync } from "node:fs";
import { dirname, join } from "node:path";

// Phase E — read slot→file map from tools/photos.json. The picker HTML at
// tools/image-picker.html writes this file; we read it. Underscore-prefixed
// keys (_comment, _root) are ignored. Falls back to the hardcoded CURATION
// constant below if photos.json is missing or unreadable.
const MANIFEST_PATH = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\tools\\photos.json";
let MANIFEST_CURATION = null;
if (existsSync(MANIFEST_PATH)) {
	try {
		const raw = readFileSync(MANIFEST_PATH, "utf8").replace(/^﻿/, "");
		const parsed = JSON.parse(raw);
		const filtered = {};
		for (const [k, v] of Object.entries(parsed)) {
			if (!k.startsWith("_") && typeof v === "string" && v.length) filtered[k] = v;
		}
		if (Object.keys(filtered).length > 0) MANIFEST_CURATION = filtered;
		console.log(`Loaded ${Object.keys(filtered).length} slot picks from photos.json`);
	} catch (e) {
		console.warn(`Failed to parse photos.json: ${e.message} — falling back to hardcoded CURATION.`);
	}
}

// Phase F — Drive is now read-zero. All sourcing happens from a local
// staging folder under Downloads. To repopulate staging, run a manual
// PowerShell copy or open the Drive in Explorer and drag-drop. The
// optimize pipeline and the picker never touch the Drive directly.
const SRC = "C:\\Users\\dogom\\Downloads\\showtime-staging";
const DEST = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\showtime-pools-child\\assets\\img";

/**
 * Slot → source-relative-path
 * Slot becomes the filename: {slot}.webp + {slot}.jpg
 * Matches the imagery.php local-file resolver.
 *
 * Source: Steve's CANONICAL "Showtime Pools NN.jpg" portfolio set —
 * 88 files he pre-curated and numbered, spread across the topic
 * folders. These are the "publish-ready" shots; previous Phase C
 * pulled raw largest-file picks from each folder which often landed
 * on in-progress jobsite shots (bug). This map fixes that.
 */
const CURATION = {
  // Homepage hero — most premium finished pool shot from curated set.
  "hero": "POOLS/Showtime Pools 47.jpg",

  // About hero — different finished-pool angle for visual variety.
  "about_hero": "POOLS/Showtime Pools 50.jpg",

  // Inspections backdrop — diagnostic-feeling pool shot.
  "inspections_bg": "POOLS/Showtime Pools 09.jpg",

  // Lifestyle slots — five distinct finished-pool shots.
  "lifestyle_main": "POOLS/Showtime Pools 48.jpg",
  "lifestyle_1": "POOLS/Showtime Pools 49.jpg",
  "lifestyle_2": "POOLS/Showtime Pools 51.jpg",
  "lifestyle_3": "POOLS/Showtime Pools 52.jpg",
  "lifestyle_4": "POOLS/Showtime Pools 70.jpg",

  // 12 services — each from the canonical set, topic-aligned via folder.
  "service_pool-repairs-plumbing": "REPAIR/Showtime Pools 07.jpg",
  "service_weekly-pool-maintenance": "WEEKLY CLEANING/Showtime Pools 24.jpg",
  "service_pool-remodeling-resurfacing": "REMODELING/Showtime Pools 26.jpg",
  "service_equipment-installation-upgrades": "EQUIPMENT & PLUMBING/Showtime Pools 79.jpg",
  "service_pool-inspections-diagnostics": "POOLS/Showtime Pools 11.jpg",
  "service_smart-pool-automation": "EQUIPMENT & PLUMBING/Showtime Pools 80.jpg",
  "service_custom-pool-design-construction": "POOLS/Showtime Pools 36.jpg",
  "service_spa-installation-renovations": "SPA/Showtime Pools 20.jpg",
  "service_tile-coping-plaster-decking": "POOLS/Showtime Pools 84.jpg",
  "service_outdoor-living-hardscape": "POOLS/Showtime Pools 12.jpg",
  "service_outdoor-kitchens-bbq": "POOLS/Showtime Pools 13.jpg",
  "service_fire-water-features": "POOLS/Showtime Pools 35.jpg",

  // 6 service areas — each a finished pool shot, one per neighborhood.
  "area_sherman-oaks": "POOLS/Showtime Pools 30.jpg",
  "area_encino": "POOLS/Showtime Pools 35.jpg",
  "area_beverly-hills": "POOLS/Showtime Pools 36.jpg",
  "area_studio-city": "POOLS/Showtime Pools 39.jpg",
  "area_tarzana": "POOLS/Showtime Pools 42.jpg",
  "area_woodland-hills": "POOLS/Showtime Pools 43.jpg",

  // 8 projects — largest curated POOLS shots used as portfolio hero
  // images when the project has no featured image set in WP admin.
  "project_1": "POOLS/Showtime Pools 47.jpg",
  "project_2": "POOLS/Showtime Pools 48.jpg",
  "project_3": "POOLS/Showtime Pools 49.jpg",
  "project_4": "POOLS/Showtime Pools 50.jpg",
  "project_5": "POOLS/Showtime Pools 51.jpg",
  "project_6": "POOLS/Showtime Pools 52.jpg",
  "project_7": "POOLS/Showtime Pools 18.jpg",
  "project_8": "POOLS/Showtime Pools 19.jpg",

  // Blog category covers.
  "blog_trends": "POOLS/Showtime Pools 27.jpg",
  "blog_tips": "WEEKLY CLEANING/Showtime Pools 53.jpg",
  "blog_equipment": "EQUIPMENT & PLUMBING/Showtime Pools 17.jpg",
  "blog_default": "POOLS/Showtime Pools 06.jpg",
};

// Photos used as backgrounds/heroes/lifestyles keep extra width.
// Cards (services, areas) are smaller in layout, so render smaller too.
const WIDE_SLOTS = new Set([
  "hero", "about_hero", "inspections_bg",
  "lifestyle_main", "lifestyle_1", "lifestyle_2", "lifestyle_3", "lifestyle_4",
  "blog_trends", "blog_tips", "blog_equipment", "blog_default",
  "project_1", "project_2", "project_3", "project_4",
  "project_5", "project_6", "project_7", "project_8",
]);
const WIDE_WIDTH = 1400;
const CARD_WIDTH = 960;
const WEBP_QUALITY = 70;
const JPG_QUALITY = 72;

if (!existsSync(DEST)) mkdirSync(DEST, { recursive: true });

// Live curation comes from photos.json when present, hardcoded fallback otherwise.
const LIVE_CURATION = MANIFEST_CURATION ?? CURATION;

let okCount = 0;
let errCount = 0;
let totalBytes = 0;

for (const [slot, srcRel] of Object.entries(LIVE_CURATION)) {
  const srcPath = join(SRC, srcRel.replace(/\//g, "\\"));
  if (!existsSync(srcPath)) {
    console.error(`MISS ${slot}  ←  ${srcRel}`);
    errCount++;
    continue;
  }
  const webpOut = join(DEST, `${slot}.webp`);
  const jpgOut = join(DEST, `${slot}.jpg`);

  try {
    const meta = await sharp(srcPath).metadata();
    const targetWidth = WIDE_SLOTS.has(slot) ? WIDE_WIDTH : CARD_WIDTH;
    const resize = meta.width > targetWidth ? { width: targetWidth } : null;

    const buf = await (resize
      ? sharp(srcPath).rotate().resize(resize).toBuffer()
      : sharp(srcPath).rotate().toBuffer());

    await sharp(buf).webp({ quality: WEBP_QUALITY, effort: 4 }).toFile(webpOut);
    await sharp(buf).jpeg({ quality: JPG_QUALITY, mozjpeg: true }).toFile(jpgOut);

    const wSize = statSync(webpOut).size;
    const jSize = statSync(jpgOut).size;
    totalBytes += wSize + jSize;
    console.log(`OK   ${slot.padEnd(45)}  ${(wSize/1024).toFixed(0)}KB webp + ${(jSize/1024).toFixed(0)}KB jpg`);
    okCount++;
  } catch (e) {
    console.error(`FAIL ${slot}  ${e.message}`);
    errCount++;
  }
}

console.log(`\nDone. ${okCount} ok, ${errCount} err, ${(totalBytes/1024/1024).toFixed(2)} MB total.`);

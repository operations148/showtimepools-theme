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
import { existsSync, mkdirSync, statSync } from "node:fs";
import { dirname, join } from "node:path";

const SRC = "C:\\Official Drive\\Showtime\\My Drive\\IT'S SHOWTIME!";
const DEST = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\showtime-pools-child\\assets\\img";

/**
 * Slot → source-relative-path
 * Slot becomes the filename: {slot}.webp + {slot}.jpg
 * Matches the imagery.php local-file resolver.
 */
const CURATION = {
  // Homepage hero
  "hero": "POOLS/IMG-20260325-WA0053.jpg",

  // About / founder
  "about_hero": "REMODELING/IMG-20260302-WA0143.jpg",
  "inspections_bg": "TASKS/POOL INSPECTIONS & DIAGNOSTICS/IMG-20260208-WA0045.jpg",

  // Lifestyle (existing slots)
  "lifestyle_main": "POOLS/IMG-20260325-WA0093.jpg",
  "lifestyle_1": "POOLS/IMG-20260324-WA0094.jpg",
  "lifestyle_2": "POOLS/IMG-20260204-WA0192.jpg",
  "lifestyle_3": "POOLS/IMG-20260413-WA0070.jpg",
  "lifestyle_4": "POOLS/IMG-20260325-WA0051.jpg",

  // 12 services
  "service_pool-repairs-plumbing": "TASKS/POOL REPAIRS & PLUMBING/IMG-20260324-WA0238.jpg",
  "service_weekly-pool-maintenance": "TASKS/WEEKLY POOL MAINTENANCE/IMG-20251106-WA0024.jpg",
  "service_pool-remodeling-resurfacing": "TASKS/POOL REMODELING, RESURFACING & FINISHES/IMG-20260324-WA0114.jpg",
  "service_equipment-installation-upgrades": "TASKS/EQUIPMENT INSTALLATION & UPGRADES/IMG-20260204-WA0285.jpg",
  "service_pool-inspections-diagnostics": "TASKS/POOL INSPECTIONS & DIAGNOSTICS/IMG-20260125-WA0140.jpg",
  "service_smart-pool-automation": "TASKS/SMART POOL AUTOMATION UPGRADES/IMG-20260204-WA0458.jpg",
  "service_custom-pool-design-construction": "TASKS/CUSTOM POOL DESIGN & NEW CONSTRUCTION/IMG-20260302-WA0036.jpg",
  "service_spa-installation-renovations": "TASKS/SPA INSTALLATION & RENOVATIONS/IMG-20260204-WA0064.jpg",
  "service_tile-coping-plaster-decking": "TASKS/TILE, COPING, PLASTER & DECKING/IMG-20260208-WA0082.jpg",
  "service_outdoor-living-hardscape": "TASKS/OUTDOOR LIVING & HARDSCAPE/IMG-20260131-WA0016.jpg",
  "service_outdoor-kitchens-bbq": "TASKS/OUTDOOR KITCHENS & BBQ AREAS/IMG-20260306-WA0082.jpg",
  "service_fire-water-features": "TASKS/FIRE FEATURES & WATER FEATURES/IMG-20260203-WA0047.jpg",

  // 6 service areas (lifestyle pool shots, distinct from above)
  "area_sherman-oaks": "REMODELING/IMG-20260302-WA0136.jpg",
  "area_encino": "REMODELING/IMG-20260302-WA0139.jpg",
  "area_beverly-hills": "REMODELING/IMG-20260324-WA0112.jpg",
  "area_studio-city": "REMODELING/IMG-20260302-WA0144.jpg",
  "area_tarzana": "REMODELING/IMG-20260324-WA0113.jpg",
  "area_woodland-hills": "REMODELING/IMG-20260302-WA0142.jpg",

  // 8 featured/projects gallery (REMODELING after-shots)
  "project_1": "REMODELING/IMG-20260309-WA0011.jpg",
  "project_2": "REMODELING/IMG-20260208-WA0079.jpg",
  "project_3": "REMODELING/IMG-20260324-WA0114.jpg",
  "project_4": "REMODELING/IMG-20260324-WA0225.jpg",
  "project_5": "REMODELING/IMG-20260324-WA0345.jpg",
  "project_6": "TASKS/POOL REMODELING, RESURFACING & FINISHES/IMG-20260208-WA0079.jpg",
  "project_7": "TASKS/TILE, COPING, PLASTER & DECKING/IMG-20260302-WA0035.jpg",
  "project_8": "TASKS/CUSTOM POOL DESIGN & NEW CONSTRUCTION/IMG-20260208-WA0082.jpg",

  // Blog category covers
  "blog_trends": "REMODELING/IMG-20260302-WA0143.jpg",
  "blog_tips": "TASKS/WEEKLY POOL MAINTENANCE/IMG-20251106-WA0024.jpg",
  "blog_equipment": "TASKS/EQUIPMENT INSTALLATION & UPGRADES/IMG-20260204-WA0285.jpg",
  "blog_default": "POOLS/IMG-20260325-WA0093.jpg",
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

let okCount = 0;
let errCount = 0;
let totalBytes = 0;

for (const [slot, srcRel] of Object.entries(CURATION)) {
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

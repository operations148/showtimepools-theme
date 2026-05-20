/**
 * Drive image catalog + static HTML picker generator.
 *
 * Walks `C:\Official Drive\Showtime\My Drive\IT'S SHOWTIME!\`, classifies
 * every image, and writes two artifacts:
 *
 *   1. tools/catalog.json   — full inventory with topical category tags.
 *      Not committed (gitignored as a local cache).
 *
 *   2. tools/image-picker.html — static HTML page that displays candidate
 *      thumbnails per slot. Open via file:// or http://localhost. Click
 *      a candidate to mark it; hit "Export photos.json" to dump the
 *      manifest you paste into tools/photos.json.
 *
 * The picker uses file:// URLs to render the Drive thumbnails directly
 * — no server-side thumbnail proxy needed. Open the HTML in Chrome /
 * Firefox by double-clicking it in Explorer, or via your local dev URL
 * once placed under the theme's admin-tools/ folder.
 *
 * Run:  node tools/catalog-photos.mjs
 */

import { readdirSync, statSync, writeFileSync, readFileSync, existsSync } from "node:fs";
import { join, relative, sep } from "node:path";

// Phase F — Drive is now read-zero. Walk the local staging folder under
// Downloads. To refresh staging, copy candidate folders from the Drive
// via Explorer or PowerShell; this script and the optimize pipeline
// never read the Drive directly.
const SRC      = "C:\\Users\\dogom\\Downloads\\showtime-staging";
const OUT_JSON = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\tools\\catalog.json";
const OUT_HTML = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\tools\\image-picker.html";
const PHOTOS   = "C:\\xampp\\htdocs\\showtimepools\\showtimepools\\tools\\photos.json";

const IMAGE_EXT = /\.(jpg|jpeg|png|webp|heic)$/i;
const SKIP_FOLDERS = new Set(["TASKS"]); // TASKS is a meta-folder of subfolders; subfolders are walked anyway

function walk(dir, root) {
	const out = [];
	let entries;
	try { entries = readdirSync(dir, { withFileTypes: true }); } catch { return out; }
	for (const e of entries) {
		const full = join(dir, e.name);
		if (e.isDirectory()) {
			out.push(...walk(full, root));
		} else if (e.isFile() && IMAGE_EXT.test(e.name)) {
			try {
				const st = statSync(full);
				out.push({
					rel:  relative(root, full).replace(/\\/g, "/"),
					full: full,
					name: e.name,
					size: st.size,
					mtime: st.mtimeMs,
				});
			} catch { /* skip */ }
		}
	}
	return out;
}

function categorize(file) {
	const parts = file.rel.split("/");
	const folder = parts[0] || "";
	const sub = parts.length > 2 ? parts[1] : "";
	if (/^Showtime Pools \d+\.(jpg|jpeg|png)$/i.test(file.name)) {
		return { topic: "curated", folder, sub };
	}
	if (folder === "OTHERS" && sub === "Per Address") {
		return { topic: "address", folder, sub: parts[2] || "" };
	}
	if (folder === "TASKS") {
		return { topic: "service", folder, sub };
	}
	if (folder === "REMODELING")        return { topic: "remodel",   folder, sub };
	if (folder === "POOLS")              return { topic: "pool",      folder, sub };
	if (folder === "REPAIR")             return { topic: "repair",    folder, sub };
	if (folder === "SPA")                return { topic: "spa",       folder, sub };
	if (folder === "WEEKLY CLEANING")    return { topic: "cleaning",  folder, sub };
	if (folder === "EQUIPMENT & PLUMBING") return { topic: "equipment", folder, sub };
	if (folder === "EQUIPMENT PHOTOS")   return { topic: "equipment-catalog", folder, sub };
	if (folder === "Reggie")             return { topic: "team",      folder, sub };
	return { topic: "raw", folder, sub };
}

console.log(`Walking ${SRC} ...`);
const files = walk(SRC, SRC);
for (const f of files) {
	const c = categorize(f);
	f.topic  = c.topic;
	f.folder = c.folder;
	f.sub    = c.sub;
}
console.log(`Found ${files.length} image files`);

writeFileSync(OUT_JSON, JSON.stringify({
	scanned_at: new Date().toISOString(),
	root: SRC,
	total: files.length,
	files,
}, null, 2));
console.log(`Catalog written → ${OUT_JSON}`);

// Build candidate buckets per slot.
const photos = existsSync(PHOTOS)
	? JSON.parse(readFileSync(PHOTOS, "utf8").replace(/^﻿/, ""))
	: {};

const SERVICE_FOLDER_MAP = {
	"service_pool-repairs-plumbing":           "POOL REPAIRS & PLUMBING",
	"service_weekly-pool-maintenance":         "WEEKLY POOL MAINTENANCE",
	"service_pool-remodeling-resurfacing":     "POOL REMODELING, RESURFACING & FINISHES",
	"service_equipment-installation-upgrades": "EQUIPMENT INSTALLATION & UPGRADES",
	"service_pool-inspections-diagnostics":    "POOL INSPECTIONS & DIAGNOSTICS",
	"service_smart-pool-automation":           "SMART POOL AUTOMATION UPGRADES",
	"service_custom-pool-design-construction": "CUSTOM POOL DESIGN & NEW CONSTRUCTION",
	"service_spa-installation-renovations":    "SPA INSTALLATION & RENOVATIONS",
	"service_tile-coping-plaster-decking":     "TILE, COPING, PLASTER & DECKING",
	"service_outdoor-living-hardscape":        "OUTDOOR LIVING & HARDSCAPE",
	"service_outdoor-kitchens-bbq":            "OUTDOOR KITCHENS & BBQ AREAS",
	"service_fire-water-features":             "FIRE FEATURES & WATER FEATURES",
};

function candidatesForSlot(slot) {
	let list = [];

	// Slot-specific bucket priorities
	if (slot.startsWith("service_")) {
		const taskSub = SERVICE_FOLDER_MAP[slot];
		list = files.filter(f => f.folder === "TASKS" && f.sub === taskSub);
		// also include curated topic-matching shots
		if (slot === "service_pool-repairs-plumbing")            list = list.concat(files.filter(f => f.topic === "curated" && f.folder === "REPAIR"));
		else if (slot === "service_weekly-pool-maintenance")     list = list.concat(files.filter(f => f.topic === "curated" && f.folder === "WEEKLY CLEANING"));
		else if (slot === "service_pool-remodeling-resurfacing") list = list.concat(files.filter(f => f.topic === "curated" && f.folder === "REMODELING"));
		else if (slot === "service_equipment-installation-upgrades" || slot === "service_smart-pool-automation")
			list = list.concat(files.filter(f => f.topic === "curated" && f.folder === "EQUIPMENT & PLUMBING"));
		else if (slot === "service_spa-installation-renovations") list = list.concat(files.filter(f => f.topic === "curated" && f.folder === "SPA"));
	}
	else if (slot.startsWith("project_") || slot === "hero" || slot === "about_hero" || slot.startsWith("lifestyle_") || slot.startsWith("area_") || slot.startsWith("blog_") || slot === "inspections_bg") {
		// Curated POOLS shots first, then any address-keyed projects
		list = files.filter(f => f.topic === "curated" && f.folder === "POOLS");
		if (slot.startsWith("project_")) {
			list = list.concat(files.filter(f => f.topic === "address"));
			list = list.concat(files.filter(f => f.topic === "remodel"));
		}
	}
	else {
		list = files.filter(f => f.topic === "curated");
	}

	// Dedupe + cap
	const seen = new Set();
	const unique = [];
	for (const f of list) {
		if (seen.has(f.rel)) continue;
		seen.add(f.rel);
		unique.push(f);
		if (unique.length >= 60) break;
	}
	return unique;
}

// Generate the HTML picker.
const slots = Object.keys(photos).filter(k => !k.startsWith("_"));

function fileUrl(rel) {
	// file:// URLs need forward slashes and properly-encoded segments.
	// Drive path has spaces & ! — must encode each segment.
	const segments = rel.split("/").map(s => encodeURIComponent(s));
	const root = SRC.replace(/\\/g, "/").split("/").map(s => encodeURIComponent(s)).join("/");
	return "file:///" + root + "/" + segments.join("/");
}

const html = `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Showtime Pools — Image Picker</title>
<style>
:root {
  --c-ink: #0B1733;
  --c-muted: #5b6680;
  --c-bg: #F1F7FF;
  --c-card: #fff;
  --c-aqua: #1E5DFF;
  --c-border: rgba(11,23,51,0.08);
}
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0; padding: 0;
  font: 14px/1.45 -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
  background: var(--c-bg); color: var(--c-ink);
}
header {
  position: sticky; top: 0; z-index: 10;
  background: #fff; border-bottom: 1px solid var(--c-border);
  padding: 14px 24px;
  display: flex; align-items: center; justify-content: space-between;
  box-shadow: 0 2px 10px rgba(11,23,51,.04);
}
header h1 { font-size: 16px; font-weight: 700; margin: 0; }
header .actions { display: flex; gap: 10px; }
button {
  background: var(--c-aqua); color: #fff;
  border: 0; padding: 8px 18px;
  border-radius: 999px; font-weight: 600;
  cursor: pointer; font-size: 13px;
}
button.secondary { background: #fff; color: var(--c-aqua); border: 1.5px solid var(--c-aqua); }
button:hover { transform: translateY(-1px); }
main { padding: 24px; max-width: 1600px; margin: 0 auto; }
.slot {
  background: var(--c-card); border: 1px solid var(--c-border);
  border-radius: 20px; padding: 18px;
  margin-bottom: 24px;
  box-shadow: 0 4px 18px -8px rgba(11,23,51,.06);
}
.slot__head {
  display: flex; align-items: center; gap: 16px;
  padding-bottom: 14px; margin-bottom: 16px;
  border-bottom: 1px solid var(--c-border);
}
.slot__name {
  font-family: monospace; font-size: 14px;
  background: var(--c-bg); padding: 4px 10px; border-radius: 6px;
  color: var(--c-aqua); font-weight: 600;
}
.slot__current {
  display: flex; align-items: center; gap: 12px; margin-left: auto;
  font-size: 12px; color: var(--c-muted);
}
.slot__current img {
  width: 60px; height: 40px; object-fit: cover;
  border-radius: 6px; border: 2px solid var(--c-aqua);
}
.slot__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 10px;
}
.thumb {
  position: relative;
  border-radius: 10px; overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: transform .15s ease, border-color .15s ease;
}
.thumb img {
  width: 100%; aspect-ratio: 4/3; object-fit: cover;
  display: block; background: #ddd;
}
.thumb:hover { transform: translateY(-2px); border-color: rgba(30,93,255,.4); }
.thumb.is-selected { border-color: var(--c-aqua); box-shadow: 0 0 0 4px rgba(30,93,255,.18); }
.thumb__cap {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: linear-gradient(180deg, transparent, rgba(0,0,0,.7));
  color: #fff; padding: 18px 8px 6px;
  font-size: 10px; line-height: 1.2;
  word-break: break-all;
}
.export-panel {
  position: fixed; bottom: 20px; right: 20px;
  background: #0B1733; color: #fff;
  padding: 18px; border-radius: 14px;
  max-width: 440px; box-shadow: 0 20px 50px -10px rgba(11,23,51,.4);
  display: none;
}
.export-panel textarea {
  width: 100%; height: 200px;
  font-family: monospace; font-size: 11px;
  background: #061027; color: #fff; border: 1px solid rgba(255,255,255,.1);
  border-radius: 8px; padding: 10px; resize: vertical;
}
.export-panel .row { display: flex; gap: 8px; margin-top: 10px; }
.export-panel button { font-size: 12px; }
.legend { color: var(--c-muted); font-size: 13px; margin-bottom: 20px; }
</style>
</head>
<body>
<header>
  <h1>Showtime Pools — Image Picker (${slots.length} slots, ${files.length} candidates)</h1>
  <div class="actions">
    <button class="secondary" onclick="showExport()">Export photos.json</button>
    <button onclick="resetLocal()">Reset to defaults</button>
  </div>
</header>
<main>
<p class="legend">
  Click a thumbnail to pick it for that slot. Picks save to your browser localStorage immediately.
  When done, hit <strong>Export photos.json</strong>, copy the JSON, paste it into
  <code>tools/photos.json</code>, then run <code>node tools/optimize-photos.mjs</code> to rebuild.
</p>

${slots.map(slot => {
	const candidates = candidatesForSlot(slot);
	const current = photos[slot] || "";
	return `<section class="slot" data-slot="${slot}">
  <div class="slot__head">
    <span class="slot__name">${slot}</span>
    <div class="slot__current" data-current-cap>
      <img data-current-thumb src="${current ? fileUrl(current) : ""}" alt="">
      <span data-current-name>${current}</span>
    </div>
  </div>
  <div class="slot__grid">
${candidates.map(c => `    <div class="thumb${c.rel === current ? " is-selected" : ""}" data-rel="${c.rel}" onclick="pick(this)">
      <img src="${fileUrl(c.rel)}" loading="lazy" alt="">
      <div class="thumb__cap">${c.rel}</div>
    </div>`).join("\n")}
  </div>
</section>`;
}).join("\n")}

</main>
<div class="export-panel" id="ep">
  <h3 style="margin:0 0 10px;font-size:14px">Paste this into <code>tools/photos.json</code></h3>
  <textarea id="ep-text" readonly></textarea>
  <div class="row">
    <button onclick="copyExport()">Copy</button>
    <button class="secondary" onclick="document.getElementById('ep').style.display='none'">Close</button>
  </div>
</div>
<script>
const DEFAULTS = ${JSON.stringify(photos, null, 2)};
const STORE_KEY = "showtime_image_picker_v1";

function getPicks() {
  try { return JSON.parse(localStorage.getItem(STORE_KEY)) || {}; } catch { return {}; }
}
function setPicks(p) { localStorage.setItem(STORE_KEY, JSON.stringify(p)); }

function pick(el) {
  const slot = el.closest(".slot").dataset.slot;
  const rel  = el.dataset.rel;
  const picks = getPicks();
  picks[slot] = rel;
  setPicks(picks);

  el.closest(".slot").querySelectorAll(".thumb").forEach(t => t.classList.remove("is-selected"));
  el.classList.add("is-selected");

  const head = el.closest(".slot").querySelector(".slot__current");
  head.querySelector("[data-current-thumb]").src = el.querySelector("img").src;
  head.querySelector("[data-current-name]").textContent = rel;
}

function buildOutput() {
  const picks = getPicks();
  const merged = { ...DEFAULTS, ...picks };
  return JSON.stringify(merged, null, 2);
}

function showExport() {
  document.getElementById("ep-text").value = buildOutput();
  document.getElementById("ep").style.display = "block";
}
function copyExport() {
  const ta = document.getElementById("ep-text");
  ta.select(); document.execCommand("copy");
  navigator.clipboard?.writeText(ta.value).catch(()=>{});
  ta.focus();
}
function resetLocal() {
  if (confirm("Reset all picks to defaults?")) {
    localStorage.removeItem(STORE_KEY);
    location.reload();
  }
}

// Restore picks on load
window.addEventListener("DOMContentLoaded", () => {
  const picks = getPicks();
  Object.entries(picks).forEach(([slot, rel]) => {
    const section = document.querySelector('.slot[data-slot="' + slot + '"]');
    if (!section) return;
    const thumb = section.querySelector('.thumb[data-rel="' + rel.replace(/"/g, '\\\\"') + '"]');
    section.querySelectorAll(".thumb").forEach(t => t.classList.remove("is-selected"));
    if (thumb) {
      thumb.classList.add("is-selected");
      const head = section.querySelector(".slot__current");
      head.querySelector("[data-current-thumb]").src = thumb.querySelector("img").src;
      head.querySelector("[data-current-name]").textContent = rel;
    }
  });
});
</script>
</body>
</html>
`;

writeFileSync(OUT_HTML, html);
console.log(`Picker HTML written → ${OUT_HTML}`);
console.log(`Open in browser: file:///${OUT_HTML.replace(/\\/g, "/")}`);

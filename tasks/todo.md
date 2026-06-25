# Showtime Pools — Build Plan

Live build plan for showtimepools.com. Tracks every phase, every deliverable, every checkpoint.

**Working directory:** `C:\xampp\htdocs\showtimepools\showtimepools\`

---

# ACTIVE — Four Fixes (2026-06-25)

Backup branch: `backup/four-fixes-pre-20260625` (from clean main). No push.
One concern per commit. Prove each with local screenshots.

## FIX 1 — /contact/ → GHL form tH1eoDpRA4hMEb04GgzX (native form broken in prod)
- [ ] Add CMS field `showtime_ghl_contact_url` to GHL Forms settings (class-settings-page.php), default = new const `SHOWTIME_CONTACT_FORM_URL` (tH1).
- [ ] page-contact.php: replace `<form class="contact-form">…</form>` (112-191) with clean GHL iframe embed (mirror page-iframe.php `iframe-frame__wrap`, id = bare form id for form_embed.js auto-size). No double-card.
- [ ] Append UTM (website / organic / contact_form) to embed URL so n8n still sees source.
- [ ] Enqueue form_embed.js on page-contact.php; stop enqueuing contact.js on /contact/ (keep /shop/).
- [ ] Leave ContactController REST handler dormant (registered, unused). Keep contact.css.
- [ ] Verify render + submit. DECISION asked re: homepage duplicate.

## FIX 2 — popup modal too small
- [ ] components.css `.stp-popup__dialog`: max-width 460→600px desktop, max-height 90vh, full-ish mobile, keep overflow:auto.
- [ ] Bump `.stp-popup__embed` + iframe min-height. Accessibility (focus trap/ESC) unchanged. Verify fit.

## FIX 3 — reviews 9 cards, no carousel (Trustindex)
- [ ] Honest assessment delivered; dashboard layout setting is the clean way. No hardcoded reviews. DECISION asked.

## FIX 4 — NO_LCP diagnosis (old live code; error new)
- [ ] Hero already eager + fetchpriority + preload. No un-deployed regression. NO_LCP+FCP+TBT = PSI lab-load failure signature (transient/live-side).
- [ ] Defensive: explicit `loading="eager"` on hero img + poster. Verify.

---

**Deliverable structure:**
```
showtimepools/
├── showtime-pools-child/        # child theme, deploys to /wp-content/themes/
├── showtime-pools-core/         # custom plugin, deploys to /wp-content/plugins/
├── tasks/
│   ├── todo.md                  # this file
│   └── lessons.md               # corrections + patterns
├── DEPLOY.md                    # deploy instructions for user
└── CLAUDE.md                    # workflow contract (existing)
```

**Division of labor:**
- User owns: Cloudways provisioning, DNS, SSL, plugin license purchases, Steve-facing brand confirmations
- Claude owns: 100% of code (theme, plugin, templates, REST endpoints, JS, schema, design system)

---

## Phase 1A — Infrastructure (USER ONLY, skip in code)
- [x] N/A — user provisions Cloudways + WP install

## Phase 1B — Child theme + core plugin skeleton (DONE)

- [x] `showtime-pools-child/style.css` — theme header, parent ref to Blocksy
- [x] `showtime-pools-child/functions.php` — bootstrap, includes `inc/*`
- [x] `showtime-pools-child/inc/enqueue.php` — parent + child styles, deferred JS, async fonts
- [x] `showtime-pools-child/inc/theme-setup.php` — theme support, image sizes, menus
- [x] `showtime-pools-child/inc/blocksy-overrides.php` — Blocksy filter hooks
- [x] `showtime-pools-child/inc/security.php` — disable XML-RPC, hide WP version, login hardening
- [x] `showtime-pools-child/inc/performance.php` — defer non-critical CSS, preconnect hints
- [x] `showtime-pools-child/screenshot.txt` — placeholder note
- [x] `showtime-pools-child/README.md` — install + edit guidelines
- [x] `showtime-pools-core/showtime-pools-core.php` — plugin header, PSR-4-style autoloader, activation hooks
- [x] `showtime-pools-core/uninstall.php` — clean uninstall (remove options, preserve CPTs)
- [x] `showtime-pools-core/includes/class-plugin.php` — singleton bootstrap
- [x] `showtime-pools-core/includes/cpt/` — folder ready
- [x] `showtime-pools-core/includes/rest/` — folder ready
- [x] `showtime-pools-core/includes/integrations/` — folder ready
- [x] `showtime-pools-core/includes/admin/class-settings-page.php` — Showtime Pools settings UI w/ GHL fields
- [x] `showtime-pools-core/README.md`
- [x] `DEPLOY.md`

## Phase 1C — Design tokens + global styles + style guide (DONE, awaiting CHECKPOINT 1)

- [x] `showtime-pools-child/assets/css/tokens.css` — color, type, space, radius, shadow, motion, layer, layout
- [x] `showtime-pools-child/assets/css/base.css` — resets, base elements, fluid type
- [x] `showtime-pools-child/assets/css/utilities.css` — container, stack, grid utilities
- [x] `showtime-pools-child/assets/css/blocks.css` — Gutenberg + Blocksy block overrides
- [x] `showtime-pools-child/assets/css/components.css` — buttons, cards, badges, form fields, alerts
- [x] `showtime-pools-child/assets/css/editor.css` — block editor styles
- [x] `showtime-pools-child/page-style-guide.php` — full design system showcase
- [ ] CHECKPOINT 1 → user reviews `/style-guide/`, approves or requests color/type swaps

## Phase 1D — Header + footer (DONE)
- [x] `showtime-pools-child/header.php` — full WP head + utility bar + primary nav
- [x] `showtime-pools-child/footer.php`
- [x] `showtime-pools-child/template-parts/header/utility-bar.php`
- [x] `showtime-pools-child/template-parts/header/site-branding.php`
- [x] `showtime-pools-child/template-parts/header/primary-nav.php`
- [x] `showtime-pools-child/template-parts/header/cta-button.php`
- [x] `showtime-pools-child/template-parts/header/mobile-drawer.php`
- [x] `showtime-pools-child/template-parts/footer/footer-main.php`
- [x] `showtime-pools-child/template-parts/footer/footer-legal.php`
- [x] `showtime-pools-child/template-parts/footer/local-business-schema.php`
- [x] `showtime-pools-child/assets/js/header.js`
- [x] `showtime-pools-child/assets/css/header.css`, `footer.css`

## Phase 1E — Homepage (11 sections, DONE)
- [x] `showtime-pools-child/front-page.php` — orchestrator with `showtime/home_sections` filter
- [x] All 11 `template-parts/home/section-*.php` files
- [x] `showtime-pools-child/assets/css/home.css`
- [x] `showtime-pools-child/assets/js/home.js`

## Phase 1F — 8 service pages (DONE)
- [x] `showtime-pools-core/includes/data/services.php` — 8-service registry, single source of truth (slugs aligned to homepage links)
- [x] `showtime-pools-core/includes/class-services.php` — `Showtime\Services::all/get/related/slugs`, memoized
- [x] `showtime-pools-core/includes/admin/class-page-seeder.php` — idempotent admin Tools UI + admin-post handler
- [x] Seeder wired into `Showtime\Plugin::register()`
- [x] `showtime-pools-child/acf-json/group_service_meta.json` — 7 fields (hero_summary, price, turnaround, disclaimer, includes, faqs, related_areas), auto-loaded
- [x] `showtime-pools-child/page-service.php` — single template, registry+ACF merge, `showtime/service_sections` filter
- [x] `showtime-pools-child/template-parts/service/section-hero.php` — breadcrumbs, brand gradient, dual CTA
- [x] `showtime-pools-child/template-parts/service/section-includes.php` — 6-up check-bullet grid
- [x] `showtime-pools-child/template-parts/service/section-process.php` — 4-step horizontal flow
- [x] `showtime-pools-child/template-parts/service/section-pricing.php` — soft "starting at" + turnaround pair
- [x] `showtime-pools-child/template-parts/service/section-faq.php` — `<details>` accordion, mirrors home pattern
- [x] `showtime-pools-child/template-parts/service/section-related.php` — 3 related cards + Phase 2A `do_action` hook
- [x] `showtime-pools-child/template-parts/service/section-cta.php` — full-width close-out
- [x] `showtime-pools-child/template-parts/service/schema.php` — Service + FAQPage JSON-LD, `provider.@id` references LocalBusiness
- [x] `showtime-pools-child/assets/css/service.css` — token-only, hero gradient, includes grid, pricing card, related cards
- [x] `inc/enqueue.php` — `is_page_template('page-service.php')` branch

**Runtime verified on local XAMPP preview at http://localhost/showtimepools/wp/**
- All 9 pages seeded (parent /services/ + 8 children) and HTTP 200
- Service template renders all 7 sections + JSON-LD Service + JSON-LD FAQPage
- service.css enqueues only on service template
- PHP lint clean on all 14 new/modified files
- ACF JSON validates clean

## Phase 1G — Inspections hub + 3 inspection pages (DONE, sub-brand)
- [x] `showtime-pools-core/includes/data/inspections.php` — 3 inspection types, single source of truth
- [x] `showtime-pools-core/includes/class-inspections.php` — memoized helper
- [x] `showtime-pools-child/page-inspections.php` — Mechanics hub (charcoal/amber)
- [x] `showtime-pools-child/page-inspection.php` — single inspection detail template
- [x] 3 child pages seeded: pre-purchase, seasonal, equipment-diagnostics
- [x] Sub-brand visual: charcoal background, amber accent, distinct from brand-blue
- [x] Service + FAQPage JSON-LD per inspection page

## Phase R — Visual redesign + content fillout (DONE this turn)

**Brief from user:** "your design is too generic! please redesign it, and i want all pages with content, make sure all of that, a fully functinoal website. now the the GHL, let work that out soon, since the priority is to make this website up and running"

**Approach:** Three color anchors (brand-blue + accent-aqua + warm sand/cream + charcoal/amber for inspections). Founder-led voice ("Steve answers the phone. Steve's name is on the warranty."). Asymmetric magazine layouts. Real numbers (CSLB #985241, 1,824 pools, $2M insurance). Pentair / Jandy / PebbleTec / Raypak named specifically. Six neighborhoods called out by name with local conditions and street references.

**Tokens added:** sand 50-500, amber 50-600, fs-7xl

**Sections rewritten (homepage):**
- Hero — asymmetric grid, layered SVG pool with animated waves + shimmer, anchored "4.9 Google" + "23yrs" floating badges, founder-led copy with gradient accent on second line, locale chip with pulsing dot, microcopy strip
- Trust bar — cream/sand surface with oversized tabular numerals (5xl), credentials line
- Services — 2 featured (brand-blue cards) + 6 standard with 01-08 numerals, sweep underline on hover
- Map preview — stylized SVG of SF Valley with 6 named neighborhoods, blue/green/orange pin clusters, "1,824 pools" overlay
- Featured projects — 3 magazine cards with neighborhood pill, scope/finish/duration/value meta, animated wave overlay
- Process — ink section with curved SVG path threading 4 numbered nodes
- Inspections callout — charcoal/amber sub-brand with mock inspection report card showing PASS/AGE/FAIL pills (Pentair, Raypak, Hayward TCELL15, etc.)
- Reviews — scroll-snap track with 6 quote cards (oversized opening quote, avatar initials, source pill)
- Service areas — 6-card grid pulled from registry, gradient backgrounds, neighborhood pill, pool count
- CTA banner — split layout: phone on left (oversized tabular), quote CTA on right, vertical divider

**New pages built (all with substantive content):**
- `/about/` — Steve story, 6 values, 4-person team, 8 credentials
- `/projects/` — 9-project gallery placeholder (Phase 2A replaces with Mapbox)
- `/reviews/` — 12-review wall (Phase 2B replaces with GBP CPT)
- `/service-areas/` + 6 children — registry-driven with local copy, conditions, common jobs, sample streets, area-served Service schema per page
- `/pool-inspections/` + 3 children — Mechanics sub-brand, registry-driven, Service + FAQPage JSON-LD
- `/privacy/`, `/terms/` — long-form legal copy via shared page-legal.php
- `404.php` — branded "Pool not found" with shortcuts

**Files added/changed:** 25+ new templates + assets, 1 shared `interior.css`, 1 rewritten `home.css`, tokens extended

**Runtime verified at http://localhost/showtimepools/wp/:**
- 22 / 22 valid URLs return 200 (homepage, /about/, /projects/, /reviews/, /service-areas/ + 6, /pool-inspections/ + 3, /services/ + 8, /contact/, /quote/, /book/, /privacy/, /terms/)
- Bad URLs return 404 (and render the branded 404 template)
- Specific brand/credential/place keywords appear 25+ times on homepage alone
- All new design language classes render (svc-card--featured, trust-bar__num, inspections-callout__report, review-card__quote-mark, area-card__pill, process__path, map-preview__svg, cta-banner__phone)

## Phase 1H — Contact + GHL redirects (DONE)

**Architecture:** Native HTML form on /contact/ posts to `/wp-json/showtime/v1/contact`, which forwards to GHL via the central `Showtime\Integrations\Ghl::forward()`. When FluentForms is later installed, the FF→GHL bridge routes every FF submission through the same forwarder. One outbox, two inputs.

- [x] `showtime-pools-core/includes/integrations/class-ghl.php` — central forwarder, optional HMAC signing, payload filter, structured logging
- [x] `showtime-pools-core/includes/integrations/class-fluent-forms.php` — `fluentform/submission_inserted` + legacy hook → Ghl::forward
- [x] `showtime-pools-core/includes/rest/class-contact-controller.php` — POST /wp-json/showtime/v1/contact w/ nonce + honeypot + 2-sec timestamp + 5/hour IP rate limit + field validation + sanitization
- [x] Settings page: GHL Quote URL + GHL Inspection Booking URL fields (sanitize_callback=esc_url_raw)
- [x] `showtime-pools-child/page-contact.php` — hero + two-column form/info + map embed + hours; CTA banner reused
- [x] `showtime-pools-child/page-iframe.php` — generic GHL iframe template; slug→option mapping; empty-state fallback w/ phone CTA
- [x] `showtime-pools-child/assets/css/contact.css` — token-only, contact + iframe page styles
- [x] `showtime-pools-child/assets/js/contact.js` — vanilla submit, inline field errors, top-level alerts, double-submit guard
- [x] `inc/enqueue.php` — contact.css on contact + iframe templates; contact.js on contact only
- [x] Page seeder extended with `static_pages()` group; default status bumped to `publish`
- [x] Plugin::register wires REST controller + FF bridge

**Runtime verified on local preview:**
- /contact/ → 200, form renders, contact.css + contact.js enqueued
- /quote/, /book/ → 200, iframe-hero renders, fallback alert shown (URLs unset locally)
- POST /wp-json/showtime/v1/contact:
  - Bad nonce → 403 `rest_forbidden`
  - Invalid fields → 422 with per-field error map
  - Honeypot tripped → 200 silent-OK (doesn't tip off bots)
  - Valid POST → 200 OK + `forwarded_ok:false` (URL unset; GHL forward gracefully no-ops)

**Out of scope (intentional):** FluentForms config JSON. Once Steve provisions FF Pro on Cloudways and builds the forms in the FF UI, the FF→GHL bridge auto-handles them. Exporting an FF config without FF installed locally is empty.

## Phase 1I — AI chat widget (CHECKPOINT 2 after this)
- [ ] `showtime-pools-core/includes/integrations/openai-assistant.php`
- [ ] `showtime-pools-core/includes/rest/chat-controller.php`
- [ ] `showtime-pools-core/includes/cpt/class-chat-log.php`
- [ ] `showtime-pools-child/assets/js/chat-widget.js` (~150 lines vanilla)
- [ ] `showtime-pools-child/assets/css/chat-widget.css`
- [ ] Rate limiting via transients (20/session, 100/IP/day)
- [ ] Lead capture handoff to GHL after 3 msgs or pricing question
- [ ] CHECKPOINT 2 → user reviews chat behavior

## Phase 2A — Project CPT + Mapbox map
- [ ] `showtime-pools-core/includes/cpt/class-project.php` (full CPT registration)
- [ ] ACF JSON: `project_meta` (lat, lng, service_type, completion_date, client_name, gallery, before_photos, after_photos, description, linked_review)
- [ ] Custom taxonomies: `service-category`, `neighborhood`
- [ ] `showtime-pools-core/includes/rest/projects-geojson-controller.php` → `/wp-json/showtime/v1/projects-geojson`
- [ ] `showtime-pools-child/page-projects.php` — full Mapbox map page
- [ ] `showtime-pools-child/assets/js/mapbox-map.js` (~250 lines)
- [ ] `showtime-pools-child/assets/css/mapbox-map.css`
- [ ] Three pin layers (jobs blue, reviews green, offices orange)
- [ ] Filter chips, side panel, clustering, fullscreen, reset
- [ ] Mapbox token in WP options table, exposed via REST nonce-gated endpoint to frontend

## Phase 2B — Review CPT + GBP import
- [ ] `showtime-pools-core/includes/cpt/class-review.php`
- [ ] `showtime-pools-core/includes/integrations/gbp-import.php`
- [ ] WP cron: pull GBP reviews daily, dedupe by review_id
- [ ] Review schema markup
- [ ] Reviews carousel component (reusable)

## Phase 2C — Gallery
- [ ] `showtime-pools-core/includes/cpt/class-gallery-item.php`
- [ ] Taxonomies: `service-type`, `pool-style`
- [ ] `showtime-pools-child/page-gallery.php` filterable + lightbox

## Phase 2D — 6 service area pages
- [ ] `showtime-pools-core/includes/cpt/class-service-area.php` (or pages w/ ACF, decide on impl)
- [ ] 6 pages: sherman-oaks, encino, beverly-hills, studio-city, tarzana, woodland-hills
- [ ] Each: unique 600+ word copy, mini-map of local projects, local reviews block, neighborhood-scoped LocalBusiness schema, internal links to nearby areas

## Phase 2E — About + blog (CHECKPOINT 3 after this)
- [ ] `showtime-pools-child/page-about.php` (Steve story, team, certs, values)
- [ ] `showtime-pools-child/single.php` blog single (TOC, related, schema)
- [ ] `showtime-pools-child/archive.php` blog archive
- [ ] CHECKPOINT 3 → full content + IA review

## Phase 3 — Pre-launch QA + LIVE (CHECKPOINT 4)
- [ ] Schema validation (Schema.org validator + Google Rich Results Test)
- [ ] Core Web Vitals audit (target ≥90 mobile, ≥98 desktop)
- [ ] Sitemap submission GSC + Bing
- [ ] OTTO pixel install
- [ ] Rank Tracker baseline keywords loaded
- [ ] Wordfence final scan
- [ ] DNS cutover
- [ ] CHECKPOINT 4 → LIVE

---

## Phase REBRIEF — Brand-level reset per Denz's comprehensive brief (IN PROGRESS)

**Trigger:** User sent comprehensive brief swapping foundational decisions. Old design + content was built against an outdated brief and needs a reset, not a polish pass.

**Reference:** Astra "Brikly Construction" demo for VISUAL STYLE ONLY (https://websitedemos.net/brikly-construction-company-04/). Real menu/services/team/copy comes from showtimepoolservice.com + showtimepoolmechanics.com. NOT migrating those, rebuilding fresh on showtimepools.com.

**Stack swap:** Blocksy → Astra parent. Native Gutenberg only. No Elementor/Bricks/Divi.

**Brand tokens (LOCKED):**
- Display: Playfair Display 400/500 (Google Fonts) — was Plus Jakarta Sans
- Body: Inter 400/500/600 — unchanged
- Palette: ink #0A0A0A, off-white #F5F2ED, white #FFF, charcoal #1F1F1F, pool aqua #5C8A9E, sand #C9BFB1, hairline rgba(10,10,10,0.12) — was brand-blue/aqua/sand
- Typography rules: H1 Playfair 400 56-72px tight, H2 Playfair 400 40-48px, H3 Playfair 500 24-28px, body Inter 400 16-18px line-height 1.65, eyebrows Inter 600 11-12px tracking 0.12-0.18em uppercase

**Real content (LOCKED, from existing sites):**
- Tagline: "Stop juggling contractors. One team handles it all."
- Subline: "Repairs • Weekly Service • Remodels • New Equipment"
- Positioning: "Complete Pool Care, Start to Finish"
- Trust pillars: Licensed & Insured / Clear Timelines / Quality Workmanship
- 3-step process: Request Free Assessment → We Assess + Provide Options → Expert Execution
- Phone: (323) 825-2099
- Offices: 15301 Ventura Blvd Sherman Oaks 91403 (main), 1925 Century Park East Ste 1700 LA 90067 (Century City), 9461 Charleville Blvd #1902 Beverly Hills 90212 (BH)
- Hours: Mon-Sat 8a-5p, Sun by appt for emergencies

**Menu (LOCKED, 5 top-level):**
- Home
- About (dropdown: About Us, The Founder, Blog Insights, Recent Articles & Updates)
- Services
- Contact
- Shop (placeholder)
- Header CTA: "Get a Free Quote" → /quote/
- Header phone click-to-call: (323) 825-2099

**Services (LOCKED, 12 total, replaces old 8):**
1. Pool Repairs & Plumbing
2. Weekly Pool Maintenance
3. Pool Remodeling, Resurfacing & Finishes
4. Equipment Installation & Upgrades
5. Pool Inspections & Diagnostics
6. Smart Pool Automation Upgrades
7. Custom Pool Design & New Construction
8. Spa Installation & Renovations
9. Tile, Coping, Plaster & Decking
10. Outdoor Living & Hardscape
11. Outdoor Kitchens & BBQ Areas
12. Fire Features & Water Features

**Team (LOCKED, real 4):**
- Steve Adams, Founder & CEO
- Viktor O, Repair Manager
- Felipe A, Pool Service Technician
- George C, Senior Cleaner

**Reset checklist:**
- [ ] Tokens: swap palette to ink/off-white/pool-aqua/sand
- [ ] Tokens: swap display font from Plus Jakarta Sans to Playfair Display
- [ ] enqueue.php: swap Google Fonts request to Playfair Display + Inter
- [ ] style.css: change parent Template from blocksy to astra
- [ ] enqueue.php: remove Blocksy parent stylesheet dependency, run standalone
- [ ] Install Astra free locally (parent theme on Cloudways will be Astra)
- [ ] Hero copy: "Stop juggling contractors. One team handles it all." + subline
- [ ] Trust pillars section: Licensed & Insured / Clear Timelines / Quality Workmanship
- [ ] Process section: 3-step Brikly flow (Request → Assess → Execute)
- [ ] Services registry: rewrite from 8 to 12 services per brief
- [ ] About page: real Steve story + 4 real team members
- [ ] Phone: replace (818) 555-POOL → (323) 825-2099 sitewide
- [ ] Addresses: 3 office locations in footer + LocalBusiness schema
- [ ] Hours: Mon-Sat 8a-5p, Sun emergencies
- [ ] Primary nav: 5-item menu with About dropdown
- [ ] Footer: real social links (FB, IG, GBP, LI, TT, YT)
- [ ] Fix hero badges escaping container on narrow widths
- [ ] Fix lifestyle grid overflow on tablet
- [ ] Fix any other canvas-escape bugs found during smoke test
- [ ] Delete old service pages (8 slugs), re-seed 12 new pages
- [ ] Re-sync to live preview, smoke test all URLs

---

## Decisions made (senior-dev defaults, user can override)

- **Brand colors:** placeholder palette (deep ocean blue + aqua + warm off-white). Token-driven, swappable in 30 sec from `tokens.css` once Steve confirms.
- **Type pairing:** Plus Jakarta Sans (display) + Inter (body), both Google Fonts, self-hosted via WP Rocket optimization. Premium feel, free, ubiquitous browser support.
- **Logo:** placeholder SVG wordmark. Drop-in real logo when Steve sends.
- **Custom plugin name:** `showtime-pools-core`. Houses CPTs, REST, integrations. Child theme handles presentation only. Clean separation per WP best practice (theme stays portable, business logic survives theme swap).
- **GHL webhook URL:** stored in `wp_options` under `showtime_ghl_webhook_url`, settable via core plugin admin page. No hardcoding.
- **OpenAI key:** stored in `wp_options` under `showtime_openai_api_key`, server-side only, never in JS.
- **Mapbox token:** stored in `wp_options`, exposed only via nonce-protected REST endpoint to frontend.

---

## Phase C — Full Dynamic + Photos + /blog/ + Schema + Design Polish (DONE)

**Trigger:** User asked for a complete audit, dynamic conversion of every hardcoded copy block, bundled photography (no manual /uploads/ work), a content sub-page, dynamic Schema.org, and a final design polish — all in one sweep.

**Delivered:**

- **Photo bundle** — 38 photos curated from the user's Google Drive (`C:\Official Drive\Showtime\My Drive\IT'S SHOWTIME!\`), optimized via sharp-cli to WebP@q70 + JPG@q72 at 1400px (wide) / 960px (card). Total bundle ~28MB. Saved to `showtime-pools-child/assets/img/{hero, about_hero, inspections_bg, lifestyle_*, service_<slug>, area_<slug>, project_1..8, blog_*}.{webp,jpg}`. The existing `showtime_image()` slot resolver picks them up automatically. Added `showtime_picture()` helper for native `<picture>` markup w/ WebP source + JPG fallback.
- **ACF Page Copy options page** — `group_site_page_copy.json` exposes hero/inspections/about/founder/contact/credentials copy + image fields under Site Content → Page Copy. Templates read with PHP fallbacks so the site renders correctly even if ACF is deactivated or every field is blank.
- **Hardcoded copy refactor** — `section-01-hero`, `section-07-inspections-callout`, `page-about` (Who we are + Value cards repeater), `page-contact` (hero + form + offices + hours + map), `page-founder` (full rebuild as 6-section ACF-driven narrative with story blocks repeater + pullquote + promises strip), `template-parts/service/section-hero` (now uses `service_<slug>` bundled photo + dark overlay). 23+ hardcoded blocks moved to ACF.
- **Dynamic LocalBusiness JSON-LD** — `template-parts/footer/local-business-schema.php` fully rewritten. Name, telephone, email, sameAs from Customizer filters; address parsed from ACF `offices` repeater (regex splits "City, ST ZIP" → locality/region/postal); opening hours parsed from ACF `hours_rows`; aggregateRating + numberOfEmployees + founder from ACF Page Copy → Trust tab. Branch offices emit separate LocalBusiness nodes with `branchOf` references. **Schema is now also actually wired into footer.php** (the schema file existed but was never `get_template_part`-included).
- **Project CPT** — `\Showtime\Cpt\Project` registers `project` post type + `project_service` taxonomy. Single posts at /projects/<slug>/. ACF `group_project_meta.json` covers neighborhood, finish, scope, value, duration, before/after images, gallery, client quote. Section-05 + page-projects.php now query the CPT directly (orderby menu_order) with `apply_filters('showtime/image/slot_for_project',...)` to align project N → `project_N` bundled photo. Soft fallback grid renders if zero projects exist.
- **Project seed registry** — `showtime-pools-core/includes/data/projects.php` ships 8 demo projects matching the 8 bundled before/after-ish photos. Seeder writes them into the CPT on first activation, copying registry fields into post meta. Idempotent on slug.
- **/blog/ hub + archive + single + post seeds** — `page-blog.php` (Pinch A Penny-style: hero + 3-category card strip + featured + grid + sidebar + CTA card), `archive.php` (category/tag/author archive w/ pagination), `single.php` (sticky TOC sidebar + Article JSON-LD + BreadcrumbList JSON-LD + related posts + share row). 3 categories (Pool Trends / Maintenance Tips / Equipment Guides) + 6 demo posts seeded with paraphrased original copy aligned to Showtime voice. `blog.css` (~330 lines, token-only) + `blog.js` (TOC builder + scroll-spy, no deps, prefers-reduced-motion respected).
- **/the-founder/ full build** — Was a 3-section stub. Now 6 sections: hero, story (ACF repeater of headline + body + image blocks), centered pullquote w/ quote-mark SVG, three numbered promises strip, contact list (phone/email/shop/LinkedIn — all dynamic), call-Steve CTA row. Person JSON-LD references `home_url('/#localbusiness')`.
- **footer-legal social URL parity** — `template-parts/footer/footer-legal.php` was calling `esc_url(Array)` because the Customizer bridge emits list-of-dicts while the default was dict-with-keys. Normalized to dict-with-keys before render. Was causing sitewide 500 in the earlier debug log.

**Files added (12):**
- `showtime-pools-child/assets/img/photos/` (38 WebP + 38 JPG)
- `showtime-pools-child/assets/css/blog.css`
- `showtime-pools-child/assets/js/blog.js`
- `showtime-pools-child/archive.php`
- `showtime-pools-child/single.php`
- `showtime-pools-child/acf-json/group_site_page_copy.json`
- `showtime-pools-child/acf-json/group_project_meta.json`
- `showtime-pools-core/includes/cpt/class-project.php`
- `showtime-pools-core/includes/class-projects.php`
- `showtime-pools-core/includes/data/projects.php`
- `showtime-pools-core/includes/data/blog-seed.php`
- `tools/optimize-photos.mjs`, `tools/run-seeder.php`

**Files modified (15):** imagery.php, enqueue.php, footer.php, footer-legal.php, primary-nav.php, section-01-hero.php, section-05-featured-projects.php, section-07-inspections-callout.php, local-business-schema.php, section-hero.php (service), page-about.php, page-contact.php, page-founder.php, page-blog.php, page-projects.php, interior.css, service.css, class-options-page.php, class-page-seeder.php, class-plugin.php.

**Verification (runtime, http://localhost/showtimepools/):**

- PHP lint: 21/21 files clean (after fixing two stray `<?php` openers in section-01-hero.php and page-projects.php)
- HTTP 200 on every locked URL: /, /about/, /the-founder/, /blog/, /blog/<post>/, /category/<slug>/, /projects/, /projects/<slug>/, /contact/, /services/, /services/<slug>/, /service-areas/, /service-areas/<slug>/, /pool-inspections/, /pool-inspections/<slug>/, /reviews/, /privacy/, /terms/, /quote/, /book/
- JSON-LD validity: 5 blocks on /, 6 on /the-founder/, 7 on /blog/<post>/ — all parse as valid JSON via ConvertFrom-Json. Types observed: WebSite (Rank Math), LocalBusiness w/ 2 branches, FAQPage, Article, BreadcrumbList, Person
- Seeder ran via `tools/run-seeder.php`: 17 created, 34 skipped, 8 projects published, 6 posts published, 4 categories
- Bundled photos resolve in HTML: 12+ unique bundled URLs on homepage; all serve 200 at `/wp/wp-content/themes/showtime-pools-child/assets/img/*.webp`
- No PHP fatal/warning/notice in WP debug.log today after the footer-legal fix
- Plugin junction was missing — re-junctioned per L-004 (old physical copy backed up to `.bak-phase-c-<timestamp>`)

**Out of scope (intentional):**
- /style-guide/ — not seeded as a page; route returns 404 (template exists but no post). Not part of locked URL set.
- Project before/after image pairs in admin — registry seeds the title/excerpt/meta only. Featured images can be set per post via the ACF image fields or by uploading; templates fall back to `project_N` bundled photo if no featured image set.
- WP-CLI seeding workflow on Cloudways — `tools/run-seeder.php` is local-only; first-run on Cloudways runs via plugin activation hook which calls the same `run_all_idempotent()` method.

## Review log

(append here at each checkpoint)

---

# PHASE 4 — Full Dynamic CMS + Schema + SEO/AEO/GEO Domination

Approved plan: `C:\Users\dogom\.claude\plans\1-pleae-recommend-the-ticklish-knuth.md`.
Goal: 100% client-editable, attribute-rich schema, content engineered to win Google rankings + AI engine citations (ChatGPT, Perplexity, Gemini AI Mode, Claude, Copilot) for Showtime Pools + LA pool-service queries.

## P4.1 — Settings consolidation + NAP single source of truth

- [ ] Extend `showtime-pools-core/includes/admin/class-settings-page.php` with the Business Identity tab: business name, legal name, PostalAddress (street, city, region, postal, country), telephone, SMS, email, hours per day (openingHoursSpecification), holiday hours
- [ ] Add Social + Trust tab: GBP CID URL, Yelp, IG, FB, YT, TikTok, LinkedIn, BBB profile URL, CSLB license number, insurance carrier name, founding year, founder name + title, employee count
- [ ] Add Search tab: GA4 ID, GSC verification meta, Bing Webmaster verification meta, default OG image picker, default aggregateRating fallback (overridden when Review CPT data exists)
- [ ] Replace hardcoded NAP fallbacks in `showtime-pools-child/template-parts/footer/local-business-schema.php` lines 98-206
- [ ] Replace hardcoded areaServed array in `showtime-pools-child/template-parts/service/schema.php` lines 31-37 with `\Showtime\Areas::all_names()` call
- [ ] Replace hardcoded employee + foundingDate + foundingLocation in `showtime-pools-child/page-about.php` lines 35-39 with Settings + Site Content lookups
- [ ] Add admin NAP-drift validator (`wp_footer` hook) that compares rendered NAP to canonical Settings, emits `wp_admin_notice` on mismatch
- [ ] Final grep sweep: zero matches for "(323) 825-2099", "Sherman Oaks, Los Angeles", "operations@showtimepoolmechanics.com" outside settings + registry files
- [ ] Verify on localhost: change phone in Settings, view-source on /, /services/pool-repairs-plumbing/, /service-areas/sherman-oaks/, /projects/<slug>/, all NAP + JSON-LD reflect the change

## P4.2 — Review + FAQ CPTs

- [ ] Create `showtime-pools-core/includes/cpt/class-review.php` (CPT `review`, fields: author_name, author_location, rating, body, source, source_url, date_received, linked_service, linked_area, linked_project, featured image)
- [ ] Create `showtime-pools-core/includes/cpt/class-faq.php` (CPT `faq`, taxonomy `faq_scope` with terms service/area/global, meta `target_slug`)
- [ ] Register both in `showtime-pools-core/includes/class-plugin.php`
- [ ] Build `\Showtime\Reviews::aggregate()` returning ratingValue + reviewCount for AggregateRating schema
- [ ] Build `\Showtime\Faqs::for_context($type, $slug)` returning matching Q/A array
- [ ] Wire FAQ queries into home, service, area, Service x Area, project single templates
- [ ] Replace hardcoded 4.9 / 184 AggregateRating in footer schema with `\Showtime\Reviews::aggregate()`
- [ ] Verify: seed 5 reviews + 10 FAQs, schema validates, AggregateRating computes, frontend renders

## P4.3 — Schema expansion

- [ ] Per-item `Review` JSON-LD on `/reviews/<slug>/` (if singular) or in aggregated block on `/reviews/`
- [ ] `FAQPage` schema on every area page (currently missing)
- [ ] `Article` schema on `single.php` with author Person, publisher Organization, datePublished, dateModified, mainEntityOfPage
- [ ] Per-team-member `Person` schema driven by Content admin Team tab, with `@id`, jobTitle, image, knowsAbout, worksFor reference, sameAs (LinkedIn)
- [ ] `Speakable` BETA spec targeting `.answer-capsule` on home, service, area
- [ ] Service x Area page schema graph: LocalBusiness (city-scoped) + Service + FAQPage, all `@id`-linked
- [ ] Validate every URL on Schema.org Validator + Google Rich Results Test: 0 errors, 0 warnings

## P4.4 — Content architecture (SEO + AEO + GEO)

- [ ] Build `inc/related-links.php` with `showtime_related_links($context)` helper (service -> areas + service-area pages, area -> services, project -> related projects + reviews)
- [ ] Add `[showtime_capsule]` shortcode + Gutenberg block rendering 40-60 word answer capsule with `.answer-capsule` class (no outbound links inside)
- [ ] Add visible "Last reviewed" + "Last updated" timestamps on pillar/service/area templates, tied to `dateModified`
- [ ] Add `wp_cron` weekly job that flags priority pages untouched for 30 days via admin notice
- [ ] Extend `inc/imagery.php`: emit `<link rel="preload" as="image" fetchpriority="high">` for explicit LCP hero attachment; ensure all `<img>` carry width/height + lazy/decoding attrs
- [ ] Default title/meta formula in `inc/seo.php` when per-page meta blank: `{Primary keyword} {City} | Showtime Pools` (50-60 char), answer + proof + CTA (140-160 char)
- [ ] Verify: every priority page has capsule in first 30%, at least 2 stats with sources, at least 1 quote with `<blockquote cite>`, FAQ section, related-links block, visible freshness markers

## P4.5 — Service Area expansion (6 -> 12 cities) + Service x Area matrix

- [ ] Extend `showtime-pools-core/includes/data/areas.php` with 6 new entries: Bel Air, Brentwood, Pacific Palisades, Hollywood Hills, West Hollywood, Calabasas (lat/lng, zip_codes, characteristics, common_jobs, sample_streets, gradient)
- [ ] Wire `class-page-seeder.php` to seed the 6 new area pages on plugin activation
- [ ] Build Service x Area route via rewrite rule `/[service-slug]-in-[city-slug]/` resolving to a shared template `page-service-area.php`
- [ ] Implement index gating: 36 strong combos (8 services x top 4 cities + 4 city pillars) index, 60 remaining get `noindex,follow`
- [ ] Add Areas + Services admin column showing index/noindex status with promote button (sets when content meets thresholds: 500+ words, 2+ reviews, 2+ projects)
- [ ] Each city page passes Phase 4.4 content checklist (capsule, stats, quote, FAQ, related links, freshness)

## P4.6 — Technical SEO + AI crawler access

- [ ] Filter `robots_txt` in `inc/seo.php` to emit AI bot allowlist (GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, PerplexityBot, Perplexity-User, Google-Extended, CCBot, bingbot) plus sitemap reference
- [ ] Ship `llms.txt` at root: brand entity, NAP, service list, area list, links to canonical pillar pages
- [ ] CWV: hero LCP preload, Mapbox lazy-load via IntersectionObserver, chat widget deferred to `requestIdleCallback`, all images width/height/lazy/decoding correct
- [ ] Confirm the live sitemap source (core /wp-sitemap.xml or Search Atlas) covers pages, services, areas, projects, posts, and that the URL submitted in GSC matches what actually resolves
- [ ] Verify PageSpeed Insights mobile + desktop on / + 1 service + 1 area: all metrics green or documented path to green

## P4.7 — Off-page entity + authority deliverable

- [ ] Write `docs/off-page-checklist.md`: Wikidata submission steps, LinkedIn profile complete checklist, GBP optimization checklist, Yelp/Houzz/Angi/Thumbtack/Nextdoor profiles, Reddit/YouTube authority plan, earned-media outreach list
- [ ] Hand to Steve / VA, not code work

## P4.8 — Verification (runs after each sub-phase)

- [ ] `tasks/verify-P4.N.md` per sub-phase with: editing proof, image proof, Schema validator screenshots, Lighthouse mobile 90+, hardcoded sweep grep proof, GSC URL Inspection HTML proof
- [ ] AEO sampling (30+ runs/query per Princeton ALCE standard) on ChatGPT, Perplexity, Gemini AI Mode, Claude, Copilot for the 6 anchor queries (see plan)

## Execution order

P4.1 -> P4.2 -> P4.3 -> P4.6 (quick win) -> P4.4 -> P4.5 -> P4.7. Verify after each.

Starting P4.1 now.


---

## Phase SEO-T1 — Technical SEO implementation (audit items 1-6, confirmed 2026-06-11)

Theme owns the server-rendered head. Live runs Search Atlas OTTO (client-side JS); Rank Math never installed on live, so all RANK_MATH checks are dead code.

- [x] T1.1 `feat(blog)`: byline links to /the-founder/, visible Updated date (suppressed on day-one posts), Article author -> Person @id /the-founder/#person, dateModified always emitted. Files: single.php, assets/css/blog.css
- [x] T1.2 `perf(hero)`: preload LCP hero (front page desktop/mobile pair + single post), width/height only on real attachment thumbnails, no crop changes. Files: inc/imagery.php (helpers), inc/performance.php (preload hook), single.php, template-parts/home/section-01-hero.php (helper swap only)
- [x] T1.3 `feat(nav)`: footer Service Areas column from \Showtime\Areas registry + grid CSS, nav + drawer Location -> /service-areas/. Files: footer-main.php, footer.css, primary-nav.php, mobile-drawer.php
- [x] T1.4 `perf(fonts)`: self-host DM Sans variable woff2 in assets/fonts/, fonts.css @font-face, preload latin normal, remove Google Fonts + both preconnects, keep Unsplash preconnect. Files: enqueue.php, assets/css/fonts.css, assets/fonts/*
- [x] T1.5 `fix(seo)`: delete meta keywords output. File: inc/seo-defaults.php
- [x] T1.6 `refactor(seo)`: remove all Rank Math conditionals/hooks (seo.php gate, seo-defaults.php filters + helper, class-plugin.php sitemap filter), verify core wp-sitemap.xml includes project CPT, sweep DEPLOY.md
- [x] Review section below

### SEO-T1 review (2026-06-11)

Commits bd95488, a2468f1, 81b6e28, d38cfe5, 77fbcc2, 1e4594d. All verified on localhost: post page shows linked byline + dates with Article author @id /the-founder/#person; hero preloads emit on front page (deduped when desktop/mobile URLs match) and single posts; footer renders 6 registry-driven area links + hub link, nav/drawer point at /service-areas/; DM Sans self-hosted (4 variable woff2, latin + latin-ext), zero fonts.googleapis/gstatic requests; meta keywords gone; zero rank_math references left in code; core /wp-sitemap.xml includes wp-sitemap-posts-project-1.xml.

Open items for live (not code): GSC sitemap URL needs manual verification (Rank Math sitemap_index.xml never existed on live; submit /wp-sitemap.xml or Search Atlas's sitemap, whichever is canonical). If a WP nav menu is assigned on live, the menu overrides the fallback nav, so Steve must repoint the Location menu item in wp-admin. Core sitemap also exposes wp-sitemap-users-1.xml (author enumeration); consider disabling the users provider in a future security pass.

---

## Phase SEO-T2 — Schema spec compliance + crawl hardening (2026-06-12)

Bugs 1a/1b (homepage canonical -> blog post, double-brand title) proven NOT theme-side: 12-page local audit shows one self-referencing canonical, one description, single-brand title everywhere. Source is OTTO's rewriting layer on live; fix is dashboard config (below), theme hardened so nothing feeds it bad defaults.

- [x] fix(schema): entity types [HomeAndConstructionBusiness, GeneralContractor] (invalid PoolCleaningService removed), @id #organization across 10 files, name hardcoded "Showtime Pools" (never blogname), phone E.164 +13238252099, Yelp in sameAs, natural URL scheme
- [x] fix(schema): BlogPosting on posts, BreadcrumbList deduped on post/project singles
- [x] fix(schema): about-page org facts merged into canonical @id node
- [x] feat(seo): hand-written titles+metas for 11 hub/utility pages (kills "{Page} - {blogname}" double-brand path)
- [x] feat(seo): inc/crawl.php with AI-bot robots allowlist; users sitemap and uncategorized term removed
- [x] Verification: 12/12 pages PASS (canon=1, desc=1, jsonld parse clean, crumbs<=1, brand<=1); zero #localbusiness refs left

### Live checklist (before/with next deploy, in order)
1. SearchAtlas dashboard: disable OTTO title/meta-description/canonical rewriting modules (theme owns the head). This IS the fix for the homepage canonical and double-brand title.
2. Live wp-admin: Settings -> General -> if Site Title is "Showtime Pools Mechanics", change to "Showtime Pools" (schema no longer reads it, but wp_title fallbacks and emails do).
3. Live wp-admin: delete /lander, /privacy-policy-2, /terms-2, Sample Page, Hello World (Steve, content side).
4. After deploy: verify https://showtimepools.com/robots.txt shows the AI-bot groups; view-source homepage: canonical = homepage, title single brand.
5. GSC: confirm submitted sitemap is the URL that actually resolves; then run the index request list (in session deliverable).

Local-only artifacts: robots.txt 404s on localhost (subdirectory install, WP at /wp/); works on live root install, verified via direct filter execution instead.

---

# T3 — Forms+UTM, Homepage Redesign, Performance (2026-06-20)

Backup branch: `backup/seo-t3-pre`. Local-only, not pushed. `/book/` untouched.

## Done (one concern per commit)
- **A2 — Contact UTM → GHL.** CMS-editable UTM defaults (Site Content → Homepage), `contact.js` overrides from real `?utm_*`, REST controller forwards `context.utm_*` to GHL. Also fixed a latent fatal: Site Content Homepage/Hub tabs called undefined `render_home()`/`render_hubs()`.
- **A3 — Sitewide popup.** GHL form `pZm1…` in an accessible modal (focus trap, ESC, 7-day localStorage), exit-intent desktop / 30s mobile, GHL iframe injected only on open (no LCP cost), excluded on contact/quote/book. CMS enable toggle. Popup UTM `utm_medium=popup&utm_content=weekly_maintenance`.
- **B1 — Hero video scaffold.** CMS `hero_video_url` + `hero_poster` (Site Images). `<video>` only when a URL is set, else the existing `<picture>` unchanged; mobile = poster only. No video hardcoded.
- **B2 — about_split slot.** Homepage About image now its own slot, independent of the /about/ hero, distinct pool placeholder. Site Images → "Homepage About section photo".
- **B3 — Services carousel.** Dependency-free accessible `[data-carousel]` (3 visible, arrows+swipe+keyboard, scroll-snap), reusable `assets/js/carousel.js`. Verified 3-up via screenshot.
- **B5 — Reviews container.** Centered + width-capped the live Trustindex widget (carousel layout itself is a Trustindex dashboard setting). No review text hardcoded.
- **B7 — Footer logo + map.** CMS `footer_logo` (Site Images → Branding) + lazy Google Map of the Sherman Oaks office, exact NAP reused from `offices[0]`.
- **C2 — CLS.** Explicit width/height on below-fold homepage images (containers already reserved space → CLS already ~0; this is the Lighthouse best-practice pass).

## Resolved without code (verified + flagged)
- **B4 — Lifestyle dedup.** lifestyle_1/lifestyle_4 are already independent CMS fields with distinct bundled + fallback images (md5-verified). The reuse is live Media Library content (same photo on multiple slots) → Steve swaps in Site Images. Also noted: bundled `lifestyle_2.jpg` == `about_hero.jpg` (also content-overridden on live).
- **B6 — Where We Work.** Screenshot confirms 8 areas already render as a clean 3-col grid, last row (West Hollywood, Bel Air) left-aligned, uniform heights — exactly the locked spec. No change (no fake 9th area).
- **C2 critical-CSS.** SKIPPED per the zero-FOUC condition: WP Rocket "Optimize CSS delivery" handles it in prod, async CSS risks FOUC, not measurable on local XAMPP.

## C3 — Expected Core Web Vitals (new code) + what's server-side
- **LCP:** unchanged/slightly better. Hero still preloaded `fetchpriority=high`; hero video is `preload=none` + poster so it never competes. Popup/carousel JS deferred and inert at paint; GHL iframe lazy.
- **CLS:** ~0 and hardened. All below-fold images sit in fixed-height/`aspect-ratio` containers; explicit width/height added. Popup/carousel inject nothing above the fold.
- **INP:** negligible impact — carousel/popup are event-bound, no work on load; popup adds one passive `mouseout`/timer.
- **Stays OTTO / server-side (post-deploy, not code):** SearchAtlas OTTO head rewrites; WP Rocket CSS-delivery/critical-CSS, JS delay, cache; Cloudflare/Cloudways edge cache, Brotli, HTTP/2, image CDN/AVIF. Enable WP Rocket "Optimize CSS delivery" rather than inlining critical CSS in the theme.

## Flags for Steve (content-side, post-deploy)
1. Trustindex dashboard → set widget layout to carousel (~3 visible).
2. Upload unique pool photos: Site Images → Lifestyle 1 (Sherman Oaks), Lifestyle 4 (Studio City), Homepage About section photo.
3. Upload the JLo hero video (Site Content → Homepage → Hero video URL) + Hero video poster (Site Images).
4. Form `tH1eoDpRA4hMEb04GgzX` is NOT placed — tell me which page/section and I'll embed it.
5. (Optional) Enable WP Rocket "Optimize CSS delivery" for critical-CSS in prod.

---

# T4 — LCP performance pass (2026-06-20)

Live mobile before: Perf 77, **LCP 5.9s (red)**; FCP 1.4s, TBT 70ms, CLS 0, SI 3.6s.
LCP element = homepage hero image. Local-only, not pushed.

## Root cause
`showtime_image('hero', W)` returned `wp_get_attachment_url()` — the **full original** — for an
uploaded attachment (the `W` arg only applied to the Unsplash fallback). The hero shipped the full
1200×1600 portrait (~297 KB) with **no srcset**, cropped to a landscape banner. On live the upload
is larger, so the served LCP image is bigger still. This is also the bulk of "image delivery, 853 KiB".

## Theme commits (done)
- **C1 — Hero responsive image + preload** (`inc/imagery.php`, `template-parts/home/section-01-hero.php`,
  `inc/performance.php`). `showtime_front_hero_image()` builds `src`+`srcset`+`sizes=100vw` from the
  registered landscape crops (showtime-card 720 / showtime-card-2x 1440 / showtime-hero 1920), using each
  crop's actual width and deduping identical files; caps the served image at ≤1920×1080 instead of the
  unbounded original. Hero `<img srcset sizes width height fetchpriority=high>`. Preload switched to
  `imagesrcset/imagesizes` so mobile preloads the right small crop. **Proven:** standard-DPR mobile now
  picks the 720w crop (**100 KB vs 297 KB full, −66%**); the unbounded original is now capped (the live win).
- **C2 — Right-size attachment images sitewide** (`inc/imagery.php`). `showtime_attachment_sized_url()`:
  when an upload is >1.5× the display width, serve the smallest generated size ≥ width; else keep the
  original (no regression on small uploads). Verified: 1200px orig @400 → 720w (96 KB vs 320 KB full).
  Activates on live's large uploads → the non-hero share of the 853 KiB. (Below-fold/lazy → helps the
  audit + bandwidth, not LCP directly.)
- **C3 — Defer footer.css** (`inc/enqueue.php`). media=print + onload swap (+`<noscript>`); footer is
  always below the fold → zero hero FOUC. blocks.css kept blocking (block content can be above the fold).
- **C4 — Responsive srcset on all landing-page heroes** (`inc/imagery.php` + 11 page templates). Same
  single-full-image bug applied to every SEO landing-page hero (the LCP element on those pages). New
  `showtime_hero_srcset_attr($slot)` injects srcset (landscape crops) + sizes=100vw into the hero `<img>`
  of: contact, area, areas hub, services hub, projects, about, founder, inspections, reviews, blog, shop.
  `/book` + `/quote` (page-iframe.php) untouched (locked). Remaining (optional): single.php /
  single-project.php heroes (featured-image path) could get the same treatment later.

## Server-side / WP-Rocket / GTM — apply on the droplet after deploy (NO theme code)
1. **Render-blocking CSS (−1,170 ms):** WP Rocket → File Optimization → **Optimize CSS delivery** (critical
   CSS + async rest; no hero FOUC because used CSS is inlined). Also Minify/Combine CSS+JS. This is the fix.
2. **WebP/AVIF on uploads (compounds C1):** Cloudflare **Polish** (WebP/AVIF) or Imagify/EWWW → the 100 KB
   hero crop → ~40–60 KB.
3. **Cache lifetimes:** add the nginx/Apache `Cache-Control: public, immutable` + `expires 1y` blocks for
   css/js/images/fonts (in the plan file). Enable **Brotli** (Cloudflare auto; nginx `ngx_brotli`).
4. **Unused JS (91 KiB):** theme JS already all `defer`/footer (`main, header, home, carousel, popup,
   contact, blog`; GHL defer; Turnstile async) — nothing to change. **GTM is third-party** → WP Rocket
   **"Delay JavaScript Execution"** + prune unused GTM tags.

## Expected LCP
- Hero mobile bytes: **297 KB → ~100 KB** (right-sized crop), **~40–60 KB** with edge WebP; on live the
  absolute saving is larger (capped vs the multi-hundred-KB/MB original) + the correct candidate is preloaded.
- With WP Rocket CSS delivery (−~1,170 ms) + edge cache/Brotli: **LCP ≈ 5.9 s → ~2.5–3.5 s** (exact figure
  depends on server TTFB + CDN; theme commits are the precondition, the server flags close the rest).

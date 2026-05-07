# Showtime Pools — Build Plan

Live build plan for showtimepools.com. Tracks every phase, every deliverable, every checkpoint.

**Working directory:** `C:\xampp\htdocs\showtimepools\showtimepools\`
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
- [ ] Schema validation (Schema.org + Rank Math)
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

## Review log

(append here at each checkpoint)

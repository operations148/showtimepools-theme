# Dynamic Content Architecture — Showtime Pools

Steve needs to edit every customer-facing string, image, address, and link from WP admin without ever touching PHP. This file inventories every hardcoded surface and assigns it a WP-native editor (Customizer / ACF Pro / Options page / CPT / nav menu / WP media library).

## Architectural choices — why each editor

| Editor | When we pick it | How Steve sees it |
|---|---|---|
| **WP Customizer** | Sitewide brand identity, business info, social URLs. One value, many templates. | Appearance → Customize → side panel with live preview |
| **ACF Pro options page** | Long-form sitewide repeater data (offices, hours, FAQ, team, credentials, social links). Steve already has ACF Pro. | Top-level admin menu "Site Content" |
| **ACF Pro field group on a page** | Page-specific copy + images (homepage hero, services-hub intro, contact page sidebar, founder bio). | Field group appears below the WP editor on each page |
| **Custom Post Type** | Repeating items with their own URL: services (already exists in registry), areas, projects, inspections, reviews | Sidebar entry "Services / Areas / Projects" with native WP add/edit/delete |
| **WP nav menu** | Header + footer + mobile drawer menus | Appearance → Menus, the WP-native nav menu builder |
| **WP Media Library** | Every image swap | Standard image picker, used by every ACF image field |

## Priority ladder

The conversion is staged so Steve gets the high-value editors first. Each phase is shippable on its own.

---

### Phase A — Brand identity (Customizer)
**Effort:** ~2–3 hours. Highest leverage: one update changes the value across ~40 templates.

| Surface | Currently | Convert to |
|---|---|---|
| Phone `(323) 825-2099` | `apply_filters('showtime/business/phone', '(323) 825-2099')` in 8 templates | Customizer setting `showtime_phone`, rendered through the same filter |
| Email `operations@showtimepoolmechanics.com` | filter default in 6 templates | Customizer setting `showtime_email` |
| Hours object (Mon-Sat / Sunday) | hardcoded in `footer-main.php` | Customizer 2-line text + appointment override |
| 3 Office addresses | hardcoded array in `footer-main.php` | ACF options page repeater (3 rows max) — see Phase B |
| 6 Social URLs (FB, IG, Google, LinkedIn, TikTok, YouTube) | hardcoded array in `footer-main.php` | Customizer URL fields (6) |
| Site tagline + business description | hardcoded in `footer-main.php` and meta tags | Customizer textarea `showtime_tagline` |
| Logo upload | already in Customizer (Site Identity → Logo) ✓ | done |

**Implementation:** Add a `Customize` panel `Showtime Brand` registering `customize_register` actions in `showtime-pools-core/includes/admin/class-customizer.php`. Sanitize via `sanitize_text_field` / `esc_url_raw`. Existing `showtime/business/*` filters continue to work — Steve can override programmatically too.

---

### Phase B — Sitewide content blocks (ACF Pro options page)
**Effort:** ~4–6 hours. Editable ONCE, surfaces everywhere.

| Surface | Currently | Convert to |
|---|---|---|
| Offices (label, street, city, zip, lat/lng) | array in `footer-main.php` | ACF options page repeater "Offices" |
| Hours block | array in `footer-main.php` | ACF options page repeater "Hours rows" |
| Trust pillars (3 items) | array in `template-parts/home/section-02-trust-bar.php` | ACF options page repeater "Trust Pillars" |
| Why-us / 6 trust pillars | array in `template-parts/home/section-why-us.php` | ACF options page repeater "Why Us Pillars" |
| Process steps (3) | array in `template-parts/home/section-06-process.php` | ACF options page repeater "Process Steps" |
| Team (4) | array in `page-about.php` | Optional CPT "Team Member" w/ name, role, photo, note OR ACF options repeater |
| Credentials (6) | array in `page-about.php` and `page-founder.php` | ACF options page repeater "Credentials" |
| Homepage Reviews (6) | array in `template-parts/home/section-08-reviews.php` | Optional CPT "Review" (Phase 2B in todo.md) OR ACF repeater for now |
| Site-wide FAQ (homepage) | array in `template-parts/home/section-10-faq.php` | ACF options page repeater "Homepage FAQ" |

**Implementation:** Single ACF options page registered via `acf_add_options_page` in the core plugin. Field groups stored as JSON in `acf-json/` so they version-control. Templates read via `get_field( 'offices', 'option' )`.

---

### Phase C — Page-specific content (ACF Pro per-page field groups)
**Effort:** ~4–6 hours. Steve edits each page directly in the WP block/page editor.

| Page | Currently | Convert to |
|---|---|---|
| Homepage `front-page.php` hero | hardcoded in `template-parts/home/section-01-hero.php` | ACF group "Home Hero" on the front page: chip text, H1 line 1, H1 line 2 (accent), lead, CTA1 label/url, CTA2 label/url, hero image, 3 stats (label/value × 3), locale string |
| About story | hardcoded in `page-about.php` | ACF group "About": story headline, 3 paragraphs, photo, photo caption |
| About values (5–6) | array in `page-about.php` | ACF repeater on the page |
| About team note copy | hardcoded | ACF text |
| Founder bio | hardcoded | ACF group "Founder": eyebrow, H1, lead, photo, body paragraphs (wysiwyg), contact rows (already in options) |
| Services hub intro | hardcoded in `page-services-hub.php` | ACF group "Services Hub": eyebrow, H1, lead, hero image |
| Service single (12 pages) | partially ACF already (`group_service_meta`), partially registry | Extend existing ACF group with hero_image, includes_image, all FAQ via repeater (already supported), CTA |
| Inspections hub intro | hardcoded | ACF group on `page-inspections.php` |
| Inspection single (3 pages) | registry-driven | Migrate registry → CPT "Inspection" with ACF |
| Service Areas hub intro | hardcoded | ACF group |
| Service Area single (6 pages) | registry-driven | Migrate registry → CPT "Service Area" with ACF (lat/lng, characteristics, common_jobs, sample_streets, photo) |
| Projects page | hardcoded array of 9 | CPT "Project" (Phase 2A in todo.md) |
| Reviews page | hardcoded array of 12 | CPT "Review" |
| Contact hero copy | hardcoded | ACF group |
| Quote/Book hero copy + 3 steps + fallback | hardcoded array | ACF group "GHL Iframe": hero, eyebrow, lead, 3-step repeater, CTA URLs (already in options) |
| Footer CTA tier | hardcoded in `template-parts/footer/footer-cta.php` | ACF options page (sitewide closer) |
| Privacy + Terms long-form | hardcoded HTML in `page-legal.php` | Just use the WP block editor — switch template to `the_content()` |
| Shop categories preview | hardcoded array | ACF repeater on the page |

---

### Phase D — Menus & nav
**Effort:** 30 min.

The header + mobile drawer already use `wp_nav_menu` with a "primary" location and a manual fallback. The footer uses `footer-main.php` hardcoded service links derived from the services registry.

**Action:** seed a default WP menu programmatically on plugin activation so Steve walks into Appearance → Menus and sees a working primary menu he can edit. Replace footer hardcoded service links with `wp_nav_menu( 'footer' )` and seed that too.

---

### Phase E — Imagery (already mostly done, finalize)

The `showtime_image()` resolver already supports:
- WP Media Library override via `showtime/image/{slot}` filter
- Local file drop at `assets/img/{slot}.{ext}` (just expanded to cover area_* and project_* slots in this turn)
- Unsplash stock fallback

**Action remaining:** add an admin Tools page under "Showtime Pools" with one image upload field per slot. Each upload writes to `wp_options` as `showtime_image_{slot}` with the attachment ID; the resolver checks options first before checking local file then Unsplash.

This way Steve never edits PHP to swap any image — just admin → Showtime Pools → Imagery → upload.

---

## Recommended ship order

1. **Phase A** — Customizer brand identity. Biggest immediate win, smallest code surface. Ship today.
2. **Phase D** — Seed nav menus. Tiny effort, unblocks Steve from editing the menu.
3. **Phase B** — ACF options page for repeater content. Batch it since it's all the same pattern.
4. **Phase C** — Page-by-page ACF field groups, ordered by traffic: Home → Services → Contact → About → Founder → Areas → Inspections → Projects → Reviews → Shop → Blog.
5. **Phase E** — Admin imagery panel. The existing local-file + filter override already covers 95% of swaps; the panel is polish.

---

## What NOT to convert

- **Service registry** (`showtime-pools-core/includes/data/services.php`) — keep as code-of-record. The 12 services have stable slugs that drive URLs and schema. Steve can override per-page copy via ACF on each service page (already wired). Adding/removing a service is a deliberate dev action with SEO consequences.
- **Tokens** (`assets/css/tokens.css`) — design system constants, not editorial content.
- **Schema.org JSON-LD** — code-derived, hydrates from the editable content above.
- **Plugin activation defaults** (page seeder, default settings) — admin-side only, no customer impact.

---

## Migration safety

Every conversion follows the same pattern so nothing breaks mid-migration:

```php
// Read with a sane fallback so the page never goes blank if ACF is offline
$hero_h1 = function_exists( 'get_field' )
    ? (string) ( get_field( 'home_hero_h1' ) ?: 'Stop juggling contractors.' )
    : 'Stop juggling contractors.';
```

`get_field()` returns the user's edit when ACF is loaded. The PHP literal is the safety net. Same pattern the `showtime/business/*` filters already follow — there is no breaking change to ship; each conversion is additive.

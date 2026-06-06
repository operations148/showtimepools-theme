# Standing deployment contract — READ FIRST

This is the deployment model for Showtime Pools. Every contributor (including Claude Code) follows it. No exceptions.

## CODE vs CONTENT split

**CODE** — edited LOCALLY, committed, pushed to GitHub, pulled on live. This is the only thing that travels local → live.

- PHP templates (`showtime-pools-child/`, `showtime-pools-core/`)
- CSS, JS, fonts, bundled images in `assets/`
- Plugin logic, CPTs, REST endpoints, hooks
- ACF JSON field definitions
- Theme/plugin configuration files

**CONTENT** — edited DIRECTLY on the LIVE site via wp-admin. Steve owns this. It lives in the live database only.

- Pages and their post_meta (hero text, section copy, captions)
- Images in the Media Library
- Reviews, menus, blog posts, project CPT entries
- Customizer values
- Site Content tab data (team, certifications, reviews widget config, GBP URL)

**DATABASE** — the one-time local → live migration is **DONE**. Never re-import the local DB to live again. Local DB exists only for code testing.

## Why this split exists

Re-importing the local DB to live would:
- Wipe Steve's content edits
- Reintroduce old duplicates (e.g., the privacy-policy duplicates we already cleaned up)
- Reset image URLs and break Media Library references
- Overwrite the Reviews widget shortcode and GBP URL

The seeder we built was a **one-time** tool to pre-fill empty fields on a fresh site. It has already run on live. Do not run it again unless Steve explicitly asks, and never as part of a routine deploy.

## What Claude Code does, by request type

| Request | Action |
|---|---|
| "Change this template / feature / styling / bug fix" | Edit locally → wait for Steve to push + pull |
| "Add new text to a page / change the H1 / replace a photo" | **STOP.** Tell Steve to do it in live wp-admin. Do not edit the database, do not write a migration script. |
| "Add a new page / menu item / blog post" | **STOP.** Tell Steve to do it in live wp-admin. |
| "Add a new editable FIELD to the meta box" | This is a CODE change — register the new meta field in `inc/meta-fields.php` locally, push, pull. Steve fills the value via wp-admin. |
| "Run the seeder on live" | **STOP.** Confirm explicitly with Steve. Do not run it as part of any other task. |
| Task seems to require touching live content via code | **STOP.** Flag it to Steve. Do not proceed. |

## When in doubt

If a request is ambiguous (code change or content change?), ask. Routing a content change through code creates DB drift; routing a code change through wp-admin creates code drift.

---

# Deploying Showtime Pools to Cloudways

Once the Cloudways DigitalOcean Premium 2GB instance is up and WP 6.7+ / PHP 8.2 is installed, follow these steps. This file is the only thing you need to read.

## 0. Get the code onto the server

Two equivalent paths — pick one.

### A) Cloudways Git Deployment (recommended)

1. In the Cloudways panel: **Application → Deployment via Git**.
2. Connect the GitHub repo (private is fine; add the deploy key Cloudways generates as a GitHub deploy key).
3. **Branch:** `main`.
4. **Deployment path:** `private_html/showtimepools-src` (clone outside the web root; we symlink into `public_html/wp-content/` next).

### B) Manual git clone over SSH

```bash
ssh master_user@APP_IP -p 22   # Cloudways gives master SSH creds
cd ~/applications/APP_ID/private_html
git clone git@github.com:USER/REPO.git showtimepools-src
```

### 0.1 Symlink the two deployable folders into wp-content

The repo contains the whole build context (docs, tasks, tools); only the two folders below ship into the running WP install. Symlinks mean a `git pull` updates the live site instantly with zero copy step.

```bash
cd ~/applications/APP_ID/public_html/wp-content/themes
ln -s ~/applications/APP_ID/private_html/showtimepools-src/showtime-pools-child .

cd ~/applications/APP_ID/public_html/wp-content/plugins
ln -s ~/applications/APP_ID/private_html/showtimepools-src/showtime-pools-core .
```

Future updates from your laptop:

```bash
# laptop
git push origin main
# Cloudways auto-pulls (option A) OR: ssh + cd to repo + git pull (option B)
```

No rsync, no copy step, no local↔live drift.

## 1. Install the parent theme + required plugins

WP admin → Appearance → Themes → Add New → install and activate **Astra** (free, wp.org repo). The child theme declares `Template: astra` and depends on it being present.

WP admin → Plugins → Add New, install and activate (in this order):

| Plugin | Source | License needed at this step |
| --- | --- | --- |
| Advanced Custom Fields Pro | upload .zip | Yes |
| FluentForms Pro | upload .zip | Yes |
| Rank Math Pro | upload .zip | Yes |
| WP Rocket | upload .zip | Yes |
| ShortPixel Image Optimizer | wp.org repo | Account API key |
| UpdraftPlus Premium | upload .zip | Yes |
| Wordfence Security | wp.org repo | Free is fine for launch |

Plus our own **Showtime Pools — Core** (already symlinked in step 0.1; just activate it).

## 2. Activate the child theme

WP admin → Appearance → Themes → activate **Showtime Pools Child**.

(The folder is already in place from step 0.1's symlink.)

## 3. Activate the core plugin

WP admin → Plugins → activate **Showtime Pools — Core**.

**On first activation, the plugin runs a one-time setup that:**

- Sets permalink structure to `/post-name/` (required for our slug-based templates)
- Brands the site name (`Showtime Pools`) and timezone (`America/Los_Angeles`)
- Seeds every structural page idempotently — services parent + 8 service children, areas parent + 6 area children, inspections parent + 3 inspection children, plus contact / quote / book / about / projects / reviews / privacy / terms

A `showtime_first_run_complete` flag is set in `wp_options` so reactivating the plugin will not re-seed. To force a re-run after manual cleanup, delete that single option.

## 4. Configure secrets (Showtime Pools → Settings)

Fill in only what's needed for the current phase. Phase 1B = nothing yet. Phase 1H = GHL webhook URL. Phase 1I = OpenAI key + assistant ID. Phase 2A = Mapbox token. Phase 2B = GBP IDs.

## 5. Optional: upload a custom Site Icon (favicon)

The theme ships with a bundled favicon generated from `assets/img/logo.png`. For the cleanest browser-tab presentation, upload a hand-tuned 512×512 mark via **Customizer → Site Identity → Site Icon**. The bundled fallback automatically steps aside the moment you save a Customizer site icon.

## 6. Verify

- Frontend loads, no PHP warnings (check WP debug log).
- Visit `/services/`, `/contact/`, `/quote/`, `/about/` — every URL resolves (the seeder created them).
- Browser tab shows the favicon.
- View source: no `<meta name="generator" content="WordPress ...">`, no emoji JS, no `?ver=` query strings on assets.
- Login error returns generic message regardless of which field is wrong.
- Showtime Pools menu item visible in WP admin sidebar.
- `/wp-json/showtime/v1/` responds with WP REST envelope.

If all six pass, the deploy is live.

## File map

```
showtimepools/                   <- this repo, lands at private_html/showtimepools-src/
├── showtime-pools-child/        <- symlinked into wp-content/themes/
├── showtime-pools-core/         <- symlinked into wp-content/plugins/
├── tools/                       <- repo-only utilities (favicon generator, etc.)
├── tasks/
│   ├── todo.md                  <- live build plan
│   └── lessons.md               <- corrections received
├── DEPLOY.md                    <- this file
├── README.md                    <- repo overview
└── CLAUDE.md                    <- workflow contract for the dev agent
```

> **`/tools` is dev-only and never deploys.** Only `showtime-pools-child/` and `showtime-pools-core/` are symlinked into `wp-content/`; the `tools/` folder (favicon generator, photo catalog, `seeder-dry-run.php`, `run-seeder.php`) stays in the repo and is excluded from the deploy bundle. Nothing in `tools/` runs on live.

## What ships via git vs. what does not

**Ships via git (visual parity local↔live, automatic):**
- All theme code, CSS, JS, templates, fonts, images bundled in `showtime-pools-child/assets/`
- All plugin code, CPTs, REST endpoints, integrations, admin pages
- ACF Pro field-group definitions (`showtime-pools-child/acf-json/group_*.json` — auto-loaded by ACF Pro)
- Customizer setting **defaults** (every setting has a hardcoded default, used until you customize)
- Page structure (auto-seeded on plugin activation)
- Site name + timezone + permalink structure (set on activation)
- Bundled favicon

**Does NOT ship via git (these live in the WP database, not in code):**
- Media library uploads (`wp-content/uploads/` — gitignored on purpose, since user uploads accumulate over time)
- Plugin licenses (entered in each plugin's settings panel)
- Customizer values you've changed from default
- ACF Options page values (e.g., business address, hours table content)
- Posts and post revisions

For phase 1, all of the above either have hardcoded defaults that render correctly out of the box, or are configured per-environment (licenses, secrets). When you start adding ACF Options content or media uploads on the live site, those stay on live — that is the intended source-of-truth split.

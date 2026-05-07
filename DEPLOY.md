# Deploying Showtime Pools to Cloudways

Once the Cloudways DigitalOcean Premium 2GB instance is up and WP 6.7+ / PHP 8.2 is installed, follow these steps. This file is the only thing you need to read.

## 0. Get the code onto the server

Two equivalent paths — pick one.

### A) Cloudways Git Deployment (recommended)

1. In the Cloudways panel: **Application → Deployment via Git**.
2. Connect the GitHub repo (private is fine; add the deploy key Cloudways generates as a GitHub deploy key).
3. **Branch:** `main`.
4. **Deployment path:** `public_html/` (the repo lands at `public_html/`, then symlink the two deployable folders into place — see step 0.1 below).

### B) Manual git clone over SSH

```bash
ssh master_user@APP_IP -p 22  # Cloudways gives the master SSH creds
cd ~/applications/APP_ID/private_html
git clone git@github.com:USER/REPO.git showtimepools-src
```

### 0.1 Symlink the two deployable folders into wp-content

The repo contains the whole build context (docs, tasks, tools); only the two folders below ship into the running WP install. Symlink them so a `git pull` updates the live site instantly with zero copy step.

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
# Cloudways auto-pulls (option A) OR ssh + cd to repo + git pull (option B)
```

That's the entire deploy story. Symlinks mean no rsync, no copy step, no drift.

## 1. Install the parent theme + required plugins

From WP admin → Appearance → Themes → Add New, install and activate **Blocksy** (free).

From WP admin → Plugins → Add New, install and activate (in this order):

| Plugin | Source | License needed at this step |
| --- | --- | --- |
| Blocksy Companion Pro | upload .zip | Yes |
| Advanced Custom Fields Pro | upload .zip | Yes |
| FluentForms Pro | upload .zip | Yes |
| Rank Math Pro | upload .zip | Yes |
| WP Rocket | upload .zip | Yes |
| ShortPixel Image Optimizer | wp.org repo | Account API key |
| UpdraftPlus Premium | upload .zip | Yes |
| Wordfence Security | wp.org repo | Free is fine for launch |

Total: 8 plugins after our `showtime-pools-core` is added. Cap is 12.

## 2. Drop in the child theme

SFTP/SSH to `/applications/{app-id}/public_html/wp-content/themes/`.

Upload the `showtime-pools-child/` folder from this repo.

WP admin → Appearance → Themes → activate **Showtime Pools Child**.

## 3. Drop in the core plugin

Upload `showtime-pools-core/` to `/wp-content/plugins/`.

WP admin → Plugins → activate **Showtime Pools — Core**.

## 4. Configure secrets (Showtime Pools → Settings)

Fill in only what's needed for the current phase. Phase 1B = nothing yet. Phase 1H = GHL webhook URL. Phase 1I = OpenAI key + assistant ID. Phase 2A = Mapbox token. Phase 2B = GBP IDs.

## 5. Set permalinks

WP admin → Settings → Permalinks → Post name. Save (forces a flush).

## 6. Verify

- Frontend loads, no PHP warnings (check WP debug log).
- `/wp-json/showtime/v1/` responds 404 with WP REST envelope (no endpoints yet, that's correct for Phase 1B).
- View source: no `<meta name="generator" content="WordPress ...">`, no emoji JS, no `?ver=` query strings on assets.
- Login error returns generic message regardless of which field is wrong.
- Showtime Pools menu item visible in WP admin sidebar.

If all six pass, Phase 1B is live. Move to Phase 1C.

## File map

```
showtimepools/                   <- this repo (local)
├── showtime-pools-child/        <- deploys to /wp-content/themes/
├── showtime-pools-core/         <- deploys to /wp-content/plugins/
├── tasks/
│   ├── todo.md                  <- live build plan
│   └── lessons.md               <- corrections received
├── DEPLOY.md                    <- this file
└── CLAUDE.md                    <- workflow contract for the dev agent
```

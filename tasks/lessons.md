# Lessons — Showtime Pools Project

Corrections received from user. Re-read at session start. Add a new entry every time the user corrects me.

---

## L-001 — Hosting and infra are user's job, not Claude's

**Date:** 2026-05-06

**Correction:** "why? its my job to host it, its your job to develop the project"

**Pattern:** I asked for Cloudways access, datacenter region, server naming, Cloudflare account, SMTP choice, WP admin creds, Wordfence tier as blockers to start.

**Rule:** Never request hosting, server, DNS, SSL, SMTP, datacenter, or Cloudflare info as a development blocker. The user provisions infra. I build code in the local XAMPP path and deliver a deployable bundle. I only need from them: WP admin login + SFTP/SSH + plugin license keys (and only at the moment those are needed, not preemptively).

**How to apply:** When a phase has both an "infra" piece and a "build" piece (e.g., Phase 1A vs 1B), execute the build piece in local XAMPP. Skip the infra ask entirely.

---

## L-002 — User is senior, does not need step-by-step guidance asks

**Date:** 2026-05-06

**Correction:** "build form phase on and continue! i dont have to guid you since you are senior developer"

**Pattern:** I asked which datacenter, which SMTP provider, what username convention, what tier of Wordfence, etc. Multiple choice asks treated as required gates.

**Rule:** Make decisions. Apply senior-dev defaults. Document the decision and the reasoning. Only ask when the choice is irreversible AND the user's preference isn't inferable from spec/context. For everything else, pick the strongest option and move.

**How to apply:** Before asking a question, ask myself "would a senior dev with 15 years experience pause to ask this, or just decide?" If the latter, decide. Document in code comments or tasks/todo.md.

---

## L-003 — CLAUDE.md is the workflow contract, follow it

**Date:** 2026-05-06

**Correction:** "no read that CLAUDE.md, its your command!"

**Pattern:** I started building before writing the plan to tasks/todo.md and capturing lessons.

**Rule:** At session start: read CLAUDE.md + tasks/lessons.md. Before non-trivial work: write plan to tasks/todo.md. After every correction: update tasks/lessons.md. Mark tasks complete only after verification.

**How to apply:** Treat the CLAUDE.md in the working directory as binding workflow law for this project regardless of which prior project it references.

---

## L-004 — Source bundle vs deployed theme: always run the local WP install as a junction

**Date:** 2026-05-07

**Correction:** "i shutdown and restart my laptop, none of it change the hamburger menu on mobile! please extensively review it why its not changing"

**Pattern:** I had been editing `C:\xampp\htdocs\showtimepools\showtimepools\showtime-pools-child\` (the deliverable bundle) while the user's local WP at `localhost/showtimepools/wp/` loaded a separate, stale physical copy at `C:\xampp\htdocs\showtimepools\wp\wp-content\themes\showtime-pools-child\`. Two divergent folders. Restarts cannot reconcile that.

**Rule:** On any local WordPress test environment for this project, the `wp-content/themes/showtime-pools-child` path must be an NTFS junction (`mklink /J`) pointing at the source bundle in this repo. Same goes for `showtime-pools-core` once it's added. Never maintain two physical copies.

**Why:** `inc/enqueue.php:20` already cache-busts assets via `filemtime()`, so a junction gives instant feedback on save with zero sync overhead. ACF JSON two-way writes still work through a junction, and the Cloudways deploy story (one-shot upload of the bundle folder) is unchanged.

**How to apply:** Every new local WP install for this project — first verify (`dir wp-content\themes` → look for `<JUNCTION>`); if it's a real folder, `rmdir /S /Q` the deployed copy and `mklink /J` it to the bundle. Do this before any visual debugging session. If the user reports "my edits aren't showing," check the junction first before suspecting browser cache, opcache, or asset enqueue.

---

## Verifying visual changes: render the way the USER's browser does, and diff against live

**Context:** Built `/affiliate`. I "verified" with headless Chrome using `--ignore-certificate-errors` and opened the page over `http://`. User saw a completely unstyled page and (twice) thought I'd destroyed the menu.

**Two distinct traps hit:**

1. **Forced-HTTPS assets + local self-signed cert.** `functions.php:16` defines `SHOWTIME_CHILD_URI = set_url_scheme(get_stylesheet_directory_uri(), 'https')` — every asset URL is `https://localhost/...`. Over the local self-signed cert, a normal browser silently blocks all CSS/JS subrequests, so the page renders as plain HTML. My headless `--ignore-certificate-errors` masked this; the user's real browser did not. **Rule:** never validate styling with cert errors ignored and call it done. Either open the **https** URL and accept the cert once (grants the whole-origin exception so https assets load), or test in the browser the user actually uses. A cert-ignored headless shot proves markup, not that the user will see styling.

2. **Nav diverged from live because a menu was auto-seeded locally.** `template-parts/header/primary-nav.php` renders a canonical hardcoded nav (mega-menus, `.primary-nav__link`, items incl. Location/Shop) **only when no WP menu exists**; if any menu is assigned it uses `wp_nav_menu` with WP-default markup (no `.primary-nav__link` → global `a{underline}` shows, wrong items). The seeder's old `seed_primary_menu()` auto-created/assigned a menu on first-run, so **local** showed the broken version while **live** (no assigned menu) showed the canonical one. Fixed by removing the auto-seed. **Rule:** when the user says a global element "looks wrong / not the original," diff the **rendered markup** local-vs-live (`curl | grep` the component) before assuming it's unchanged — class presence (e.g. `.primary-nav__link` count) pinpoints CSS-vs-markup fast. Menus are content (per DEPLOY contract); the canonical nav lives in the template, and nothing should auto-create a menu that shadows it.

**How to apply:** (a) For any styling verification on this project, use the https URL or the user's browser, never cert-ignored-only. (b) When a shared/global UI element is questioned, compare local rendered HTML against the live screenshot/markup and check whether a DB menu/option is overriding a template fallback — don't just check git for file changes.

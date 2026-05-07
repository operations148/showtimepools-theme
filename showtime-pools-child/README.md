# showtime-pools-child

Child theme for Showtime Pools (Sherman Oaks, LA). Pairs with the Blocksy parent theme and the `showtime-pools-core` companion plugin.

## What lives here

| Concern | Location |
| --- | --- |
| Theme metadata | `style.css` (header only) |
| Bootstrap | `functions.php` |
| Theme support, image sizes, menus | `inc/theme-setup.php` |
| Asset enqueue (CSS, JS, fonts) | `inc/enqueue.php` |
| Blocksy parent overrides | `inc/blocksy-overrides.php` |
| Lightweight WP hardening | `inc/security.php` |
| WP bloat removal | `inc/performance.php` |
| Design tokens (CSS vars) | `assets/css/tokens.css` |
| Base + utilities + components | `assets/css/{base,utilities,components}.css` |
| Block overrides | `assets/css/blocks.css` |
| Global JS | `assets/js/main.js` |
| Page templates | `page-{slug}.php`, `front-page.php` |
| Reusable parts | `template-parts/{header,footer,home,...}/*.php` |

## What does NOT live here

- Custom Post Types, REST endpoints, third-party API integrations, admin settings → `showtime-pools-core` plugin
- Lead routing, GHL webhook posting, OpenAI proxying, Mapbox token storage → `showtime-pools-core` plugin
- Caching, image optimization → handled by WP Rocket + ShortPixel

The split keeps theme presentation portable. If we ever swap themes, business logic survives.

## Editing rules

1. Never edit Blocksy parent files. Override via filter/action in `inc/blocksy-overrides.php`.
2. Never put production CSS in `style.css`. That file is metadata only. Real CSS lives in `assets/css/`.
3. Every CSS file is enqueued explicitly; nothing auto-loads from `assets/`.
4. JS is deferred and footer-loaded by default. If you need head-loaded JS, justify it in a code comment.
5. All custom code must be commented for the WHY (not the WHAT).
6. Touch only what you need. No drive-by refactors.

## Local dev

The repo expects to be deployed at `/wp-content/themes/showtime-pools-child/`. For local XAMPP testing, symlink or copy this folder into a WP install with Blocksy parent already activated.

## Version

`0.1.0` — Phase 1B scaffold (this commit).

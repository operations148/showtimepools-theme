# Showtime Pools — WordPress build

Source repo for the Showtime Pools website (showtimepools.com). Two
deployable artifacts live here:

- `showtime-pools-child/` — child theme (activates against the parent theme on the live server). Owns every visual layer, header, footer, page templates, and ACF JSON groups.
- `showtime-pools-core/` — site-specific plugin with CPTs, REST endpoints, GHL / Mapbox / OpenAI integrations, and admin settings. Theme-agnostic business logic.

Everything else in this repo is build context: docs (`DEPLOY.md`,
`CLAUDE.md`), tooling scripts (`tools/`), and the live build plan
(`tasks/`). None of it ships to the production server.

## Quick start (local)

A separate XAMPP WordPress install lives at `C:\xampp\htdocs\showtimepools\wp\`. The two deployable folders are mounted into that install via NTFS junctions so edits land in the source bundle and appear instantly on `localhost/showtimepools/wp/`. See `tasks/lessons.md` (L-004) for the rationale and the `mklink /J` commands.

## Deploying to live

See **`DEPLOY.md`** — the only document you need. It covers parent theme + plugin install order, the child theme + core plugin upload paths, secrets configuration, and the post-deploy verification checklist.

## Development workflow

- Plan in `tasks/todo.md` before any non-trivial change.
- Capture corrections in `tasks/lessons.md`.
- The behavioral contract for the dev agent lives in `CLAUDE.md`.

## Stack

- WordPress 6.7+, PHP 8.2+
- Child theme + custom plugin (no page builders — native Gutenberg + Blocksy Pro blocks only)
- ACF Pro, FluentForms Pro, Search Atlas (OTTO), WP Rocket, Wordfence, UpdraftPlus, ShortPixel
- Mapbox GL JS v3, custom OpenAI Assistants API integration
- Hosting: Cloudways DigitalOcean Premium 2GB

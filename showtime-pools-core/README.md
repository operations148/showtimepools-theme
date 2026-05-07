# showtime-pools-core

Companion plugin to the `showtime-pools-child` theme. Holds all business logic that should survive a theme swap.

## Responsibilities

- Custom Post Types: `project`, `review`, `gallery_item`, `service_area`, `chat_log`
- Custom taxonomies: `service-category`, `neighborhood`, `service-type`, `pool-style`
- REST endpoints under `/wp-json/showtime/v1/`:
  - `chat` (AI chat proxy → OpenAI Assistants)
  - `projects-geojson` (Mapbox map data feed)
  - `mapbox-token` (nonce-gated token release)
- Integrations:
  - GHL inbound webhook poster (FluentForms hook + chat lead handoff)
  - OpenAI Assistants API server-side proxy
  - Google Business Profile review sync (cron)
  - Mapbox token gate
- Admin settings page (Showtime Pools → Settings)

## Why a plugin and not the child theme

Child theme = presentation. Plugin = behavior. If we ever swap themes (Blocksy → something else), the plugin keeps running. Customer-facing data (projects, reviews) doesn't disappear.

## Folder structure

```
showtime-pools-core/
├── showtime-pools-core.php     # entry point + autoloader
├── uninstall.php               # cleanup on plugin delete
├── includes/
│   ├── class-plugin.php        # bootstrap (wires subsystems)
│   ├── admin/                  # WP admin UI
│   ├── cpt/                    # Custom Post Type registrations
│   ├── rest/                   # REST API controllers
│   └── integrations/           # third-party API adapters
└── README.md
```

## Autoloader

PSR-4-ish: `\Showtime\Admin\SettingsPage` → `includes/admin/class-settings-page.php`. No Composer needed; the bundle stays zero-dep.

## Deployment

Drop the folder into `/wp-content/plugins/`. Activate from WP admin. Configure under **Showtime Pools → Settings**.

## Phase status

- 1B (this commit): bootstrap, autoloader, settings page shell, GHL webhook fields
- 1H: GHL webhook poster wiring
- 1I: OpenAI Assistant integration + chat REST endpoint + chat_log CPT + rate limiting
- 2A: project CPT, projects-geojson endpoint, Mapbox token gate
- 2B: review CPT, GBP cron sync
- 2C: gallery_item CPT
- 2D: service_area CPT

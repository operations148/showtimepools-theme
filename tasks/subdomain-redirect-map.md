# area.showtimepools.com → showtimepools.com 301 redirect map

Decommissioning the legacy subdomain. Every indexed subdomain URL gets a
permanent (301) redirect to its closest match on the main site so link equity
and any existing rankings transfer instead of dying as 404s.

**Apply on the SUBDOMAIN's host only. Do NOT touch main-site server config.**

## IMPORTANT: the subdomain is a Next.js app

`area.showtimepools.com` serves a Next.js application (response exposes
`/_next/static/...`). If it is hosted on Vercel or any Node runtime, the
Apache `.htaccess` and nginx blocks below will NOT run. Use the
`next.config.js` / `vercel.json` form in that case. All three express the same
map; apply whichever matches the actual host.

## The mapping

Subdomain slugs are no-trailing-slash; main-site targets use trailing slashes.

| Subdomain URL | 301 target on showtimepools.com |
|---|---|
| `/expertise/pool-cleaning` | `/services/weekly-pool-maintenance/` |
| `/expertise/pool-repair` | `/services/pool-repairs-plumbing/` |
| `/expertise/pool-remodeling` | `/services/pool-remodeling-resurfacing/` |
| `/expertise/equipment-installation` | `/services/equipment-installation-upgrades/` |
| `/expertise/pool-inspections` | `/services/pool-inspections-diagnostics/` |
| `/expertise/smart-pool-automation` | `/services/smart-pool-automation/` |
| `/expertise/pool-design-construction` | `/services/custom-pool-design-construction/` |
| `/expertise/spa-installation` | `/services/spa-installation-renovations/` |
| `/expertise/tile-coping-plaster` | `/services/tile-coping-plaster-decking/` |
| `/expertise/outdoor-living` | `/services/outdoor-living-hardscape/` |
| `/expertise/outdoor-kitchens` | `/services/outdoor-kitchens-bbq/` |
| `/expertise/fire-water-features` | `/services/fire-water-features/` |
| `/service-areas/sherman-oaks` | `/service-areas/sherman-oaks/` |
| `/service-areas/encino` | `/service-areas/encino/` |
| `/service-areas/studio-city` | `/service-areas/studio-city/` |
| `/service-areas/tarzana` | `/service-areas/tarzana/` |
| `/service-areas/woodland-hills` | `/service-areas/woodland-hills/` |
| `/service-areas/beverly-hills` | `/service-areas/beverly-hills/` |
| `/service-areas/calabasas` | `/service-areas/` (no page on main site) |
| `/service-areas/hidden-hills` | `/service-areas/` (no page on main site) |
| `/service-areas/brentwood` | `/service-areas/` (no page on main site) |
| `/service-areas/pacific-palisades` | `/service-areas/` (no page on main site) |
| `/service-areas` (index) | `/service-areas/` |
| `/` (root) | `/service-areas/` (per Steve's instruction) |
| anything else | `/` (catch-all, no orphan 404s) |

Note: the 4 areas with no main-site equivalent (Calabasas, Hidden Hills,
Brentwood, Pacific Palisades) point at the hub. If Steve wants real pages for
any of them later, repoint that row to the new `/service-areas/<slug>/`.

## Apache (.htaccess on the subdomain docroot)

```apache
RewriteEngine On

# expertise -> services
RewriteRule ^expertise/pool-cleaning/?$            https://showtimepools.com/services/weekly-pool-maintenance/ [R=301,L]
RewriteRule ^expertise/pool-repair/?$              https://showtimepools.com/services/pool-repairs-plumbing/ [R=301,L]
RewriteRule ^expertise/pool-remodeling/?$          https://showtimepools.com/services/pool-remodeling-resurfacing/ [R=301,L]
RewriteRule ^expertise/equipment-installation/?$   https://showtimepools.com/services/equipment-installation-upgrades/ [R=301,L]
RewriteRule ^expertise/pool-inspections/?$         https://showtimepools.com/services/pool-inspections-diagnostics/ [R=301,L]
RewriteRule ^expertise/smart-pool-automation/?$    https://showtimepools.com/services/smart-pool-automation/ [R=301,L]
RewriteRule ^expertise/pool-design-construction/?$ https://showtimepools.com/services/custom-pool-design-construction/ [R=301,L]
RewriteRule ^expertise/spa-installation/?$         https://showtimepools.com/services/spa-installation-renovations/ [R=301,L]
RewriteRule ^expertise/tile-coping-plaster/?$      https://showtimepools.com/services/tile-coping-plaster-decking/ [R=301,L]
RewriteRule ^expertise/outdoor-living/?$           https://showtimepools.com/services/outdoor-living-hardscape/ [R=301,L]
RewriteRule ^expertise/outdoor-kitchens/?$         https://showtimepools.com/services/outdoor-kitchens-bbq/ [R=301,L]
RewriteRule ^expertise/fire-water-features/?$      https://showtimepools.com/services/fire-water-features/ [R=301,L]

# service-areas with a main-site match
RewriteRule ^service-areas/sherman-oaks/?$    https://showtimepools.com/service-areas/sherman-oaks/ [R=301,L]
RewriteRule ^service-areas/encino/?$          https://showtimepools.com/service-areas/encino/ [R=301,L]
RewriteRule ^service-areas/studio-city/?$     https://showtimepools.com/service-areas/studio-city/ [R=301,L]
RewriteRule ^service-areas/tarzana/?$         https://showtimepools.com/service-areas/tarzana/ [R=301,L]
RewriteRule ^service-areas/woodland-hills/?$  https://showtimepools.com/service-areas/woodland-hills/ [R=301,L]
RewriteRule ^service-areas/beverly-hills/?$   https://showtimepools.com/service-areas/beverly-hills/ [R=301,L]

# service-areas with no match -> hub
RewriteRule ^service-areas/(calabasas|hidden-hills|brentwood|pacific-palisades)/?$ https://showtimepools.com/service-areas/ [R=301,L]
RewriteRule ^service-areas/?$ https://showtimepools.com/service-areas/ [R=301,L]

# root + catch-all
RewriteRule ^$ https://showtimepools.com/service-areas/ [R=301,L]
RewriteRule ^(.*)$ https://showtimepools.com/ [R=301,L]
```

## nginx (inside the subdomain server block)

```nginx
location = /expertise/pool-cleaning            { return 301 https://showtimepools.com/services/weekly-pool-maintenance/; }
location = /expertise/pool-repair              { return 301 https://showtimepools.com/services/pool-repairs-plumbing/; }
location = /expertise/pool-remodeling          { return 301 https://showtimepools.com/services/pool-remodeling-resurfacing/; }
location = /expertise/equipment-installation   { return 301 https://showtimepools.com/services/equipment-installation-upgrades/; }
location = /expertise/pool-inspections         { return 301 https://showtimepools.com/services/pool-inspections-diagnostics/; }
location = /expertise/smart-pool-automation    { return 301 https://showtimepools.com/services/smart-pool-automation/; }
location = /expertise/pool-design-construction { return 301 https://showtimepools.com/services/custom-pool-design-construction/; }
location = /expertise/spa-installation         { return 301 https://showtimepools.com/services/spa-installation-renovations/; }
location = /expertise/tile-coping-plaster      { return 301 https://showtimepools.com/services/tile-coping-plaster-decking/; }
location = /expertise/outdoor-living           { return 301 https://showtimepools.com/services/outdoor-living-hardscape/; }
location = /expertise/outdoor-kitchens         { return 301 https://showtimepools.com/services/outdoor-kitchens-bbq/; }
location = /expertise/fire-water-features      { return 301 https://showtimepools.com/services/fire-water-features/; }

location = /service-areas/sherman-oaks   { return 301 https://showtimepools.com/service-areas/sherman-oaks/; }
location = /service-areas/encino         { return 301 https://showtimepools.com/service-areas/encino/; }
location = /service-areas/studio-city    { return 301 https://showtimepools.com/service-areas/studio-city/; }
location = /service-areas/tarzana        { return 301 https://showtimepools.com/service-areas/tarzana/; }
location = /service-areas/woodland-hills { return 301 https://showtimepools.com/service-areas/woodland-hills/; }
location = /service-areas/beverly-hills  { return 301 https://showtimepools.com/service-areas/beverly-hills/; }

location = /service-areas/calabasas         { return 301 https://showtimepools.com/service-areas/; }
location = /service-areas/hidden-hills      { return 301 https://showtimepools.com/service-areas/; }
location = /service-areas/brentwood         { return 301 https://showtimepools.com/service-areas/; }
location = /service-areas/pacific-palisades { return 301 https://showtimepools.com/service-areas/; }
location = /service-areas                   { return 301 https://showtimepools.com/service-areas/; }

location = / { return 301 https://showtimepools.com/service-areas/; }
location / { return 301 https://showtimepools.com/; }
```

## Next.js / Vercel (next.config.js — use this if the host is Vercel/Node)

```js
// next.config.js
module.exports = {
  async redirects() {
    const expertise = {
      'pool-cleaning': 'weekly-pool-maintenance',
      'pool-repair': 'pool-repairs-plumbing',
      'pool-remodeling': 'pool-remodeling-resurfacing',
      'equipment-installation': 'equipment-installation-upgrades',
      'pool-inspections': 'pool-inspections-diagnostics',
      'smart-pool-automation': 'smart-pool-automation',
      'pool-design-construction': 'custom-pool-design-construction',
      'spa-installation': 'spa-installation-renovations',
      'tile-coping-plaster': 'tile-coping-plaster-decking',
      'outdoor-living': 'outdoor-living-hardscape',
      'outdoor-kitchens': 'outdoor-kitchens-bbq',
      'fire-water-features': 'fire-water-features',
    }
    const areaMatch = ['sherman-oaks','encino','studio-city','tarzana','woodland-hills','beverly-hills']
    const areaHub = ['calabasas','hidden-hills','brentwood','pacific-palisades']

    return [
      ...Object.entries(expertise).map(([from, to]) => ({
        source: `/expertise/${from}`,
        destination: `https://showtimepools.com/services/${to}/`,
        permanent: true,
      })),
      ...areaMatch.map((slug) => ({
        source: `/service-areas/${slug}`,
        destination: `https://showtimepools.com/service-areas/${slug}/`,
        permanent: true,
      })),
      ...areaHub.map((slug) => ({
        source: `/service-areas/${slug}`,
        destination: 'https://showtimepools.com/service-areas/',
        permanent: true,
      })),
      { source: '/service-areas', destination: 'https://showtimepools.com/service-areas/', permanent: true },
      { source: '/', destination: 'https://showtimepools.com/service-areas/', permanent: true },
      { source: '/:path*', destination: 'https://showtimepools.com/', permanent: true },
    ]
  },
}
```

## After applying

1. Spot-check 3 URLs with `curl -I` and confirm `HTTP/1.1 301` + correct `Location`.
2. In GSC for the subdomain property: use Removals only if you want them gone
   fast; otherwise the 301s let Google transfer signals over a few weeks.
3. Keep the redirects live for at least 12 months so external backlinks resolve.

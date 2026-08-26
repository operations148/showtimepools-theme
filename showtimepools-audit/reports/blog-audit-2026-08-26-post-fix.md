## Blog Audit — Post-Fix Rerun — showtimepools.com

**Audit date:** 2026-08-26 (post-fix)
**Baseline:** `blog-audit-2026-08-26.md`
**Scoring:** unchanged — `analyze_blog.py`, 5-category / 100-point. Re-running the baseline captures through this pass reproduces an average of **55.7**, identical to the original run, which confirms the two passes are methodologically aligned.

> **Scope of this rerun — read before quoting any number.** The score movement below reflects the **article-content rewrite published to production on or after 2026-08-25**, which this run measured on the live site. It does **not** reflect this branch's code changes. The blog-archive, pagination, canonical and author-schema fixes are committed but **not deployed**, so at audit time live `/blog/` still renders 9 of 12 cards. Archive figures are therefore reported twice: as measured on live now, and as verified on the branch.

### Health Overview

| Metric | Before | After |
|--------|-------:|------:|
| Total published posts | 12 | 12 |
| Average score | 55.7 | **61.9** (+6.3) |
| Excellent (90+) | 0 | 0 |
| Good (70-89) | 1 | 1 |
| Needs work (50-69) | 7 | 11 |
| Poor (<50) | 4 | **0** |
| Articles visible on live `/blog/` | 9 | 9 — unchanged, fix not yet deployed |
| Articles on `/blog/` verified on branch | — | 12, plus crawlable `/blog/page/N/` |
| Orphaned articles (no nav path) | 3 | 3 on live until deploy; **0 on branch** |
| Blog-to-blog in-body links | 0 | **22** |
| Posts with ≥1 outbound sibling link | 0 | 11 / 12 |
| Broken internal article links | — | 0 |
| Self-links | — | 0 |
| Duplicate sections | present (4 duplicated H2s on pool-tile alone) | 0 |
| Table-rendering problems | reported | 0 concatenated-text artifacts; 9/12 posts use real `<table>` |
| Metadata mismatches | — | 0 real (1 false positive — HTML-entity encoding) |
| Legacy "Showtime Pool(s) Mechanics" references | — | 0 |
| Posts in HTML sitemap | 12 | 12 |
| Posts in XML sitemap | 12 | 12 |
| Author/byline consistency | `Person` node named "Showtime Pools" | fixed in code → Organization author |

### Per-Post Scores

| Post | Before | After | Delta | Rating |
|------|------:|------:|------:|--------|
| [pool-replaster-cost-and-timing-guide](https://showtimepools.com/pool-replaster-cost-and-timing-guide/) | 71 | 70 | -1 | Acceptable |
| [pool-remodeling-los-angeles](https://showtimepools.com/pool-remodeling-los-angeles/) | 69 | 69 | 0 | Below Standard |
| [when-to-replace-your-pool-pump](https://showtimepools.com/when-to-replace-your-pool-pump/) | 49 | 66 | +17 | Below Standard |
| [why-pebble-finishes-replacing-plaster](https://showtimepools.com/why-pebble-finishes-replacing-plaster/) | 49 | 66 | +17 | Below Standard |
| [pentair-vs-jandy-salt-systems](https://showtimepools.com/pentair-vs-jandy-salt-systems/) | 51 | 65 | +14 | Below Standard |
| [weekly-pool-care-checklist-la-homeowners](https://showtimepools.com/weekly-pool-care-checklist-la-homeowners/) | 49 | 64 | +15 | Below Standard |
| [custom-pool-new-construction-process-showtime-pools](https://showtimepools.com/custom-pool-new-construction-process-showtime-pools/) | 60 | 62 | +2 | Below Standard |
| [spring-pool-opening-step-by-step](https://showtimepools.com/spring-pool-opening-step-by-step/) | 51 | 60 | +9 | Below Standard |
| [pool-pump-repair-services](https://showtimepools.com/pool-pump-repair-services/) | 54 | 58 | +4 | Rewrite |
| [2026-pool-design-trends-los-angeles](https://showtimepools.com/2026-pool-design-trends-los-angeles/) | 46 | 55 | +9 | Rewrite |
| [complete-pool-maintenance-guide-los-angeles](https://showtimepools.com/complete-pool-maintenance-guide-los-angeles/) | 58 | 55 | -3 | Rewrite |
| [pool-tile-and-coping-installation-los-angeles](https://showtimepools.com/pool-tile-and-coping-installation-los-angeles/) | 61 | 53 | -8 | Rewrite |

**Category averages (before → after):** content quality 17.3 → 16.9 · SEO 21.2 → 19.9 · E-E-A-T 6.2 → **9.3** · technical 3.9 → **5.3** · AI-citation 7.2 → **10.4**.

Every post in the "Poor" band cleared it. The four biggest gains are the former stub posts (+14 to +17).

Three posts scored slightly lower. That is a **length effect, not a regression**: those were the long posts carrying duplicated sections, and removing the duplication shortened them. `pool-tile-and-coping` fell from 42 headings — four H2s repeated verbatim — to 8 clean ones. The scorer rewards length; removing duplicated boilerplate is still the correct outcome, and it is why content-quality and SEO sub-scores dipped while E-E-A-T, technical and AI-citation all rose.

### Sitemap Verification (independent — not inherited from the baseline)

| Check | Result |
|-------|--------|
| `robots.txt` declares | `https://showtimepools.com/wp-sitemap.xml` — correct |
| XML index | `/wp-sitemap.xml` — HTTP 200, `application/xml; charset=UTF-8` |
| `/sitemap.xml` | **HTTP 301 → `/wp-sitemap.xml`** — a redirect, not a duplicate index |
| `/sitemap_index.xml` | HTTP 404 — no Yoast/RankMath provider present |
| Child sitemaps | `wp-sitemap-posts-post-1.xml`, `wp-sitemap-posts-page-1.xml`, `wp-sitemap-posts-project-1.xml` |
| Posts child sitemap | all 12 canonical article URLs present |
| All three confirmed orphans in XML | **yes** — positions 1, 2 and 3 in the posts child sitemap |
| All 12 in HTML sitemap `/sitemap/` | **yes** |

**No XML or HTML sitemap code was changed.** Both already carried all 12 posts, including the three orphans. The omission was confined to the blog archive template. Regression coverage was added instead of a fix. There is no duplicate-sitemap-index problem to resolve.

### Article-to-Article Link Matrix

| Post | Outbound siblings | Inbound siblings |
|------|-------------------|------------------|
| 2026-pool-design-trends-los-angeles | why-pebble-finishes-replacing-plaster, pool-remodeling-los-angeles | none |
| complete-pool-maintenance-guide-los-angeles | spring-pool-opening-step-by-step | weekly-pool-care-checklist, spring-pool-opening, custom-pool-new-construction, pool-remodeling, pool-tile-and-coping |
| custom-pool-new-construction-process-showtime-pools | pool-remodeling-los-angeles, complete-pool-maintenance-guide-los-angeles | none |
| pentair-vs-jandy-salt-systems | when-to-replace-your-pool-pump | none |
| pool-pump-repair-services | when-to-replace-your-pool-pump, weekly-pool-care-checklist-la-homeowners | when-to-replace-your-pool-pump |
| pool-remodeling-los-angeles | why-pebble-finishes, pool-replaster-cost, pool-tile-and-coping, complete-pool-maintenance | 2026-pool-design-trends, custom-pool-new-construction, pool-tile-and-coping, pool-replaster-cost |
| pool-replaster-cost-and-timing-guide | pool-remodeling-los-angeles, why-pebble-finishes-replacing-plaster | pool-remodeling-los-angeles, pool-tile-and-coping-installation |
| pool-tile-and-coping-installation-los-angeles | complete-pool-maintenance, pool-remodeling, pool-replaster-cost | pool-remodeling-los-angeles |
| spring-pool-opening-step-by-step | weekly-pool-care-checklist, complete-pool-maintenance-guide | weekly-pool-care-checklist, complete-pool-maintenance-guide |
| weekly-pool-care-checklist-la-homeowners | spring-pool-opening-step-by-step, complete-pool-maintenance-guide | spring-pool-opening-step-by-step, pool-pump-repair-services |
| when-to-replace-your-pool-pump | pool-pump-repair-services | pentair-vs-jandy-salt-systems, pool-pump-repair-services |
| **why-pebble-finishes-replacing-plaster** | **none** | 2026-pool-design-trends, pool-remodeling, pool-replaster-cost |

**22** in-body blog-to-blog links across the set, up from **0** at baseline. Zero self-links. Zero broken destinations — every link resolves HTTP 200 and is canonical (no redirect hops, no parameters).

All three baseline clusters are now interlinked: **pool pumps** (when-to-replace ↔ pool-pump-repair, plus pentair-vs-jandy → when-to-replace), **maintenance** (complete-guide ↔ weekly-checklist ↔ spring-opening), and **finishes/remodel** (pool-remodeling as hub → why-pebble, pool-replaster, pool-tile).

> **The content pass's claim that *every* article links to a sibling is not met.** `why-pebble-finishes-replacing-plaster` has **zero** outbound sibling links, though it receives three inbound. It is the single remaining dead-end and needs one in-body link added in wp-admin — the natural target is `pool-replaster-cost-and-timing-guide`, which already links back to it.

### Remaining Blockers — content-owned, not fixable from this repository

Per `CLAUDE.md`, blog posts, images and their metadata are **CONTENT** edited directly in live wp-admin. These are reported, not changed:

1. **`why-pebble-finishes-replacing-plaster` has no outbound sibling link** (above).
2. **Six of twelve posts share one image** — `finish.jpeg` is the hero for `2026-pool-design-trends`, `why-pebble-finishes`, `weekly-pool-care-checklist`, `spring-pool-opening`, `pentair-vs-jandy`, and `when-to-replace-your-pool-pump`.
3. **AI-generated images making claims they cannot support.** `pool-pump-repair-services` uses `ChatGPT-Image-Jul-14-2026-09_07_11-AM.png` with alt text asserting *"Showtime Pools technician repairing a pool pump beside a swimming pool"* — a specific claim about a named company's technician that a generated image cannot evidence. `pool-replaster-cost-and-timing-guide` uses `ChatGPT-Image-Jul-23-2026-07_26_38-AM.png`. Two further heroes are UUID-named uploads (`19dc7c9b-…`, `29cfcfcd-…`). Flagged for replacement with real job-site photography. **Nothing was generated, replaced or deleted.**
4. **Empty `alt` attributes** on in-body images across the set (the hero images that do carry alt text are accurate; the duplicated in-body copies are empty).
5. **`pentair-vs-jandy-salt-systems` meta description** opens *"Salt water vs chlorine pool?"* while the article compares two salt **brands** and does not deliver a salt-versus-chlorine comparison. Flagged at baseline; still live.
6. **Byline naming.** All 12 bylines read "Showtime Pools". The code now emits a matching `Organization` author. Attributing to Steve Adams as a `Person` would require business confirmation that he authored or reviewed each post — that confirmation does not exist in the repository, so it was not assumed.

### What This Branch Changed — verified on branch, pending deploy

| Fix | Evidence |
|-----|----------|
| `/blog/` page size 9 → 12 | 12 cards render; 21-assertion suite passes |
| Crawlable `/blog/page/N/` | page 2 = HTTP 200 with distinct posts and a real `<a href>` pager |
| No duplicate cards across pages | asserted with fixtures |
| Paginated pages self-canonicalise | page 2 canonical was `/blog/`; now `/blog/page/2/` |
| Author schema contradiction | `@type: Person` named "Showtime Pools" → `Organization` node |

Drafts, private, pending, scheduled, pages and project records are all asserted to stay out of the archive. Coming Soon project exclusions verified unchanged.

### Not Measured

- **Live `/blog/` card count after deploy.** Verified on the branch, not on production — nothing was deployed.
- **Search-performance effects** (rankings, impressions, citation pickup). No Search Console data was pulled; no indexing was requested.

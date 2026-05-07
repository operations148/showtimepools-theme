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

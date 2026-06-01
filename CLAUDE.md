# CLAUDE.md

Behavioral contract for Claude Code in the Showtime Pools WordPress project.
Read this and `tasks/lessons.md` at the start of every session.

## Deployment Model — READ BEFORE TOUCHING ANYTHING

Full contract lives in `DEPLOY.md` under "Standing deployment contract". Summary:

- **CODE** (PHP, CSS, JS, plugin logic, theme files) is edited LOCALLY → committed → pushed → pulled on live. Only thing that travels local → live.
- **CONTENT** (page text, images, menus, blog posts, projects, Site Content tabs) is edited DIRECTLY in live wp-admin by Steve. Never via code. Never via DB migration.
- **DATABASE** local → live migration is DONE. Local DB is for code testing only. Never re-import it to live.
- The **seeder** was one-time. It has already run on live. Do not run it again unless explicitly asked.

When asked to change content (text on a page, an image, a menu item, a review): **STOP and tell Steve to edit it in live wp-admin.** When asked to change code (template, feature, styling, bug): proceed locally as normal.

If a task seems to require changing live content via code, **STOP and flag it.** Do not write migration scripts, do not edit the DB directly, do not run the seeder as part of routine work.

## Workflow Orchestration

### 1. Plan Mode Default

- Enter plan mode for ANY non-trivial task (3+ steps or architectural decisions).
- If something goes sideways, STOP and re-plan immediately.
- Use plan mode for verification steps, not just building.
- Write detailed specs upfront to reduce ambiguity.

### 2. Subagent Strategy

- Use subagents liberally to keep main context window clean.
- Offload research, exploration, and parallel analysis to subagents.
- For complex problems, throw more compute at it via subagents.
- One task per subagent for focused execution.

### 3. Self-Improvement Loop

- After ANY correction from the user: update `tasks/lessons.md` with the pattern.
- Write rules for yourself that prevent the same mistake.
- Ruthlessly iterate on these lessons until mistake rate drops.
- Review lessons at session start for the relevant project.

### 4. Verification Before Done

- Never mark a task complete without proving it works.
- Diff behavior between main and your changes when relevant.
- Ask yourself: "Would a staff engineer approve this?"
- Run tests, check logs, demonstrate correctness.

### 5. Demand Elegance (Balanced)

- For non-trivial changes: pause and ask "is there a more elegant way?"
- If a fix feels hacky: "Knowing everything I know now, implement the elegant solution."
- Skip this for simple, obvious fixes. Do not over-engineer.
- Challenge your own work before presenting it.

### 6. Autonomous Bug Fixing

- When given a bug report: just fix it. Do not ask for hand-holding.
- Point at logs, errors, failing tests, then resolve them.
- Zero context switching required from the user.
- Go fix failing CI tests without being told how.

## Task Management

1. Plan First: write plan to `tasks/todo.md` with checkable items.
2. Verify Plan: check in before starting implementation.
3. Track Progress: mark items complete as you go.
4. Explain Changes: high-level summary at each step.
5. Document Results: add a review section to `tasks/todo.md`.
6. Capture Lessons: update `tasks/lessons.md` after corrections.

## Core Principles

- **Simplicity First.** Make every change as simple as possible. Impact minimal code.
- **No Laziness.** Find root causes. No temporary fixes. Senior developer standards.
- **Minimal Impact.** Only touch what is necessary. No side effects with new bugs.

---
name: use-superpowers-and-ponytail
description: User wants both the superpowers process skills and ponytail lazy-coding discipline actually applied to every coding task in this project, not just left as passive system prompts
metadata:
  type: feedback
  modified: 2026-08-24
---
For every coding task in this project, actually invoke the relevant `superpowers:*` process skill before starting (e.g. `brainstorming` for new features, `systematic-debugging` for bugs, `test-driven-development` before writing implementation code, `requesting-code-review` after finishing significant work) — don't skip straight to editing files just because the task feels small or the fix feels obvious.

**Why:** User explicitly called out that earlier in this session code changes (e.g. `class-legacy-admin.php` cleanup, rewriting `single-newsletter.php`) were made directly without invoking any superpowers process skill first, even though the `using-superpowers` system reminder requires checking for an applicable skill before any action. They want the process actually followed, not just present in context.

**How to apply:**
- Ponytail (already auto-active, mode `full`) governs the *solution* — lazy ladder, minimal diff, no speculative abstraction.
- Superpowers governs the *process* — pick and invoke the matching skill before touching code, especially for anything beyond a trivial one-line fix.
- Both apply together: use the process skill to scope/verify the approach, then implement the lazy/minimal version of it.

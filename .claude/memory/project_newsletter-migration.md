---
name: Newsletter Migration Plan
description: Moving newsletter hosting from Flowpaper + FileZilla to WordPress self-hosted; legacy import phase is complete
metadata:
  type: project
  modified: 2026-08-24
---
# Newsletter Migration to WordPress

## Status (as of 2026-08-24)
Legacy migration is done. All existing Flowpaper export folders have been imported via the ZIP upload flow. **No more legacy newsletters will be uploaded going forward.**

Every new newsletter from now on is designed in Figma and coded by hand into the `newsletter` CPT as a bespoke `single-newsletter.php` landing page — see [[feedback_no-newsletter-template-picker]]. The Legacy Import admin page and its ZIP-upload code path (`class-legacy-admin.php`) still exist and must keep working (old newsletters still need to be servable/re-importable if something breaks), but it is no longer part of the active workflow — don't propose it for new content, and don't spend effort extending it beyond bug fixes.

**Why:** User explicitly said the legacy upload phase is finished; all new work is Figma-design → hand-coded build.
**How to apply:** When the user asks to add a new newsletter, assume Figma + custom HTML/CSS per [[project_INT-Q4-2026-cover]] workflow, not ZIP upload. Legacy-import code is maintenance-only now.

## Approach Chosen: Cách C (Plugin Custom Endpoint — Secure)
- Legacy folders stored in `wp-content/uploads/legacy-newsletters/` (NOT in web root)
- Plugin creates custom endpoints to serve static files with proper MIME types, incl. HTTP Range support for video
- HTML paths are rewritten to point through WordPress endpoints
- WordPress controls access, no direct file execution possible

## New Newsletter Workflow (Figma → code, active workflow)
1. Design in Figma (bespoke per issue)
2. Export section images
3. Hand-code as `the_content()` of a `newsletter` CPT post, sections as `background-image` — a blank page built from scratch to match the Figma design exactly
4. `single-newsletter.php` only wraps it as a standalone `<!DOCTYPE html>` document (no theme header/footer) — **no Scroll/Flip toggle, no fit-to-page viewer, no admin picker**. That toggle mechanism (2026-05 era) was a test harness for two trial issues (Q3 2026, Q4 2026); both were deleted 2026-08-24 and no published newsletter uses it. See [[project_landing-page-modes]] (deprecated).

## Plugin Structure (verified 2026-08-24)
```
wp-content/plugins/fls-newsletter/
├── fls-newsletter.php              # Main plugin file
├── includes/
│   ├── class-legacy-serve.php      # Secure endpoint serving legacy folders (Range support)
│   ├── class-legacy-admin.php      # Admin ZIP-upload import (legacy, maintenance-only)
│   └── class-newsletter-cpt.php    # CPT + case-sensitive slug routing + template_include
└── assets/
    ├── flipbook.js / flipbook.css
    ├── electric-landing.css        # Shared landing page styles (Scroll/Flip modes)
    └── newsletter-q3-2026.css, newsletter-q4-2026.css  # Per-issue styles
```
`single-newsletter.php` itself lives in `wp-content/themes/twentytwentyfive/`, not in the plugin.

## Completed / Verified
1. Legacy folder serving — case-sensitive URL + 301 redirect from lowercase, confirmed correct even under WP Studio's SQLite (its MySQL-compat layer applies `COLLATE NOCASE` to TEXT columns, so case-insensitive meta_query lookups behave the same as MySQL).
2. CPT `newsletter` — single-template landing pages, no template picker.
3. ~~Scroll + Flip(fit-to-page)/Flipbook modes~~ — deprecated 2026-08-24, see item 4 above.
4. Admin ZIP import — cleaned up 2026-08-24: removed dead single-file handler (`handle_zip_import`, unreachable since the form always posts `zip_files[]`) and dead `folder_has_post()`; import now strips `__MACOSX/` artifacts so they don't get extracted into newsletter folders.

## Project docs
`AGENTS.md` is the single source of truth for current rules; `CLAUDE.md` just points to it (`@AGENTS.md`). This `.claude/memory/` directory (git-committed) is the cross-agent-readable historical record — separate from Claude Code's own machine-local auto-memory and from the `claude-mem` plugin's observation log, neither of which is visible to other agents (e.g. Codex) that clone this repo.

**Why:** User explicitly chose security over convenience. URL format must be preserved for branding and existing links.
**How to apply:** Always use endpoint-based serving for legacy content, never copy to web root. Maintain slug-based URLs for all newsletters.

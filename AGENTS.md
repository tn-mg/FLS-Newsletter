# AI Instructions

This is a local WordPress site managed by [WordPress Studio](https://developer.wordpress.com/studio/).
For WordPress Studio instructions, see @STUDIO.md.

## Project Context

This project is the FLS newsletter migration site. It replaces the old Flowpaper + FileZilla workflow with WordPress-hosted newsletters.

- Current Q3 2026 source design: [FLS Newsletter](https://www.figma.com/design/3cot8wjUvwe8Svg74me9XB/FLS-Newsletter?node-id=0-1&m=dev). Before changing this issue, inspect the relevant desktop/mobile node and compare the rendered result against Figma.

- Legacy newsletters are Flowpaper export folders.
- New newsletters are created from Figma/HTML and published as WordPress `newsletter` posts.
- The main custom plugin is `wp-content/plugins/fls-newsletter/`.
- The active custom templates currently live in `wp-content/themes/twentytwentyfive/`.

## WordPress Studio Rules

- Always use `studio wp` for WP-CLI commands. Do not use a bare `wp` command.
- Run `studio site status` to get the current local URL and admin details. Do not hardcode ports.
- This Studio site uses SQLite. Do not add MySQL `DB_*` assumptions.
- Do not edit `wp-admin/`, `wp-includes/`, `wp-content/db.php`, or the SQLite must-use plugin.

## Newsletter Rules

- Newsletter URLs must preserve the folder-style format: `/{Folder-Name}/`.
- Exact case matters. Lowercase or wrong-case URLs should redirect 301 to the stored exact-case URL when supported.
- Legacy files belong in `wp-content/uploads/legacy-newsletters/` and must be served through the plugin endpoint, never copied to the web root. Reference them as `/{Folder-Name}/images/...`; the raw `/wp-content/uploads/...` path bypasses the plugin and its Range handling.
- `FLS_Legacy_Serve::serve_file()` must keep HTTP Range support (`Accept-Ranges`, `206 Partial Content`, `416` on a bad range, `flush()` per chunk). Without it large MP4s cannot seek and Safari often refuses to play them at all.
- Legacy flipbooks embed domain-locked FlowPaper license keys that only validate on `newsletter.fls-group.com`. `localhost`, `127.0.0.1`, `192.168.*` and `file://` bypass the check, so a passing local test says nothing about production. Do not attempt to patch or disable the check.
- Legacy ZIP upload should stay one-step: upload ZIP, auto-extract, auto-create a published newsletter post. **Legacy import is complete (as of 2026-08-24)** — all existing Flowpaper export folders have been migrated. Don't propose the ZIP-upload path for new content; it stays in the codebase for maintenance only (re-serving or re-importing already-existing legacy folders).
- New newsletters use the `newsletter` CPT but have **no shared template, toggle, or viewer mechanism**. Each issue is built completely from scratch as a blank page: view the Figma design, hand-code the HTML/CSS/images directly as the post's `the_content()`, matching its visual composition subject to the responsive content-flow rules below — no Scroll/Flip mode toggle, no fit-to-page scaling, no admin picker, since every issue's layout is different. `single-newsletter.php` still renders the CPT post as a standalone `<!DOCTYPE html>` document (own `wp_head()`/`wp_footer()`, no theme header/footer) so each newsletter stays its own landing page, but do not add the Scroll/Flip toggle UI to new issues. Legacy newsletters still redirect to their serve endpoint before any output, unaffected by this.
- **Follow Figma for visual intent:** section order, imagery and crops, colors, backgrounds, major composition, responsive card arrangement, interaction pattern, decorative shapes, border treatment, and deliberate inclusion/omission of content. An explicitly supplied Figma node/frame is the acceptance reference for these decisions.
- **Do not follow Figma mechanically for long-form copy on mobile/tablet:** prefer consistent gutters, readable font size and line-height, even tag/title/paragraph rhythm, browser-native wrapping, and enough card/section height to avoid clipping or overlap. Do not force `<br>` breaks merely to reproduce a screenshot; responsive copy and ACF text should wrap naturally. Deliberate branded display-title breaks may remain on desktop.
- For Q3 responsive copy, use the established rhythm unless a later design requires otherwise: mobile `440px` canvas with `32px` content gutters; tablet `960px` canvas with `60px` outer gutters and `32px` card gutters; body copy `14px/1.45`; paragraph gap about `16px`. Keep sibling cards aligned even when their copy lengths differ.
- If the user explicitly says a Figma alignment is wrong or asks for practical visual judgment, that instruction overrides the exact Figma spacing. After responsive changes, verify `375x812`, `768x1024`, `1024x768`, and `1440x900` for natural wrapping, clipping/overlap, horizontal overflow, carousel controls, and console errors.
- The Scroll/Flip mode toggle system (`.fls-mode-toggle` + `.fls-flipbook-page` fit-to-page scaling JS in `single-newsletter.php`, styled by `electric-landing.css`) was a test harness built for two trial issues (Q3 2026, Q4 2026 landing pages). Both trial posts have since been deleted and no published newsletter uses it — treat it as unused/legacy code, not a pattern to extend or maintain. History: `.claude/memory/project_landing-page-modes.md`.

## Development Preferences

- Prefer focused custom code over off-the-shelf plugins when the custom approach is reasonable ("vibe coding" — the user consistently chooses hand-written code over installing a plugin).
- Keep changes scoped to `wp-content/themes/` and `wp-content/plugins/` unless explicitly asked otherwise.
- Preserve existing meta keys, URL behavior, and newsletter workflows unless the user asks for a migration.

## Deployment

Target is Hostinger at `newsletter.fls-group.com`, migrating off Studio's SQLite to MySQL via All-in-One WP Migration. Two things bite hardest: WordPress `siteurl`/`home` must be changed to the real domain (DNS alone does not do it, and `class-legacy-serve.php` rewrites FlowPaper asset paths through `home_url()`), and the Studio/SQLite drop-ins must be excluded from the export. The ~14GB `legacy-newsletters/` directory exceeds AIOWM's free 512MB cap and moves separately over SFTP. Full checklist: `.claude/memory/project_hostinger-deployment.md`.

## Project Memory

`.claude/memory/MEMORY.md` indexes historical context (past decisions, discoveries, open items) built up across sessions — check it for nuance beyond this file, but this file (`AGENTS.md`) is the source of truth for current rules.

## Skills

This project ships custom skills under `.claude/skills/` for agents that support the Skill tool (e.g. Claude Code):

- `self-improve` — auto-update memory and this file when the user gives feedback or confirms decisions.
- `karpathy-guidelines` — reduce common LLM coding mistakes: Think Before Coding, Simplicity First, Surgical Changes, Goal-Driven Execution.
- `agent-browser` — browser automation CLI for AI agents; prefer this over built-in browser tools for navigating pages, filling forms, testing web apps.
- `frontend-design` — guidance for distinctive, production-grade UI work; avoids generic "AI slop" aesthetics.

## Onboarding

`AGENTS.md` (this file) is the single source of truth for project rules and is the primary onboarding source for every agent, including Codex. `CLAUDE.md` only points here. The `.claude/memory/` files are supplementary historical context, not rules.

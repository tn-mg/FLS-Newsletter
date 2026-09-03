---
name: no-newsletter-template-picker
description: Newsletter CPT no longer has a standard-vs-landing template choice — every newsletter is its own standalone landing page
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 68a9c21f-3f67-4e2b-8c9c-442a200682f2
  modified: 2026-08-17T05:02:48.865Z
---

Every newsletter post is now its own standalone landing page, not a page wrapped in the theme's header/footer. The user removed the "standard template" vs "landing template" choice entirely — there is only one template (`single-newsletter.php`), which renders a full `<!DOCTYPE html>` document with its own `wp_head()`/`wp_footer()` and no `get_header()`/`get_footer()` calls.

**Why:** Each newsletter issue is now sent by the team as its own separate Figma link with its own bespoke design ("mỗi cuốn team sẽ gửi 1 link figma riêng, thiết kế riêng"), so there's no shared "template mode" to pick between — every issue is custom-coded HTML content per [[project_INT-Q4-2026-cover]]-style workflow. Confirmed 2026-08-17: zero live newsletter posts used the old "standard" or "landing" meta-driven templates (all 29 published `newsletter` posts were legacy imports), so this was a safe, no-migration consolidation.

**How to apply:**
- Do not reintroduce a `_fls_newsletter_template` admin dropdown or branch `template_include` on it. All non-legacy newsletters route to `single-newsletter.php` unconditionally.
- The old admin meta boxes "Newsletter Pages" (image gallery), "Flipbook" (enable checkbox), and "Template" (dropdown) were removed as dead code along with their meta keys (`_fls_newsletter_pages`, `_fls_enable_flipbook`, `_fls_newsletter_template`) — they only served the retired scroll+toggle "standard" template.
- Legacy (Flowpaper ZIP) newsletters are untouched by this — they still redirect straight to the plugin's serve endpoint before any template loads. See [[feedback_security-and-url]].
- **Update 2026-08-24:** this went one step further — the Scroll/Flip mode toggle itself (in `single-newsletter.php`/`electric-landing.css`) is also deprecated now, not just the template picker. See [[project_landing-page-modes]] (now marked deprecated) and [[project_newsletter-migration]]. New newsletters are blank pages built from scratch per Figma, with no shared toggle/viewer mechanism at all — `single-newsletter.php` only still provides the standalone-document wrapper (no theme header/footer), nothing more.

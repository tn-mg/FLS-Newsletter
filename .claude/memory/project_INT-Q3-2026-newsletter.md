---
name: INT-FLS-Newsletter-Q3-2026 Creation
description: Created a sample newsletter post with cut sections from reference image as backgrounds.
type: project
originSessionId: dcf920f5-c198-4751-b02b-72b0ddd4f3f3
---
Created newsletter post "INT FLS Newsletter Q3 2026" (post ID 46, slug INT-FLS-Newsletter-Q3-2026). This is a demonstration of building a newsletter landing page from a reference image by cutting it into sections and coding each part as HTML with background images.

**Why:** User requested a sample newsletter created from a reference image to understand the workflow of coding each part individually.

**How to apply:**
1. Reference image was cut into 5 sections and saved to `wp-content/uploads/2026/05/`:
   - `newsletter-q3-hero.png`
   - `newsletter-q3-tip1.png`
   - `newsletter-q3-tip2.png`
   - `newsletter-q3-tip3.png`
   - `newsletter-q3-cta.png`
2. Post content uses inline CSS + HTML with each section as a separate `<section>` with `background-image: url(...)`.
3. Template used: `landing` (via `_fls_newsletter_template` meta).
4. Case-sensitive slug: `INT-FLS-Newsletter-Q3-2026`.
5. URL: `http://localhost:8882/INT-FLS-Newsletter-Q3-2026/`

**Key files used:**
- `wp-content/uploads/2026/05/newsletter-q3-*.png` — Cut image sections
- Theme template: `single-newsletter-landing.php`
- Plugin assets: `electric-landing.css`

**Workflow for future newsletters:**
1. Upload reference image to WordPress media library.
2. Cut into logical sections (hero, tips, cta, etc.) using image editor or Python PIL.
3. Upload sections to media library.
4. Create new newsletter CPT post with template = landing.
5. Code HTML for each section with corresponding background image and overlaid text content.
6. Set case-sensitive slug in post meta for custom URL.

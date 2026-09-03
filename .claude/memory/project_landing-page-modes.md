---
name: Landing Page Viewing Modes
description: "DEPRECATED 2026-08-24: Scroll/Flip toggle viewer was a test harness for Q3/Q4 2026 trial issues, both now deleted. No published newsletter uses this anymore — do not extend or reuse it for new newsletters."
type: project
originSessionId: e34c394e-fcd9-473c-a173-f73d6fd5a883
modified: 2026-08-24
---
> **DEPRECATED 2026-08-24.** This Scroll/Flip/Flipbook toggle mechanism was built for two trial landing-page issues (Q3 2026, Q4 2026). The user deleted both test posts — they were never real published issues, just a test of the approach. Going forward, new newsletters have **no shared template, toggle, or viewer mechanism at all**: each is a blank page, hand-coded from scratch to match its Figma design (see [[project_newsletter-migration]]). The code below (`single-newsletter.php` toggle JS, `electric-landing.css`) still exists in the repo but is unused — kept as historical reference only, not a pattern to follow.

# Landing Page Newsletter — Three Viewing Modes (historical, unused)

## Files
- `wp-content/themes/twentytwentyfive/single-newsletter.php` — Standalone template (no theme header/footer) with mode toggle + JS logic. As of 2026-08-17, this is the ONLY newsletter template — `single-newsletter-landing.php` and the old "standard" template were merged/retired since every newsletter is now its own bespoke landing page (see [[feedback_no-newsletter-template-picker]]).
- `wp-content/plugins/fls-newsletter/assets/electric-landing.css` — Styles for all modes

## Modes

### 1. Scroll (default)
- Content flows naturally, full page width
- Sections fade in via IntersectionObserver (`fls-animate-in` / `is-visible`)
- A4 content width (794px) centered on dark gray background (#222222)

### 2. Flip (scroll per page)
- Each `<section>` becomes a scrollable page
- Pages have `overflow-y: auto` with max-height matching viewport
- Prev/Next buttons + keyboard arrows + swipe gestures
- Built by cloning sections from scroll view on first activation
- **Known limitation:** Long pages require scrolling inside the page card

### 3. Flipbook (fit-to-page + zoom)
- Each page fits entirely within the viewport via `transform: scale()`
- No per-page scrolling — content is scaled down to fit
- Zoom In / Zoom Out / Fit buttons in bottom nav
- Scale calculated as `containerHeight / contentHeight`, clamped min 0.25
- Reset to fit-scale when switching pages
- A4 container (794px × 1123px) centered, with `overflow: hidden`
- Prev/Next + keyboard arrows + swipe gestures

## Key Implementation Details

**Scroll animations issue:**
- `initScrollAnimations()` adds `fls-animate-in` (opacity: 0) to sections
- When cloning for Flip/Flipbook, must strip `fls-animate-in` and add `is-visible`
- Both root element and all nested `.fls-animate-in` children need stripping

**Flipbook scaling:**
- Outer `.fls-flipbook-page`: `position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)`
- Inner `.fls-flipbook-page__inner`: `transform-origin: top center; transition: transform 0.2s ease`
- `fitBookPage()` measures `inner.scrollHeight` vs `container.clientHeight - 40`
- Zoom buttons multiply/divide scale by 1.2, clamped 0.2–3.0

**Side arrows (mobile UX optimization):**
- Fixed circular buttons at `top: 50%; transform: translateY(-50%)`, near left/right edges
- Blue `rgba(0, 85, 165, 0.85)` with white arrow SVGs
- 48px desktop / 44px mobile, `z-index: 101`
- Classes: `fls-flip-side-prev`, `fls-flip-side-next`, `fls-flipbook-side-prev`, `fls-flipbook-side-next`

**Touch swipe fix:**
- Swipe handler attached per-page wrapper (not container) because `.fls-flip-page.is-active` has `pointer-events: auto`, blocking container events
- `handleTouch()` uses `{ passive: true }` so vertical scroll isn't blocked
- Threshold: 50px horizontal difference

**Responsive:**
- Mobile: page width becomes `calc(100vw - 24px)`, height derived from A4 ratio
- Nav buttons shrink, zoom controls compacted
- Side arrows shrink to 44px and move to 4px from edge

## Why
User wanted Flowpaper-style viewing where every page fits on screen without internal scrolling. Created as a separate third mode to preserve existing Flip behavior for comparison.

**Why side arrows + swipe:** User found bottom nav arrows hard to operate on mobile (2026-05-07). Added side arrows at mid-screen for thumb reach + fixed swipe gesture to match native app behavior.

## How to apply
- When editing flip/flipbook JS, always strip `fls-animate-in` from cloned content
- When adding new sections, ensure they use semantic `<section>` tags so Flip/Flipbook can split them into pages
- Test zoom behavior on both desktop and mobile viewports
- When modifying touch/swipe: attach handler to individual `.fls-flip-page` / `.fls-flipbook-page` elements, not the container

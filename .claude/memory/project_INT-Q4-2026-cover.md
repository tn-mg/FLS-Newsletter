---
name: INT Q4 2026 Newsletter — Pages 1–3 Implementation
description: Fibonacci cover (p01), About Author (p02), Magazine Foreword (p03) for INT FLS Newsletter Q4 2026 — Figma Magazine Template
type: project
originSessionId: 9d420ecf-c8fa-4a33-bd7e-579806eeebed
---
## Newsletter post (WP ID 48)

- **Post ID:** 48  
- **Slug:** `INT-FLS-Newsletter-Q4-2026`  
- **URL:** `/INT-FLS-Newsletter-Q4-2026/`  
- **Template:** `landing` (`_fls_newsletter_template`)  
- **Flipbook:** enabled (`_fls_enable_flipbook = 1`)  
- **CSS file:** `wp-content/plugins/fls-newsletter/assets/newsletter-q4-2026.css`

## Cover page visual layout (Figma → HTML)

The Figma design (node `0:4`, file `hUmdd7T14zdRxKwcVeiBBL`) uses an internal `rotate-180` container. After accounting for the flip, the **visual layout** on the 1190×1684px page is:

```
x=0          733     907   1190
y=0   ┌──────────┬────────────┐
      │  gr2     │  gr3       │  ← top 43.5% (733px)
      │ (jungle) │(terrarium) │
y=453 │          ├────┬───────┤
      │          │ gr6│ gr4   │
y=560 │          ├────┘       │
      │          │ gr5        │
y=733 ├──────────┴────────────┤
      │   MAIN HERO IMAGE     │  ← bottom 56.5% (951px)
      │   (dark forest)       │
y=1090│   [gradient starts]   │
y=1141│   TEXT OVERLAY        │
y=1684└───────────────────────┘
```

- **Golden ring:** center (593px, 887px), radius ~906px, border 3px `#c9a96e`, `opacity: 0.75`
- **Dark veil:** `rgba(0,14,9,0.48)`, covers full page
- **Gradient:** 594px tall at bottom (`rgba(0,0,0,0) → rgba(0,0,0,0.64)`)

## Image assets (plugin assets)

Stored in `wp-content/plugins/fls-newsletter/assets/images/q4-2026/`:

| File | Figma source (MCP asset ID) | Visual role |
|------|-----------------------------|-------------|
| `p01-gr2.jpg` | `ddb37d18` (imgImage5) | Large left panel — jungle with vines |
| `p01-gr3.jpg` | `8c8d2114` (imgPhoto036) | Upper right — glass terrarium |
| `p01-gr4.jpg` | `d19babf7` (imgImage7) | Small right-bottom panel |
| `p01-gr5.jpg` | `e9505a0c` (imgImage8) | Small mid-right panels (gr5 + gr6) |
| `p01-main.jpg` | `c8941a8e` (imgImage4) | Main hero: dark jungle |
| `p01-spiral.png` | `dd61fe67` (imgImage14) | Texture (not used; golden ring is CSS) |

| `p02-author.png` | `848620d0` | Author circular portrait (568×568 RGBA) |
| `p03-botanical-bottomright.png` | `56280ca8` | Vine/leaf botanical, bottom-right, opacity 30% |
| `p03-botanical-topleft.png` | `c422fcc1` | Top-left floral (mostly transparent — Figma auth needed) |
| `p03-botanical-bottomleft.png` | `75f7956e` | Bottom-left lily (mostly transparent — Figma auth needed) |
| `p03-mask-topleft.png` | `6258f96f` | CSS mask for top-left botanical |
| `p03-mask-bottomleft.png` | `79561b61` | CSS mask for bottom-left botanical |

⚠️ Figma MCP asset URLs expire in 7 days from 2026-05-10. Images already saved locally. The top-left and bottom-left botanicals downloaded as near-transparent PNGs — Figma MCP assets can only be reliably fetched via the MCP tool itself, not via curl.

## CSS classes (newsletter-q4-2026.css)

- `.q4-p01__cell` — base for all Fibonacci image panels (`position: absolute; overflow: hidden; border: 2px solid rgba(0,14,9,0.8)`)
- `.q4-p01__gr2..gr6` — individual panel positions (percentage-based)
- `.q4-p01__hero` — main hero image, `top: 43.53%` to bottom
- `.q4-p01__veil` — full-page dark veil
- `.q4-p01__gradient` — bottom gradient
- `.q4-p01__spiral-ring` — CSS golden circle ring
- `.q4-p01__text` — text wrapper at `top: 67.82%`, `left: 10.08%`
- `.q4-p01__title` — serif title (Georgia), `font-size: clamp(28px, 9.75vw, 116px)`, uppercase
- `.q4-p01__sub` — subtitle, Gotham, `clamp(12px, 2.02vw, 24px)`

Scroll/flip visibility rules added (mirrors Q3 pattern):
- `.fls-landing-content .q4-page-flip { display: none !important; }`
- `.fls-flip-page .q4-page-scroll { display: none !important; }`

## Current title & subtitle

- Title: `INT FLS<br>Newsletter<br>Q4 2026`
- Subtitle: `Kết nối toàn cầu — Hiệu quả trong từng tuyến đường vận chuyển`

### Pages 2–3 CSS classes

- `.q4-p02-scroll / .q4-p02-flip` — About Author page (bg `#002517`, aspect-ratio 1190/1684)
- `.q4-p02__inner-rect` — subtle grey veil (15% opacity, `#d9d9d9`)
- `.q4-p02__content` — centered flex column, 732/1190 wide, starts at 134/1684 from top
- `.q4-p02__portrait` — circular image 284/1190 wide, `border-radius: 50%`
- `.q4-p02__title` — Georgia serif, `clamp(18px, 3.19vw, 38px)`, white, centered
- `.q4-p02__body` — Gotham, `clamp(11px, 2.02vw, 24px)`, white
- `.q4-p02__contacts` — absolute, `top: 1472/1684`, centered, 712/1190 wide
- `.q4-p03-scroll / .q4-p03-flip` — Magazine Foreword page (bg `#eeece4`, same aspect-ratio)
- `.q4-p03__botanical-tl/bl/br` — botanical decorations at corners (opacity 20–30%, mix-blend-multiply)
- `.q4-p03__br-fade` — gradient fade masking left edge of bottom-right botanical
- `.q4-p03__content` — absolute, translate(-50%,-50%), centered both axes, 694/1190 wide
- `.q4-p03__title` — Georgia serif, same clamp as p02 title
- `.q4-p03__body` — same as p02 body but `color: #0c0c0c`

### Figma source nodes
- P01 cover: node `0:4` (or `0:5`)
- P02 About Author: node `0:36`
- P03 Magazine Foreword: node `0:50`
All from file `hUmdd7T14zdRxKwcVeiBBL`

**Why:** User asked to implement Figma Magazine Template pages 2 and 3 (nodes 0:36 and 0:50) for the Q4 2026 newsletter.  
**How to apply:** When editing this newsletter, reference the CSS class structure above. To swap images, replace files in `assets/images/q4-2026/`. The top-left/bottom-left botanical PNGs (p03-botanical-topleft/bottomleft.png) may need to be replaced with real botanical artwork.

# FLS Newsletter Analytics

Reusable GA4 + GTM tracking for FLS WordPress newsletter posts.

## Scope

The plugin loads only on the `newsletter` custom post type and recognizes both variants of each quarterly issue:

- `INT-FLS-Newsletter-Q3-2026` → `newsletter_audience: INT`
- `EXT-FLS-Newsletter-Q3-2026` → `newsletter_audience: EXT`

Future URLs such as `INT-FLS-Newsletter-Q4-2026` work without a GTM change.

## Connected analytics

- GTM container: `GTM-NSKQ344W`
- GA4 property: `FLS Newsletter` (`552695374`)
- Web stream: `newsletter.fls-group.com`
- Measurement ID: `G-2YXTB2XDL7`

The website sends events to `dataLayer`. GTM owns GA4 delivery; the plugin does not add a second direct `gtag.js` implementation.

## Events

- `newsletter_view`
- `newsletter_scroll` at 25, 50, 75, and 90 percent, once per threshold
- `newsletter_section_view` when at least half of a semantic section is visible
- `newsletter_nav_click`
- `newsletter_cta_click`
- `newsletter_video_play`
- `newsletter_carousel_interaction` when explicitly marked by an issue implementation
- `newsletter_download`
- `newsletter_contact_click`
- `newsletter_outbound_click`

Email addresses and phone numbers are never placed in the event payload. Link query strings and fragments are stripped before tracking.

## Installation

1. Deploy this folder to:
   `wp-content/plugins/fls-newsletter-analytics/`
2. In WordPress Admin, open **Plugins** and activate **FLS Newsletter Analytics**.
3. Keep `wp_head()` and `wp_footer()` in the standalone newsletter template.
4. Add `<?php wp_body_open(); ?>` immediately after the template's opening `<body>` tag. The repository patch already includes this in `wp-content/themes/twentytwentyfive/single-newsletter.php`.
5. Deploy the Q3 section partial updates from this repository. They add analytics-only `data-fls-*` attributes and do not alter layout or copy.
6. Purge the WordPress/Hostinger/CDN cache.

Do not paste another GTM or GA4 snippet into the theme. The plugin already loads the dedicated newsletter container.

## Semantic markup for future issues

The visual classes and layout can change. Analytics depends only on semantic attributes:

```html
<section data-fls-section="leadership_message">
  <a
    href="https://fls-group.com/contact/"
    data-fls-track="cta_click"
    data-fls-label="contact_team">
    Contact us
  </a>

  <button
    type="button"
    data-fls-track="video_play"
    data-fls-video="ceo_interview">
    Play video
  </button>
</section>
```

Use business meanings such as `leadership_message`, `case_studies`, and `contact_team`. Do not use presentation names such as `blue_card` or `section_3`.

For a carousel, send/mark `newsletter_carousel_interaction` only after the selected item actually changes. Include semantic values for `carousel_name`, `carousel_item`, and `interaction_action`.

## Local JavaScript tests

```bash
node --test wp-content/plugins/fls-newsletter-analytics/tests/tracking.test.js
```

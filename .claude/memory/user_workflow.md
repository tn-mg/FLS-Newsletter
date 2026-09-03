---
name: User Newsletter Workflow
description: How the user creates and publishes newsletters (current, as of 2026-08-24)
type: user
originSessionId: e34c394e-fcd9-473c-a173-f73d6fd5a883
modified: 2026-08-24
---
# User Workflow

## Before (historical — pre-migration)
1. Create newsletter in Flowpaper
2. Export to folder (e.g. EXT-FLS-Newsletter-Q1-2026)
3. Upload folder via FileZilla to hosting

## Legacy migration (done, 2026-08-24)
All existing Flowpaper export folders were uploaded via the plugin's ZIP-import (upload ZIP → auto-extract → auto-published `newsletter` post). Nothing left to migrate — this is maintenance-only from here on. See [[project_newsletter-migration]].

## Current & going forward: new newsletters
1. Team sends a Figma link for the issue — every issue is its own bespoke design, no shared template.
2. View the Figma design, export section assets as needed.
3. Hand-code the issue from scratch as the `newsletter` CPT post's content (`the_content()`) — plain HTML/CSS matching the Figma layout exactly.
4. **No display-mode choice, no Scroll/Flip toggle, no admin picker** — that toggle system was a test harness for two now-deleted trial issues (Q3/Q4 2026) and is not used anymore. Each new post is just a blank page built to match its design 1:1.
5. `single-newsletter.php` still wraps it as a standalone landing page (own `<!DOCTYPE html>`, no theme header/footer), so the URL/hosting side is unchanged — only the "shared viewer mechanism" idea was dropped.

## User Approach
- Prefers to "vibecode" — custom code solutions, not off-the-shelf plugins
- Values security (chose most secure option C despite more code)
- Wants clean URL structure (`subdomain/tên-folder`)
- **Important:** Wants URL case to match folder name exactly (e.g. `INT-FLS-Newsletter-Q1-2026` not lowercase)
- Wants lowercase URLs to still work but redirect 301 to correct case

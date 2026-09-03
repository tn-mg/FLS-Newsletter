---
name: flowpaper-license-domain-lock
description: Legacy newsletters carry domain-locked FlowPaper license keys valid for newsletter.fls-group.com — they fail on any other domain
metadata: 
  node_type: memory
  type: project
  originSessionId: 68a9c21f-3f67-4e2b-8c9c-442a200682f2
  modified: 2026-08-17T11:06:58.683Z
---

The 27 legacy Flowpaper newsletters each embed a FlowPaper Flipbook Maker license key in their `index.html` (the `"key"` field in the `FlowPaperViewer()` config). Two distinct keys are in use: `$e76129f04a1821f586d` on 26 of them, and `$6bf4c92cc5fa07368cd` on `fls-the-world-movers-q12025-ext`. Both keys are **domain-locked** and both were verified live in production on `https://newsletter.fls-group.com` on 2026-08-17 — so both licenses are valid for that domain and the migration needs no vendor contact and no rebuild.

On any other hostname the viewer shows an alert: "License key not accepted. Please check your configuration settings." This is what appears on the Hostinger staging domain (`*.hostingersite.com`) and it is expected, not a migration failure.

`FlowPaperViewer.js`'s `ba()` helper skips the license check entirely for `localhost`, `127.0.0.1`, `192.168.*`, `10.1.1.*`, `file://`, and FlowPaper's own domains. That is why local Studio testing at `localhost:8882` always passes regardless of license validity — local success proves nothing about production.

**How to apply:**
- Never judge legacy flipbook health by the Hostinger staging domain; test by adding `<Hostinger-IP> newsletter.fls-group.com` to `/etc/hosts` (browser sees the real hostname, content comes from the real server, no bypass applies). Expect a cert warning until SSL is issued post-DNS.
- Do not patch or disable FlowPaper's license check — that is license circumvention. The legitimate fixes are serving on the licensed domain, asking Devaldi for a new key, or dropping FlowPaper via the rebuild path.
- When deploying, WordPress `siteurl`/`home` must be set to the licensed domain: `class-legacy-serve.php` rewrites FlowPaper asset paths through `home_url()`, so a stale staging URL sends CSS/JS/images to the wrong host.
- The rebuild alternative (own viewer over the pre-rendered `docs/*.jpg` page images) removes this third-party dependency permanently — relevant if the company ever changes domain again. See [[feedback_no-newsletter-template-picker]].

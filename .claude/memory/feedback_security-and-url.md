---
name: Security and URL Format Preferences
description: User chose secure endpoint approach and specific URL format for newsletters
type: feedback
originSessionId: e34c394e-fcd9-473c-a173-f73d6fd5a883
---
# Security & URL Preferences

## Rule: Always prefer secure endpoint serving over web root
**Why:** User explicitly asked which option is most secure and chose Option C (Plugin Custom Endpoint) even though it requires more code. Security is a priority.

**How to apply:** When serving legacy/static content in WordPress, never place files in document root. Always use plugin-controlled endpoints from `wp-content/uploads/` or similar non-web-root locations.

## Rule: Maintain `subdomain/tên-folder` URL format
**Why:** User has existing links and branding. Folder names must be preserved exactly as exported (e.g. `ext-fls-newsletter-q1-2026`, `int-fls-newsletter-q1-2026`).

**How to apply:** All newsletter URLs, both legacy and new, must follow `subdomain/folder-name` format. Do not add `/newsletter/` or other prefixes unless user explicitly requests.

## Rule: Support both scroll and simple flipbook modes
**Why:** User wants viewers to be able to choose between scrolling and a simple page-turning experience (just prev/next arrows, no WebGL/3D).

**How to apply:** Every new newsletter should support both modes. Legacy newsletters keep their original Flowpaper viewer. New newsletters use custom JavaScript flipbook (simple image swap with arrows).

## Rule: Preserve exact URL case with lowercase fallback
**Why:** User explicitly said: "nếu nhập chữ thường thì vẫn vào được nhưng link sẽ tự đổi cho đúng" (if typing lowercase, it should still work but the URL should auto-correct to the right case).

**How to apply:** Store exact folder case in `_fls_legacy_slug` meta. When serving legacy content, check for exact case match first. If only lowercase match found, 301 redirect to the exact case URL. Always return 301, never 200 on wrong case.

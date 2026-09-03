---
name: Simple ZIP-Only Upload Workflow
description: User wants the newsletter admin workflow to be as simple as uploading a ZIP file — auto-extract and auto-import in one step
type: feedback
originSessionId: 886e68b2-d6ea-4647-8179-929650d3f55d
---
# Simple ZIP-Only Upload Workflow

## Rule: Newsletter import must be ZIP-only, one-step
**Why:** User explicitly said "cái newsletter tui chỉ cần upload zip thôi" — they do not want multi-step manual extraction or going through Legacy Import tabs. The workflow should be: upload ZIP → done.

**How to apply:**
- Provide a direct ZIP upload in the main Newsletters admin (not buried in Legacy Import tabs)
- Auto-extract the ZIP into `wp-content/uploads/legacy-newsletters/`
- Auto-create the `newsletter` post with title derived from ZIP filename
- Show progress/results inline, no page reload required if possible
- Support bulk upload of multiple ZIPs at once

## Rule: Avoid manual steps after upload
**Why:** The user has 25+ newsletters to manage. Any per-newsletter manual step is friction.

**How to apply:** After ZIP upload, the post should be created automatically with status Published. The user should only need to upload the file, nothing else.

## Rule: Use filename as title source
**Why:** The ZIP filename (e.g. `EXT-FLS-Newsletter-Q1-2026.zip`) already encodes the title.

**How to apply:** Derive post title from ZIP filename by replacing `-` and `_` with spaces and applying `ucwords()`. Do not ask the user to enter a title manually for legacy imports.

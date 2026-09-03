---
name: legacy-video-assets
description: Legacy newsletter videos are oversized (4.56GB, one is 1.2GB) — needs Range serving plus compression before production
metadata:
  type: project
---

The legacy newsletters embed 22 MP4 files totalling ~4.56GB, but only 11 are distinct — every INT/EXT pair ships a byte-identical copy (verified by md5 on 2026-08-17). Several are far too large for web playback: `FLS Projects-VN-AU.mp4` is 1.2GB, `Our-Message-Video.mp4` (Q2 2026) is 498MB, `Our Message.mp4` (Q4 2025) is 175MB. They are ordinary H.264/AAC but encoded at source resolution and bitrate — the Q2 2026 clip is a 60-second 2160×3840 video at 69 Mbps.

**Two separate causes of "video won't play"**

1. *Missing Range support* — fixed 2026-08-17 in `class-legacy-serve.php::serve_file()`. It now answers `Range` requests with `206 Partial Content` (plus `Accept-Ranges`, a `416` path for bad ranges, `flush()` per chunk, and an output-buffer clear before streaming). Without this the endpoint returned the whole file on every request, so `<video>` could not seek and Safari often refused to start at all. Keep this behavior on any edit to that method.
2. *Raw file size* — not fixed. Even with correct Range serving, a 498MB clip stalls for real users, especially on mobile.

**How to apply:** always reference legacy assets through the plugin endpoint (`/{Folder-Name}/images/...`), never the raw `/wp-content/uploads/legacy-newsletters/...` path — the raw path is served statically and skips the Range handling entirely, which silently reintroduces the stall. Compression was trialled and reverted on 2026-08-17 (the user asked to undo the rebuild experiment), but the recipe worked: ffmpeg with `-c:v libx264 -preset medium -crf 23 -vf "scale='min(1280,iw)':-2" -c:a aac -b:a 128k -movflags +faststart` cut the set by ~85% (1.2GB→53M, 498M→30M, 175M→26M) with acceptable quality. Two files barely moved (`FLS Warehousing x DOW - HD Video.mp4` 46M→49M, `71bf1eea-…mp4` 50M→49M) because they were already efficiently encoded — skip those. ffmpeg is not installed on this machine and Homebrew needs sudo; a static build from evermeet.cx runs fine without installing. Compress before the Hostinger upload, since it also cuts transfer time. See [[hostinger-deployment]].

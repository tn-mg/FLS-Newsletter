---
name: hostinger-deployment
description: Plan and gotchas for moving this Studio site to Hostinger — SQLite→MySQL, 14GB media, domain-locked license, siteurl
metadata:
  type: project
---

The site moves from WordPress Studio (local, SQLite) to Hostinger (MySQL) and will be served at `newsletter.fls-group.com` — the same subdomain the old Flowpaper site still runs on as of 2026-08-17. Chosen tool: All-in-One WP Migration, which reads through `$wpdb` and therefore converts SQLite content to MySQL on import without a manual SQL dump.

**Order of operations**

1. Add `newsletter.fls-group.com` in hPanel *before* touching DNS — this creates the vhost server-side. Hostinger warns the domain is not pointed yet; that is expected. Without this step a hosts-file test hits the default page, not the site.
2. Set WordPress `siteurl`/`home` to `https://newsletter.fls-group.com`. DNS alone does not change this, and a stale staging URL breaks two things: `class-legacy-serve.php` rewrites FlowPaper asset paths through `home_url()` (CSS/JS/images would point at `*.hostingersite.com`), and the newsletter CPT pages get canonical-redirected back to the staging domain.
3. Verify before the DNS switch by adding `<Hostinger-IP> newsletter.fls-group.com` to `/etc/hosts`. Expect a TLS warning — SSL cannot be issued until DNS actually points at Hostinger.
4. Flip DNS. Lower the record TTL to ~300s a few hours ahead so rollback is fast, and do it off-hours since the old site is live.
5. Issue Let's Encrypt SSL in hPanel, then flush permalinks (Settings → Permalinks → Save).

**Must exclude from the AIOWM export** (Studio/SQLite-only files that would break a MySQL host): `wp-content/db.php`, `wp-content/mu-plugins/sqlite-database-integration/`, `wp-content/mu-plugins/99-studio-loader.php`, `wp-content/database/`.

**Media is too big for AIOWM.** `wp-content/uploads/legacy-newsletters/` is ~14GB while the free AIOWM caps at 512MB. Exclude that directory from the export and move it separately over SFTP/SSH — faster than stuffing it into a `.wpress` file. Buying the Unlimited extension (~$70) is the alternative. Most of the 14GB is disposable: `locale/` (30+ unused languages), `assets_zine/` (page-flip skins and sounds), and the FlowPaper JS libraries; only `docs/*.jpg` and the videos are real content. See [[legacy-video-assets]].

**How to apply:** legacy flipbooks will show a license error on the Hostinger staging domain no matter what — that is [[flowpaper-license-domain-lock]], not a broken migration. Judge the migration by the hosts-file test on the real domain instead. Two folders are junk or already broken and worth checking before upload: `__MACOSX/` (extraction debris) and `FLS issue 1 2 (1)/`, which has no root `index.html` (it holds a nested `FLS issue 1/` folder) and so is likely already serving a 404.

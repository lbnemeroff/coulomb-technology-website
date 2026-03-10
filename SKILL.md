---
name: coulomb-website
description: >-
  Maintain, update, and build pages for the Coulomb Technology WordPress website
  (coulombtechnology.com). Use this skill for any task involving the Coulomb Technology
  site: editing page content, updating images, fixing CSS/layout issues, adding new pages,
  updating nav, configuring forms, uploading media to WordPress, and deploying changes.
  Covers the full workflow: local file editing, deploy via REST API, Git commit, and known gotchas.
---

# Coulomb Technology Website

## CRITICAL: Scope of Changes

**Only modify files that are explicitly part of the current user request.** Once a page is live and approved, it must not be touched unless the user specifically asks for a change to that page.

- If a task requires editing `coulomb-pages.php` (e.g., to bump a version), limit the edit to only what is necessary — do not modify any other page's HTML or CSS as a side effect.
- Do not "improve" or "align" other pages while working on a requested page, even if inconsistencies are noticed.
- Do not deploy files for pages not mentioned in the current request.
- If a potential issue is noticed on an unrelated approved page, **report it to the user** rather than fixing it silently.

## Architecture

The site runs on WordPress (GoDaddy hosting) with a custom plugin called **coulomb-custom-pages** that injects fully custom HTML/CSS into specific pages by slug, bypassing Avada/theme rendering entirely.

**Local plugin directory:** `/home/ubuntu/coulomb-pages-plugin/`

```
coulomb-pages-plugin/
├── coulomb-pages.php       # Main plugin: slug matching, CSS enqueue, AJAX handlers
├── html/                   # Per-page HTML body files
│   ├── home-body.html
│   ├── seriesb-body.html   # 279V Series B (Smart EMS)
│   ├── seriesdc-body.html  # 279V Series DC (Core BESS)
│   ├── seriesr-body.html   # 48V Series R (Smart EMS)
│   ├── seriesm-body.html   # 48V Series M (Core BESS)
│   ├── seriess-body.html   # 12V Series S (Core BESS)
│   ├── contact-body.html
│   ├── ci-body.html        # Commercial & Industrial
│   ├── def-body.html       # Defense & Government
│   ├── mf-body.html        # Motive & Fleet
│   ├── tb48-body.html      # 48V Traction Battery
│   ├── smartems-body.html  # Smart EMS Technology
│   └── besscore-body.html  # BESS Core Technology
└── css/                    # Per-page CSS files (same names as html/)
```

**Deploy scripts:** Use the specific deploy scripts generated in `/home/ubuntu/` (e.g., `deploy_sodium_live.py`) for targeted deployments. The generic `deploy_coulomb.py` may not exist or may be outdated.

## Page Slug → File Mapping

| WordPress Slug | HTML File | CSS File |
|---|---|---|
| `/` (home) | `home-body.html` | `home.css` |
| `contact` | `contact-body.html` | `contact.css` |
| `commercial-industrial` | `ci-body.html` | `ci.css` |
| `defense-government` | `def-body.html` | `def.css` |
| `motive-fleet` | `mf-body.html` | `mf.css` |
| `48v-traction-battery` | `tb48-body.html` | `tb48.css` |
| `279v-series-b` | `seriesb-body.html` | `seriesb.css` |
| `279v-series-dc` | `seriesdc-body.html` | `seriesdc.css` |
| `48v-series-r` | `seriesr-body.html` | `seriesr.css` |
| `series-m` | `seriesm-body.html` | `seriesm.css` |
| `series-s` | `seriess-body.html` | `seriess.css` |
| `smart-ems` | `smartems-body.html` | `smartems.css` |
| `bess` | `besscore-body.html` | `besscore.css` |

**Note:** The All Products page (`all-products` slug) is a native WordPress page — edit its content via WP REST API, not via the plugin HTML files.

## Deploy Workflow

Always follow this sequence for any change:

```bash
# 1. Edit files in /home/ubuntu/coulomb-pages-plugin/
# 2. Deploy to live site
python3 /home/ubuntu/deploy_coulomb.py

# 3. Verify in browser (add ?v=N to bust cache)
# https://coulombtechnology.com/PAGE-SLUG/?v=N

# 4. Commit to Git
cd /home/ubuntu/coulomb-pages-plugin && git add -A && git commit -m "Description"
```

**Never skip verification before committing.** Deploy immediately after each change — do not batch multiple unverified changes.

## WordPress REST API

Credentials are in `deploy_coulomb.py`:
- `WP_USER = "Nemeroff"`
- `WP_PASS = "kMWpFhpjHqawGtOEBO9CM9f6"`
- Base URL: `https://coulombtechnology.com/wp-json/`

Use the REST API for:
- Uploading media (images, PDFs) to WordPress media library
- Updating native WordPress pages (like All Products)
- Querying existing media URLs

```python
import requests, base64
auth = base64.b64encode(b"Nemeroff:kMWpFhpjHqawGtOEBO9CM9f6").decode()
headers = {"Authorization": f"Basic {auth}"}
# Upload media
with open("/path/to/file.pdf", "rb") as f:
    r = requests.post("https://coulombtechnology.com/wp-json/wp/v2/media",
        headers={**headers, "Content-Disposition": 'attachment; filename="file.pdf"',
                 "Content-Type": "application/pdf"}, data=f)
wp_url = r.json()["source_url"]
```

## Nav Structure (All Pages)

Every page uses identical nav HTML. The canonical nav includes:
- `id="mainNav"` on the `<nav>` element
- `id="hamburger"` on the mobile hamburger button
- Products dropdown with **All Products** (blue `#0066FF`, bold) at top, then Smart EMS group (Series-B, Series-R) and Core BESS group (Series-DC, Series-M, Series-S)
- Nav dropdown product label format: `Series-X (Subtitle) [voltage tag right-aligned]`

**Product naming convention in nav:**
- Series-B: (Business) — 279V
- Series-DC: (DC Block) — 279V
- Series-R: (Residential) — 48V
- Series-M: (Mobility) — 48V
- Series-S: (Starter) — 12V

When updating nav across all pages, use a Python script — do NOT manually edit each file. After any bulk nav update, verify all pages have the IntersectionObserver script intact (see below).

## Avada CSS Override Patterns

Avada's global CSS resets many properties. Using `!important` is a good first step, but often insufficient. Use high-specificity selectors to guarantee overrides.

### Centering Content

To center-align text content inside a container, Avada's `text-align` overrides must be fought with `!important` on both the container and its children:

```css
.container-class {
  text-align: center !important;
}
.container-class * {
  text-align: center !important;
}
```

### Forcing Text Color

To force a specific text color (e.g., white text on a dark background), a simple `color: #fff !important` is often not enough. Avada may apply styles to `span` or `a` tags inside your `h4` or `p` tags. Use a high-specificity selector that targets all possible children:

```css
.card-class h4,
.card-class h4 a,
.card-class h4 span {
  color: #ffffff !important;
}
.card-class p,
.card-class p span {
  color: rgba(255,255,255,0.85) !important;
}
```

### Mobile Nav

```css
/* Mobile nav — must be hidden globally, not inside media query only */
.mobile-nav { display: none !important; }
.mobile-nav.open { display: block !important; }
```

**Never scope mobile-nav CSS to a page-specific class** (e.g., `.coulomb-home-page .mobile-nav`) — if the wrapper class doesn't exist on the page, `display: none` is never applied and the raw nav list renders as a visible bullet list at the top of the page.

## Scroll Animations (fade-up) — CRITICAL

Every page uses `IntersectionObserver` to trigger `.fade-up` → `.visible` transitions. This script **must** be present at the end of every page's `<script>` block:

```javascript
// Intersection observer for fade-up animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });
document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
```

**If content sections are invisible after deploy:** the IntersectionObserver is missing. Diagnose with browser console:
```javascript
document.querySelectorAll('.fade-up').length        // > 0 means elements exist
document.querySelectorAll('.fade-up.visible').length // 0 means observer not running
// Quick fix to reveal content temporarily:
document.querySelectorAll('.fade-up').forEach(el => el.classList.add('visible'));
```

**After any bulk script that modifies HTML files (e.g., nav normalization), always verify this block is preserved in every file.**

## Contact Form

The contact form on `/contact/` uses a custom AJAX handler in `coulomb-pages.php`.

- **Notification recipients:** `tim@coulombtechnology.com` (To), `lisa@coulombtechnology.com` (CC)
- **Thank-you message:** The success `<div id="cfSuccess">` must be **outside** the `<form>` element (but inside `.contact-form-wrap`). If nested inside `<form>`, it gets hidden along with the form on success.

## Brochures

All product brochures are hosted in WordPress media library at:
`https://coulombtechnology.com/wp-content/uploads/`

When uploading large PDFs (>10MB), compress first using `pypdf` or ghostscript. The REST API upload times out on files >20MB over slow connections.

**Never link brochures to Google Drive** — always upload to WordPress and link to the `wp-content/uploads` URL.

## WordPress Page Template — CRITICAL

Every custom plugin page **must** use the `blank.php` page template in WordPress. Without it, Avada wraps the page content in a constrained `.post-content` container (~1200px wide), which prevents dark/colored sections from bleeding full width across the viewport.

To check or set the template for a page:
```python
import requests, base64
auth = base64.b64encode(b'Nemeroff:kMWpFhpjHqawGtOEBO9CM9f6').decode()
headers = {'Authorization': f'Basic {auth}', 'Content-Type': 'application/json'}
# Check template
r = requests.get('https://coulombtechnology.com/wp-json/wp/v2/pages?slug=PAGE-SLUG&_fields=id,slug,template', headers=headers)
print(r.json())
# Set template to blank.php
r = requests.post('https://coulombtechnology.com/wp-json/wp/v2/pages/PAGE_ID', headers=headers, json={'template': 'blank.php'})
```

**Symptom:** Dark/colored section backgrounds stop ~1200px from the left edge, leaving a white gap on the right side of the viewport.
**Fix:** Set the page template to `blank.php` via the REST API (see above).
**Verified pages using blank.php:** all product pages (series-b, series-dc, series-m, series-r, series-s), all industry pages (commercial-industrial, defense-government, motive-traction), contact.
**About Us** uses `100-width.php` — do not change it.

## Common Tasks

### Shared Section CSS — Economics / TCO

The Bankable Economics / TCO section (`.economics-section`, `.econ-proof-*`, `.tco-chart-wrap`, `.tco-assumptions`, `.tco-table`) uses shared CSS that **must be explicitly included in every product page CSS file** that uses this section. It is NOT globally shared — each page's CSS file is independent.

When adding the economics section to any product page:
1. Copy the full `/* BANKABLE ECONOMICS / TCO SECTION */` block from `seriesdc.css` (lines ~1036–1090) into the target page's CSS file
2. Verify the section renders with styled stat boxes, chart card, and assumptions table — not as plain text
3. The canonical source of this CSS block is `seriesdc.css`

### Update an image on a product page
1. Extract image from brochure PDF using `pdf2image` + `PIL` crop
2. Upload to WordPress via REST API → get `source_url`
3. Replace `src="..."` in the relevant HTML file
4. Deploy and verify

### Add a new page
1. Create the WordPress page with the desired slug via WP admin or REST API (use `create_PAGENAME_page.py` pattern)
2. Create `html/PAGENAME-body.html` and `css/PAGENAME.css` in the plugin
3. Add slug matching + CSS enqueue block to `coulomb-pages.php` — include: CSS enqueue, Avada content hide, `wpautop` disable, shortcode registration, and `no_texturize` registration
4. Write a targeted deploy script `deploy_PAGENAME_live.py` using the `files` array payload format (see existing scripts in `/home/ubuntu/`)
5. Deploy and verify

**CRITICAL — Body-only HTML:** Plugin HTML files must contain ONLY the body content — no `<!DOCTYPE>`, `<html>`, `<head>`, or `<style>` tags. If building a standalone mockup first, strip the wrapper before deploying:
```python
from bs4 import BeautifulSoup
with open('mockup.html') as f:
    soup = BeautifulSoup(f.read(), 'html.parser')
body_content = soup.body.decode_contents()
with open('plugin/html/PAGENAME-body.html', 'w') as f:
    f.write(body_content)
```

### Mockup-First Workflow (CEO Review)

All proposed changes must go through a local mockup review before deploying live:
1. Build standalone mockup HTML files in `/home/ubuntu/mockup/` (full `<!DOCTYPE>` wrappers, self-contained)
2. Add orange dashed annotation boxes for changed sections (see homepage mockup for the exact CSS/HTML format)
3. Serve locally via `python3 -m http.server 8080` and expose via `expose` tool
4. Get approval before deploying to live
5. When deploying, strip the mockup wrapper (see above) and use the targeted deploy script

**Exception:** Brand-new pages (not modifying existing content) do not need orange annotation boxes — they are self-evidently new.

### Upload Images to WordPress

Images used in plugin HTML files must be uploaded to WordPress media library — local mockup paths (`/home/ubuntu/mockup/`) will 404 on the live site:
```python
import requests, base64, os
auth = base64.b64encode(b"Nemeroff:kMWpFhpjHqawGtOEBO9CM9f6").decode()
headers = {"Authorization": f"Basic {auth}"}
for img_path in ["/home/ubuntu/mockup/image.jpg"]:
    fname = os.path.basename(img_path)
    ext = fname.split('.')[-1].lower()
    mime = {'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png', 'webp': 'image/webp'}.get(ext, 'image/jpeg')
    with open(img_path, 'rb') as f:
        r = requests.post("https://coulombtechnology.com/wp-json/wp/v2/media",
            headers={**headers, "Content-Disposition": f'attachment; filename="{fname}"', "Content-Type": mime},
            data=f)
    wp_url = r.json()["source_url"]
    print(f"{fname} → {wp_url}")
```
Then replace the local paths in the HTML file with the returned `wp_url` values before deploying.

### Publish / Unpublish a WordPress Page

```python
import requests, base64
auth = base64.b64encode(b"Nemeroff:kMWpFhpjHqawGtOEBO9CM9f6").decode()
headers = {"Authorization": f"Basic {auth}", "Content-Type": "application/json"}
# Publish (status: publish) or unpublish (status: draft)
requests.post("https://coulombtechnology.com/wp-json/wp/v2/pages/PAGE_ID",
    headers=headers, json={"status": "publish"})  # or "draft"
```
To find a page ID: `GET /wp-json/wp/v2/pages?slug=PAGE-SLUG`

### Deploy Script Pattern

All deploy scripts use the `files` array payload format:
```python
import requests, base64, os, json
WP_USER, WP_PASS = "Nemeroff", "kMWpFhpjHqawGtOEBO9CM9f6"
DEPLOY_ENDPOINT = "https://coulombtechnology.com/wp-json/coulomb/v1/deploy"
auth = base64.b64encode(f"{WP_USER}:{WP_PASS}".encode()).decode()
headers = {"Authorization": f"Basic {auth}", "Content-Type": "application/json"}
files_to_deploy = [
    {"path": "html/PAGENAME-body.html", "content": open("/home/ubuntu/coulomb-pages-plugin/html/PAGENAME-body.html").read()},
    {"path": "css/PAGENAME.css", "content": open("/home/ubuntu/coulomb-pages-plugin/css/PAGENAME.css").read()},
]
r = requests.post(DEPLOY_ENDPOINT, headers=headers, json={"files": files_to_deploy})
print(r.json())
```

### Update nav across all pages
Use a Python script to replace nav HTML patterns — never edit files one by one. After any bulk nav update, verify:
- All pages have `id="mainNav"` and `id="hamburger"`
- All pages have the mobile nav `<div class="mobile-nav">` block
- All pages have the IntersectionObserver script intact
- No page-scoped CSS rules for `.mobile-nav` (must be global)

### Slug → File Mapping (updated)

Add new pages to the slug table in this skill file when created:

| WordPress Slug | HTML File | CSS File |
|---|---|
| `technology/sodium-ion` | `sodium-body.html` | `sodium.css` |

## Design System

| Token | Value |
|---|---|
| `--green` | `#39FF14` (neon green — CTAs, accents) |
| `--blue` | `#0066FF` (Coulomb blue — All Products nav link) |
| `--charcoal` | `#1a1a1a` |
| `--text-light` | `#555` |
| Nav height | `72px` |
| Max content width | `1200px` |
| Hero font (h1) | Inter, 700 weight |

## Git Repository

Local: `/home/ubuntu/coulomb-pages-plugin/`
All changes must be committed after each deploy verification. Use descriptive commit messages.

## Page Revert Workflow

To revert a page to a previous version:
```bash
cd /home/ubuntu/coulomb-pages-plugin
git log --oneline html/PAGENAME-body.html  # find the commit before the change
git show COMMIT_HASH:html/PAGENAME-body.html > /tmp/PAGENAME-prev.html
git show COMMIT_HASH:css/PAGENAME.css > /tmp/PAGENAME-prev.css
cp /tmp/PAGENAME-prev.html html/PAGENAME-body.html
cp /tmp/PAGENAME-prev.css css/PAGENAME.css
# Then run the deploy script and commit the revert
```

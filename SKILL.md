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
| `motive-traction` | `mf-body.html` | `mf.css` |
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

## Nav Structure (All Pages) — CANONICAL DEFINITION

**Consistency is imperative.** Every page on the site must use exactly the same nav HTML. Never create a custom or modified nav for any page — always copy the canonical block below verbatim.

### Canonical Nav HTML

```html
<nav class="nav" id="mainNav">
  <div class="nav-inner">
    <a href="https://coulombtechnology.com/" class="nav-logo" aria-label="Coulomb Technology">
      <img src="https://coulombtechnology.com/wp-content/uploads/2026/02/coulomb-logo-black-1.jpg" alt="Coulomb Technology">
    </a>
    <ul class="nav-links">
      <li class="nav-has-dropdown">
        <a href="#">Industries <span class="nav-arrow">▼</span></a>
        <div class="nav-dropdown">
          <a href="/commercial-industrial/" class="nav-drop-item">Commercial &amp; Industrial</a>
          <a href="/defense-government/" class="nav-drop-item">Defense &amp; Government</a>
          <a href="/motive-traction/" class="nav-drop-item">Motive / Traction Batteries</a>
        </div>
      </li>
      <li class="nav-has-dropdown">
        <a href="/all-products/">Products <span class="nav-arrow">▼</span></a>
        <div class="nav-dropdown">
          <a href="/all-products/" class="nav-drop-item nav-drop-all-products">All Products <span class="nav-drop-tag nav-drop-tag-green">Overview</span></a>
          <div class="nav-drop-divider"></div>
          <div class="nav-drop-section-label">Smart EMS</div>
          <a href="/279v-series-c/" class="nav-drop-item">Series-C <span class="nav-drop-tag">279V</span></a>
          <a href="/48v-series-r/" class="nav-drop-item">Series-R <span class="nav-drop-tag">48V</span></a>
          <div class="nav-drop-divider"></div>
          <div class="nav-drop-section-label">Core BESS</div>
          <a href="/279v-series-dc/" class="nav-drop-item">Series-DC <span class="nav-drop-tag">279V</span></a>
          <a href="/series-m/" class="nav-drop-item">Series-M <span class="nav-drop-tag">48V</span></a>
          <a href="/series-s/" class="nav-drop-item">Series-S <span class="nav-drop-tag">12V</span></a>
        </div>
      </li>
      <li class="nav-has-dropdown">
        <a href="#">Technology <span class="nav-arrow">▼</span></a>
        <div class="nav-dropdown">
          <a href="/smart-ems/" class="nav-drop-item">Smart EMS <span class="nav-drop-tag nav-drop-tag-green">AI-Powered</span></a>
          <a href="/bess/" class="nav-drop-item">BESS Core <span class="nav-drop-tag">Open Protocol</span></a>
          <a href="/technology/sodium-ion/" class="nav-drop-item">Sodium-Ion Technology <span class="nav-drop-tag nav-drop-tag-green">Chemistry</span></a>
        </div>
      </li>
      <li><a href="/about-us/" class="nav-link">About Us</a></li>
    </ul>
    <div class="nav-cta-group">
      <a href="/contact/" class="nav-cta">Contact Us</a>
    </div>
    <div class="nav-hamburger" id="hamburger">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
</nav>
```

### Nav Rules (MUST follow)

- **All links must be relative paths** (e.g., `/series-s/`) — never absolute URLs like `https://coulombtechnology.com/series-s/`
- **Industries dropdown label:** "Motive / Traction Batteries" → `/motive-traction/` (NOT "Motive & Fleet" or `/motive-fleet/`)
- **Products top-level link:** `href="/all-products/"` (NOT `href="#"`)
- **Technology links:** `/smart-ems/`, `/bess/`, `/technology/sodium-ion/` — never `/technology/smart-ems/` or `/technology/bess-core/`
- **About Us label:** always "About Us" with `class="nav-link"` — never just "About"
- **No "Coming Soon" items** in the nav — do not add placeholder products until their pages are fully built
- **All Products page** is a native WordPress page (not a plugin shortcode page) — its nav must be updated via the WP REST API when nav changes are made

### Updating Nav Across All Pages

When the nav structure changes (new product added, link updated, etc.):
1. Update the canonical nav block above in this skill file
2. Run a Python script to replace the nav in all plugin HTML files (never edit files one by one)
3. Also update the All Products WordPress page nav via REST API (page ID: 2538)
4. Deploy all pages using `/home/ubuntu/deploy_all_pages.py`
5. Verify all pages in browser before committing

After any bulk nav update, verify all pages have the IntersectionObserver script intact (see below).

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

## Known Gotchas & Lessons Learned

### WordPress Page Caching — "Blank Page" or "Shortcode Not Rendering"

**Symptom:** A page appears blank, or shows the raw shortcode text (e.g., `[coulomb_seriesb]`) instead of the rendered content.

**Root cause:** This is almost always a **WordPress page caching issue**, NOT a plugin or shortcode registration problem. The live site caches rendered pages, and after a deploy or content update the cache may serve a stale version that shows the raw shortcode.

**Diagnosis before assuming a bug:**
1. Check the page via the WP REST API with `?context=edit` — if `rendered` length is > 0, the shortcode IS working server-side
2. Check the raw content — if it contains `[coulomb_PAGENAME]`, the shortcode is correctly stored
3. Add `?v=N` (cache-busting query param) to the URL in the browser to bypass CDN/browser cache
4. If the REST API `rendered` field shows the full HTML but the browser shows blank — it's purely a cache issue

**Fix:** Wait 1–2 minutes for the cache to expire, or append `?nocache=1` or `?v=N` to the URL. Do NOT attempt to rewrite the plugin PHP, change the page template, or push the full HTML directly into WP page content — these are unnecessary and can cause new problems.

**Confirmed working architecture for all product pages:**
- WordPress page content stores: `[coulomb_PAGENAME]` shortcode (17 chars)
- Plugin PHP registers the shortcode and returns `file_get_contents('html/PAGENAME-body.html')`
- Page template: `blank.php`
- This pattern works for: series-m, series-s, series-r, series-dc, 279v-series-c, smart-ems, bess, about-us, contact, all industry pages

### Series C (279v-series-c) — Shortcode Name

The Series C page (WP page ID: 2363, slug: `279v-series-c`) uses the shortcode `[coulomb_seriesb]` and the HTML file `seriesb-body.html`. This is because the page was originally created as "Series B" and renamed to "Series C" — the underlying shortcode name was not changed. This is intentional and working correctly. Do NOT rename the shortcode or HTML file unless doing a full migration.

### Nav Label vs. Slug Mismatch (Series C)

The nav shows **"Series-C"** but the link points to `/279v-series-c/` (not `/series-c/`). This is correct — the WordPress page slug is `279v-series-c` and cannot be changed without breaking existing links. The display label in the nav is "Series-C" for user clarity.

### All Products Page — Native WordPress Page

The All Products page (`/all-products/`) is NOT a plugin shortcode page — its full HTML (including nav) is stored directly in the WordPress page content. When making nav changes, this page must be updated separately via the WP REST API (page ID: 2538). The `deploy_all_pages.py` script handles this automatically.

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

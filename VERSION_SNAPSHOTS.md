# Coulomb Technology Website — Version Snapshots

This file records the last known-good state of each page after a major update.
If work is lost or a page regresses, reference these snapshots to restore.

---

## Homepage (`/`) — v1.x STABLE
**Date:** 2026-02-27
**Git Commit:** `2030059` (coulomb-pages-plugin master)
**Files:** `html/home-body.html` (932 lines), `css/home.css` (1,653 lines)

### Design Summary
- **Nav:** White background (`rgba(255,255,255,0.97)`), black Coulomb logo (`coulomb-logo-black-1.jpg`), dropdowns with Industries / Products / Technology / About. Contact Us button `#0066FF` blue. Hamburger menu on mobile.
- **Hero:** Full-screen video (`hero-video.mp4`) with dark overlay and fallback poster image (`coulomb_hero_montage.jpg`). H1: *"Energy Storage You Can Bank On"*. Two CTAs: green "Find Your Solution" → `/contact/`, white outline "Why Sodium-Ion?" → `#why`.
- **Section 2 — Industries:** Three industry cards (Commercial & Industrial, Defense & Government, Motive & Fleet) with icons and links.
- **Section 3b — Intelligent Energy Platform:** Feature showcase for Smart EMS / BESS Core with product cards.
- **Section 3 — Why Sodium-Ion:** Bold tabbed split section comparing sodium-ion vs lithium-ion across Safety, Performance, Economics tabs.
- **Section 4 — Partners:** Research partners logo strip: Oak Ridge, Argonne, Rutgers, NEI Corp, Cleantech Open, Innovation Crossroads, Spark Cleantech, Bronco Ventures, Cradle to Commerce, NENY.
- **Section 5 — Final CTA:** White/light background. H2: *"Ready to Get Started?"*. CTA cards grid.
- **Footer:** Custom dark footer (Avada footer hidden by plugin PHP). White logo (`coulomb-logo-white-1.webp`), Products / Industries / Company columns, LinkedIn + Twitter social icons, copyright, Made in America tagline.

### CSS Design Tokens
- `--green: #6BBF00` (homepage uses this variant, not `#4caf1a`)
- `--blue: #0066FF`
- `--charcoal: #1A1A1A`
- `--light: #F7F7F7`
- `--max-w: 1400px`

### Key Notes
- Homepage nav uses **black logo** (light nav bar) — all other pages use **white logo** (dark nav bar).
- Hero video autoplay is forced via JS with fallback to poster image if autoplay is blocked.
- The Why Sodium-Ion section uses tabbed JS interaction (`data-tab` attributes).

---

## BESS Core Page (`/bess/`) — v2.0 STABLE
**Date:** 2026-02-27
**Git Commit:** `6a8d960` (coulomb-pages-plugin master)
**Files:** `html/besscore-body.html` (622 lines), `css/besscore.css` (470 lines)

### Design Summary
- **Hero:** Solid dark gradient (`#0a0a0a` → `#0d1117` → `#0f2010`), no slideshow. Neon green eyebrow pill, white H1 with brand green italic emphasis, 4-stat bar (dark background, white text).
- **What Is a BESS?** — 3 explainer cards on white background.
- **Three Pillars** — Dark cards (`#1a1f2e`), unified neon green icons, white headings with `!important` overrides (Avada resets text color).
- **How NFPP Chemistry Works** — 4-step science cards (Na⁺, Fe, ∞, ✓) on light grey.
- **Performance** — Color-coded horizontal bar charts: NFPP green (100%), LFP amber (~67%), NMC red (~33%), Lead-Acid dark red (~8%). Two charts: Cycle Life and Temperature Range.
- **Head-to-Head Table** — NFPP vs Liquid-Cooled LFP, 10 criteria, green checkmarks vs red X.
- **TCO Bar Chart** — LFP $146/kWh (red bar) vs NFPP $126/kWh (green bar), $20/kWh savings badge.
- **Applications** — 6 cards on white background.
- **CTA Band** — Light grey (`#f1f3f4`) background (NOT dark — avoids black-on-black merge with footer). Heading/body text forced black via inline `style` attributes. Green primary button, dark-outline secondary button.
- **Footer** — Custom dark footer (Avada footer hidden by plugin PHP). Logo `height: 32px`.
- **Nav** — Custom nav with Contact Us button `#0066FF` (blue).

### Key CSS Gotchas Fixed
- Pillar card text required `!important` on `color: #ffffff` — Avada overrides `h3` and `p` colors inside dark cards.
- CTA band text required inline `style="color:#1a1a1a"` — Avada overrides even `!important` CSS on `h2` inside certain containers.
- Stats bar was `#f1f3f4` (light) — changed to `#0d1117` (dark) to flow from hero; all stat text updated to white.
- Footer was missing from HTML (removed by mistake) — restored from `smartems-body.html`.
- Footer CSS was missing `.footer-logo img` rule — added `height: 32px; width: auto`.
- `#footer-wrapper { display: none !important; }` must be present — plugin PHP hides Avada footer globally.

---

## Smart EMS Page (`/smart-ems/`) — v1.x STABLE
**Date:** 2026-02-27
**Files:** `html/smartems-body.html`, `css/smartems.css`

### Design Summary
- Dark navy/charcoal hero, AI rendering images, architecture diagram.
- Three technology pillars (EnergiOS™, Coulomb AI™, GridIQ™).
- Revenue/ROI section with charts.
- Light grey CTA band above dark footer.
- Nav Contact Us button: `#0066FF` (blue).

---

## Contact Page (`/contact/`) — v1.x STABLE
**Date:** 2026-02-27
**Files:** `html/contact-body.html`, `css/contact.css`

### Design Summary
- Dark hero, 2-column layout (form left, sidebar right).
- 6 inquiry type cards, full form with AJAX submit.
- Footer logo fixed: `height: 32px` added to `contact.css` (was missing, causing 392px oversized logo).

---

## Version Snapshot Protocol

**After every major page update, add a new entry above with:**
1. Page name and URL slug
2. Date
3. Git commit hash (`git log --oneline -1`)
4. File names and line counts (`wc -l html/PAGE-body.html css/PAGE.css`)
5. Brief design summary (sections, colors, key layout decisions)
6. Any CSS gotchas or Avada override notes discovered

**To restore a page from snapshot:**
```bash
# Check out the specific commit
git show COMMIT_HASH:html/PAGE-body.html > /tmp/restore.html
git show COMMIT_HASH:css/PAGE.css > /tmp/restore.css
# Then copy to plugin dir and deploy
```

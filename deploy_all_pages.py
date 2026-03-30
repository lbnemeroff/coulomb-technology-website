"""
Deploy all Coulomb Technology plugin pages to the live WordPress site.
This ensures every page has the canonical nav and latest content.
"""
import requests, base64, os, json

WP_USER = "Nemeroff"
WP_PASS = "kMWpFhpjHqawGtOEBO9CM9f6"
DEPLOY_ENDPOINT = "https://coulombtechnology.com/wp-json/coulomb/v1/deploy"
BASE = "/home/ubuntu/coulomb-pages-plugin/"

auth = base64.b64encode(f"{WP_USER}:{WP_PASS}".encode()).decode()
headers = {"Authorization": f"Basic {auth}", "Content-Type": "application/json"}

# All page file pairs (html, css)
PAGE_FILES = [
    ("html/home-body.html", "css/home.css"),
    ("html/allproducts-body.html", "css/allproducts.css"),
    ("html/besscore-body.html", "css/besscore.css"),
    ("html/ci-body.html", "css/ci.css"),
    ("html/def-body.html", "css/def.css"),
    ("html/mf-body.html", "css/mf.css"),
    ("html/seriesb-body.html", "css/seriesb.css"),
    ("html/seriesdc-body.html", "css/seriesdc.css"),
    ("html/seriesr-body.html", "css/seriesr.css"),
    ("html/seriesm-body.html", "css/seriesm.css"),
    ("html/seriess-body.html", "css/seriess.css"),
    ("html/smartems-body.html", "css/smartems.css"),
    ("html/sodium-body.html", "css/sodium.css"),
    ("html/about-body.html", "css/about.css"),
    ("html/contact-body.html", "css/contact.css"),
    ("html/privacy-body.html", "css/legal.css"),
    ("html/terms-body.html", "css/legal.css"),
    ("html/tb48-body.html", "css/tb48.css"),
]

files_to_deploy = []
skipped = []

for html_path, css_path in PAGE_FILES:
    html_full = BASE + html_path
    css_full = BASE + css_path

    if not os.path.exists(html_full):
        print(f"SKIP (html not found): {html_path}")
        skipped.append(html_path)
        continue

    with open(html_full, 'r') as f:
        html_content = f.read()
    files_to_deploy.append({"path": html_path, "content": html_content})

    if os.path.exists(css_full):
        with open(css_full, 'r') as f:
            css_content = f.read()
        files_to_deploy.append({"path": css_path, "content": css_content})
    else:
        print(f"NOTE (css not found, skipping css): {css_path}")

print(f"Deploying {len(files_to_deploy)} files to live site...")

r = requests.post(DEPLOY_ENDPOINT, headers=headers, json={"files": files_to_deploy}, timeout=120)
print(f"Status: {r.status_code}")
try:
    result = r.json()
    print(json.dumps(result, indent=2))
except Exception as e:
    print(f"Response: {r.text[:500]}")

if skipped:
    print(f"\nSkipped files: {skipped}")

# 2-Day Shower Specialists — Landing Page

Static, mobile-first landing page + a minimal PHP form handler for a Hostinger VPS deployment.

## What’s in this repo

- `index.html` — single-page funnel/landing page
- `assets/`
  - `assets/css/styles.css` — all site styles
  - `assets/js/main.js` — minimal first‑party JS (year + form status message)
  - `assets/js/tracking.js` — Meta Pixel + Google Ads loader (gated until you set real IDs)
  - `assets/images/` — all site images (responsive variants used via `srcset`)
- `submit.php` — form handler (validates + blocks simple bots + appends leads to CSV)
- `data/` — server-side storage directory (contains `.gitkeep` only in git)
- `robots.txt`, `sitemap.xml` — crawl/index support
- `README.md`, `.gitignore`

## Local development

This project is plain HTML/CSS/JS + PHP.

### Option A — open the page directly (no form submission)

You can open `index.html` in a browser to review layout. The form POST won’t work in this mode.

### Option B — run a local PHP server (form works)

From the project root:

```bash
php -S localhost:8080
```

Then visit:

- `http://localhost:8080/`

Submissions will be written to:

- `data/leads.csv`

## Production deployment (Hostinger VPS)

### 1) Upload files

Upload the repo contents to your web root (example):

- `/var/www/showerspecialists.fcjamison.com/public`

### 2) Make sure PHP is enabled

You need either:

- Apache + PHP module, or
- Nginx + PHP-FPM

### 3) Ensure `data/` is writable

The form handler writes to:

- `data/leads.csv`
- `data/rate_limit.json`

So the web server user must be able to create/write files in `data/`.

Example (common on Ubuntu/Debian):

```bash
sudo chown -R www-data:www-data data
sudo chmod 755 data
```

If your server user is not `www-data`, adjust accordingly.

### 3a) Protect `data/` from public access

Because `data/` lives under the web root in this repo, you should explicitly block web access to it so lead files can’t be downloaded.

Apache (via `.htaccess` inside `data/`):

```apache
Require all denied
```

Nginx (server block):

```nginx
location ^~ /data/ {
  deny all;
}
```

### 4) Confirm the form endpoint

The form posts to:

- `/submit.php`

A successful submit redirects to:

- `/?submitted=1#quote`

An invalid submit redirects to:

- `/?submitted=0#quote`

The UI message is shown by `assets/js/main.js`.

## Form handling details

`submit.php`:

- Requires `POST`
- Honeypot: `bot-field` (if filled, it silently redirects as “success”)
- Validates required fields: `name`, `phone`, `email`, `zip`
- Validates email format
- Basic per-IP rate limit (12 submissions/hour)
- Appends a CSV row with timestamp, IP, user agent, and referrer

### Lead storage

Leads are stored on the server in `data/leads.csv`.

These runtime files are intentionally ignored by git via `.gitignore` (`data/*.csv`, `data/*.json`).

## SEO / domain configuration

This repo currently targets:

- `https://showerspecialists.fcjamison.com/`

If you deploy under a different domain, update:

- `index.html` canonical + OG URL + JSON-LD URL
- `robots.txt` sitemap URL
- `sitemap.xml` `<loc>`

## Tracking (Meta / Google Ads)

Tracking is intentionally “safe by default”.

- `assets/js/tracking.js` will **not** load Meta/Google scripts until you replace the placeholder IDs:
  - `YOUR_META_PIXEL_ID`
  - `YOUR_GOOGLE_ADS_ID`

After you set real IDs, the tracking scripts will load asynchronously.

## Repo hygiene

- PDF/extraction artifacts are not tracked (see `.gitignore`).
- Images live under `assets/images/`.

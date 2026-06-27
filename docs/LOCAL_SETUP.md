# Local Setup Guide — TwinTack Rebuild 2026

Replicate production behavior in the Local by Flywheel development environment.

## Prerequisites

- [Local by Flywheel](https://localwp.com/) installed
- Site created: **twintack-rebuild-2026** (PHP 8.x, preferred web server)
- This repo cloned or initialized at `app/public/wp-content/`
- Access to production hosting (database export, `wp-content/uploads/`, SFTP)

## Environment overview

| Item | Local value | Production |
|------|-------------|------------|
| Site name | twintack-rebuild-2026 | twintack.com |
| WP root | `.../app/public/` | hosting docroot |
| Repo root | `.../app/public/wp-content/` | same relative path |
| Database | `local` / `root` / `root` | hosting credentials |
| URL | `https://twintack-rebuild-2026.local` (verify in Local) | `https://twintack.com` |

Open the site URL from Local's site overview panel after starting the site.

## Step 1 — WordPress baseline

Local provides WordPress core and `wp-config.php`. Confirm:

- WordPress version is current stable (match production major version if possible)
- Permalinks: **Settings → Permalinks → Post name**
- PHP memory limit ≥ 256M (Local site settings if needed)

## Step 2 — Theme and plugins

**If starting from this repo after 2026-06-26:** The production theme and full plugin set were already copied from twintack.com into Local. Custom TwinTack plugins are in Git; third-party plugins are on disk but not tracked — see [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md).

**If cloning fresh:** Copy third-party plugins from a production backup or reinstall from WP admin / license zips per the inventory. Only `plugins/twintack-*` come from Git.

Typical production stack (all present locally as of port):

1. **WooCommerce** 10.9.1
2. **Advanced Custom Fields PRO** 6.8.4
3. **WooCommerce Stripe Gateway** 10.8.3 — use test API keys locally
4. Wholesale suite, variation swatches, Klaviyo, integrations — see inventory

Do not commit third-party plugins to Git.

## Step 3 — Activate theme and plugins

After DB import (or on fresh Local with plugins on disk):

1. Activate `twintack2025` under **Appearance → Themes**
2. Activate custom TwinTack plugins under **Plugins**
3. Resolve fatal errors before proceeding (check `logs/php/error.log` in Local site folder)

See [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) for remaining port tasks.

## Step 4 — Import production database

### Export from production

Use one of:

- **Hosting panel** (SiteGround, WP Engine, etc.) — phpMyAdmin or backup tool
- **WP-CLI** on server: `wp db export twintack-production.sql`
- **Plugin** — WP Migrate, All-in-One WP Migration (export without media first for speed)

### Import to Local

**Important:** The BlueHost production export uses table prefix **`sCO_`**, not `wp_`. After import, update `wp-config.php`:

```php
$table_prefix = 'sCO_';
```

Without this, WordPress will connect to empty `wp_` tables while production data lives in `sCO_*`.

**Option A — Local's Adminer (recommended for first import)**

1. Local → Site → Database → Open Adminer
2. Select database `local`
3. Import → choose `.sql` file
4. Wait for completion (large WooCommerce DBs may take several minutes)

**Option B — WP-CLI**

```bash
cd "/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/app/public"
wp db import /path/to/twintack-production.sql
```

### Search-replace URLs

After import, replace production URLs with the Local URL:

```bash
wp search-replace 'https://twintack.com' 'https://twintack-rebuild-2026.local' --all-tables
wp search-replace 'http://twintack.com' 'https://twintack-rebuild-2026.local' --all-tables
```

Verify with:

```bash
wp option get siteurl
wp option get home
```

### Sanitization (recommended)

Before import, or immediately after:

- Replace admin email with a dev address
- Disable or reconfigure SMTP (avoid sending real customer emails)
- Set WooCommerce Stripe to **test mode**
- Disable Make.com / webhook plugins or redirect to staging URLs
- Remove or anonymize customer PII if not needed for your test cases

## Step 5 — Import media (uploads)

Production media is **not in Git**. Copy the uploads directory:

```bash
# From production backup or SFTP download
rsync -avz --progress /path/to/production/wp-content/uploads/ \
  "/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/app/public/wp-content/uploads/"
```

Or use WP Migrate / All-in-One WP Migration media addon for integrated transfer.

### After uploads sync — re-run URL search-replace

Syncing uploads does not update the database, but a **fresh DB import after uploads** (or imports that include attachment meta) often still contains `twintack.com` URLs in post content and serialized options. Always run search-replace again after combining DB + uploads:

```bash
wp search-replace 'https://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables
wp search-replace 'http://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables
wp rewrite flush --hard
```

Full checklist: [PARITY_TESTING.md](PARITY_TESTING.md).

After copy, regenerate thumbnails if needed:

```bash
wp media regenerate --yes
```

## Step 6 — WooCommerce-specific setup

1. **Settings → General** — confirm timezone and currency (USD)
2. **WooCommerce → Settings → Advanced → Page setup** — verify cart/checkout/my-account pages exist
3. **Products** — spot-check variable products and custom grip products
4. **Payment gateways** — Stripe test keys only
5. **Shipping zones** — confirm rates load (may need API keys for live rate plugins)
6. Flush rewrite rules: `wp rewrite flush`

## Step 7 — Verify parity

Full walkthrough: **[PARITY_TESTING.md](PARITY_TESTING.md)** — post-uploads URL fixes, data volume checks, and page-by-page sign-off.

Quick smoke test:

- [ ] Homepage loads with hero, featured products, and footer
- [ ] Shop archive and single product pages
- [ ] Add variable product to cart and reach checkout (test mode)
- [ ] Custom grip product/configurator (if applicable)
- [ ] My Account login (reset password for imported users or create test customer)
- [ ] Key static pages: Our Story, Technology, Contact

## Step 8 — Enable debugging (development only)

In `wp-config.php` (Local-managed, not in repo):

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

Logs: `app/public/wp-content/debug.log`

## Troubleshooting

| Issue | Fix |
|-------|-----|
| White screen after import | Check PHP error log; often missing plugin or PHP version mismatch |
| Broken images | Incomplete uploads sync; verify `uploads/` year folders |
| Mixed content / CSS broken | Run URL search-replace; check `siteurl` and `home` options |
| 404 on product pages | Flush permalinks: Settings → Permalinks → Save |
| ACF fields empty | DB import incomplete or ACF Pro not activated |
| Stripe errors on checkout | Expected with live keys — switch to test mode |

## Ongoing sync

For fresh production data during development:

1. Export new DB snapshot (schedule weekly or before major testing)
2. Re-import to Local (backup local DB first if you have test orders)
3. Rsync changed uploads only
4. Re-run URL search-replace if needed

Do not commit database dumps to this repository.

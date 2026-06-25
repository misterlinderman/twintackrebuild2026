# Plugin Inventory

Track every plugin on production and document keep / install / consolidate / remove decisions for the rebuild.

**Last updated:** 2026-06-25 (initial template — fill in after production audit)

## How to use this document

1. Export the production plugin list from **WP Admin → Plugins**
2. Fill in each row with the production status
3. Mark the **Rebuild action** before installing locally
4. Update **Installed version** after Local setup is complete

## Custom TwinTack plugins (track in Git)

| Plugin | Production active? | Legacy repo path | Rebuild action | Installed version | Notes |
|--------|-------------------|------------------|----------------|-------------------|-------|
| TwinTack Grip Manager | TBD | `plugins/twintack-grip-manager*` | Consolidate to single `plugins/twintack-grip-manager/` | — | Multiple version folders in legacy repo — use production-active copy |

## Third-party plugins (document version, do not commit)

| Plugin | Production active? | Rebuild action | Installed version | Notes |
|--------|-------------------|----------------|-------------------|-------|
| WooCommerce | TBD | Install via WP admin | — | Core e-commerce |
| Advanced Custom Fields PRO | TBD | Install from license zip | — | Field groups may live in DB + theme `acf-json/` |
| WooCommerce Stripe Gateway | TBD | Install via WP admin | — | Test keys only in Local |
| WooCommerce POS | TBD | Evaluate — remove if unused | — | Legacy repo has zip only |

## Plugins to evaluate for removal

Document plugins that are installed on production but likely unnecessary:

| Plugin | Reason to remove | Confirmed safe to remove? |
|--------|------------------|---------------------------|
| (fill after audit) | | |

## Legacy duplicate folders (do not port)

These exist in [misterlinderman/twintack](https://github.com/misterlinderman/twintack) but should **not** be copied:

- `plugins/twintack-grip-manager 1-0-0`
- `plugins/twintack-grip-manager 1-3-2`
- `plugins/twintack-grip-manager 1-4-2`
- `plugins/twintack-grip-manager 1501`

Merge any unique code into the canonical plugin, then archive the rest in the legacy repo only.

## Production audit command

If WP-CLI is available on production or a DB import:

```bash
wp plugin list --format=table
```

Save output here or in a dated note when auditing.

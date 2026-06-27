# Project Intent — TwinTack Rebuild 2026

## Background

[TwinTack](https://twintack.com/) is a WordPress/WooCommerce e-commerce site selling patented sporting grips for baseball and fishing. The live site runs a custom theme (`twintack2025`) and several custom plugins developed over time in a direct-to-production workflow.

The legacy codebase lives at [github.com/misterlinderman/twintack](https://github.com/misterlinderman/twintack). That repo accumulated technical debt typical of production-only development:

- Multiple versions of the same plugin side by side (e.g. `twintack-grip-manager`, `twintack-grip-manager 1-4-2`)
- Theme backup copies (`twintack2025-previous`)
- Third-party plugins committed to Git (WooCommerce, ACF Pro zips)
- No branch-based review before production changes

## What this rebuild is

This repository (`twintackrebuild2026`) is a **clean development foundation** — not a fork of production history. It establishes:

1. **Local-first development** using [Local by Flywheel](https://localwp.com/) site `twintack-rebuild-2026`
2. **Version-controlled custom code** with meaningful branches and pull requests
3. **A consolidation pass** on theme and plugin architecture — centered on `twintack-custom-grips`, retiring Gravity Forms, Make.com, and Monday.com
4. **Documented parity steps** so local dev reflects real WooCommerce products, orders context, and media

## What this rebuild is not

- A full WordPress installation repo (core stays managed by Local)
- A media or database backup store
- An immediate production replacement — production deploys happen only after local validation

## Guiding principles

### 1. Track custom code, document dependencies

Commit TwinTack theme and custom plugin source. Record third-party plugin **names and versions** in `PLUGIN_INVENTORY.md`; install them via WP admin, Composer, or documented zip paths — do not commit vendor plugin trees unless there are local patches.

### 2. One canonical version of each custom plugin

When porting from the legacy repo, pick the **current production-active** version of each custom plugin. Do not copy version-suffixed duplicate folders. Merge useful differences into a single plugin directory with proper semver in the plugin header.

### 3. Theme consolidation over duplication

Port `themes/twintack2025/` as the starting point. Do not port `twintack2025-previous` unless specific code needs recovery — extract what you need, then delete the backup copy from this repo.

### 4. Production data for realistic testing

WooCommerce variable products, custom grip configurations, ACF field groups, and media-dependent layouts require a **sanitized production import**. Code changes should be tested against real product structures, not empty Local installs.

### 5. Branch before you build

| Branch | Purpose |
|--------|---------|
| `main` | Stable, deployable custom code |
| `develop` | Integration branch for ongoing work |
| `feature/*` | Individual features or fixes |
| `migration/*` | One-time porting and setup tasks |

### 6. Minimize production risk

- Never push untested theme/plugin changes directly to production
- Use search-replace on URLs when importing DB (`twintack.com` → local URL)
- Keep Stripe and payment gateways in **test mode** locally
- Disable outbound webhooks (Make.com, etc.) or point them to staging endpoints

## Success criteria

- [ ] Local site renders the TwinTack homepage and shop with production-equivalent content
- [ ] Custom grip ordering/management flows work end-to-end in Local
- [x] Single canonical theme and plugin set with no duplicate version folders
- [x] Documented plugin keep/remove decisions in `PLUGIN_INVENTORY.md`
- [ ] Git workflow used for all custom code changes before production

## Current port status (2026-06-26)

Production theme and plugins copied directly from **twintack.com** into Local:

- **Theme:** `themes/twintack2025/` — see [THEME_OVERVIEW.md](THEME_OVERVIEW.md)
- **Custom plugins:** 10 `twintack-*` plugins (Grip Manager v1.7.05, Manual Order Payments v4.6.1, etc.)
- **Third-party plugins:** 22 plugins present locally, versions documented in [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md), excluded from Git
- **Pending:** Production database + `uploads/` import for full parity

## Consolidation priorities (post-import)

See **[PLUGIN_CONSOLIDATION.md](PLUGIN_CONSOLIDATION.md)** for the full roadmap.

1. **Retire Make.com + Monday.com** — legacy artwork/production automation; native workflow lives in `twintack-custom-grips`
2. **Retire Gravity Forms** — replace form intake with native Custom Grips forms; remove theme/Grip Manager GF hooks
3. **Merge Grip Manager → Custom Grips** — single workflow plugin owns CPT, intake, WC sync, team/customer UI
4. **Marketing duplication** — theme `template-parts/marketing/v2/` vs `twintack-marketing` plugin
5. **Wholesale workarounds** — merge or replace `twintack-product-save-bypass` and `twintack-admin-console-fixes`
6. **Theme cleanup** — remove integration webhooks, GF script fixes, debug artifacts

## Legacy reference map

| Source | Rebuild target | Status |
|--------|----------------|--------|
| Production `themes/twintack2025/` | `themes/twintack2025/` | Copied |
| Legacy `twintack2025-previous/` | Do not port | Skipped |
| Production `plugins/twintack-*` | `plugins/twintack-*/` | Copied (10 plugins) |
| Legacy duplicate grip manager folders | Single `twintack-grip-manager/` | Not copied |
| Third-party plugins | Local install only | Copied, not in Git |

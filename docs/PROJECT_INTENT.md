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
- [x] Git workflow used for custom code — production port committed; Phase 1 on `consolidation/phase-1-retire-integrations` (pushed)

## Current status (2026-06-26)

**Local site:** `http://twintack-rebuild-2026.local` — running with production DB import, `twintack2025` theme active, core TwinTack plugins active. Outbound integrations blocked via `mu-plugins/twintack-local-sandbox.php`.

### What is in the current build

| Layer | Component | Version | Notes |
|-------|-----------|---------|-------|
| Theme | `twintack2025` | 1.0.0 | Active; Make.com webhooks removed |
| Workflow | `twintack-custom-grips` | 1.2.4 | Canonical team dashboard + customer UI |
| Infrastructure | `twintack-grip-manager` | 1.7.06 | CPT + WC bridge; Phase 1 integrations removed |
| Payments | `twintack-manual-order-payments` | 4.6.1 | Inactive locally (sandbox) |
| Supporting | 7 other `twintack-*` plugins | — | Marketing, filters, security, wholesale fixes, etc. |
| Third-party | WooCommerce, ACF Pro, wholesale suite, etc. | — | Local only, not in Git |
| Sandbox | `twintack-local-sandbox.php` | 1.0.0 | Blocks Stripe/Klaviyo/Meta/QuickBooks/Amazon/Make.com |

### Migration progress

| Milestone | Status |
|-----------|--------|
| Theme + custom plugins copied from production | Done — in Git |
| Production database imported + URL replaced | Done |
| Media (`uploads/`) fully synced | Pending |
| End-to-end parity testing | Pending |

See [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) for the full checklist.

## Consolidation roadmap

See **[PLUGIN_CONSOLIDATION.md](PLUGIN_CONSOLIDATION.md)** for the full roadmap.

| Phase | Focus | Status |
|-------|-------|--------|
| **1** | Retire Make.com + Monday.com dead code | **Complete** — merged to `main` |
| **2** | Retire Gravity Forms — native Custom Grips intake | **Ready to start** — [PHASE_2_KICKOFF.md](PHASE_2_KICKOFF.md) |
| **3** | Merge Grip Manager → Custom Grips | Not started |
| **4** | Theme cleanup — presentation only | Not started |

### Remaining consolidation work

1. ~~**Retire Make.com + Monday.com**~~ — done in Phase 1
2. **Retire Gravity Forms** — replace form intake with native Custom Grips forms; remove theme/Grip Manager GF hooks
3. **Merge Grip Manager → Custom Grips** — single workflow plugin owns CPT, intake, WC sync, team/customer UI
4. **Marketing duplication** — theme `template-parts/marketing/v2/` vs `twintack-marketing` plugin
5. **Wholesale workarounds** — merge or replace `twintack-product-save-bypass` and `twintack-admin-console-fixes`
6. **Theme cleanup** — GF script fixes, debug artifacts (Make.com webhooks already removed)

## Legacy reference map

| Source | Rebuild target | Status |
|--------|----------------|--------|
| Production `themes/twintack2025/` | `themes/twintack2025/` | Copied |
| Legacy `twintack2025-previous/` | Do not port | Skipped |
| Production `plugins/twintack-*` | `plugins/twintack-*/` | Copied (10 plugins) |
| Legacy duplicate grip manager folders | Single `twintack-grip-manager/` | Not copied |
| Third-party plugins | Local install only | Copied, not in Git |

# TwinTack Rebuild 2026

Development repository for rebuilding [twintack.com](https://twintack.com/) — a WordPress/WooCommerce e-commerce site selling patented baseball and fishing grips — on a modern Local-first workflow with Git branches before production deployment.

This repo tracks **custom WordPress code only** (`themes/` + `plugins/twintack-*`). WordPress core, media uploads, and the database live outside Git and are managed locally.

## What this project is

TwinTack's production site grew over years of direct-to-production development. The legacy repo ([misterlinderman/twintack](https://github.com/misterlinderman/twintack)) accumulated duplicate plugin folders, committed third-party vendors, and brittle external integrations (Gravity Forms → Make.com → Monday.com).

**This rebuild establishes a clean foundation:**

| Goal | Approach |
|------|----------|
| Safe iteration | [Local by Flywheel](https://localwp.com/) site `twintack-rebuild-2026` |
| Version control | Git branches + PRs for all custom code |
| Architecture cleanup | Consolidate around `twintack-custom-grips` as the canonical grip workflow |
| Production parity | Import real WooCommerce data, products, and media for realistic testing |
| Reduced risk | Local sandbox blocks outbound webhooks, payments, and customer emails |

Production is **not** replaced until changes are validated locally.

## Current build (2026-06-26)

**Local URL:** `http://twintack-rebuild-2026.local`

### In Git (this repo)

| Component | Details |
|-----------|---------|
| **Theme** | `themes/twintack2025/` v1.0.0 — active locally; WooCommerce template overrides, grip configurator, marketing templates |
| **Custom plugins** | 10 `twintack-*` plugins — see [Plugin Inventory](docs/PLUGIN_INVENTORY.md) |
| **Sandbox** | `mu-plugins/twintack-local-sandbox.php` — blocks Stripe/Klaviyo/Meta/QuickBooks/Amazon/Make.com HTTP; routes mail to Mailpit |
| **Docs + Cursor rules** | Setup, migration, consolidation roadmap, AI guidance |

### Local only (not in Git)

| Component | Details |
|-----------|---------|
| **WordPress core** | Managed by Local at `app/public/` |
| **Third-party plugins** | 22 plugins copied from production (WooCommerce 10.9.1, ACF Pro 6.8.4, wholesale suite, etc.) |
| **Database** | Production import (`meoefemy_WPNS6.sql.gz` — **do not commit**) with URL search-replace to local |
| **Media** | `uploads/` — partially synced; full import still pending |

### Grip workflow architecture (today)

```
Customer / team UI     twintack-custom-grips (canonical)
                           ├── Team dashboard (/team-dashboard/)
                           ├── My Custom Grips (/my-account/my-custom-grips/)
                           └── Status machine, mockups, messaging

Infrastructure         twintack-grip-manager v1.7.06
                           ├── grip_design post type
                           ├── WooCommerce order bridge + cart/reorder
                           └── GF intake hooks (Phase 2 — to replace)

Presentation           twintack2025 theme
                           └── Configurator UI, templates (no Make.com webhooks)
```

**Retired:** Make.com webhooks and Monday.com REST API removed in consolidation Phase 1. Historical `_grip_monday_*` post meta preserved read-only.

**Still pending:** Gravity Forms intake replacement (Phase 2), Grip Manager merge into Custom Grips (Phase 3).

## Progress

### Foundation — mostly complete

| Milestone | Status |
|-----------|--------|
| Local site running | Done |
| Production theme + custom plugins in Git | Done — commit `20f545a` on `consolidation/phase-1-retire-integrations` |
| Production DB imported | Done |
| Theme + core plugins activated | Done |
| Local sandbox (outbound integrations blocked) | Done |
| Full media (`uploads/`) sync | Pending |
| End-to-end parity testing | Pending |

### Consolidation roadmap

See [Plugin Consolidation](docs/PLUGIN_CONSOLIDATION.md) for full detail.

| Phase | Focus | Status |
|-------|-------|--------|
| **1** | Retire Make.com + Monday.com dead code | **Complete** — branch pushed |
| **2** | Replace Gravity Forms with native Custom Grips intake | Not started |
| **3** | Merge Grip Manager into Custom Grips | Not started |
| **4** | Theme cleanup (presentation only, no business logic) | Not started |

### Active Git branch

```
consolidation/phase-1-retire-integrations  →  origin (pushed)
```

Open a PR: https://github.com/misterlinderman/twintackrebuild2026/pull/new/consolidation/phase-1-retire-integrations

## Related repositories and sites

| Resource | URL |
|----------|-----|
| Production site | https://twintack.com/ |
| Legacy code repo | https://github.com/misterlinderman/twintack |
| This repo | https://github.com/misterlinderman/twintackrebuild2026 |

## Repository structure

```
wp-content/                  ← Git repo root (this directory)
├── themes/twintack2025/     ← Custom theme
├── plugins/twintack-*/     ← Custom plugins only (third-party excluded via .gitignore)
├── mu-plugins/             ← Local sandbox (safe on production — no-op outside local)
├── docs/                   ← Setup, migration, consolidation docs
└── .cursor/rules/          ← Cursor AI guidance
```

Local site path (not in repo):

```
/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/
└── app/public/              ← WordPress root (core + wp-config.php)
    └── wp-content/          ← This repo
```

## Documentation

| Document | Purpose |
|----------|---------|
| [Project Intent](docs/PROJECT_INTENT.md) | Why this rebuild exists and guiding principles |
| [Local Setup Guide](docs/LOCAL_SETUP.md) | Replicate production in Local by Flywheel |
| [Local Sandbox](docs/LOCAL_SANDBOX.md) | Disable outbound integrations for safe dev |
| [Migration Checklist](docs/MIGRATION_CHECKLIST.md) | Port and parity checklist |
| [Plugin Inventory](docs/PLUGIN_INVENTORY.md) | All plugins — keep/remove/consolidate |
| [Plugin Consolidation](docs/PLUGIN_CONSOLIDATION.md) | GF/Make/Monday retirement roadmap |
| [Theme Overview](docs/THEME_OVERVIEW.md) | twintack2025 structure and cleanup targets |
| [Git Workflow](docs/GIT_WORKFLOW.md) | Branching, commits, deployment |

## Quick start

1. Open **twintack-rebuild-2026** in Local and start the site.
2. Clone this repo into `app/public/wp-content/`.
3. Copy third-party plugins from production or reinstall per [Plugin Inventory](docs/PLUGIN_INVENTORY.md) (not in Git).
4. Import production database and media per [Local Setup Guide](docs/LOCAL_SETUP.md).
5. Use WP-CLI via Local shell or `source app/.envrc` from the site root.
6. Work on a feature branch; merge to `main` via PR before production deploy.

## What not to commit

- WordPress core (`app/public/` outside `wp-content/`)
- `uploads/` media files
- Database dumps with customer PII
- `wp-config.php`, API keys, Stripe credentials, or license keys
- Third-party plugin source — document versions in `PLUGIN_INVENTORY.md` instead

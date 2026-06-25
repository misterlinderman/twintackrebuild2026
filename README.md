# TwinTack Rebuild 2026

Development repository for rebuilding [twintack.com](https://twintack.com/) on a modern Local by Flywheel workflow with Git branch versioning before production deployment.

This repo tracks **custom WordPress code only** — themes and TwinTack plugins under `wp-content/`. WordPress core, media uploads, and the database are managed locally and imported separately.

## Related repositories and sites

| Resource | URL |
|----------|-----|
| Production site | https://twintack.com/ |
| Legacy code repo | https://github.com/misterlinderman/twintack |
| This repo | https://github.com/misterlinderman/twintackrebuild2026 |

## Project goals

Move from a manual, labor-intensive dev-to-production workflow to an iterative development experience:

- **Local by Flywheel** for isolated WordPress/WooCommerce testing
- **Git branches** for feature work, review, and staged releases
- **Theme consolidation** — streamline the existing `twintack2025` theme
- **Plugin rationalization** — keep custom TwinTack plugins, consolidate duplicates, remove unnecessary ones
- **Production parity** — import WooCommerce, media, and WordPress data to validate changes locally

## Repository structure

```
wp-content/                  ← Git repo root (this directory)
├── themes/                  ← Custom TwinTack theme(s)
├── plugins/                 ← Custom TwinTack plugins only
├── docs/                    ← Setup, migration, and workflow docs
├── .cursor/rules/           ← Cursor AI guidance for this project
└── .gitignore
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
| [Migration Checklist](docs/MIGRATION_CHECKLIST.md) | Step-by-step port from legacy repo and production |
| [Plugin Inventory](docs/PLUGIN_INVENTORY.md) | Track what to keep, consolidate, or remove |
| [Git Workflow](docs/GIT_WORKFLOW.md) | Branching, commits, and deployment strategy |

## Quick start

1. Open the **twintack-rebuild-2026** site in Local by Flywheel and start it.
2. Clone this repo into `app/public/wp-content/` (or work in place if already there).
3. Follow [Migration Checklist](docs/MIGRATION_CHECKLIST.md) to port theme and plugins from [twintack](https://github.com/misterlinderman/twintack).
4. Import production database and media per [Local Setup Guide](docs/LOCAL_SETUP.md).
5. Create a feature branch and begin refinement work.

## What not to commit

- WordPress core (`app/public/` outside `wp-content/`)
- `uploads/` media files
- Database dumps with customer PII (store outside repo or use sanitized exports)
- `wp-config.php`, API keys, Stripe credentials, or license keys
- Third-party plugin source (WooCommerce, ACF Pro, etc.) — document versions instead

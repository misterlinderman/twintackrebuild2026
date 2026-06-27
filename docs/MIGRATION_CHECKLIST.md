# Migration Checklist — Port from Legacy to Rebuild

Use this checklist when bringing code from [misterlinderman/twintack](https://github.com/misterlinderman/twintack) and production into the Local rebuild environment.

**Status as of 2026-06-27:** Production database re-imported with table prefix `sCO_`. Uploads synced (~3.1 GB). URL search-replace complete. **Initial parity sign-off complete** — team dashboard, product admin, and recent visual changes verified locally.

---

## Phase 0 — Preparation

- [x] Production plugin list captured — see [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md)
- [ ] Clone legacy repo for reference (optional): `git clone https://github.com/misterlinderman/twintack.git ~/twintack-legacy`
- [x] Export production database — `meoefemy_WPNS6.sql.gz` (store outside Git)
- [x] Download production `uploads/` folder — synced from production (~3.1 GB)
- [x] Custom code committed — `consolidation/phase-1-retire-integrations` pushed to GitHub

## Phase 1 — Theme port

- [x] Theme copied to `themes/twintack2025/` (from production server)
- [x] Default Twenty* themes removed
- [x] **Did not copy** `twintack2025-previous`
- [ ] Review theme `style.css` header — bump version on first rebuild release
- [ ] Run theme asset build if modifying JS pipeline (`npm install` in theme dir)
- [x] Activate theme in Local WP admin
- [ ] Audit hardcoded production URLs — see [THEME_OVERVIEW.md](THEME_OVERVIEW.md)
- [ ] Review debug artifacts (`checkout-debug.js`, `debug-viewer.php`, etc.)

### Theme cleanup targets

- [ ] Remove unused template files identified during audit
- [ ] Consolidate duplicate CSS/JS enqueue logic
- [x] Verify WooCommerce template overrides exist in `woocommerce/` (168 files)
- [ ] Export ACF field groups to JSON if desired for version control
- [ ] Resolve theme vs plugin overlap (marketing, how-to videos, klaviyo) — see [THEME_OVERVIEW.md](THEME_OVERVIEW.md)

## Phase 2 — Custom plugin port

- [x] Single canonical `plugins/twintack-grip-manager/` (v1.7.06 — Phase 1 integrations removed)
- [x] No duplicate version-suffixed plugin folders
- [x] All 10 custom TwinTack plugins present — see [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md)
- [x] Activate and smoke-test admin screens and front-end hooks — team dashboard, product edit verified 2026-06-27
- [ ] Evaluate merging `twintack-product-save-bypass` + `twintack-admin-console-fixes`
- [ ] Evaluate consolidating `twintack-marketing` with theme marketing v2 templates

### Custom plugins copied

| Plugin | Version |
|--------|---------|
| twintack-grip-manager | 1.7.06 |
| twintack-custom-grips | 1.2.4 |
| twintack-manual-order-payments | 4.6.1 |
| twintack-marketing | 1.2.0 |
| twintack-enhanced-shop-filters | 1.0.2 |
| twintack-how-to-videos | 1.1.0 |
| twintack-admin-console-fixes | 1.0.9 |
| twintack-product-save-bypass | 1.0.1 |
| twintack-security | 1.0.0 |
| twintack-amazon-tracking-bridge | 1.1.0 |

## Phase 3 — Third-party plugin install

Copied from production (present locally, **not tracked in Git**):

- [x] WooCommerce 10.9.1
- [x] Advanced Custom Fields PRO 6.8.4
- [x] WooCommerce Stripe Gateway 10.8.3 (inactive locally — sandbox)
- [x] Wholesale suite (5 plugins)
- [x] Variation Swatches + Pro 2.3.0
- [x] Klaviyo, Meta for WooCommerce, Pixel Manager, Solid Affiliate (integrations inactive locally — sandbox)
- [x] WP-Lister Amazon, QuickBooks integration (inactive locally — sandbox)
- [x] Admin Menu Editor, Classic Editor, FileBird, SVG Support
- [x] Advanced Dynamic Pricing 4.13.2

See [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md) for full list and consolidation notes.

## Phase 4 — Database and media import

Follow [LOCAL_SETUP.md](LOCAL_SETUP.md) Steps 4–5:

- [x] Database imported
- [x] URL search-replace completed — re-run after each DB re-import; see [PARITY_TESTING.md](PARITY_TESTING.md) Step 1
- [x] Uploads fully synced from production
- [x] Payment gateways in test mode / deactivated (sandbox)
- [x] Outbound webhooks blocked (Make.com removed in Phase 1; sandbox blocks remaining integrations)

## Phase 5 — Configuration parity

- [ ] **Settings → Reading** — static front page matches production
- [ ] **Appearance → Menus** — primary/footer menus assigned
- [ ] **Appearance → Customize** — theme mods present (or in DB from import)
- [ ] **WooCommerce** settings verified
- [ ] **ACF** field groups visible (sync from JSON if needed)
- [ ] Cron: `wp cron event list` — no stuck failed jobs from production URLs

## Phase 6 — Code quality pass

- [ ] Remove `console.log` / debug statements from ported JS
- [ ] Remove commented-out dead code blocks from port
- [ ] Ensure no production API keys in theme/plugin source
- [ ] Run PHPCS or basic lint if theme has config
- [x] Commit custom code — `20f545a` on `consolidation/phase-1-retire-integrations`

## Phase 7 — Consolidation (ongoing)

See [PLUGIN_CONSOLIDATION.md](PLUGIN_CONSOLIDATION.md):

- [x] **Phase 1** — Retire Make.com + Monday.com dead code (merged to `main`)
- [ ] **Phase 2** — Replace Gravity Forms with native Custom Grips intake — see [PHASE_2_KICKOFF.md](PHASE_2_KICKOFF.md)
- [ ] **Phase 3** — Merge Grip Manager into Custom Grips
- [ ] **Phase 4** — Theme cleanup (presentation only)

## Phase 8 — Sign-off

Follow [PARITY_TESTING.md](PARITY_TESTING.md) for the full walkthrough.

- [x] Homepage matches production layout — recent visual changes confirmed 2026-06-27
- [ ] Checkout flow works in Stripe test mode (optional — sandbox off)
- [ ] Custom grip flows tested end-to-end (mockup upload, customer review, order sync)
- [x] Team dashboard and product admin verified with production credentials
- [x] `PLUGIN_INVENTORY.md` populated
- [x] Merge `consolidation/phase-1-retire-integrations` → `main` (2026-06-27)

## Files to leave behind (legacy repo)

Do not port these to the rebuild repo root:

- `wp-config-250519.php` — secrets; Local manages config
- `artwork-status-test.php`, `debug-rest-api.php`, etc. — one-off scripts; port only if still needed under `tools/`
- `claude notes/` — reference only, not production code
- Duplicate plugin version directories
- Committed WooCommerce / ACF vendor trees (present locally only, excluded from Git)
- Database dumps (`*.sql`, `*.sql.gz`) — import locally, never commit

If debug scripts are still useful, add them under a `tools/` directory with a README explaining usage and safety (local only).

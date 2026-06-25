# Migration Checklist — Port from Legacy to Rebuild

Use this checklist when bringing code from [misterlinderman/twintack](https://github.com/misterlinderman/twintack) and production into the Local rebuild environment.

## Phase 0 — Preparation

- [ ] Clone legacy repo for reference: `git clone https://github.com/misterlinderman/twintack.git ~/twintack-legacy`
- [ ] Confirm production plugin list (WP Admin → Plugins) — may differ from legacy repo
- [ ] Export production database (see [LOCAL_SETUP.md](LOCAL_SETUP.md))
- [ ] Download production `uploads/` folder
- [ ] Create branch: `git checkout -b migration/initial-port`

## Phase 1 — Theme port

### Copy active theme

```bash
LEGACY=~/twintack-legacy
LOCAL="/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/app/public/wp-content"

cp -R "$LEGACY/themes/twintack2025" "$LOCAL/themes/"
```

- [ ] Theme copied to `themes/twintack2025/`
- [ ] **Do not copy** `twintack2025-previous` unless recovering specific code
- [ ] Review theme `style.css` header — update version if consolidating changes
- [ ] Run theme asset build if applicable (`npm install && npm run build` in theme dir)
- [ ] Activate theme in Local WP admin
- [ ] Document any hardcoded production URLs in theme — replace with `home_url()` or relative paths

### Theme cleanup targets

- [ ] Remove unused template files identified during audit
- [ ] Consolidate duplicate CSS/JS enqueue logic
- [ ] Verify WooCommerce template overrides in `woocommerce/` subfolder
- [ ] Check ACF JSON sync folder (`acf-json/`) is present and loads field groups

## Phase 2 — Custom plugin port

### Identify canonical plugin version

Legacy repo contains multiple grip manager folders. On production, note which plugin folder is **actually active**, then:

```bash
# Example — adjust source folder to match production-active version
cp -R "$LEGACY/plugins/twintack-grip-manager" "$LOCAL/plugins/"
```

- [ ] Single canonical `plugins/twintack-grip-manager/` directory
- [ ] Compare against other version folders — merge missing features, discard duplicates
- [ ] Normalize folder name (no spaces, no version suffix in directory name)
- [ ] Update plugin header `Version:` to semver
- [ ] Activate and smoke-test admin screens and front-end hooks

### Plugin audit worksheet

For each plugin on production, record in [PLUGIN_INVENTORY.md](PLUGIN_INVENTORY.md):

| Question | Action |
|----------|--------|
| Is this custom TwinTack code? | Port to `plugins/` |
| Is this a free WP.org plugin? | Install fresh, document version |
| Is this a premium/licensed plugin? | Install from license, document version |
| Is this unused/disabled on production? | Do not install — note as removed |
| Are there duplicate custom plugins? | Merge into one, delete extras |

## Phase 3 — Third-party plugin install

Install (do not copy from legacy repo unless patched):

- [ ] WooCommerce
- [ ] Advanced Custom Fields PRO
- [ ] WooCommerce Stripe Gateway
- [ ] (Add others from inventory)

Match production versions where practical for compatibility testing.

## Phase 4 — Database and media import

Follow [LOCAL_SETUP.md](LOCAL_SETUP.md) Steps 4–5:

- [ ] Database imported
- [ ] URL search-replace completed
- [ ] Uploads synced
- [ ] Payment gateways in test mode
- [ ] Webhooks disabled or pointed to staging

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
- [ ] Commit ported code: `git add themes/ plugins/ docs/ && git commit -m "Port twintack2025 theme and custom plugins from legacy repo"`

## Phase 7 — Sign-off

- [ ] Homepage matches production layout (allow for data differences)
- [ ] Checkout flow works in Stripe test mode
- [ ] Custom grip flows tested
- [ ] `PLUGIN_INVENTORY.md` complete
- [ ] Merge `migration/initial-port` → `develop`

## Files to leave behind (legacy repo)

Do not port these to the rebuild repo root:

- `wp-config-250519.php` — secrets; Local manages config
- `artwork-status-test.php`, `debug-rest-api.php`, etc. — one-off scripts; port only if still needed under `tools/`
- `claude notes/` — reference only, not production code
- Duplicate plugin version directories
- Committed WooCommerce / ACF vendor trees

If debug scripts are still useful, add them under a `tools/` directory with a README explaining usage and safety (local only).

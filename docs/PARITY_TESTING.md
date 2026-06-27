# Parity Testing — Local vs Production

Walk through this after **production database** and **uploads** are both in place on Local.

**Local URL:** `http://twintack-rebuild-2026.local`  
**Production reference:** https://twintack.com/

---

## Prerequisites

Both layers must match production:

| Layer | Location | Status check |
|-------|----------|--------------|
| Database | Local MySQL via import | `wp eval 'echo $GLOBALS["wpdb"]->get_var("SELECT COUNT(*) FROM {$GLOBALS["wpdb"]->posts} WHERE post_type=\"product\"");'` should return **hundreds**, not 0 |
| Media files | `wp-content/uploads/` | `du -sh uploads/` — expect multi-GB; year folders `2024/`, `2025/`, `2026/` |
| Custom code | This repo | Theme + `twintack-*` plugins active |
| Sandbox | `mu-plugins/twintack-local-sandbox.php` | Outbound integrations blocked; WC customer emails off |

### Uploads-only is not enough

WordPress stores attachment metadata, product data, orders, and grip designs in the **database**. Files on disk without matching DB rows will not appear in the media library, shop, or grip dashboards.

If the shop shows **“No products were found”** or post counts are in the teens, re-import the production database before continuing.

**Production table prefix:** BlueHost export uses `sCO_`, not the default `wp_`. After import, set in `wp-config.php`:

```php
$table_prefix = 'sCO_';
```

Without this, WordPress reads empty `wp_` tables while production data sits in `sCO_*`.

---

## Step 1 — Post-uploads database fixes

Run after every DB import **and** after syncing uploads (upload sync can leave production URLs in serialized meta).

From the WordPress root (`app/public/`), with Local running:

```bash
source "/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/app/.envrc"
cd app/public
```

### 1a. Search-replace URLs

```bash
# Dry-run first — should show replacements needed after a fresh import
wp search-replace 'https://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables --dry-run
wp search-replace 'http://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables --dry-run

# Apply (remove --dry-run)
wp search-replace 'https://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables
wp search-replace 'http://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables
```

Verify:

```bash
wp option get siteurl   # http://twintack-rebuild-2026.local
wp option get home
wp search-replace 'https://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables --dry-run
# Expect: 0 replacements
```

### 1b. Flush rewrites and cache

```bash
wp rewrite flush --hard
wp cache flush
```

### 1c. Reactivate plugins (if import cleared active_plugins)

After some imports, all plugins deactivate. Reactivate:

```bash
wp plugin activate twintack-custom-grips twintack-grip-manager twintack-marketing \
  twintack-enhanced-shop-filters twintack-how-to-videos twintack-admin-console-fixes \
  twintack-product-save-bypass twintack-security woocommerce advanced-custom-fields-pro \
  woo-variation-swatches woo-variation-swatches-pro woocommerce-wholesale-prices \
  woocommerce-wholesale-prices-premium woocommerce-wholesale-order-form \
  woocommerce-wholesale-lead-capture woocommerce-wholesale-payments \
  advanced-dynamic-pricing-for-woocommerce filebird svg-support classic-editor admin-menu-editor
```

Integration plugins (Stripe, Klaviyo, Meta, QuickBooks, Amazon) stay **inactive** locally — sandbox handles this.

### 1d. Optional — regenerate thumbnails

Only if product/category images look wrong after uploads sync:

```bash
wp media regenerate --yes
```

This can take a long time on a large library. Skip unless you see broken image sizes.

### 1e. Confirm data volume

```bash
wp eval '
global $wpdb;
foreach (["product","attachment","grip_design","shop_order"] as $type) {
  $c = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $type
  ));
  echo "$type: $c\n";
}
'
```

Expected ballpark after full production import:

| Post type | Expect |
|-----------|--------|
| `product` | 100+ published |
| `attachment` | 1000+ |
| `grip_design` | dozens to hundreds |
| `shop_order` | production order count |

---

## Step 2 — Configuration parity

Quick admin checks (most come from DB import):

- [ ] **Settings → Reading** — front page / blog settings match production
- [ ] **Appearance → Menus** — primary and footer menus assigned to theme locations
- [ ] **WooCommerce → Settings → Advanced** — Shop, Cart, Checkout, My Account pages set
- [ ] **ACF** — field groups visible under Custom Fields (from DB; sync JSON only if using file-based groups)
- [ ] **Permalinks** — Settings → Permalinks → Save (no 404s on products)
- [ ] **Cron** — `wp cron event list` — no jobs failing on `twintack.com` URLs

---

## Step 3 — Visual / layout parity

Compare side-by-side with production (same browser width):

| Page | Local URL | Pass? |
|------|-----------|-------|
| Homepage | `/` | [x] Recent visual changes confirmed 2026-06-27 |
| Shop archive | `/shop/` | [ ] Product grid, filters, pagination |
| Single product (variable) | pick a bat grip SKU | [ ] Gallery, swatches, add to cart |
| Single product (simple) | pick an accessory | [ ] Price, add to cart |
| Static pages | Our Story, Technology, Contact, etc. | [ ] Content + images load |
| Wholesale pages | wholesale login/registration if used | [ ] Forms render |

### Image sanity check

On homepage and a product page, open DevTools → Network:

- [ ] Image URLs use `twintack-rebuild-2026.local`, not `twintack.com`
- [ ] No mass of 404s on `/wp-content/uploads/`

---

## Step 4 — WooCommerce flows (sandbox-safe)

Payment and email integrations are blocked locally. Test cart/checkout UI only unless you temporarily disable sandbox for a **Stripe test-mode** run.

| Flow | Steps | Pass? |
|------|-------|-------|
| Variable product → cart | Select color/size swatches, add to cart | [ ] |
| Cart page | `/cart/` — line items, totals | [ ] |
| Checkout page | `/checkout/` — fields populate (logged in or guest) | [ ] |
| My Account | `/my-account/` — login with test customer | [ ] |
| Wholesale pricing | Log in as wholesale test user if applicable | [ ] |

### Stripe test checkout (optional, end of testing)

1. Set `TWINTACK_DISABLE_SANDBOX` to `true` in `wp-config.php` **or** deactivate `twintack-local-sandbox.php`
2. `wp plugin activate woocommerce-gateway-stripe`
3. Configure Stripe **test** keys in WooCommerce → Payments
4. Complete one test order; confirm in Stripe dashboard (test mode)
5. Re-enable sandbox

---

## Step 5 — Custom grip workflow

Reference: [`plugins/twintack-custom-grips/WORKFLOW.md`](../plugins/twintack-custom-grips/WORKFLOW.md)

Use an existing `grip_design` post or create one via admin/deposit flow.

| Step | URL / action | Pass? |
|------|--------------|-------|
| Team dashboard loads | `/team-dashboard/` (staff login) | [x] Verified 2026-06-27 |
| Design list shows records | filter by status | [ ] |
| Single design view | `/team-dashboard/design/{id}/` | [ ] |
| Mockup upload | team uploads image → status `pending_review` | [ ] |
| Customer My Custom Grips | `/my-account/my-custom-grips/` | [ ] |
| Customer review | approve or request changes | [ ] |
| Status sync | final order → Processing → `in_production` | [ ] |

### Legacy paths (should redirect or still work)

- [ ] `/my-account/grip-designs/` redirects to My Custom Grips
- [ ] Old grip design single template renders mockup/artwork from meta

---

## Step 6 — Admin smoke test

Log in as administrator:

| Area | Pass? |
|------|-------|
| Products → edit variable product (save without wholesale conflict) | [x] Verified 2026-06-27 |
| Grip design post edit — legacy Monday panel read-only when meta exists | [ ] |
| TwinTack Custom Grips admin / notification manager | [ ] |
| TwinTack Marketing carousel settings | [ ] |
| No PHP fatals in `wp-content/debug.log` while browsing | [ ] |

---

## Step 7 — Sign-off

**Initial sign-off (2026-06-27):** Production credentials used locally. Team dashboard, product editing, and recent visual/layout changes verified. Checkout and full grip workflow E2E remain optional follow-ups.

When all critical paths pass:

- [x] Initial local parity — dashboard, admin, visuals (2026-06-27)

- [ ] Update [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) Phase 8 items
- [ ] Note any intentional local differences (sandbox, missing GF plugin, etc.)
- [ ] Merge `consolidation/phase-1-retire-integrations` → `main` via PR
- [ ] Begin consolidation Phase 2 (native grip intake)

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| Shop empty, ~18 posts in DB | Production DB not imported | Re-import `meoefemy_WPNS6.sql.gz` via Adminer or `wp db import` |
| Images 404, DB has attachments | Uploads path wrong or incomplete sync | Re-rsync `uploads/`; check file permissions |
| Images point to `twintack.com` | URL replace not run after import | Step 1a search-replace |
| Broken layout, no CSS | Mixed content or wrong `siteurl` | Step 1a + hard refresh |
| Product 404 | Permalinks | `wp rewrite flush --hard` |
| `wc_get_page_id()` fatal | WooCommerce inactive | Activate WooCommerce |
| All plugins inactive | Common after DB import | Step 1c reactivate |
| Team dashboard 302 to login | Expected | Log in as user with team capability |
| Gravity Forms on grip order page | GF not installed locally | Expected until Phase 2; use existing grip posts for workflow testing |

---

## Quick verification script

Save time before manual walkthrough:

```bash
source "/Volumes/A&D/Dropbox/Development/Local/twintack-rebuild-2026/app/.envrc"
cd app/public

echo "=== URLs ==="
wp option get siteurl
wp search-replace 'https://twintack.com' 'http://twintack-rebuild-2026.local' --all-tables --dry-run | tail -1

echo "=== Post counts ==="
wp eval 'global $wpdb; foreach(["product","attachment","grip_design"] as $t){ echo "$t: ".$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s",$t))."\n"; }'

echo "=== HTTP smoke ==="
for path in / /shop/ /my-account/; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://twintack-rebuild-2026.local$path")
  echo "$path → $code"
done

echo "=== Uploads on disk ==="
du -sh wp-content/uploads
```

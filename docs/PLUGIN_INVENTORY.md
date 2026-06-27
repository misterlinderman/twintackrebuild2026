# Plugin Inventory

Production plugin set copied from **twintack.com** into Local on 2026-06-25.

**Last updated:** 2026-06-26

## Git tracking policy

| Category | Local install | Tracked in Git |
|----------|---------------|----------------|
| TwinTack custom (`plugins/twintack-*`) | Yes | **Yes** |
| Third-party / premium | Yes (copied from production) | **No** — versions documented here |

Third-party plugins remain on disk for Local parity but are excluded via `.gitignore`. Reinstall from WP admin or license zip on a fresh clone.

---

## Custom TwinTack plugins (track in Git)

**Consolidation target:** [`PLUGIN_CONSOLIDATION.md`](PLUGIN_CONSOLIDATION.md) — `twintack-custom-grips` becomes the workflow foundation; Grip Manager slimmed/merged; GF / Make.com / Monday.com retired.

| Plugin | Folder | Version | Purpose | Consolidation notes |
|--------|--------|---------|---------|---------------------|
| TwinTack Custom Grips | `twintack-custom-grips` | 1.3.0 | **Canonical** team dashboard, customer My Custom Grips, mockups, messaging, statuses, **native intake (Phase 2)** | **Foundation** — absorb Grip Manager intake + CPT over time |
| TwinTack Grip Manager | `twintack-grip-manager` | 1.7.06 | `grip_design` CPT, WC order bridge, cart/reorder | Phase 1 complete — Make/Monday removed; GF intake remains for Phase 2 |
| TwinTack Manual Order Payments | `twintack-manual-order-payments` | 4.6.1 | Stripe checkout for manual/WP admin orders | Integrates with Grip Manager |
| TwinTack Marketing | `twintack-marketing` | 1.2.0 | Homepage carousel, featured products, announcement bar | **Overlap** with theme `template-parts/marketing/v2/` — consolidate later |
| TwinTack Enhanced Shop Filters | `twintack-enhanced-shop-filters` | 1.0.2 | Shop filtering, color swatches, sorting | Depends on WooCommerce + variation swatches |
| TwinTack How-To Videos | `twintack-how-to-videos` | 1.1.0 | Product how-to video management | Theme also has `inc/how-to-videos.php` |
| TwinTack Admin Console Fixes | `twintack-admin-console-fixes` | 1.0.9 | WC admin JS/React fixes, wholesale conflict patches | Candidate to merge with product-save-bypass |
| TwinTack Product Save Bypass | `twintack-product-save-bypass` | 1.0.1 | Bypass wholesale plugin interference on product save | **Workaround** — fix wholesale conflict at root cause, then remove |
| TwinTack Security Suite | `twintack-security` | 1.0.0 | SMS gateway spam / WC security hardening | Keep — production-specific threat model |
| TwinTack Amazon Tracking Bridge | `twintack-amazon-tracking-bridge` | 1.1.0 | Shippo tracking → WP-Lister Amazon meta | Requires `wp-lister-amazon` (third-party) |

---

## Third-party plugins (local only, do not commit)

### WooCommerce core stack

| Plugin | Folder | Version | Rebuild action | Notes |
|--------|--------|---------|----------------|-------|
| WooCommerce | `woocommerce` | 10.9.1 | Keep — install locally | Core e-commerce |
| WooCommerce Stripe Gateway | `woocommerce-gateway-stripe` | 10.8.3 | Keep — test mode locally | Never commit live keys |
| Advanced Custom Fields PRO | `advanced-custom-fields-pro` | 6.8.4 | Keep — license install | Field groups primarily in DB |
| Variation Swatches for WooCommerce | `woo-variation-swatches` | 2.3.0 | Keep | Free base for Pro |
| Variation Swatches for WooCommerce - Pro | `woo-variation-swatches-pro` | 2.3.0 | Keep — license | Used by shop filters |
| Advanced Dynamic Pricing and Discount Rules | `advanced-dynamic-pricing-for-woocommerce` | 4.13.2 | Keep — license | Promotions / cart rules |

### Wholesale suite (Rymera)

| Plugin | Folder | Version | Rebuild action | Notes |
|--------|--------|---------|----------------|-------|
| WooCommerce Wholesale Prices | `woocommerce-wholesale-prices` | 2.2.8 | Keep | Base wholesale plugin |
| WooCommerce Wholesale Prices Premium | `woocommerce-wholesale-prices-premium` | 2.0.4 | Keep — license | Causes product-save conflicts (see bypass plugins) |
| WooCommerce Wholesale Order Form | `woocommerce-wholesale-order-form` | 3.0.6.1 | Keep — license | B2B ordering |
| WooCommerce Wholesale Lead Capture | `woocommerce-wholesale-lead-capture` | 2.0.1 | Keep — license | Wholesale registration |
| WooCommerce Wholesale Payments | `woocommerce-wholesale-payments` | 1.0.6 | Keep — license | Wholesale payment terms |

### Marketing, analytics, and integrations

| Plugin | Folder | Version | Rebuild action | Notes |
|--------|--------|---------|----------------|-------|
| Klaviyo | `klaviyo` | 3.8.0 | Keep — disable outbound locally | Theme has `inc/klaviyo-proxy.php` |
| Meta for WooCommerce | `facebook-for-woocommerce` | 3.7.3 | Keep — disable pixel locally | Facebook/Meta catalog sync |
| Pixel Manager for WooCommerce | `woocommerce-google-adwords-conversion-tracking-tag` | 1.60.0 | Keep — disable tracking locally | Google Ads / GA4 pixels |
| Solid Affiliate | `solid_affiliate` | 3.1.0 | Keep — license | Affiliate program |

### Operations and admin

| Plugin | Folder | Version | Rebuild action | Notes |
|--------|--------|---------|----------------|-------|
| WP-Lister Pro for Amazon | `wp-lister-amazon` | 2.9.2.1 | Keep — license | Required by Amazon Tracking Bridge |
| Integration for WooCommerce and QuickBooks | `wp-woocommerce-quickbooks` | 1.3.5 | Keep — license | Accounting sync — disable locally |
| Admin Menu Editor | `admin-menu-editor` | 1.15.1 | Evaluate | Admin UX convenience |
| Classic Editor | `classic-editor` | 1.7.0 | Evaluate | May not be needed on WP 6.x |
| FileBird Lite | `filebird` | 6.5.5 | Keep | Media library organization |
| SVG Support | `svg-support` | 2.5.16 | Keep | Theme also has `inc/svg-support.php` |

---

## Plugins to evaluate for removal

| Plugin / system | Reason | Confirmed safe? |
|-----------------|--------|-----------------|
| **Gravity Forms** | Replaced by native intake in `twintack-custom-grips` (planned) | No — GF hooks still in Grip Manager + theme |
| **Make.com** | Retired — webhook code removed in Phase 1 | Yes — removed from theme + Grip Manager |
| **Monday.com** | Retired — REST API removed; legacy meta read-only | Yes — API removed; historical meta preserved |
| TwinTack Product Save Bypass | Workaround for wholesale plugin conflict — fix root cause instead | No |
| Classic Editor | Block editor is default; may be legacy preference only | No |
| Admin Menu Editor | Admin convenience, not customer-facing | No |
| Duplicate marketing logic | Theme `marketing/v2/` vs `twintack-marketing` plugin | No |

---

## Legacy duplicate folders (not present — good)

The production copy has a **single canonical folder** per plugin. Legacy [twintack](https://github.com/misterlinderman/twintack) duplicate grip manager folders were not copied:

- `twintack-grip-manager 1-0-0`
- `twintack-grip-manager 1-3-2`
- `twintack-grip-manager 1-4-2`
- `twintack-grip-manager 1501`

---

## Dependency map

```
twintack-custom-grips (canonical workflow)
  └── twintack-grip-manager (legacy infra — to be merged)
        └── WooCommerce order sync, grip_design CPT, cart/reorder

twintack-grip-manager (until merged)
  └── twintack-manual-order-payments (Stripe manual orders)

twintack-amazon-tracking-bridge
  └── wp-lister-amazon (Shippo tracking meta bridge)

twintack-enhanced-shop-filters
  └── woo-variation-swatches + woo-variation-swatches-pro

woocommerce-wholesale-prices-premium
  └── twintack-product-save-bypass (conflict workaround)
  └── twintack-admin-console-fixes (admin JS patches)
```

---

## Verify active status after DB import

Once the production database is imported, confirm activation state:

```bash
wp plugin list --format=table
```

Update the **Rebuild action** column above if any plugin is inactive on production.

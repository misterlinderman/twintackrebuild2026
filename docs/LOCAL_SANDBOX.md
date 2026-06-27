# Local Sandbox — Theme & Plugin Development

Configure Local so you can build the theme and consolidate TwinTack plugins **without hitting production services**. Use real production data for layout and content; block outbound side effects.

**Applies when:** `WP_ENVIRONMENT_TYPE` is `local` (already set in `wp-config.php`).

---

## What runs automatically

The must-use plugin `mu-plugins/twintack-local-sandbox.php` activates on every Local load and:

| Protection | Behavior |
|------------|----------|
| Make.com HTTP | Blocked via `pre_http_request` (defense in depth — webhook code removed in Phase 1) |
| Stripe / Klaviyo / Meta / QuickBooks / Amazon API | Blocked via `pre_http_request` |
| WooCommerce customer emails | Disabled (processing, completed, invoice, note) |
| Other email | Routed to **Mailpit** (Local → site → **Mailpit** tab) |
| Admin notice | Reminds you sandbox mode is on |

To temporarily disable the sandbox (e.g. final Stripe test):

```php
// wp-config.php — add above "stop editing" line
define( 'TWINTACK_DISABLE_SANDBOX', true );
```

Remove or set to `false` when done testing.

---

## Recommended plugin tiers

### Keep active — daily theme & custom plugin work

These support the storefront, ACF fields, and TwinTack code you are consolidating:

```
woocommerce
advanced-custom-fields-pro
twintack-grip-manager
twintack-custom-grips
twintack-marketing
twintack-enhanced-shop-filters
twintack-how-to-videos
twintack-admin-console-fixes
twintack-product-save-bypass
twintack-security
woo-variation-swatches
woo-variation-swatches-pro
advanced-dynamic-pricing-for-woocommerce
woocommerce-wholesale-prices
woocommerce-wholesale-prices-premium
woocommerce-wholesale-order-form
woocommerce-wholesale-lead-capture
woocommerce-wholesale-payments
classic-editor
svg-support
filebird
admin-menu-editor
```

Wholesale plugins stay on because production uses them and your bypass/fix plugins address their admin conflicts. They do not send customer-facing outbound traffic during normal browsing.

### Deactivate — not needed for theme/plugin dev

No impact on homepage, shop templates, grip UI, or admin consolidation work:

| Plugin | Why deactivate |
|--------|----------------|
| `klaviyo` | Newsletter API calls |
| `facebook-for-woocommerce` | Meta catalog / pixel sync |
| `woocommerce-google-adwords-conversion-tracking-tag` | Pixel Manager / ad tracking |
| `solid_affiliate` | Affiliate tracking |
| `wp-woocommerce-quickbooks` | Accounting sync |
| `wp-lister-amazon` | Amazon listing sync |
| `twintack-amazon-tracking-bridge` | Depends on WP-Lister |

### Deactivate until final checkout tests

| Plugin | When to reactivate |
|--------|-------------------|
| `woocommerce-gateway-stripe` | Final checkout / payment flow testing |
| `twintack-manual-order-payments` | Testing manual orders, Stripe payment links, Shippo sync |

---

## One-time setup commands

Run from **Local’s site shell** (or after `source app/.envrc`):

```bash
cd app/public

# Deactivate outbound integration plugins
wp plugin deactivate \
  klaviyo \
  facebook-for-woocommerce \
  woocommerce-google-adwords-conversion-tracking-tag \
  solid_affiliate \
  wp-woocommerce-quickbooks \
  wp-lister-amazon \
  twintack-amazon-tracking-bridge \
  woocommerce-gateway-stripe \
  twintack-manual-order-payments \
  --skip-themes

# Confirm what's still active
wp plugin list --status=active --skip-themes
```

---

## wp-config.php tweaks (optional, Local only)

Add between the custom values line and `/* That's all, stop editing! */` in `app/public/wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );

// Optional: stop WP-Cron from firing integration jobs while you browse locally
define( 'DISABLE_WP_CRON', true );
```

`wp-config.php` is not in Git — safe to edit on your machine only.

With `DISABLE_WP_CRON`, run cron manually when needed:

```bash
wp cron event run --due-now --skip-themes
```

---

## Make.com / Monday.com / Gravity Forms

**Phase 1 complete (2026-06-26):** Make.com webhooks and the Monday.com REST API were removed from the theme and Grip Manager. Historical `_grip_monday_*` post meta is preserved and shown read-only in admin when present.

**Still pending (Phase 2+):** Gravity Forms intake hooks in Grip Manager and theme GF script workarounds — see [`PLUGIN_CONSOLIDATION.md`](PLUGIN_CONSOLIDATION.md).

Native production runs entirely in `twintack-custom-grips` (see `plugins/twintack-custom-grips/WORKFLOW.md`).

The sandbox mu-plugin still blocks Make.com HTTP on Local as defense in depth.

---

## Final checkout test checklist

When you are ready to test payments (once, at the end):

1. Set `TWINTACK_DISABLE_SANDBOX` to `true` in `wp-config.php` **or** temporarily deactivate `mu-plugins/twintack-local-sandbox.php`
2. `wp plugin activate woocommerce-gateway-stripe twintack-manual-order-payments --skip-themes`
3. WooCommerce → Settings → Payments → Stripe → **Enable test mode**
4. Enter [Stripe test keys](https://docs.stripe.com/testing) (never live keys on Local)
5. Test card: `4242 4242 4242 4242`, any future expiry, any CVC
6. Re-deactivate Stripe plugins and re-enable sandbox when done

---

## What you can safely do every day

- Edit `themes/twintack2025/` templates, CSS, JS
- Refactor and merge `plugins/twintack-*` code
- Browse shop, product pages, custom grip flows, my-account layouts
- Create test orders in admin (emails go to Mailpit, not customers)
- Change order/grip statuses (no outbound integration webhooks)

## What to avoid even with sandbox on

- Importing fresh production DB over local without re-running plugin deactivation steps
- Committing `meoefemy_WPNS6.sql*` or API keys to Git
- Leaving `TWINTACK_DISABLE_SANDBOX` on permanently

---

## Mailpit

Local captures email at **Site → Mailpit** (or the mail icon in the site toolbar). Use it to verify WooCommerce email template changes without sending to real addresses.

If emails don't appear, check the Mailpit port in Local's site `php.ini` (`sendmail_path`) and update the port in `twintack_sandbox_mailpit_smtp()` if needed. This site uses port **10046**.

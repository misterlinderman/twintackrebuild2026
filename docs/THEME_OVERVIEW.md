# Theme Overview — twintack2025

Production theme copied from **twintack.com** on 2026-06-25. Default Twenty* themes removed from Local.

**Path:** `themes/twintack2025/`  
**Version:** 1.0.0 (style.css header — consider bumping on first rebuild release)  
**Stack:** WordPress + WooCommerce + ACF Pro + custom TwinTack plugins

---

## Structure at a glance

| Area | Location | Notes |
|------|----------|-------|
| Entry / setup | `functions.php`, `inc/class-theme-setup.php`, `inc/class-loader.php` | Central bootstrapping |
| WooCommerce overrides | `woocommerce/` (**168** template files) | Emails, cart, checkout, my account, single product |
| Page templates | `templates/` (16 files) | Homepage v2, sport pages, grip forms, login, technology |
| Marketing sections | `template-parts/marketing/v2/` | Hero, featured products, UGC carousel, video tabs |
| Custom grip flow | `page-custom-grip-intro.php`, `inc/class-twintack-grip-configurator.php` | Custom grip intro + configurator |
| Styles | `css/main.css` + component partials | No Sass build required for runtime — `package.json` exists for WP scripts |
| Scripts | `js/` | Header, product gallery, stripe fixes, password reset |
| Account / auth | `page-login.php`, `template-parts/account/`, `woocommerce/myaccount/` | Custom login/lost password flows |

---

## Key page templates

| Template | File | Use |
|----------|------|-----|
| Homepage (marketing v2) | `templates/template-homepage-v2.php` | Current production homepage layout |
| Homepage (legacy marketing) | `templates/template-homepage-marketing.php` | Older marketing layout |
| Custom grip intro | `page-custom-grip-intro.php` | Custom grip onboarding |
| Grip form | `templates/template-gripform.php`, `template-gripform-clean.php` | Grip order forms |
| Sport landing | `templates/template-sport.php` | Baseball / fishing category pages |
| Login | `templates/template-login.php`, `page-login.php` | Branded login |

---

## WooCommerce customization depth

The theme carries extensive overrides beyond typical child-theme scope:

- **Emails** — custom order, invoice, failed order, reset password templates
- **Cart / checkout** — mini-cart, coupon, login at checkout
- **My Account** — dashboard, grip designs, navigation, view order
- **Single product** — gallery, tabs, add-to-cart variants, marketing v2 product layout
- **Archive** — shop archive, loop, result count

Refactoring should preserve behavior file-by-file; do not bulk-delete overrides without diffing against WooCommerce defaults.

---

## Overlap with plugins (consolidation targets)

| Concern | Theme location | Plugin equivalent |
|---------|----------------|-------------------|
| Marketing homepage | `template-parts/marketing/v2/*` | `twintack-marketing` |
| How-to videos | `inc/how-to-videos.php`, `css/components/_how-to-videos.css` | `twintack-how-to-videos` |
| Manual order payments | `inc/class-manual-order-payment-fix.php` | `twintack-manual-order-payments` |
| Klaviyo | `inc/klaviyo-proxy.php`, `inc/klaviyo-settings.php` | Klaviyo plugin |
| SVG uploads | `inc/svg-support.php` | SVG Support plugin |
| Shop filters | WooCommerce archive templates | `twintack-enhanced-shop-filters` |

During consolidation, pick **one owner** per feature (theme vs plugin) and deprecate the duplicate.

---

## Build tooling

`package.json` includes `@wordpress/scripts` (Underscores starter scaffold). Check whether production uses compiled assets or serves CSS/JS directly before running builds — current theme loads `css/main.css` via `@import` in `style.css`.

```bash
cd themes/twintack2025
npm install   # if modifying JS build pipeline
```

---

## Debug / dev artifacts to review

These files suggest active debugging on production — review before next deploy:

| File | Action |
|------|--------|
| `js/checkout-debug.js` | Remove or gate behind `WP_DEBUG` |
| `js/theme-conflict-detector.js` | Remove or local-only |
| `js/product-gallery-test.js` | Remove or local-only |
| `inc/debug-viewer.php` | Remove or local-only |

---

## Hardcoded production references

Initial audit found `support@twintack.com` in `functions.php` (email sender/reply-to). Email addresses may be intentional; audit for hardcoded **URLs** before local DB import:

```bash
grep -r "twintack\.com" themes/twintack2025/ --include="*.php" --include="*.js"
```

Replace site URLs with `home_url()` where appropriate; email addresses may stay as production contact info.

---

## ACF field groups

No `acf-json/` sync folder present in the theme. Field groups are likely stored in the **database** (imported with production DB) or managed only in ACF admin. After DB import, export field groups to JSON if you want them version-controlled in a future pass.

---

## Removed default themes

Stock Twenty Twenty-Three/Four/Five themes were removed from `themes/`. Local WordPress core may reinstall them on update — they remain in `.gitignore`.

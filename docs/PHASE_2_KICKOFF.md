# Phase 2 Kickoff — Replace Gravity Forms Intake

**Status:** Ready to begin (2026-06-27)  
**Prerequisite:** Phase 1 merged to `main` — Make.com / Monday.com integrations removed  
**Branch naming:** `consolidation/phase-2-native-grip-intake`

---

## Goal

Replace Gravity Forms (GF) as the customer-facing custom grip order entry point with a **native form in `twintack-custom-grips`**, while preserving the existing deposit → cart → `grip_design` post creation flow.

After Phase 2:

- No `gform_*` hooks on the front-end
- No GF plugin required locally or on production
- Theme grip form templates use native markup, not `[gravityform]`
- Grip Manager GF Importer remains only as a one-time historical migration tool (optional removal later)

---

## What already exists (reuse, don't rebuild from scratch)

| Asset | Location | Notes |
|-------|----------|-------|
| Team manual create form | `twintack-custom-grips/templates/partials/new-design.php` | Staff-only at `/team-dashboard/new/` — good field reference |
| Team form AJAX | `twintack-custom-grips/assets/js/dashboard.js` | Creates grip posts directly (no cart) |
| Deposit → post creation | `twintack-grip-manager/includes/class-grip-form-handler.php` | WC cart hooks + `process_completed_order()` — **port cart/deposit logic** |
| Cart metadata display | Same file — `add_grip_data_to_cart`, `display_cart_item_custom_data` | Keep until Phase 3 merge |
| Configurator UI shell | `themes/twintack2025/inc/class-twintack-grip-configurator.php` | Refactor GF selectors to native form |
| Grip form page templates | `themes/twintack2025/templates/template-gripform*.php` | Replace GF embed with native partial |

---

## Gravity Forms inventory (forms 8 and 9)

Documented from `class-grip-form-handler.php` field IDs. **Verify against production GF export before implementation** — field IDs may have drifted.

### Form 8 — Original grip form (`process_grip_form`)

| GF field ID | Maps to | Required |
|-------------|---------|----------|
| 1.3 / 1.6 | Customer first + last name → `_grip_customer_name` | Yes |
| 8 | Team/school name → `_grip_team_name` | Yes |
| 9 | Artwork file upload → `_grip_artwork_url`, `_grip_artwork_filename` | Yes |
| 12 | Design type → `_grip_design_type` | Yes |
| 14 | Design instructions → `_grip_feedback` | No |
| 21 | Quantity → `_grip_quantity` | Yes |
| — | Logged-in user email → `_grip_customer_email` | Yes (from WP user) |
| entry ID | `_grip_form_entry_id` | Legacy meta |

### Form 9 — New configurator form (`process_new_grip_form`) — production export 2026-06-27

Reference: `gravityforms-export-2026-06-27.json` (repo root, local only)

| GF field ID | Label | Maps to |
|-------------|-------|---------|
| 1.3 / 1.6 | Name (first/last) | `_grip_customer_name` |
| 3 | Email | `_grip_customer_email` (logged-in user on native form) |
| 4 | Phone | not stored on grip_design today |
| 8 | Team/School name | `_grip_team_name` |
| 9 | Logo upload | `_grip_artwork_url`, `_grip_artwork_filename` |
| 12 | Design layout (image choice) | `_grip_design_layout` — Solid Color, 2-Color Fade, 3-Color Fade, Splatter |
| 14 | Design instructions | `_grip_feedback` |
| 21 | Quantity (25–1000) | `_grip_quantity` |
| 41 | Product color | `_grip_primary_color` |
| 42 | Second color | `_grip_secondary_color` |
| 43 | Third color | `_grip_tertiary_color` |
| — | Built string | `_grip_design_type` |
| entry ID | — | `_grip_form_entry_id` |
| — | `form_type` = `new` | order/cart metadata |

Billing/shipping address fields (13, 17, 18) are collected by GF but checkout uses WooCommerce billing — not copied to grip_design meta.

### Deposit product

- SKU: `grip-design-deposit`
- Created on demand by `get_deposit_product_id()` in Grip Manager
- Virtual, $50, sold individually

---

## Code to remove or replace

### `twintack-grip-manager`

| File | Action |
|------|--------|
| `includes/class-grip-form-handler.php` | Remove `gform_after_submission_8/9` hooks; keep WC cart/order hooks until Phase 3 |
| `includes/class-grip-admin.php` | Remove or gate GF Importer meta box after historical migration |
| `twintack-grip-manager.php` | Remove `GFAPI` dependency check / admin notice |

### `themes/twintack2025`

| File | Action |
|------|--------|
| `functions.php` ~444–537 | Remove GF 2.9.18 script loading workarounds |
| `functions.php` ~1721–1723 | Remove debug `gform_after_submission` logger |
| `inc/class-twintack-grip-configurator.php` | Replace `.gform_wrapper` / `[gravityform` detection |
| `templates/template-gripform.php` | Native form partial |
| `templates/template-gripform-clean.php` | Native form partial or consolidate to one template |
| `css/components/_gravity-forms.css` | Remove when GF styling unused |

---

## Implementation plan

### Step 1 — Document production GF forms (prerequisite)

On production (or from DB export):

1. Export GF forms 8 and 9 (Forms → Import/Export)
2. Screenshot or list all fields, conditionals, and validation rules
3. Confirm which template pages use which form (`template-gripform.php` vs `-clean`)
4. Update the field mapping table in this doc if IDs differ

**Local note:** GF is not installed locally. `uploads/gravity_forms/` may contain historical uploads referenced by existing grip posts.

### Step 2 — Customer native intake form (Custom Grips)

Build in `twintack-custom-grips`:

- [ ] New template partial (e.g. `templates/partials/customer-intake-form.php`) or shortcode/block
- [ ] AJAX handler in `class-custom-grips-ajax.php` — validate, handle file upload via WP media
- [ ] On success: populate `grip_design_data` cart metadata (same shape as GF handlers) and redirect to cart
- [ ] Require logged-in customer (match current GF behavior)
- [ ] Reuse validation rules from configurator (colors conditional on layout, quantity min, file types)

### Step 3 — Port deposit logic (minimal move)

Option A (recommended for Phase 2): Keep WC hooks in Grip Manager; Custom Grips AJAX calls shared helper or fires action `ttcg_grip_intake_submitted` that Grip Manager listens for.

Option B: Move all of `class-grip-form-handler.php` cart/order logic into Custom Grips (blurs Phase 2/3 boundary).

### Step 4 — Theme integration

- [ ] Replace GF embed in grip form templates with native partial render
- [ ] Update configurator JS to target native form selectors
- [ ] Remove GF script/style enqueues

### Step 5 — Local testing

Use [PARITY_TESTING.md](PARITY_TESTING.md) Step 5 flows:

- [ ] Customer completes native form → deposit in cart
- [ ] Checkout (sandbox) → `grip_design` post created with `artwork_pending`
- [ ] Team dashboard shows new design
- [ ] Existing grip posts unaffected

Verify:

```bash
# No GF references on front-end after Phase 2
grep -r "gform_\|gravityform\|GFAPI" themes/twintack2025 plugins/twintack-custom-grips --include="*.php"
```

### Step 6 — Production cutover

1. Deploy Phase 2 branch to staging (if available) or Local sign-off
2. Deactivate Gravity Forms on production
3. Monitor new grip deposits for 1–2 weeks
4. Remove GF Importer from Grip Manager admin (keep CLI/script for one-time entry recovery if needed)

---

## Suggested branch workflow

```bash
git checkout main
git pull origin main
git checkout -b consolidation/phase-2-native-grip-intake
```

### Scaffold landed (plugin v1.3.0)

| Component | Path |
|-----------|------|
| Intake handler | `includes/class-custom-grips-intake.php` |
| Form partial | `templates/partials/customer-intake-form.php` |
| Shortcode | `[ttcg_grip_intake]` |
| AJAX action | `ttcg_submit_grip_intake` → deposit in cart |
| Assets | `assets/css/customer-intake.css`, `assets/js/customer-intake.js` |
| Theme hook | `template-gripform-clean.php` renders native form |

**Test locally:** Log in → grip configurator page (Clean Grip Form template) → submit → cart should show deposit with grip metadata.

Commit incrementally:

1. GF field mapping doc + native form scaffold
2. AJAX + cart integration
3. Theme template swap
4. GF code removal
5. Docs + version bump

---

## Verification checklist (Phase 2 complete)

- [ ] Customer native form submits without GF plugin active
- [ ] Deposit product added to cart with correct metadata
- [ ] Order processing creates `grip_design` post (`artwork_pending`)
- [ ] Team dashboard + My Custom Grips unchanged for existing records
- [ ] No `gform_*` / `GFAPI` in front-end code paths
- [ ] Theme GF workarounds removed
- [ ] `PLUGIN_CONSOLIDATION.md` and `PLUGIN_INVENTORY.md` updated

---

## Related docs

| Doc | Purpose |
|-----|---------|
| [PLUGIN_CONSOLIDATION.md](PLUGIN_CONSOLIDATION.md) | Full consolidation roadmap |
| [WORKFLOW.md](../plugins/twintack-custom-grips/WORKFLOW.md) | Artwork status flow after deposit |
| [PARITY_TESTING.md](PARITY_TESTING.md) | End-to-end test cases |
| [LOCAL_SANDBOX.md](LOCAL_SANDBOX.md) | Safe local checkout testing |

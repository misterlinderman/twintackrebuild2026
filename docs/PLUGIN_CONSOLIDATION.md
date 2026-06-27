# Plugin Consolidation Roadmap

**Goal:** Retire Gravity Forms, Make.com, and Monday.com. **`twintack-custom-grips`** is the canonical workflow plugin for custom bat grip production. Consolidate TwinTack proprietary plugins around it.

**Canonical workflow reference:** [`plugins/twintack-custom-grips/WORKFLOW.md`](../plugins/twintack-custom-grips/WORKFLOW.md)

---

## Historical stack (retired)

```
Gravity Forms submission
    → Make.com scenario
        → Monday.com (artwork / production boards)
            → REST callbacks into WordPress (Grip Manager)
```

Problems: external dependencies, brittle automations, duplicate UI (Monday + WP admin), hardcoded webhook URLs in the theme.

---

## Current stack (production today)

| Layer | Plugin | Role |
|-------|--------|------|
| **Workflow (canonical)** | `twintack-custom-grips` | Team dashboard (`/team-dashboard/`), customer **My Custom Grips**, artwork statuses, mockups, messaging, notifications |
| **Infrastructure** | `twintack-grip-manager` | `grip_design` post type, WooCommerce order bridge, cart/reorder — **GF intake hooks remain for Phase 2** |
| **Theme** | `twintack2025` | Templates, configurator UI — **Make.com webhooks removed; GF script workarounds remain for Phase 2** |

Monday.com and Make.com integration **code was removed in Phase 1** (2026-06-26). Historical `_grip_monday_*` meta is preserved read-only.

Gravity Forms is **not installed** in the Local rebuild (only `uploads/gravity_forms/` remnants). Form intake hooks in Grip Manager still reference GF forms 8 and 9 — these must be replaced with native intake in Custom Grips.

---

## Target architecture

```
Customer / team UI          twintack-custom-grips (single workflow plugin)
    ├── Native form intake (replaces Gravity Forms)
    ├── grip_design posts + meta
    ├── Team dashboard + customer My Custom Grips
    ├── Mockups, messaging, status machine
    └── WooCommerce sync (deposit → final purchase → production → shipped)

Supporting (evaluate merge) twintack-grip-manager → slim or absorbed
Theme                       twintack2025 — presentation only, no integration webhooks
```

**Eliminate entirely:** Gravity Forms plugin, Make.com scenarios, Monday.com boards/API.

---

## Legacy code removal inventory

Use this checklist during consolidation. Do **not** delete until the native replacement is verified on Local.

### Theme — `themes/twintack2025/`

| Location | What | Action |
|----------|------|--------|
| `functions.php` ~2613–2675 | Make.com webhook URL filters + `grip_design_updated` webhook | **Done** — removed Phase 1 |
| `functions.php` ~444–537 | Gravity Forms 2.9.18 script loading workarounds | **Remove** after GF retired |
| `functions.php` ~1721 | `gform_after_submission` hook | **Remove** — logic belongs in Custom Grips |
| `inc/class-twintack-grip-configurator.php` | `.gform_wrapper` selectors, `[gravityform` detection | **Refactor** to native form markup |
| `templates/template-gripform*.php` | GF-based grip forms | **Replace** with Custom Grips templates |
| `css/components/_gravity-forms.css` | GF styling | **Remove** when forms are native |

### `twintack-grip-manager` (legacy integrations)

| Location | What | Action |
|----------|------|--------|
| `includes/class-grip-post-type.php` | Monday REST API, Make.com webhooks, Monday meta box | **Done** — API + webhooks removed; read-only legacy meta box |
| `includes/class-grip-form-handler.php` | Make.com new-design webhook | **Done** — replaced with `grip_new_design_created` action |
| `includes/class-grip-account.php` | Make.com customer feedback webhook | **Done** — replaced with `grip_customer_feedback_submitted` action |
| `MONDAY-API.md` | Deprecated API docs | **Done** — deleted |
| `includes/class-grip-admin.php` | Gravity Forms Importer meta box + AJAX | **Phase 2** — remove after historical entries migrated |
| `includes/class-grip-form-handler.php` | `gform_after_submission_8/9` hooks | **Phase 2** — replace with Custom Grips form handler |
| Plugin header description | Mentions GF + Monday | **Done** — updated in 1.7.06 |

### Post meta — keep for history, hide in UI

These fields may exist on older `grip_design` posts. Do not delete meta from the database without a migration plan:

| Meta key | Legacy source | Display |
|----------|---------------|---------|
| `_grip_monday_item_id` | Monday.com | Hide from team UI; optional read-only in admin |
| `_grip_monday_feedback` | Monday.com notes | Superseded by Custom Grips messaging |
| `_grip_monday_feedback_history` | Monday.com | Archive only |
| `_grip_form_entry_id` | Gravity Forms | Keep until GF importer removed |

Custom Grips already documents the native status flow via `_grip_artwork_status` — see WORKFLOW.md.

### `twintack-custom-grips` (expand, don't shrink)

This plugin **gains** responsibility during consolidation:

- [ ] Native custom grip order form (replace GF forms 8/9)
- [ ] Deposit → cart → `grip_design` post creation (move from Grip Manager)
- [ ] Optional: absorb `grip_design` post type registration
- [ ] Notification Manager remains here (already canonical)

### Other TwinTack plugins (unchanged scope for now)

| Plugin | Consolidation note |
|--------|-------------------|
| `twintack-marketing` | Merge with theme marketing v2 templates |
| `twintack-manual-order-payments` | Keep — separate concern (manual WC orders) |
| `twintack-enhanced-shop-filters` | Keep |
| `twintack-how-to-videos` | Evaluate merge with theme `inc/how-to-videos.php` |
| `twintack-admin-console-fixes` + `twintack-product-save-bypass` | Merge or fix wholesale root cause |
| `twintack-amazon-tracking-bridge` | Unrelated to grip workflow |

---

## Phased plan

### Phase 1 — Remove dead outbound integrations (low risk) ✅ Complete — merged to `main` (2026-06-27)

**Prerequisite:** Confirm Make.com and Monday.com scenarios are disabled in production.

1. ~~Remove Make.com webhook block from `themes/twintack2025/functions.php`~~
2. ~~Remove webhook HTTP triggers from Grip Manager~~ — replaced with WordPress actions
3. ~~Remove Monday REST API endpoint registration~~ — read-only legacy meta preserved
4. ~~Replace Monday.com admin meta box~~ — read-only **Legacy Monday.com Data** panel
5. ~~Update plugin headers and admin copy~~

**Merged:** `consolidation/phase-1-retire-integrations` → `main`

### Phase 2 — Replace Gravity Forms intake (medium risk) 🟡 Ready to start

**Kickoff doc:** [PHASE_2_KICKOFF.md](PHASE_2_KICKOFF.md)

**Prerequisite:** Document current GF form fields (forms 8/9) and map to Custom Grips form.

**Suggested branch:** `consolidation/phase-2-native-grip-intake`

1. Build native intake form in `twintack-custom-grips` (AJAX + validation + file upload)
2. Port `class-grip-form-handler.php` cart/deposit logic to Custom Grips
3. Update theme configurator and `template-gripform*.php` to use native form
4. Remove GF script workarounds from theme
5. Deactivate/uninstall Gravity Forms on production after parity testing
6. Remove GF Importer from Grip Manager admin (keep one-time migration script if needed)

### Phase 3 — Merge Grip Manager into Custom Grips (higher effort)

1. Move `grip_design` CPT registration to Custom Grips
2. Move WooCommerce order sync (`TwinTack_Grip_Account`) to Custom Grips
3. Slim Grip Manager to a compatibility shim or remove entirely
4. Single plugin version header, unified admin menu

### Phase 4 — Theme cleanup

1. Presentation-only theme — no business logic webhooks
2. Resolve marketing duplication (theme vs `twintack-marketing`)
3. Remove debug JS artifacts (`checkout-debug.js`, etc.)

---

## Verification checklist (each phase)

- [ ] New grip deposit flow creates `grip_design` post with `artwork_pending`
- [ ] Team dashboard: mockup upload → `pending_review`
- [ ] Customer: approve / request changes on `/my-account/my-custom-grips/`
- [ ] Final purchase → WC Processing → `in_production`
- [ ] Order Completed → `shipped`
- [ ] No HTTP requests to `make.com` or `monday.com` (check Network tab + `debug.log`)
- [ ] No `GFAPI` or `gform_*` function calls on front-end

---

## Documentation map

| Doc | Purpose |
|-----|---------|
| [`WORKFLOW.md`](../plugins/twintack-custom-grips/WORKFLOW.md) | Canonical status flow and UI URLs |
| [`PLUGIN_INVENTORY.md`](PLUGIN_INVENTORY.md) | All plugins — keep/remove |
| [`THEME_OVERVIEW.md`](THEME_OVERVIEW.md) | Theme structure and overlap |
| [`PHASE_2_KICKOFF.md`](PHASE_2_KICKOFF.md) | Phase 2 — GF replacement scope, field map, implementation steps |

---

## Cursor / AI guidance

When editing grip workflow code:

1. **Default to `twintack-custom-grips`** for new features (statuses, messaging, mockups, team UI)
2. **Do not add** Make.com, Monday.com, or Gravity Forms dependencies
3. **Do not restore** webhook URL filters in the theme — use WP actions in Custom Grips if external notify is ever needed again
4. When removing legacy code, grep for `monday`, `make.com`, `gform_`, `GFAPI`, `gravityform`
5. Preserve `_grip_artwork_status` as the single source of truth for production stage

# cs-osmembership-plan-styles

**System plugin for Joomla 5 / 6 that highlights OS Membership directory entries for members on selected plans by injecting a CSS class on each matching profile link.**

Use case: your OS Membership directory shows everyone in the same style, but Premium / VIP / Founders / [your-tier-name-here] members should stand out. This plugin adds a CSS class (default `cs-osm-premium-member`) to every directory link pointing at one of those members. Default styling is bold; the rest is up to your CSS.

> Free and open source. GPLv2.

---

## Why a plugin (not a template override)?

Template overrides for OS Membership live under `templates/<active-template>/html/com_osmembership/`. They survive Joomla and OS Membership updates, but they're **lost when a site rebuilds or replaces its template** — and every override has to be carried forward by hand.

A system plugin lives in `plugins/system/csosmembershipplanstyles/`, independent of template, theme, or component. It survives:

- Joomla core updates
- OS Membership component updates
- Template changes / rebuilds
- Reinstalls and migrations

The plugin runs once per page render via the `onAfterRender` event, queries the OS Membership tables for the IDs of active members on the configured plans, and injects a CSS class onto matching `<a>` links in the final HTML before it goes to the browser.

---

## Requirements

| | |
|---|---|
| Joomla | 5.0+ (also tested with 6.x) |
| PHP | 8.1+ |
| OS Membership | Any version that uses the standard `#__osmembership_plans` and `#__osmembership_subscribers` schema (4.x verified) |

---

## Installation

1. Download the latest `plg_system_csosmembershipplanstyles_v*.zip` from the [Releases page](https://github.com/cybersalt/cs-osmembership-plan-styles/releases).
2. In Joomla admin, go to **System → Install → Extensions**, drop the ZIP, install.
3. After install, click **Open Plugin Settings** on the install card.
4. Pick the OS Membership plan(s) to highlight from the **Highlighted Plans** dropdown.
5. Save.
6. Enable the plugin: **System → Plugins**, find "System - CS OS Membership Plan Styles", toggle it on.

---

## Configuration

| Setting | Default | Notes |
|---|---|---|
| **Highlighted Plans** | (empty — plugin no-op) | Multi-select of published OS Membership plans. |
| **CSS Class** | `cs-osm-premium-member` | The class name added to matching `<a>` tags. Letters, digits, hyphens, underscores only. |
| **Inject Default Stylesheet** | Yes | When on, a `<style>` block is injected before `</head>` with `font-weight: 700` for the chosen class. Turn off if your template already styles it. |
| **Custom CSS** | (empty) | Appended to the injected stylesheet. Use it to override the bold, add a colour, style the row, etc. |

### Example custom CSS

```css
/* Gold highlight on the link */
.cs-osm-premium-member {
    color: #b8860b;
    font-weight: 700;
}

/* Or, if you want the whole row treated — depends on your template's row markup */
li:has(.cs-osm-premium-member) {
    border-left: 3px solid #b8860b;
    padding-left: 0.75rem;
}
```

---

## Auto-updates via Joomla Update Manager

The plugin manifest declares an update server pointing at `update.xml` in this repository's `main` branch. Once installed, Joomla's **System → Manage → Update** screen will pick up new releases automatically.

To publish a new version: tag a release on GitHub with a ZIP asset named `plg_system_csosmembershipplanstyles_v{version}.zip`, then bump `<version>` in `update.xml` on `main`.

---

## How it works (under the hood)

1. Subscribes to `onAfterRender` (system event, fires after all rendering, before output to browser).
2. Aborts early if: backend request, no plan IDs configured, body has no `view=member` substring, no active subscribers on the configured plans.
3. Queries `#__osmembership_subscribers` for IDs where `plan_id IN (configured)` AND `plan_subscription_status = 1` AND `published = 1` AND `plan_main_record = 1`.
4. Regex-replaces every `<a href="...view=member&id=N...">` (and `&amp;id=` HTML-encoded variant) where N is a matched ID, adding the configured class to the `class` attribute (or creating one if absent).
5. Optionally injects a `<style>` block before `</head>` with the default rule plus any custom CSS.

No template files, no component files, no OS Membership files are touched.

---

## Security

- All database access uses prepared statements via Joomla's query builder + `ParameterType::INTEGER` bindings.
- CSS class name is sanitised to `[A-Za-z0-9_-]+` before being interpolated into the regex or echoed into HTML.
- Member IDs come from a typed `int` query and are cast to int again before interpolation; there's no path for user input to reach the regex.
- Custom CSS field is `filter="raw"` because CSS legitimately needs `{` / `}` / `:` etc. — administrators with plugin-edit permission already have HTML/script-injection routes elsewhere in Joomla; this isn't a new attack surface.
- No file writes, no external HTTP, no exec, no eval.

Run `security-review` skill before tagging a release.

---

## Roadmap

- [ ] **v0.2.0** — Translations for the canonical 17-language Cybersalt set.
- [ ] **v0.3.0** — Optional menu-item-scope picker (limit highlighting to specific Joomla menu items).
- [ ] **v0.4.0** — Pre-built CSS "preset" picker (Bold / Bold + Colour / Badge / Underline) as an alternative to free-form Custom CSS.
- [ ] **v1.0.0** — Marked stable when at least three Cybersalt-managed sites have it in production for 30+ days.

---

## License

GNU General Public License v2 or later — see [LICENSE](LICENSE).

## Author

[Cybersalt Consulting Ltd.](https://www.cybersalt.com) · [support@cybersalt.com](mailto:support@cybersalt.com)

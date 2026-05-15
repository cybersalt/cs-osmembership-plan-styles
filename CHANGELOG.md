# Changelog

All notable changes to **cs-osmembership-plan-styles** are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/), and this project uses [Semantic Versioning](https://semver.org/).

## 🚀 Version 0.1.0 (May 14, 2026)

### 📦 New Features
- **Initial release** — Joomla 5 / 6 system plugin that highlights OS Membership directory entries for members on selected plans.
- **Plan picker** — Multi-select of published OS Membership plans, sourced live from `#__osmembership_plans`.
- **Configurable CSS class** — Default `cs-osm-premium-member`; sanitised to a safe CSS identifier before injection.
- **Default stylesheet injection** — Inline `<style>` block emits a `font-weight: 700` rule for the chosen class; toggle off if the template handles styling.
- **Custom CSS textarea** — Free-form CSS appended to the injected stylesheet for site-specific tweaks.
- **`onAfterRender` event subscription** — Plugin runs after Joomla has built the full HTML response, so it works regardless of which OS Membership view rendered the directory page.
- **Update server** — Manifest points at `update.xml` in the GitHub repo so Joomla's Update Manager can detect new releases.
- **Post-install card** — Cybersalt-branded card with light/dark Atum theme switching and direct links to plugin settings + plugins list.
- **Database error tolerance** — If OS Membership isn't installed (table missing), the plugin fails closed instead of throwing.

### 🔍 Security
- All DB queries use prepared statements with `ParameterType::INTEGER` bindings.
- CSS class identifier sanitised to `[A-Za-z0-9_-]+` before interpolation.
- Member IDs typed and re-cast to int before being interpolated into the regex.
- Frontend-only execution (`isClient('site')` guard).
- No file writes, no external HTTP, no exec, no eval.

### 📝 Documentation
- README with installation, configuration, custom-CSS examples, auto-update workflow, and security notes.
- HTML changelog kept in sync with `CHANGELOG.md`.

---

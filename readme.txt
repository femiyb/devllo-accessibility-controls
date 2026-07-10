=== Devllo Accessibility Controls ===
Contributors: devllo, devlloplugins
Tags: accessibility, a11y, contrast, font-size, dyslexia, reading
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easy, visitor-controlled accessibility tools and admin guidance to improve readability and user experience.

== Description ==

`Devllo Accessibility Controls` provides a lightweight, privacy-first accessibility widget that lets visitors adjust how they read and interact with your site. It focuses on user-controlled presentation (text size, contrast, reading preferences) and admin guidance (checks and hints). The plugin complements good accessible design—it does not replace design, development, or manual accessibility testing.

Key visitor controls:

- Text size (increase, decrease, reset)
- Line, letter, and word spacing
- Text align left
- Dyslexia-friendly font (OpenDyslexic, bundled)
- High contrast, grayscale, and dark modes
- Brightness control
- Reading mode (narrower column, focus state)
- Reading guide and reading mask
- Big cursor and link highlighting
- Focus outline enhancement and reduced motion
- Hide images and reset preferences
- Preferences stored locally (localStorage) and restored on return visits

Admin features:

- Per-feature toggles and categorized settings (Visual, Text, Reading, Navigation)
- Widget customization (label, colors, size, position)
- Accessibility Guidance page (25-item WCAG-inspired checklist)
- Automated, informational checks (skip link, headings, image alt sampling, etc.)
- Block editor hints panel with contextual suggestions
- Frontend quick check via the admin bar
- Accessibility statement generator (creates a draft page)

What this plugin does NOT do:

- It does not perform a full accessibility audit or produce legally-binding compliance reports.
- It does not automatically fix content — it helps visitors and site owners identify and mitigate presentation issues.

== Installation ==

1. Upload the `devllo-accessibility-controls` folder to the `/wp-content/plugins/` directory, or install via the plugin installer if available.
2. Activate the plugin from the **Plugins** screen in WordPress.
3. Configure the plugin under **Settings → Accessibility Controls**:
	- Enable the widget and choose a corner position
	- Toggle features you want available to visitors
	- Customize button label, colors, and size
4. (Optional) Use the Accessibility Guidance page to run quick checks and generate an accessibility statement draft.

== Frequently Asked Questions ==

= Will this plugin make my site WCAG/ADA compliant? =

No. The plugin improves visitor experience and offers guidance, but compliance requires accessible content, semantic markup, testing, and often manual remediation.

= Does the plugin collect user data? =

No personal data is collected. Preferences are stored locally in the visitor's browser via `localStorage`.

== Screenshots ==

1. The visitor accessibility panel open with all controls visible.
2. Admin settings page with categorised feature toggles.
3. Accessibility guidance page with automated checks and WCAG checklist.
4. Block editor hints panel with colour-coded warnings.
5. Frontend quick check results panel.

== Developer Notes ==

Filters:

- `da11y_default_settings` — Modify the plugin's default settings array before initialization.
- `da11y_frontend_config` — Alter the configuration object passed to the frontend script (`da11yConfig`).

Actions:

- `da11y_before_trigger` / `da11y_after_trigger` — Hooks before/after the frontend trigger markup.
- `da11y_before_dialog` / `da11y_after_dialog` — Hooks before/after the accessibility dialog markup.

== Changelog ==

= 1.0.0 =
* Stable release — initial WordPress.org distribution (feature-complete for v1)

= 0.7.2 =
* Improved keyboard focus outlines and ARIA labeling for frontend controls
* Clarified admin setting descriptions

= 0.7.0 =
* Added many visitor-facing options: grayscale, letter/word spacing, reading guide, reading mask, big cursor, link highlighting, hide images, brightness
* Widget customization and per-feature admin toggles
* Accessibility statement generator and expanded guidance checks
* Bundled OpenDyslexic font locally

= 0.6.x and earlier =
* Iterative improvements: spacing controls, reading mode, automated guidance checks, block editor hints, and accessibility quick checks


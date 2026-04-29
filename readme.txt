=== Devllo Accessibility Controls ===
Contributors: devllo, devlloplugins
Tags: accessibility, contrast, font-size, a11y
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Visitor-controlled accessibility enhancements: text size, high contrast, and saved preferences for better readability.

== Description ==

Devllo Accessibility Controls adds a fully featured, accessible widget to your site that lets visitors adjust how they experience your content.

This plugin focuses on **user-controlled adjustments** and **admin guidance**, not on automated "fix everything" promises or legal guarantees. It is designed to complement good accessible design, not replace it.

What visitors can control:

* Text size — increase, decrease, or reset.
* Line spacing, letter spacing, and word spacing.
* Text alignment — force left alignment for easier reading.
* Dyslexia-friendly font (OpenDyslexic).
* High contrast mode.
* Grayscale mode.
* Dark mode.
* Brightness adjustment.
* Reading mode — narrows content width for focused reading.
* Reading guide — a horizontal line that follows the cursor.
* Reading mask — dims content above and below the current line.
* Big cursor — enlarges the mouse cursor.
* Link highlighting — visually emphasises all links.
* Focus outline enhancement — makes keyboard focus rings more visible.
* Reduced motion — minimises animations site-wide.
* Hide images — removes images to reduce visual clutter.
* All preferences saved to localStorage and restored on return visits.

What admins get:

* Categorised settings page — enable or disable individual features per category.
* Widget customisation — label, colours, size, and icon/text style.
* Accessibility guidance page with a 25-item WCAG 2.1 checklist.
* Automated accessibility checks — skip link, focus outline, font size, headings, image alt, form labels, lang attribute, viewport meta, link text, empty buttons, tables, PDF links, and autoplay media.
* Block editor hints panel — live post-specific accessibility hints with colour-coded warnings.
* Frontend quick check — admin bar tool that runs 15+ live checks on any page.
* Accessibility statement generator — creates a draft statement page and links it in the widget.
* Keyboard shortcut (Alt+A) to open the accessibility panel.

What this plugin does not do:

* It does **not** run a full accessibility audit.
* It does **not** guarantee ADA or WCAG compliance.
* It does **not** modify your content or automatically fix accessibility issues.

Use this plugin as one part of a broader accessibility strategy that includes proper design, development, and testing.

== Installation ==

1. Upload the plugin folder `devllo-accessibility-controls` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Go to **Settings → Accessibility Controls** to enable the widget and choose its position.
4. Visit the frontend to see the “Accessibility Options” button and open the accessibility panel.

== Frequently Asked Questions ==

= Does this plugin make my site legally compliant (ADA/WCAG)? =

No. This plugin helps visitors adjust how they view your site and supports accessibility best practices, but it does **not** guarantee legal or standards compliance. You still need proper accessible design, development, and testing.

= Does this plugin run a full accessibility audit? =

No. This version focuses on user-facing controls (text size, contrast, dyslexia-friendly reading mode, and reduced motion) and preference persistence, not on site-wide auditing.

== Features ==

**Visitor-facing widget**

* Text size controls — smaller, larger, reset (5 levels).
* Line spacing, letter spacing, and word spacing controls.
* Text align left toggle.
* Dyslexia-friendly font (OpenDyslexic, bundled locally).
* High contrast mode.
* Grayscale mode.
* Dark mode.
* Brightness control.
* Reading mode — narrows content for focused reading.
* Reading guide — horizontal line follows the cursor.
* Reading mask — dims content above and below the reading line.
* Big cursor toggle.
* Link highlighting toggle.
* Focus outline enhancement toggle.
* Reduced motion toggle — site-wide animation suppression.
* Hide images toggle.
* Reset all preferences.
* Accessibility statement link in the widget footer.
* Keyboard shortcut (Alt+A) to open/close the panel.
* All preferences persisted in localStorage.

**Admin settings**

* Enable or disable the widget entirely.
* Button position — 4 corner options.
* Per-feature toggles — categorised by Visual, Text, Reading, and Navigation.
* Widget customisation — label, background colour, text colour, size, and icon style.
* Accessibility statement generator and page selector.

**Accessibility guidance page**

* 25-item WCAG 2.1 AA checklist with status tracking (Reviewed, Needs attention, Not applicable).
* Automated checks — 13 checks run against the homepage including skip link, focus outline, font size, heading structure, image alt, form labels, lang attribute, viewport meta, generic link text, empty buttons, table headers, PDF links, and autoplay media.
* Colour-coded results — green, amber, and red indicators.

**Block editor**

* Live accessibility hints panel with colour-coded warnings.
* Checks for multiple H1s, heading level skips, missing image alt, generic link text, long paragraphs, ALL CAPS, tables without headers, video/audio content, raw URL links, new tab links, emoji-only content, and empty buttons.
* Hint count badge on the editor toolbar icon.

**Frontend quick check**

* Admin bar trigger runs 15+ live checks on the current page.
* Checks skip link, headings, images, forms, links, tables, media, tabindex, lang attribute, page title, and more.

== Developer Notes ==

Filters:

* `da11y_default_settings` – adjust the default plugin settings array before it is used.
* `da11y_frontend_config` – modify the configuration array passed to the frontend script (`da11yConfig`).

Actions:

* `da11y_before_trigger` / `da11y_after_trigger` – run before/after the frontend trigger button markup.
* `da11y_before_dialog` / `da11y_after_dialog` – run before/after the accessibility dialog markup.

== Changelog ==

= 0.7.0 =
* Added grayscale mode, letter spacing, word spacing, text align left, reading guide, reading mask, big cursor, link highlighting, focus outline enhancement, hide images, and brightness controls to the visitor widget.
* Added keyboard shortcut (Alt+A) to open/close the accessibility panel.
* Grouped widget controls into collapsible accordion sections (Text, Reading, Visual, Navigation).
* Added per-feature admin toggles categorised by Visual, Text, Reading, and Navigation.
* Added widget customisation — button label, colours, size, and icon/text style options.
* Added accessibility statement generator — creates a draft WordPress page and links it in the widget.
* Expanded accessibility checklist from 5 to 25 WCAG 2.1 AA items.
* Added 7 new automated checks — lang attribute, viewport meta, generic link text, empty buttons, table headers, PDF links, and autoplay media.
* Improved existing automated checks — focus outline now detects replacement styles, font size check now reads theme.json.
* Improved block editor hints — colour-coded warnings, heading level skip detection, image alt false positive fix, new tab links, emoji-only content, empty buttons, and missing captions.
* Bundled OpenDyslexic font locally — removed external CDN dependency.
* Fixed dialog position to follow button position setting.
* Fixed reduced motion to apply site-wide using WCAG-recommended pattern.
* Fixed dark mode using CSS invert approach for broad theme compatibility.
* Fixed high contrast mode specificity.
* Fixed spacing classes for block themes.
* Removed jQuery dependency from frontend quick check script.
* Added uninstall.php to clean up database options on plugin deletion.
* Added categorised, modern admin settings page with toggle switches.
* Added colour-coded automated check results on the guidance page.

= 0.6.3 =
* Added spacing controls to increase or reset line spacing for improved readability.
* Introduced color themes (default, high-contrast light, high-contrast dark) selectable from the accessibility panel.
* Added a reading mode toggle that narrows content width and combines readability-oriented settings for longer content.

= 0.6.2 =
* Extended the Basic automated checks on the Accessibility Guidance page to include:
* Heading structure heuristics (presence of H1 and obvious level jumps).
* Image alternative text sampling on the homepage (summary of alt usage).
* A heuristic check for form controls that appear to lack accessible labels or names.
* All checks remain partial, informational, and admin-only.

= 0.6.1 =
* Added an “Accessibility hints” sidebar panel to the block editor with soft, post-specific accessibility hints.
* Highlights issues such as multiple H1 headings, images without alt text, generic link text, long paragraphs, ALL CAPS text, tables without clear headers, and presence of video/audio content.
* Added a hint count badge to the accessibility icon in the editor toolbar so authors can quickly see when hints are present.

= 0.5.0 =
* Improved the accessibility dialog with a visible close button and `aria-expanded` state on the trigger.
* Adjusted dialog layout for better usability on small screens.
* Added filters (`da11y_default_settings`, `da11y_frontend_config`) and actions around the trigger and dialog markup for developers.

= 0.4.0 =
* Added a “Basic automated checks” section on the Accessibility Guidance page.
* Added a skip link heuristic check for the homepage.
* Added a focus outline removal check for the active theme stylesheets.
* Added a base font size heuristic check for the theme stylesheet.
* Stored results with a timestamp and clear messaging that checks are partial and informational only.

= 0.3.0 =
* Added an Accessibility Guidance admin page with a checklist of key accessibility topics.
* Let site owners track status per item (Reviewed, Needs attention, Not applicable).
* Added strong messaging to clarify that guidance is not a compliance audit or legal advice.

= 0.2.0 =
* Added dyslexia-friendly reading mode (toggle in the accessibility panel).
* Added reduced motion mode for the accessibility controls UI, respecting system prefers-reduced-motion.
* Updated settings to allow enabling/disabling dyslexia and reduced motion features.

= 0.1.0 =
* Initial prototype release with:
* Text size controls.
* High contrast mode.
* Local preference storage.

=== Devllo Accessibility Controls ===
Contributors: your-name
Tags: accessibility, contrast, font-size, a11y
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.6.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Visitor-controlled accessibility enhancements: text size, high contrast, and saved preferences for better readability.

== Description ==

Devllo Accessibility Controls adds a small, accessible widget to your site that lets visitors adjust how they experience your content.

This plugin focuses on **user-controlled adjustments** and **admin guidance**, not on automated "fix everything" promises or legal guarantees. It is designed to complement good accessible design, not replace it.

What it does in this version:

* Lets visitors increase or decrease text size, or reset back to your theme's baseline.
* Adds a high contrast mode for improved readability.
* Provides a dyslexia-friendly reading mode with an alternate font and slightly adjusted spacing.
* Offers a reduced motion mode that minimizes motion in the accessibility controls UI and respects system preferences.
* Adds an accessibility guidance page with a simple checklist to help site owners think about key accessibility topics.
* Remembers the visitor's choices using local storage so their preferences persist as they browse.

What it does not do:

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

* **Text size controls** – increase, decrease, and reset text size.
* **High contrast mode** – toggle a high contrast color scheme for better readability.
* **Dyslexia-friendly reading mode** – optional font and spacing adjustments to improve readability for some readers.
* **Reduced motion mode** – minimize motion in the accessibility controls UI and respect system reduced-motion preferences.
* **Preference persistence** – store user settings in the browser so they persist across page loads.
* **Keyboard accessible UI** – the widget and dialog are designed to be usable from the keyboard.

== Developer Notes ==

Filters:

* `da11y_default_settings` – adjust the default plugin settings array before it is used.
* `da11y_frontend_config` – modify the configuration array passed to the frontend script (`da11yConfig`).

Actions:

* `da11y_before_trigger` / `da11y_after_trigger` – run before/after the frontend trigger button markup.
* `da11y_before_dialog` / `da11y_after_dialog` – run before/after the accessibility dialog markup.

== Changelog ==

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

=== Curb Cut ===
Contributors: curb-cut-contributors
Tags: accessibility, wcag, ada, screen-reader, contrast, dyslexia, seizure-safe, ca-gov
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Level the web for all users. A real-time accessibility overlay providing WCAG 2.1 AA and CA.gov compliant features: motion safety, visual customization, contrast profiles, navigation aids, and text-to-speech.

== Description ==

**Curb Cut** adds a lightweight, zero-dependency accessibility toolbar to any WordPress site. Level the web for all users. All features are applied client-side using CSS custom properties and HTML class toggles — no server round-trips, no performance impact.

= Core Features =

**Seizure & Motion Safety**
* Seizure Safe Mode — stops all CSS animations and hides animated GIFs
* Stop All Animations — pauses transitions and animations globally
* Pause / Hide Videos — pauses all `<video>` elements on the page

**Visual Customization**
* Text Size slider — 80% to 200% in 10% steps, WCAG 1.4.4 compliant
* Line Spacing slider — 1.0 to 3.0
* Letter Spacing slider — 0 to 10 px
* Dyslexia-Friendly Font — switches to OpenDyslexic typeface
* Reading Guide — a horizontal focus bar that follows the cursor

**Contrast & Color**
* High Contrast Dark — yellow text on black background
* High Contrast Light — black text on white background
* Monochrome — full grayscale filter
* Invert Colors — inverts the palette while restoring image colors

**Navigation & Motor Aids**
* Enhanced Focus Ring — 5 px lime outline on focused elements (WCAG 2.4.7)
* Large Cursor — oversized SVG cursor for visibility
* Highlight Links — bolds and outlines every link and button
* Larger Click Targets — minimum 44×44 px touch targets (WCAG 2.5.5)

**Automatic**
* Skip to Content link (WCAG 2.4.1)
* All settings persisted to LocalStorage — remembered across page loads

= Compliance =

* WCAG 2.1 Level AA
* CA.gov Accessibility Standards
* Section 508

= Technical =

* Vanilla JavaScript (ES6) — zero dependencies
* CSS Custom Properties for real-time changes
* MutationObserver watches for dynamically added video content
* Keyboard-navigable panel with focus trapping and Escape-to-close
* Full ARIA labelling on all controls

== Installation ==

1. Upload the `curbcut-wp` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins > Installed Plugins**
3. Configure via **Settings > Accessibility**
4. Ensure your theme calls `wp_body_open()` to enable the Skip to Content link

== Frequently Asked Questions ==

= Does this store any personal data? =
No. The only data stored is the user's accessibility preferences, saved to their own browser's LocalStorage. Nothing is sent to a server.

= Can I change the button position? =
Yes — go to **Settings > Accessibility** and choose from four corner positions.

= My theme doesn't use `wp_body_open`. Will the skip link work? =
The skip link requires `wp_body_open()` in the theme's `header.php`. Most modern themes support it. If yours does not, you can manually add `<?php wp_body_open(); ?>` immediately after the opening `<body>` tag.

= What is the "Skip Target ID"? =
This is the `id` attribute of your site's main content wrapper. Common values are `content`, `main`, or `primary`. Check your theme's page template for the correct value.

== Screenshots ==

1. Floating accessibility toggle button.
2. Opened panel showing all four feature sections.
3. High Contrast Dark mode active.
4. Admin settings page.

== Changelog ==

= 1.3.4 =
* Fix: Konami code Easter egg now works reliably — keydown listener moved to capture phase so theme/plugin handlers that call stopPropagation() can no longer block it. Also fixed a missing toLowerCase() call so the B and A keys match regardless of CapsLock state.

= 1.3.3 =
* Fix: WCAG 2.1 AA and CA.gov badges in the panel footer are now centered — added justify-content:center to #oawp-panel-footer and removed the stale zero-width-space hint span that was pushing the badges left.

= 1.3.2 =
* Fix: Panel header Reset and Close buttons no longer distorted by site CSS — locked width/height/aspect-ratio/padding/border-radius/text-transform/letter-spacing with !important on all .oawp-header-btn properties.

= 1.3.1 =
* Fix: Toggle button text no longer forced to uppercase by site-wide CSS — added text-transform:none !important and letter-spacing:none !important to all panel button elements.
* Fix: Tool description sub-text was unreadable — hardened colour to #475569 with !important and bumped font-size from .63rem to .68rem for better legibility against any button background.
* Fix: Floating toggle button rendered as an oval/eye shape due to site CSS overriding width/height — locked dimensions with min/max-width/height, aspect-ratio:1/1, and inline style attribute on the button element.
* Fix: Replaced hand-built rotated-rect icon (which rendered as a peace symbol) with the exact SVG path data from the Accessible Icon Project (accessibleicon.org, public domain).
* Fix: Accessible Icon viewBox cropped too tightly, clipping the figure against the circular button boundary — expanded viewBox padding so the figure sits fully inside the inscribed circle.

= 1.3.0 =
* Feature: Reading Mode — detects article content and presents it in a distraction-free full-screen overlay with clean serif typography and an Exit button, inspired by Firefox Reader View.
* Feature: Cursor Lines — overlays full-screen horizontal and vertical crosshair lines that follow the mouse pointer for precise cursor tracking.
* Feature: Crosshair Cursor — changes the cursor shape to a crosshair (+) for precise interaction.
* Feature: Eye Safe (Warm) — shifts the page to warm amber tones (#fdf6e3 background) to reduce eye strain in low light.
* Feature: Blue Light Filter — applies a CSS sepia/desaturation filter to reduce blue light emission during evening use.
* Feature: Rainbow Text — animated spectrum color cycling on text elements, for fun and positive engagement.
* Enhancement: Pause/Hide Videos now uses a CSS class (oawp-pause-videos) that hides `<video>` elements and YouTube/Vimeo/Wistia/Loom iframe embeds, ensuring autoplay hero videos are fully stopped.
* Enhancement: Seizure Safe and Stop Animations modes now also hide video iframes via CSS, covering cases the JS pause cannot reach.
* Enhancement: Reading guide bar height widened from 2.25rem to 3.5rem for better line visibility.
* Enhancement: Every toggle button now displays a short description tag below its label, explaining the benefit to the user.
* Enhancement: Accessibility Statement shortcode [curb_cut_statement] renders a full bilingual (English/Spanish) statement. Supports site_name, contact_email, contact_url, updated, and lang attributes.
* Enhancement: CSS isolation improvements — box-sizing, font, button, SVG, and list resets scoped to #oawp-root to prevent site-wide CSS resets from breaking the panel.

= 1.2.0 =
* Feature: Quick Profiles — four one-click preset buttons at the top of the panel (Dyslexia, Low Vision, Seizure Safe, ADHD/Focus). Each preset applies a curated combination of settings. Manual adjustments remain fully available after applying a profile. Clicking the active profile a second time resets all settings.
* Feature: Read Aloud — zero-dependency Text-to-Speech using the browser's built-in Web Speech API. When active, hovering over any text element shows a dashed outline and clicking reads it aloud with a solid highlight while speaking. The feature degrades gracefully on browsers without SpeechSynthesis support.

= 1.1.2 =
* Feature: New dedicated Page Zoom slider (50%–200%) — scales the entire page universally using CSS `zoom`, affecting px, rem, em, clamp(), CSS custom properties, images, and spacing regardless of unit type. Plugin UI elements counter-zoom to stay at their correct size.
* Fix: Text Size slider now correctly targets text only (rem/em-based font sizes via `html { font-size }`), no longer affecting layout, images, or spacing. Distinguishes "resize text" (WCAG 1.4.4) from "zoom page".
* Fix: High contrast modes (Dark & Light) now correctly show which panel toggles are active — selected buttons display an inverted colour treatment (yellow background + black text in HC Dark; black background + white text in HC Light) so state is clearly visible at all contrast levels.

= 1.1.1 =
* Fix: Text size slider now uses CSS zoom instead of font-size injection, working universally across all theme unit types (px, rem, em, clamp, CSS custom properties). Counter-zoom applied to plugin UI elements so the panel remains unaffected.
* Fix: Stop All Animations now also blocks JS-driven mousemove effects (parallax, tilt, mouse-follow), matching the behaviour of Seizure Safe mode. Shared blocker correctly stays active if either feature is on and only removes when both are off.

= 1.1.0 =
* UI: Complete modern refresh — dark gradient panel header, iOS-style toggle switches, accent-filled slider track.
* UI: Replaced custom stick-figure icon with the standard universal accessibility person symbol (head + arm bar + two distinct legs), matching Apple/Google/W3C conventions.
* Feature: Windows XP Easter egg — type the Konami code (↑↑↓↓←→←→BA) to activate Bliss wallpaper, Tahoma fonts, XP window chrome, taskbar with live clock, and a balloon notification.
* Fix: Text size slider now works on all themes. Switched from inline style (overridden by theme !important rules) to a dynamically injected <style> tag with !important, covering both rem-based and em/px-based themes.
* Fix: Stop Animations and Seizure Safe modes now use animation:none and transition:none instead of near-zero durations. The previous approach still allowed elements to jump to their hover/mousemove target position instantly; none prevents the move entirely.
* Fix: Seizure Safe mode now blocks JavaScript-driven mouse-reactive effects (parallax, tilt, mouse-follow) by intercepting mousemove events at the window capture phase, which CSS alone cannot reach.
* Security: Fixed esc_attr() incorrectly wrapping selected() output in admin settings, producing malformed HTML attributes.
* Security: Replaced sanitize_html_class() with a targeted regex for the skip-link target ID (HTML IDs allow uppercase; sanitize_html_class lowercases them).
* Security: JS-side whitelist validation added for buttonPosition and accentColor from oawpConfig, preventing class/CSS injection if the config object is overridden by another script.
* Security: LocalStorage parsing now filters to DEFAULTS keys only, preventing prototype pollution via crafted __proto__ entries.
* Security: Slider values loaded from LocalStorage are clamped to their defined min/max bounds.
* Security: OpenDyslexic font is now lazy-loaded via a <link> element with an SRI integrity hash, only when the feature is activated. Removed the unconditional CDN @import from the CSS.
* i18n: aria-label strings for the toggle button are now passed via wp_localize_script for full translation support.

= 1.0.0 =
* Initial release.
* Motion & Seizure Safety: seizure safe mode, stop animations, pause videos.
* Visual Customization: text size, line/letter spacing, dyslexic font, reading guide.
* Contrast & Color: high contrast dark/light, monochrome, invert.
* Navigation & Motor: focus enhancer, large cursor, link highlighting, big targets.
* Admin settings: position, accent colour, skip-link target.
* LocalStorage persistence across page loads.
* Skip to Content link via `wp_body_open`.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

# Curb Cut — WordPress Accessibility Plugin

> **Level the web for all users.**

A real-time, zero-dependency accessibility overlay for WordPress. All features are applied client-side via CSS custom properties and class toggles — no server round-trips, no performance impact.

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://php.net)
[![WCAG 2.1 AA](https://img.shields.io/badge/WCAG-2.1%20AA-green)](https://www.w3.org/WAI/WCAG21/quickref/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/version-1.3.4-brightgreen)](https://github.com/D23M23/CurbCut-WP/releases)

---

## Features

### 🛡️ Seizure & Motion Safety
- **Seizure Safe Mode** — stops all CSS animations, hides animated GIFs and video iframes
- **Stop All Animations** — pauses transitions and animations globally, blocks JS-driven parallax/tilt effects
- **Pause / Hide Videos** — pauses all `<video>` elements and hides YouTube, Vimeo, Wistia, and Loom embeds

### 👁️ Visual Customization
- **Text Size** slider — 80% to 200% in 10% steps (WCAG 1.4.4)
- **Page Zoom** slider — 50% to 200% full-page zoom
- **Line Spacing** slider — 1.0 to 3.0
- **Letter Spacing** slider — 0 to 10 px
- **Dyslexia-Friendly Font** — switches to OpenDyslexic (lazy-loaded with SRI hash)
- **Reading Guide** — a horizontal focus bar that follows the cursor

### 🎨 Contrast & Color
- **High Contrast Dark** — yellow text on black
- **High Contrast Light** — black text on white
- **Monochrome** — full grayscale filter
- **Invert Colors** — inverts palette, restores image colors
- **Eye Safe (Warm)** — shifts to warm amber tones to reduce eye strain
- **Blue Light Filter** — CSS sepia/desaturation filter for evening use
- **Rainbow Text** — animated spectrum color cycling (fun & positive engagement)

### 🖱️ Navigation & Motor Aids
- **Enhanced Focus Ring** — 5 px lime outline on focused elements (WCAG 2.4.7)
- **Large Cursor** — oversized SVG cursor for visibility
- **Crosshair Cursor** — changes cursor to a precision crosshair (+)
- **Cursor Lines** — full-screen horizontal and vertical crosshair overlay
- **Highlight Links** — bolds and outlines every link and button
- **Larger Click Targets** — minimum 44×44 px touch targets (WCAG 2.5.5)

### 📖 Reading Aids
- **Reading Guide** — wide horizontal bar follows the cursor
- **Reading Mode** — strips page chrome, presents article content in a clean full-screen overlay
- **Read Aloud** — zero-dependency text-to-speech via the browser's built-in Web Speech API

### ⚡ Quick Profiles
One-click presets that apply curated combinations of settings:
- **Dyslexia** — OpenDyslexic font + line/letter spacing
- **Low Vision** — large text + high contrast
- **Seizure Safe** — stops all animations and videos
- **ADHD / Focus** — reading mode + reading guide

### 🔧 Automatic
- Skip to Content link (WCAG 2.4.1) via `wp_body_open`
- All settings persisted to LocalStorage — remembered across page loads
- Full ARIA labelling on all controls
- Keyboard-navigable panel with focus trapping and Escape-to-close

### 🎮 Easter Egg
Type the **Konami code** (`↑ ↑ ↓ ↓ ← → ← → B A`) to activate **Windows XP mode** — Bliss wallpaper, Tahoma fonts, XP window chrome, taskbar with live clock, and a balloon notification.

---

## Installation

1. Download or clone this repository
2. Upload the `curbcut-wp` folder to `/wp-content/plugins/`
3. Activate via **Plugins > Installed Plugins**
4. Configure via **Settings > Accessibility**
5. Ensure your theme calls `wp_body_open()` to enable the Skip to Content link

---

## Accessibility Statement Shortcode

Add a full bilingual (English/Spanish) accessibility statement to any page:

```
[curb_cut_statement]
```

Optional attributes:
| Attribute | Default | Description |
|---|---|---|
| `site_name` | Blog name | Your site's display name |
| `contact_email` | *(empty)* | Contact email for accessibility issues |
| `contact_url` | *(empty)* | URL to your contact form |
| `updated` | Current month/year | Date of last review |
| `lang` | `en` | `en` for English, `es` for Spanish |

---

## Compliance

- **WCAG 2.1 Level AA**
- **CA.gov Accessibility Standards**
- **Section 508**

---

## Technical

- Vanilla JavaScript (ES6) — **zero dependencies**
- CSS Custom Properties for real-time changes
- MutationObserver watches for dynamically added video content
- CSS isolation via `#oawp-root` scoping — immune to site-wide CSS resets
- OpenDyslexic font lazy-loaded via `<link>` with SRI integrity hash
- JS LocalStorage parsing filtered to known keys only (prototype pollution prevention)
- Slider values clamped to defined min/max on load
- `buttonPosition` and `accentColor` from `oawpConfig` whitelist-validated

---

## File Structure

```
curbcut-wp/
├── curbcut-wp.php                   ← Plugin bootstrap, overlay HTML, skip link
├── includes/
│   ├── admin.php                       ← WP admin settings page
│   └── accessibility-statement.php    ← [curb_cut_statement] shortcode
├── assets/
│   ├── css/overlay.css                 ← All overlay UI + feature CSS
│   └── js/curbcut.js               ← Full JS engine
└── readme.txt                          ← WordPress.org readme
```

---

## Changelog

See [readme.txt](readme.txt) for the full changelog.

---

## Contributing

Pull requests welcome. Please keep the zero-dependency constraint — no npm, no build step, no frameworks.

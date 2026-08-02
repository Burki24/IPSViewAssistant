# Changelog

## Unreleased

### Added

- A3 safe design transfer for existing IPSView media objects.
- Existing View inspection with semantic color extraction, page count and control count.
- Lossless styled copies that preserve pages, controls, license data and unknown IPSView fields while leaving the original untouched.
- Shared `ConfigurationFormHelper` integration and automatic helper synchronization configuration.
- Responsive design workspace with semantic color controls and live preview side by side on wide screens and stacked on narrower screens.
- A2 design assistant with IPSView Standard, Light, Dark and Custom themes.
- Twelve semantic color roles that replace direct editing of numerous technical IPSView color properties.
- Live SVG preview in the module configuration form.
- Consistent color mapping for controls, switches, sliders, dialogs, charts, calendars and status colors.
- Automated theme, palette, preview and mapping tests.
- Initial IPSView Assistant module for Symcon 9.0 and PHP 8.5.
- Guided creation of empty IPSView media objects.
- Selectable target category, aspect ratio, orientation and main page name.
- Lossless JSON document handling that preserves empty IPSView objects such as `UsedIDs` and `GroupIDs`.
- License-neutral IPSView template based on the verified creation experiment.
- Shared `tests` and `style` checks through `Symcon_ModuleCI v1.0.0`.
- Automated library metadata updates through the GitHub App.

### Fixed

- Replaced the unavailable `MEDIATYPE_DASHBOARD` runtime constant with the documented IPSView media type `0` when validating and creating design copies.
- Replaced percentage-based SelectColor widths with stable pixel widths so captions and color bars remain fully readable beside the preview.
- Reduced and centered the design preview for a more compact configuration form.
- Enlarged the semantic color controls and fixed truncated captions.
- Fixed live preview and custom View generation for integer values returned by Symcon SelectColor fields.


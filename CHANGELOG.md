# Changelog

## Unreleased

### Changed

- Replaced all generated WOFF2 preview subsets with the complete original IPSView TrueType font cuts.
- Simplified the offline font loader so every preview style uses the matching unmodified IPSView font file.
- Replaced the Segment7 preview substitute with the original G7 Segment 7 S5 font supplied by IPSView.
- Restricted bold and italic choices to the real font cuts registered by IPSView; unsupported choices are reset to normal automatically.
- Restricted font selection to the eight fonts offered by IPSView so generated Views remain portable across IPSView clients.
- Removed unsupported system-default, Segoe UI, Arial and arbitrary custom font choices from the visible Assistant form.

### Added

- Added an explicit, start-check-controlled overwrite option for one unambiguous same-name IPSView while retaining its object ID and rolling back failed content writes.
- Added local PNG/JPEG background images for the main page or all pages, including preserve, remove, tile, center and stretch modes.
- Embedded selected images directly in IPSView documents with content reuse and live SVG preview support.

- Font formatting for existing controls with preserve, normal and enabled modes for bold, italic and underline.
- Real italic and bold-italic preview cuts for Roboto, RobotoMono, OpenSans and PTSans.
- Dynamic font-format controls that follow the capabilities of the selected IPSView font family.
- Offline preview fonts for the fixed IPSView font catalogue, embedded only for the currently selected family and style in the generated SVG.
- Font license and attribution files for all bundled preview fonts.
- A5 global typography presets with preserve, compact, standard, large and custom base-size modes.
- Selectable font families aligned with the fixed IPSView font catalogue: Roboto, RobotoMono, DancingScript, IndieFlower, OpenSans, PTSans, BebasNeue and Segment7.
- Global form-language presets for square, slightly rounded, rounded, strongly rounded and custom corners.
- Global border presets with preserve, none, thin, standard, strong and custom widths.
- Safe typography application that changes only global defaults, matching default control fonts or all control fonts according to the selected design scope.
- Live preview support for font family, font size, corner radius and border width.
- Existing View inspection for current font, base size, corner radius and border width.
- Typography and form-language regression tests, including preservation of explicit fonts and relative size hierarchies.

- General shadow presets with preserve, none, subtle, medium and strong modes.
- General fill transparency with preserve, opaque and custom percentage modes.
- Generated color gradients with selectable strength and darker or lighter direction.
- Live preview support for shadows, transparency and gradients on a checkerboard background.
- Safe effect application to global fills and matching or basic control backgrounds without changing text, borders or protected association colors.
- Effect regression tests, including the full 95-page live View.

- Five additional complete design presets: Warm, Cool, Earthy, Water and Sunny.
- Theme coverage tests for all 107 global IPSView color objects and every semantic palette role.
- Preservation of IPSView alpha values, gradient types, secondary alpha values and patterns during theme changes.
- Automatic secondary gradient shades that retain the original light-to-dark direction.
- Selectable design scope for existing Views: global defaults, matching control colors or all basic control colors.
- Safe semantic matching of direct control colors against the View's current global defaults.
- Strong control-color unification for backgrounds, alternate backgrounds, text and borders while protecting unmatched association and special status colors.
- Color analysis and save reports with applied and preserved value counts.
- Automatic create-or-update workflow for styled IPSView copies.
- Persistent tracking of Assistant-managed design copies, including safe adoption of existing same-name IPSView targets.
- In-place design updates based on the current copy content so later IPSView Designer changes remain intact.
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

- Clearing the local background file now removes the persisted image data and refreshes the preview without restoring the previous image.
- Moved the bundled preview fonts from the library root into `libs/fonts` so Symcon no longer interprets `assets` as an invalid module.
- Replaced the unavailable `MEDIATYPE_DASHBOARD` runtime constant with the documented IPSView media type `0` when validating and creating design copies.
- Replaced percentage-based SelectColor widths with stable pixel widths so captions and color bars remain fully readable beside the preview.
- Reduced and centered the design preview for a more compact configuration form.
- Enlarged the semantic color controls and fixed truncated captions.
- Fixed live preview and custom View generation for integer values returned by Symcon SelectColor fields.

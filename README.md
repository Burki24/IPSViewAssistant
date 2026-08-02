# IPSViewAssistant

`IPSViewAssistant` simplifies the creation and design of IPSView projects.

The module creates a ready-initialized IPSView media object directly from a guided Symcon form. Users no longer need to create an empty media object manually and then complete the technical IPSView creation dialog.

## Included module

- **IPSView Assistant** – creates an immediately editable IPSView with a selected name, target category, aspect ratio, orientation and design.

## Requirements

- Symcon 9.0 or newer
- IPSView Designer for opening and editing the generated View

## Current functions

### Guided View creation

- View name
- target category
- main page name
- aspect ratio: 1:1, 4:3 or 16:9
- landscape or portrait orientation
- empty View template

### Simplified design selection

The user works with twelve understandable semantic color roles instead of the large number of technical IPSView color properties:

- View background
- page background
- cards and controls
- primary and secondary text
- borders and separators
- accent
- active and inactive
- success, warning and error

Available presets:

- IPSView Standard
- Light
- Dark
- Warm
- Cool
- Earthy
- Water
- Sunny
- Custom

Every preset defines all twelve semantic roles and therefore reaches all 107 global IPSView color objects. Existing alpha values, gradient types, secondary alpha values and patterns remain unchanged. Where a color object contains a second gradient color, the Assistant derives a matching second shade from the new semantic color while preserving the original light-to-dark direction.

A live SVG preview updates immediately when a preset or semantic color is changed. Colors and preview share one responsive workspace: they are displayed side by side when enough space is available and flow below one another in narrower configuration windows. The selected roles are mapped consistently to the IPSView defaults for buttons, switches, sliders, dialogs, charts, calendars and other controls.

### General visual effects

The Assistant can optionally apply one consistent effect foundation in addition to the semantic colors:

- shadows: preserve existing, none, subtle, medium or strong
- transparency: preserve existing, opaque or a freely selected percentage
- color gradients: preserve existing, none, subtle, medium or strong
- gradient direction: from the base color to a darker or lighter shade

Effects are limited to global fills and, depending on the selected design scope, matching or basic control backgrounds. Text, borders and special association colors are not made transparent or converted to gradients. The live preview shows the selected shadows, transparency and gradients immediately.

For existing Views, **Preserve existing** is the safe default. Explicit effect settings change only the separate design copy.

### Typography and form language

The Assistant also provides a small set of global design basics that remain clearly separated from the actual View construction in IPSView Designer:

- typography size: preserve existing, compact, standard, large or custom
- font family: preserve existing or one of the IPSView fonts Roboto, RobotoMono, DancingScript, IndieFlower, OpenSans, PTSans, BebasNeue and Segment7
- corners: preserve existing, square, slightly rounded, rounded, strongly rounded or custom
- borders: preserve existing, none, thin, standard, strong or custom

Typography presets scale the global IPSView font hierarchy proportionally instead of flattening all text to one size. Depending on the selected design scope, the Assistant can additionally update only control fonts that still match the previous global default, or proportionally scale all direct control fonts. Bold settings and other individual font details remain unchanged.

Corners and border widths change only the global IPSView defaults and related rounded-track switches. The Assistant does not edit element positions, sizes, page structure, navigation or individual control logic. The live preview shows the selected typography and form language together with colors and effects. For existing Views, **Preserve existing** remains the safe default.

### Safe design transfer from existing Views

An existing IPSView media object can be selected directly in the Assistant. Its current global IPSView colors are translated into the same twelve semantic roles and displayed in the live preview.

The Assistant never overwrites the selected source. Instead, it creates a separate styled copy that preserves:

- all pages and controls
- all known and unknown IPSView fields
- local IPSView license information
- dimensions, navigation and object assignments

The design scope can be selected separately:

- **Global defaults only** changes only the central IPSView design values.
- **Theme matching control colors** is the recommended mode. Direct control colors are changed only when they match one of the View's current global semantic colors. Deliberately individual colors remain untouched.
- **All basic control colors** also standardizes basic control backgrounds, alternate backgrounds, text and borders. Association colors and special status colors remain protected unless they match a global semantic color.

When a View is loaded, the Assistant reports how many global values, matching control colors and individual or special colors were detected. After saving, the exact number of applied and preserved colors is shown.

The first save creates the separate design copy. Later saves with the same copy name and target category automatically update the existing copy instead of creating another media object. The Assistant reads the current copy before applying the new colors, so pages, controls and changes made in the official IPSView Designer remain intact. Changing the copy name or target category deliberately creates another design variant.

## Development foundation

- PHP 8.5 and `IPSModuleStrict`
- shared checks from `Symcon_ModuleCI v1.0.0`
- official `symcon/StylePHP` and `symcon/SymconStubs` submodules
- shared `ConfigurationFormHelper` from `Symcon_ModuleHelper` for dynamic form generation
- required status checks: `tests` and `style`

## Roadmap

The next stages will add reusable design profiles, profile import and export, and readability or contrast checks. Page construction, control placement, navigation and object assignment remain tasks of the official IPSView Designer.

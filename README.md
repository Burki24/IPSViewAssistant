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
- Custom

A live SVG preview updates immediately when a preset or semantic color is changed. The selected roles are mapped consistently to the IPSView defaults for buttons, switches, sliders, dialogs, charts, calendars and other controls.

## Development foundation

- PHP 8.5 and `IPSModuleStrict`
- shared checks from `Symcon_ModuleCI v1.0.0`
- official `symcon/StylePHP` and `symcon/SymconStubs` submodules
- required status checks: `tests` and `style`

## Roadmap

The next stages will add safe editing of existing Views, reusable design profiles, simplified page layouts and guided creation of controls from Symcon objects.

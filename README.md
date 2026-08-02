# IPSViewAssistant

`IPSViewAssistant` simplifies the creation and later design of IPSView projects.

The first development stage creates a ready-initialized IPSView media object directly from a guided Symcon form. Users no longer need to create an empty media object manually and then complete the technical IPSView creation dialog.

## Included module

- **IPSView Assistant** – creates an empty, immediately editable IPSView with a selected name, target category, aspect ratio and orientation.

## Requirements

- Symcon 9.0 or newer
- IPSView Designer for opening and editing the generated View

## Development foundation

- PHP 8.5 and `IPSModuleStrict`
- shared checks from `Symcon_ModuleCI v1.0.0`
- official `symcon/StylePHP` and `symcon/SymconStubs` submodules
- required status checks: `tests` and `style`

## Current scope

Stage A1 intentionally supports only the empty View template. The architecture already separates the lossless IPSView document model from Symcon media creation so that themes, existing-View editing, pages and controls can be added in later stages.

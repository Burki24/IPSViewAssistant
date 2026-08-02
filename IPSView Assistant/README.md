# IPSView Assistant

## 1. Function

The module creates a fully initialized IPSView media object without the normal multi-step creation process in the Symcon object tree and the IPSView Designer.

The user selects:

- View name
- target category
- main page name
- aspect ratio: 1:1, 4:3 or 16:9
- landscape or portrait orientation
- template (currently: empty View)
- design preset or custom semantic colors

The generated media object can be opened immediately in the IPSView Designer and saved normally.

## 2. Design presets

The following presets are available:

- **IPSView Standard** – retains the original IPSView defaults
- **Light** – bright surfaces with dark text
- **Dark** – dark surfaces with light text
- **Custom** – individual semantic colors

The user does not need to know the many individual IPSView color property names. The assistant groups them into twelve roles:

- View background
- page background
- cards and controls
- primary text
- secondary text
- borders and separators
- accent
- active
- inactive
- success
- warning
- error

Changing any color automatically switches the theme selection to **Custom**. The live preview shows the result before the View is created.

## 3. Requirements

- Symcon 9.0 or newer
- IPSView Designer installed and licensed for normal editing and saving

## 4. Setup

Create one **IPSView Assistant** instance. The instance does not need a parent connection and creates no status variables.

## 5. Creating a View

1. Open the instance configuration.
2. Enter the View name and main page name.
3. Select the target category, aspect ratio and orientation.
4. Select a design preset or adjust the semantic colors.
5. Press **Create View**.
6. Open the new IPSView media object in the object tree.
7. Save it once in the IPSView Designer.

The assistant does not copy personal IPSView license data. The IPSView Designer supplies the local license information when the View is saved.

## 6. PHP command

The original A1 call remains valid and creates an IPSView with the standard design:

```php
$result = IPSVIEWA_CreateView(
    12345,
    'Haussteuerung',
    23456,
    2,
    0,
    0,
    'Hauptseite'
);
```

A theme and a custom palette can optionally be supplied:

```php
$palette = json_encode([
    'viewBackground' => '#111827',
    'pageBackground' => '#1F2937',
    'surface'        => '#273449',
    'primaryText'    => '#F9FAFB',
    'secondaryText'  => '#AEB8C7',
    'border'         => '#475569',
    'accent'         => '#3B82F6',
    'active'         => '#22C55E',
    'inactive'       => '#64748B',
    'success'        => '#22C55E',
    'warning'        => '#F59E0B',
    'error'          => '#EF4444',
], JSON_THROW_ON_ERROR);

$result = IPSVIEWA_CreateView(
    12345,
    'Haussteuerung',
    23456,
    2,
    0,
    0,
    'Hauptseite',
    3,
    $palette
);
```

Theme values:

- `0` = IPSView Standard
- `1` = Light
- `2` = Dark
- `3` = Custom

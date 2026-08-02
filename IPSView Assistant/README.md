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

The generated media object can be opened immediately in the IPSView Designer and saved normally.

## 2. Requirements

- Symcon 9.0 or newer
- IPSView Designer installed and licensed for normal editing and saving

## 3. Setup

Create one **IPSView Assistant** instance. The instance does not need a parent connection and creates no status variables.

## 4. Creating a View

1. Open the instance configuration.
2. Enter the View name and main page name.
3. Select the target category, aspect ratio and orientation.
4. Press **Create View**.
5. Open the new IPSView media object in the object tree.
6. Save it once in the IPSView Designer.

The assistant does not copy personal IPSView license data. The IPSView Designer supplies the local license information when the View is saved.

## 5. PHP command

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

Parameters after the instance ID:

1. View name
2. target category ID (`0` = Symcon root)
3. aspect ratio (`0` = 1:1, `1` = 4:3, `2` = 16:9)
4. orientation (`0` = landscape, `1` = portrait)
5. template (`0` = empty View)
6. main page name

The function returns a readable success or error message.

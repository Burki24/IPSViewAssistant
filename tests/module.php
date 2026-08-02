<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$moduleSource = file_get_contents($root . '/IPSView Assistant/module.php');
$factorySource = file_get_contents($root . '/libs/IPSViewFactory.php');
$copyFactorySource = file_get_contents($root . '/libs/IPSViewCopyFactory.php');
$form = json_decode(
    (string) file_get_contents($root . '/IPSView Assistant/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertTest(is_string($moduleSource), 'The module source could not be read.');
assertTest(is_string($factorySource), 'The IPSView factory source could not be read.');
assertTest(is_string($copyFactorySource), 'The IPSView copy factory source could not be read.');
assertTest(str_contains($moduleSource, 'extends IPSModuleStrict'), 'The module does not use IPSModuleStrict.');
assertTest(str_contains($moduleSource, 'use ConfigurationFormHelper;'), 'The module does not use ConfigurationFormHelper.');
assertTest(str_contains($moduleSource, 'public function GetConfigurationForm(): string'), 'The dynamic configuration form is missing.');
assertTest(str_contains($moduleSource, 'public function CreateView('), 'The public CreateView method is missing.');
assertTest(str_contains($moduleSource, 'public function LoadExistingView('), 'The public LoadExistingView method is missing.');
assertTest(str_contains($moduleSource, 'public function CreateStyledCopy('), 'The public CreateStyledCopy method is missing.');
assertTest(
    str_contains($moduleSource, 'public function ApplyThemePreset('),
    'The public ApplyThemePreset method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateThemePreview('),
    'The public UpdateThemePreview method is missing.'
);
assertTest(str_contains($factorySource, 'IPS_CreateMedia(0)'), 'The factory does not create an IPSView media object.');
assertTest(str_contains($factorySource, 'IPS_SetMediaFile('), 'The factory does not assign a media file.');
assertTest(str_contains($factorySource, 'IPS_SetMediaContent('), 'The factory does not write the IPSView content.');
assertTest(str_contains($factorySource, 'IPS_SendMediaEvent('), 'The factory does not announce the media update.');
assertTest(str_contains($factorySource, 'applyTheme('), 'The factory does not apply the selected theme.');
assertTest(
    str_contains($copyFactorySource, 'private const IPSVIEW_MEDIA_TYPE = 0;'),
    'The copy factory does not use a runtime-safe IPSView media type.'
);
assertTest(
    !str_contains($copyFactorySource, 'MEDIATYPE_DASHBOARD'),
    'The copy factory uses an unavailable Symcon media type constant.'
);
assertTest(
    str_contains($moduleSource, "RegisterAttributeString(self::ATTRIBUTE_MANAGED_COPIES, '[]')"),
    'The module does not persist its managed design copy registry.'
);
assertTest(
    str_contains($moduleSource, '$factory->update('),
    'The module does not update an existing design copy.'
);
assertTest(
    str_contains($moduleSource, 'findExistingTarget('),
    'The module does not adopt an existing same-name IPSView target.'
);

/**
 * @param list<array<string, mixed>> $items
 *
 * @return list<array<string, mixed>>
 */
function flattenFormItems(array $items): array
{
    $flat = [];

    foreach ($items as $item) {
        $flat[] = $item;

        if (isset($item['items']) && is_array($item['items'])) {
            $flat = [...$flat, ...flattenFormItems($item['items'])];
        }
    }

    return $flat;
}

$actions = flattenFormItems($form['actions'] ?? []);
$button = null;
$copyButton = null;
$sourceView = null;
$existingStatus = null;
$themeSelect = null;
$designScope = null;
$preview = null;
$colorFields = [];

foreach ($actions as $action) {
    if (($action['type'] ?? '') === 'Button' && ($action['caption'] ?? '') === 'Create View') {
        $button = $action;
    }

    if (($action['type'] ?? '') === 'Button' && ($action['caption'] ?? '') === 'Save styled copy') {
        $copyButton = $action;
    }

    if (($action['type'] ?? '') === 'SelectMedia' && ($action['name'] ?? '') === 'SourceViewID') {
        $sourceView = $action;
    }

    if (($action['type'] ?? '') === 'Label' && ($action['name'] ?? '') === 'ExistingViewStatus') {
        $existingStatus = $action;
    }

    if (($action['type'] ?? '') === 'Select' && ($action['name'] ?? '') === 'Theme') {
        $themeSelect = $action;
    }

    if (($action['type'] ?? '') === 'Select' && ($action['name'] ?? '') === 'DesignScope') {
        $designScope = $action;
    }

    if (($action['type'] ?? '') === 'Image' && ($action['name'] ?? '') === 'ThemePreview') {
        $preview = $action;
    }

    if (($action['type'] ?? '') === 'SelectColor') {
        $colorFields[] = $action;
    }
}

assertTest(is_array($button), 'The Create View button is missing from the form.');
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), 'IPSVIEWA_CreateView('),
    'The Create View button does not call the public module method.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$Theme'),
    'The Create View button does not pass the selected theme.'
);
assertTest(is_array($copyButton), 'The Save styled copy button is missing from the form.');
assertTest(($copyButton['name'] ?? '') === 'SaveStyledCopyButton', 'The styled copy button has no stable form name.');
assertTest(
    str_contains((string) ($copyButton['onClick'] ?? ''), 'IPSVIEWA_CreateStyledCopy('),
    'The styled copy button does not call the public module method.'
);
assertTest(is_array($sourceView), 'The existing IPSView selector is missing from the form.');
assertTest(
    str_contains((string) ($sourceView['onChange'] ?? ''), 'IPSVIEWA_LoadExistingView('),
    'Selecting an existing IPSView does not load its design.'
);
assertTest(is_array($existingStatus), 'The existing View status label is missing from the form.');
assertTest(is_array($designScope), 'The design scope selection is missing from the form.');
assertTest(
    ($designScope['value'] ?? null) === 1,
    'The recommended matching control color scope must be selected by default.'
);
assertTest(
    count($designScope['options'] ?? []) === 3,
    'The form does not offer all three design scopes.'
);
assertTest(
    str_contains((string) ($copyButton['onClick'] ?? ''), '$DesignScope'),
    'The styled copy button does not pass the selected design scope.'
);
assertTest(
    str_contains($copyFactorySource, 'applyThemeWithReport('),
    'The copy factory does not apply scoped themes with a report.'
);
assertTest(
    str_contains($copyFactorySource, 'getLastThemeReport('),
    'The copy factory does not expose its latest scope report.'
);
assertTest(
    str_contains($copyFactorySource, 'IPS_GetMediaContent('),
    'The copy factory does not read the existing media content.'
);
assertTest(is_array($themeSelect), 'The theme selection is missing from the form.');
assertTest(count($themeSelect['options'] ?? []) === 4, 'The form does not offer all four theme modes.');
assertTest(
    str_contains((string) ($themeSelect['onChange'] ?? ''), 'IPSVIEWA_ApplyThemePreset('),
    'The theme selection does not load a preset.'
);
assertTest(is_array($preview), 'The live theme preview is missing from the form.');
assertTest(($preview['image'] ?? null) === '', 'The dynamic preview placeholder must be empty in form.json.');
assertTest(($preview['width'] ?? '') === '100%', 'The live preview must fill its responsive panel.');
assertTest(($preview['center'] ?? false) === true, 'The live preview must be centered.');

$workspace = null;
$colorsPanel = null;
$previewPanel = null;
foreach ($actions as $action) {
    if (($action['name'] ?? '') === 'ThemeWorkspace') {
        $workspace = $action;
    }
    if (($action['name'] ?? '') === 'ThemeColorsPanel') {
        $colorsPanel = $action;
    }
    if (($action['name'] ?? '') === 'ThemePreviewPanel') {
        $previewPanel = $action;
    }
}
assertTest(is_array($workspace) && ($workspace['type'] ?? '') === 'RowLayout', 'The responsive theme workspace is missing.');
assertTest(($colorsPanel['width'] ?? '') === '820px', 'The color panel must have a readable responsive base width.');
assertTest(($previewPanel['width'] ?? '') === '700px', 'The preview panel must have a compact responsive base width.');
assertTest(count($colorFields) === 12, 'The form must expose exactly twelve semantic color roles.');

foreach ($colorFields as $field) {
    assertTest(
        str_contains((string) ($field['onChange'] ?? ''), 'IPSVIEWA_UpdateThemePreview('),
        'A semantic color field does not refresh the live preview.'
    );
    assertTest(($field['allowTransparent'] ?? true) === false, 'Semantic colors must not allow transparency.');
    assertTest(($field['width'] ?? '') === '360px', 'Semantic color fields must remain fully readable in the color panel.');
    assertTest(is_int($field['value'] ?? null), 'SelectColor values must use the Symcon integer format.');
}

echo "IPSView Assistant module tests passed.\n";

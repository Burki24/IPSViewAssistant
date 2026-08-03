<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$moduleSource = file_get_contents($root . '/IPSView Assistant/module.php');
$factorySource = file_get_contents($root . '/libs/IPSViewFactory.php');
$copyFactorySource = file_get_contents($root . '/libs/IPSViewCopyFactory.php');
$effectsSource = file_get_contents($root . '/libs/IPSViewEffects.php');
$typographySource = file_get_contents($root . '/libs/IPSViewTypography.php');
$shapeSource = file_get_contents($root . '/libs/IPSViewShape.php');
$form = json_decode(
    (string) file_get_contents($root . '/IPSView Assistant/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertTest(is_string($moduleSource), 'The module source could not be read.');
assertTest(is_string($factorySource), 'The IPSView factory source could not be read.');
assertTest(is_string($copyFactorySource), 'The IPSView copy factory source could not be read.');
assertTest(is_string($effectsSource), 'The IPSView effects source could not be read.');
assertTest(is_string($typographySource), 'The IPSView typography source could not be read.');
assertTest(is_string($shapeSource), 'The IPSView shape source could not be read.');
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
assertTest(
    str_contains($moduleSource, 'public function UpdateEffectsPreview('),
    'The public UpdateEffectsPreview method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateAppearancePreview('),
    'The public UpdateAppearancePreview method is missing.'
);
assertTest(
    str_contains($moduleSource, 'updateFontStyleFields('),
    'The module does not adapt font-format controls to the selected font family.'
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
$effectsPanel = null;
$shadowStyle = null;
$transparencyMode = null;
$transparencyPercent = null;
$gradientStyle = null;
$gradientDirection = null;
$appearancePanel = null;
$typographyStyle = null;
$fontFamilyMode = null;
$fontBoldMode = null;
$fontItalicMode = null;
$fontUnderlineMode = null;
$customFontSize = null;
$customFontFamily = null;
$cornerStyle = null;
$customCornerRadius = null;
$borderStyle = null;
$customBorderWidth = null;
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

    if (($action['name'] ?? '') === 'ThemeEffectsPanel') {
        $effectsPanel = $action;
    }

    if (($action['name'] ?? '') === 'ShadowStyle') {
        $shadowStyle = $action;
    }

    if (($action['name'] ?? '') === 'TransparencyMode') {
        $transparencyMode = $action;
    }

    if (($action['name'] ?? '') === 'TransparencyPercent') {
        $transparencyPercent = $action;
    }

    if (($action['name'] ?? '') === 'GradientStyle') {
        $gradientStyle = $action;
    }

    if (($action['name'] ?? '') === 'GradientDirection') {
        $gradientDirection = $action;
    }

    if (($action['name'] ?? '') === 'TypographyShapePanel') {
        $appearancePanel = $action;
    }

    if (($action['name'] ?? '') === 'TypographyStyle') {
        $typographyStyle = $action;
    }

    if (($action['name'] ?? '') === 'FontFamilyMode') {
        $fontFamilyMode = $action;
    }

    if (($action['name'] ?? '') === 'FontBoldMode') {
        $fontBoldMode = $action;
    }

    if (($action['name'] ?? '') === 'FontItalicMode') {
        $fontItalicMode = $action;
    }

    if (($action['name'] ?? '') === 'FontUnderlineMode') {
        $fontUnderlineMode = $action;
    }

    if (($action['name'] ?? '') === 'CustomFontSize') {
        $customFontSize = $action;
    }

    if (($action['name'] ?? '') === 'CustomFontFamily') {
        $customFontFamily = $action;
    }

    if (($action['name'] ?? '') === 'CornerStyle') {
        $cornerStyle = $action;
    }

    if (($action['name'] ?? '') === 'CustomCornerRadius') {
        $customCornerRadius = $action;
    }

    if (($action['name'] ?? '') === 'BorderStyle') {
        $borderStyle = $action;
    }

    if (($action['name'] ?? '') === 'CustomBorderWidth') {
        $customBorderWidth = $action;
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
assertTest(
    str_contains((string) ($sourceView['onChange'] ?? ''), '$ShadowStyle'),
    'Loading an existing IPSView does not retain the selected general effects in the preview.'
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
    str_contains((string) ($copyButton['onClick'] ?? ''), '$GradientStyle'),
    'The styled copy button does not pass the selected general effects.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$TransparencyMode'),
    'The Create View button does not pass the selected general effects.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$TypographyStyle'),
    'The Create View button does not pass the selected typography settings.'
);
assertTest(
    str_contains((string) ($copyButton['onClick'] ?? ''), '$CustomBorderWidth'),
    'The styled copy button does not pass the selected form-language settings.'
);
assertTest(
    str_contains((string) ($copyButton['onClick'] ?? ''), '$FontBoldMode'),
    'The styled copy button does not pass the selected font weight.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$FontItalicMode'),
    'The Create View button does not pass the selected font style.'
);
assertTest(
    str_contains((string) ($sourceView['onChange'] ?? ''), '$FontUnderlineMode'),
    'Loading an existing IPSView does not retain the underline setting in the preview.'
);
assertTest(
    str_contains((string) ($sourceView['onChange'] ?? ''), '$FontFamilyMode'),
    'Loading an existing IPSView does not retain the appearance settings in the preview.'
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
assertTest(count($themeSelect['options'] ?? []) === 9, 'The form does not offer all nine theme modes.');
assertTest(
    str_contains((string) ($themeSelect['onChange'] ?? ''), 'IPSVIEWA_ApplyThemePreset('),
    'The theme selection does not load a preset.'
);

$themeCaptions = array_column($themeSelect['options'] ?? [], 'caption');
foreach (['Warm', 'Cool', 'Earthy', 'Water', 'Sunny'] as $caption) {
    assertTest(
        in_array($caption, $themeCaptions, true),
        sprintf('The additional theme "%s" is missing from the form.', $caption)
    );
}
assertTest(is_array($effectsPanel), 'The general effects panel is missing from the form.');
assertTest(is_array($shadowStyle), 'The shadow style selection is missing from the form.');
assertTest(count($shadowStyle['options'] ?? []) === 5, 'The shadow selection does not offer all modes.');
assertTest(is_array($transparencyMode), 'The transparency mode selection is missing from the form.');
assertTest(count($transparencyMode['options'] ?? []) === 3, 'The transparency selection does not offer all modes.');
assertTest(is_array($transparencyPercent), 'The transparency amount field is missing from the form.');
assertTest(($transparencyPercent['minimum'] ?? null) === 0, 'The transparency minimum is incorrect.');
assertTest(($transparencyPercent['maximum'] ?? null) === 100, 'The transparency maximum is incorrect.');
assertTest(is_array($gradientStyle), 'The gradient style selection is missing from the form.');
assertTest(count($gradientStyle['options'] ?? []) === 5, 'The gradient selection does not offer all modes.');
assertTest(is_array($gradientDirection), 'The gradient direction selection is missing from the form.');
assertTest(
    str_contains((string) ($gradientStyle['onChange'] ?? ''), 'IPSVIEWA_UpdateEffectsPreview('),
    'Changing the gradient does not refresh the effect preview.'
);
assertTest(
    str_contains($effectsSource, 'public static function apply('),
    'The general IPSView effects cannot be applied.'
);
assertTest(is_array($appearancePanel), 'The typography and form-language panel is missing.');
assertTest(is_array($typographyStyle), 'The typography size selection is missing.');
assertTest(count($typographyStyle['options'] ?? []) === 5, 'The typography size selection does not offer all modes.');
assertTest(is_array($fontFamilyMode), 'The font family selection is missing.');
assertTest(count($fontFamilyMode['options'] ?? []) === 9, 'The font family selection does not offer all IPSView fonts.');
assertTest(is_array($fontBoldMode), 'The font weight selection is missing.');
assertTest(count($fontBoldMode['options'] ?? []) === 3, 'The font weight selection does not offer all modes.');
assertTest(is_array($fontItalicMode), 'The font style selection is missing.');
assertTest(count($fontItalicMode['options'] ?? []) === 3, 'The font style selection does not offer all modes.');
assertTest(is_array($fontUnderlineMode), 'The underline selection is missing.');
assertTest(count($fontUnderlineMode['options'] ?? []) === 3, 'The underline selection does not offer all modes.');
assertTest(
    str_contains((string) ($fontBoldMode['onChange'] ?? ''), 'IPSVIEWA_UpdateAppearancePreview('),
    'Changing the font weight does not refresh the appearance preview.'
);
assertTest(is_array($customFontSize), 'The custom base font size is missing.');
assertTest(($customFontSize['minimum'] ?? null) === 8, 'The custom font size minimum is incorrect.');
assertTest(($customFontSize['maximum'] ?? null) === 32, 'The custom font size maximum is incorrect.');
assertTest(is_array($customFontFamily), 'The detected font family field is missing.');
assertTest(($customFontFamily['visible'] ?? null) === false, 'The internal detected font family field must remain hidden.');
assertTest(is_array($cornerStyle), 'The corner style selection is missing.');
assertTest(count($cornerStyle['options'] ?? []) === 6, 'The corner selection does not offer all modes.');
assertTest(is_array($customCornerRadius), 'The custom corner radius is missing.');
assertTest(is_array($borderStyle), 'The border style selection is missing.');
assertTest(count($borderStyle['options'] ?? []) === 6, 'The border selection does not offer all modes.');
assertTest(is_array($customBorderWidth), 'The custom border width is missing.');
assertTest(
    str_contains((string) ($cornerStyle['onChange'] ?? ''), 'IPSVIEWA_UpdateAppearancePreview('),
    'Changing the corner style does not refresh the appearance preview.'
);
assertTest(
    str_contains($typographySource, 'public static function apply('),
    'The IPSView typography cannot be applied.'
);
assertTest(
    str_contains($shapeSource, 'public static function apply('),
    'The IPSView form language cannot be applied.'
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

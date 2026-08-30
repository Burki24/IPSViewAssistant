<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$moduleSource = file_get_contents($root . '/IPSView Assistant/module.php');
$factorySource = file_get_contents($root . '/libs/IPSViewFactory.php');
$startCheckSource = file_get_contents($root . '/libs/IPSViewStartCheck.php');
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
assertTest(is_string($startCheckSource), 'The IPSView start-check source could not be read.');
assertTest(is_string($copyFactorySource), 'The IPSView copy factory source could not be read.');
assertTest(is_string($effectsSource), 'The IPSView effects source could not be read.');
assertTest(is_string($typographySource), 'The IPSView typography source could not be read.');
assertTest(is_string($shapeSource), 'The IPSView shape source could not be read.');
assertTest(str_contains($moduleSource, 'extends IPSModuleStrict'), 'The module does not use IPSModuleStrict.');
assertTest(str_contains($moduleSource, 'use ConfigurationFormHelper;'), 'The module does not use ConfigurationFormHelper.');
assertTest(str_contains($moduleSource, 'public function GetConfigurationForm(): string'), 'The dynamic configuration form is missing.');
assertTest(str_contains($moduleSource, 'public function CreateView('), 'The public CreateView method is missing.');
assertTest(
    str_contains($moduleSource, 'public function CreateOrOverwriteView('),
    'The explicit View overwrite method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateStartCheck('),
    'The public start-check method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateStartCheckWithOverwrite('),
    'The overwrite-aware start-check method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateAssistantMode('),
    'The public assistant mode method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateUsageProfile('),
    'The public usage profile method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function MarkUsageProfileCustom('),
    'The public custom usage profile method is missing.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateQuickStartUsageProfile(')
        && str_contains($moduleSource, 'public function UpdateQuickStartPreview(')
        && str_contains($moduleSource, 'public function UpdateQuickStartBackground(')
        && str_contains($moduleSource, 'public function UpdateQuickStartCheck(')
        && str_contains($moduleSource, 'public function ValidateQuickStartCreation(')
        && str_contains($moduleSource, 'public function CreateQuickStartView('),
    'The public quick-start wizard methods are incomplete.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateDesignerHandover('),
    'The public Designer handover method is missing.'
);
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
    str_contains($moduleSource, 'public function UpdateStartGridPreview('),
    'The public start-grid preview method is missing.'
);
assertTest(
    str_contains($moduleSource, 'updateFontStyleFields('),
    'The module does not adapt font-format controls to the selected font family.'
);
assertTest(str_contains($factorySource, 'IPS_CreateMedia(0)'), 'The factory does not create an IPSView media object.');
assertTest(str_contains($factorySource, 'IPS_SetMediaFile('), 'The factory does not assign a media file.');
assertTest(str_contains($factorySource, 'IPS_SetMediaContent('), 'The factory does not write the IPSView content.');
assertTest(str_contains($factorySource, 'IPS_SendMediaEvent('), 'The factory does not announce the media update.');
assertTest(str_contains($factorySource, 'private function overwrite('), 'The factory cannot overwrite an existing View.');
assertTest(
    str_contains($factorySource, '$previousContent = IPS_GetMediaContent($mediaID);'),
    'The factory does not preserve the previous content for overwrite rollback.'
);
assertTest(str_contains($factorySource, 'applyTheme('), 'The factory does not apply the selected theme.');
assertTest(
    str_contains($factorySource, 'IPSViewStartCheck::assertReady('),
    'The View factory does not enforce the shared start check.'
);
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

        if (isset($item['popup']['items']) && is_array($item['popup']['items'])) {
            $flat = [...$flat, ...flattenFormItems($item['popup']['items'])];
        }

        if (isset($item['popup']['pages']) && is_array($item['popup']['pages'])) {
            $flat = [...$flat, ...flattenFormItems($item['popup']['pages'])];
        }
    }

    return $flat;
}

$actions = flattenFormItems($form['actions'] ?? []);
$actionsByName = [];
foreach ($actions as $action) {
    if (isset($action['name']) && is_string($action['name'])) {
        $actionsByName[$action['name']] = $action;
    }
}
$button = null;
$viewName = null;
$targetCategory = null;
$mainPageName = null;
$startCheckPanel = null;
$startCheckStatus = null;
$runStartCheckButton = null;
$overwriteExistingView = null;
$overwriteExistingViewInfo = null;
$copyButton = null;
$assistantMode = null;
$assistantModeInfo = null;
$usageProfile = null;
$usageProfileInfo = null;
$aspectRatio = null;
$orientation = null;
$fullScreen = null;
$startGrid = null;
$startGridInfo = null;
$designerHandoverPanel = null;
$designerHandoverTitle = null;
$designerObject = null;
$designerObjectHint = null;
$designerHandoverInitialInfo = null;
$existingViewPanel = null;
$templateSelect = null;
$styledCopyInfo = null;
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
$backgroundPanel = null;
$backgroundMode = null;
$backgroundFile = null;
$backgroundLayout = null;
$backgroundScope = null;
$colorFields = [];

foreach ($actions as $action) {
    if (($action['name'] ?? '') === 'ViewName') {
        $viewName = $action;
    }

    if (($action['name'] ?? '') === 'TargetCategoryID') {
        $targetCategory = $action;
    }

    if (($action['name'] ?? '') === 'MainPageName') {
        $mainPageName = $action;
    }

    if (($action['name'] ?? '') === 'StartCheckPanel') {
        $startCheckPanel = $action;
    }

    if (($action['name'] ?? '') === 'StartCheckStatus') {
        $startCheckStatus = $action;
    }

    if (($action['name'] ?? '') === 'RunStartCheckButton') {
        $runStartCheckButton = $action;
    }

    if (($action['name'] ?? '') === 'OverwriteExistingView') {
        $overwriteExistingView = $action;
    }

    if (($action['name'] ?? '') === 'OverwriteExistingViewInfo') {
        $overwriteExistingViewInfo = $action;
    }

    if (($action['name'] ?? '') === 'AssistantMode') {
        $assistantMode = $action;
    }

    if (($action['name'] ?? '') === 'AssistantModeInfo') {
        $assistantModeInfo = $action;
    }

    if (($action['name'] ?? '') === 'UsageProfile') {
        $usageProfile = $action;
    }

    if (($action['name'] ?? '') === 'UsageProfileInfo') {
        $usageProfileInfo = $action;
    }

    if (($action['name'] ?? '') === 'AspectRatio') {
        $aspectRatio = $action;
    }

    if (($action['name'] ?? '') === 'Orientation') {
        $orientation = $action;
    }

    if (($action['name'] ?? '') === 'FullScreen') {
        $fullScreen = $action;
    }

    if (($action['name'] ?? '') === 'StartGrid') {
        $startGrid = $action;
    }

    if (($action['name'] ?? '') === 'StartGridInfo') {
        $startGridInfo = $action;
    }

    if (($action['name'] ?? '') === 'DesignerHandoverPanel') {
        $designerHandoverPanel = $action;
    }

    if (($action['name'] ?? '') === 'DesignerHandoverTitle') {
        $designerHandoverTitle = $action;
    }

    if (($action['name'] ?? '') === 'DesignerObjectID') {
        $designerObject = $action;
    }

    if (($action['name'] ?? '') === 'DesignerObjectHint') {
        $designerObjectHint = $action;
    }

    if (($action['name'] ?? '') === 'DesignerHandoverInitialInfo') {
        $designerHandoverInitialInfo = $action;
    }

    if (($action['name'] ?? '') === 'ExistingViewPopup') {
        $existingViewPanel = $action;
    }

    if (($action['name'] ?? '') === 'Template') {
        $templateSelect = $action;
    }

    if (($action['name'] ?? '') === 'StyledCopyInfo') {
        $styledCopyInfo = $action;
    }

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

    if (($action['name'] ?? '') === 'BackgroundImagePopup') {
        $backgroundPanel = $action;
    }

    if (($action['name'] ?? '') === 'BackgroundImageMode') {
        $backgroundMode = $action;
    }

    if (($action['name'] ?? '') === 'BackgroundImageFile') {
        $backgroundFile = $action;
    }

    if (($action['name'] ?? '') === 'BackgroundImageLayout') {
        $backgroundLayout = $action;
    }

    if (($action['name'] ?? '') === 'BackgroundImageScope') {
        $backgroundScope = $action;
    }

    if (($action['type'] ?? '') === 'SelectColor') {
        $colorFields[] = $action;
    }
}

assertTest(is_array($button), 'The Create View button is missing from the form.');
assertTest(is_array($startCheckPanel), 'The start-check panel is missing from the form.');
assertTest(($startCheckPanel['expanded'] ?? false) === true, 'The start check must be visible immediately.');
assertTest(is_array($startCheckStatus), 'The start-check status report is missing from the form.');
assertTest(is_array($runStartCheckButton), 'The manual start-check button is missing from the form.');
assertTest(
    str_contains((string) ($runStartCheckButton['onClick'] ?? ''), 'IPSVIEWA_UpdateStartCheckWithOverwrite('),
    'The manual start-check button does not call the public module method.'
);
foreach ([$viewName, $targetCategory, $mainPageName, $aspectRatio, $orientation, $startGrid] as $startCheckField) {
    assertTest(is_array($startCheckField), 'A start-check input field is missing from the form.');
    assertTest(
        str_contains((string) ($startCheckField['onChange'] ?? ''), 'IPSVIEWA_UpdateStartCheckWithOverwrite('),
        'A relevant View setting does not refresh the start check.'
    );
}
assertTest(is_array($overwriteExistingView), 'The explicit overwrite confirmation is missing.');
assertTest(($overwriteExistingView['visible'] ?? true) === false, 'Overwrite must remain hidden without a conflict.');
assertTest(($overwriteExistingView['value'] ?? true) === false, 'Overwrite must never be enabled by default.');
assertTest(
    str_contains((string) ($overwriteExistingView['onChange'] ?? ''), '$OverwriteExistingView'),
    'The overwrite confirmation does not refresh the start check.'
);
assertTest(is_array($overwriteExistingViewInfo), 'The destructive overwrite explanation is missing.');
assertTest(($overwriteExistingViewInfo['visible'] ?? true) === false, 'The overwrite explanation is visible without a conflict.');
assertTest(
    str_ends_with((string) ($viewName['onChange'] ?? ''), ', false);')
        && str_ends_with((string) ($targetCategory['onChange'] ?? ''), ', false);'),
    'Changing the View name or target category does not reset overwrite confirmation.'
);
assertTest(
    str_contains($moduleSource, "UpdateFormField('CreateViewButton', 'enabled'")
        && str_contains($moduleSource, 'IPSViewStartCheck::assertReady($startCheck);'),
    'The start check does not control and protect View creation.'
);
assertTest(is_array($assistantMode), 'The assistant mode selection is missing from the form.');
assertTest(
    ($assistantMode['type'] ?? '') === 'RadioButtonGroup',
    'The two assistant modes are not presented as a radio-button choice.'
);
assertTest(($assistantMode['value'] ?? null) === 0, 'Quick start must be the default assistant mode.');
assertTest(count($assistantMode['options'] ?? []) === 2, 'Both assistant modes must be available.');
assertTest(
    str_contains((string) ($assistantMode['onChange'] ?? ''), 'IPSVIEWA_UpdateAssistantMode('),
    'Changing the assistant mode does not call the public module method.'
);
assertTest(is_array($assistantModeInfo), 'The assistant mode explanation is missing from the form.');
$quickStartPopup = $actionsByName['QuickStartWizardPopup'] ?? null;
$quickStartUsageProfile = $actionsByName['QuickStartUsageProfile'] ?? null;
$quickStartAspectRatio = $actionsByName['QuickStartAspectRatio'] ?? null;
$quickStartGrid = $actionsByName['QuickStartGrid'] ?? null;
$quickStartTheme = $actionsByName['QuickStartTheme'] ?? null;
$quickStartBackgroundFile = $actionsByName['QuickStartBackgroundFile'] ?? null;
$quickStartOverwrite = $actionsByName['QuickStartOverwriteExistingView'] ?? null;
$quickStartPreview = $actionsByName['QuickStartPreview'] ?? null;
assertTest(is_array($quickStartPopup), 'The quick-start wizard button is missing.');
assertTest(($quickStartPopup['type'] ?? '') === 'PopupButton', 'Quick start is not opened as a popup wizard.');
assertTest(($quickStartPopup['caption'] ?? '') === 'Open quick start', 'The quick-start button caption is incorrect.');
assertTest(($quickStartPopup['popup']['closeCaption'] ?? '') === 'Close', 'The quick-start wizard has no close action.');
$quickStartPages = $quickStartPopup['popup']['pages'] ?? [];
assertTest(($quickStartPopup['popup']['items'] ?? null) === [], 'The native wizard still has legacy popup items.');
assertTest(count($quickStartPages) === 4, 'The native quick-start wizard must have four pages.');
for ($step = 1; $step <= 4; ++$step) {
    $stepPage = $quickStartPages[$step - 1] ?? null;
    assertTest(is_array($stepPage), sprintf('Quick-start page %d is missing.', $step));
    assertTest(
        ($stepPage['name'] ?? '') === 'QuickStartStep' . $step,
        sprintf('Quick-start page %d has no stable page name.', $step)
    );
    assertTest(
        !isset($stepPage['type']) && is_array($stepPage['items'] ?? null),
        sprintf('Quick-start page %d is not a native PopupButton page.', $step)
    );
}
assertTest(
    str_contains((string) ($quickStartPages[2]['onConfirm'] ?? ''), 'IPSVIEWA_UpdateQuickStartCheck(')
        && str_contains((string) ($quickStartPages[2]['onUndo'] ?? ''), 'IPSVIEWA_ResetQuickStartOverwrite('),
    'The wizard does not run the start check before its final step.'
);
assertTest(
    str_contains((string) ($quickStartPages[3]['validate'] ?? ''), 'IPSVIEWA_ValidateQuickStartCreation('),
    'The native wizard does not validate its final page.'
);
assertTest(
    str_contains((string) ($quickStartPages[3]['onConfirm'] ?? ''), 'IPSVIEWA_CreateQuickStartView(')
        && str_starts_with((string) ($quickStartPages[3]['onConfirm'] ?? ''), 'echo '),
    'The native wizard does not create the View through its final confirmation action.'
);
assertTest(
    str_contains($moduleSource, "isset(\$item['popup']['pages'])"),
    'Dynamic form updates cannot reach fields inside native PopupButton pages.'
);
assertTest(is_array($quickStartUsageProfile), 'The wizard usage profile is missing.');
assertTest(count($quickStartUsageProfile['options'] ?? []) === 5, 'The wizard does not offer all usage profiles.');
assertTest(
    str_contains((string) ($quickStartUsageProfile['onChange'] ?? ''), 'IPSVIEWA_UpdateQuickStartUsageProfile('),
    'The wizard usage profile does not apply its format settings.'
);
assertTest(is_array($quickStartAspectRatio), 'The wizard aspect ratio is missing.');
assertTest(count($quickStartAspectRatio['options'] ?? []) === 7, 'The wizard does not offer all aspect ratios.');
assertTest(is_array($quickStartGrid), 'The wizard start grid is missing.');
assertTest(array_column($quickStartGrid['options'] ?? [], 'value') === [0, 2, 3], 'The wizard start grid is incomplete.');
assertTest(is_array($quickStartTheme), 'The wizard theme selection is missing.');
assertTest(count($quickStartTheme['options'] ?? []) === 8, 'The wizard must offer the eight ready-made themes.');
assertTest(
    !in_array(3, array_column($quickStartTheme['options'] ?? [], 'value'), true),
    'The custom theme must remain an advanced-mode function.'
);
assertTest(is_array($quickStartBackgroundFile), 'The wizard background file selection is missing.');
assertTest(
    ($quickStartBackgroundFile['extensions'] ?? '') === '.png,.jpg,.jpeg',
    'The wizard background file filter is incorrect.'
);
assertTest(
    str_ends_with((string) ($quickStartBackgroundFile['onChange'] ?? ''), ', true);'),
    'The wizard does not distinguish an explicit background-file removal.'
);
assertTest(is_array($quickStartOverwrite), 'The wizard overwrite confirmation is missing.');
assertTest(($quickStartOverwrite['visible'] ?? true) === false, 'Wizard overwrite must remain hidden without a conflict.');
assertTest(is_array($quickStartPreview), 'The wizard design preview is missing.');
assertTest(($quickStartPreview['width'] ?? '') === '700px', 'The wizard preview has an unexpected width.');
$radioButtonFields = [
    'AssistantMode',
    'QuickStartOrientation',
    'QuickStartGrid',
    'QuickStartBackgroundScope',
    'QuickStartBackgroundLayout',
    'Orientation',
    'StartGrid',
    'GradientDirection',
    'BackgroundImageScope',
    'BackgroundImageLayout',
];
foreach ($radioButtonFields as $radioButtonField) {
    assertTest(
        ($actionsByName[$radioButtonField]['type'] ?? '') === 'RadioButtonGroup',
        sprintf('The compact choice "%s" is not rendered as a RadioButtonGroup.', $radioButtonField)
    );
}
assertTest(
    str_contains($moduleSource, "'ViewSettingsPanel'")
        && str_contains($moduleSource, "'DesignPanel'")
        && str_contains($moduleSource, "'StartCheckPanel'")
        && str_contains($moduleSource, "'QuickStartWizardPopup'"),
    'The assistant modes do not separate the wizard from the direct expert workflow.'
);
assertTest(is_array($usageProfile), 'The usage profile selection is missing from the form.');
assertTest(($usageProfile['value'] ?? null) === 0, 'The wall tablet must be the default usage profile.');
assertTest(count($usageProfile['options'] ?? []) === 5, 'All usage profiles must be available.');
assertTest(
    str_contains((string) ($usageProfile['onChange'] ?? ''), 'IPSVIEWA_UpdateUsageProfile('),
    'Changing the usage profile does not apply its View settings.'
);
assertTest(is_array($usageProfileInfo), 'The usage profile explanation is missing from the form.');
assertTest(is_array($aspectRatio), 'The aspect ratio selection is missing from the form.');
assertTest(count($aspectRatio['options'] ?? []) === 7, 'Not all supported aspect ratios are available.');
$aspectRatioValues = array_column($aspectRatio['options'] ?? [], 'value');
sort($aspectRatioValues);
assertTest($aspectRatioValues === [0, 1, 2, 3, 4, 5, 6], 'The aspect ratio value mapping is incomplete.');
assertTest(is_array($orientation), 'The orientation selection is missing from the form.');
assertTest(is_array($fullScreen), 'The full-screen selection is missing from the form.');
assertTest(($fullScreen['value'] ?? null) === true, 'The wall tablet profile must start in full-screen mode.');
foreach ([$aspectRatio, $orientation, $fullScreen] as $profileField) {
    assertTest(
        str_contains((string) ($profileField['onChange'] ?? ''), 'IPSVIEWA_MarkUsageProfileCustom('),
        'A manually changed View setting does not mark the usage profile as custom.'
    );
}
assertTest(is_array($designerHandoverPanel), 'The guided Designer handover panel is missing.');
assertTest(($designerHandoverPanel['visible'] ?? true) === false, 'The Designer handover must stay hidden before creation.');
assertTest(($designerHandoverPanel['expanded'] ?? false) === true, 'The Designer handover must open expanded.');
assertTest(is_array($designerHandoverTitle), 'The created View information is missing from the Designer handover.');
assertTest(is_array($designerObject), 'The optional first Symcon object selection is missing.');
assertTest(($designerObject['value'] ?? null) === 1, 'An optional Symcon object selection must start with nothing selected.');
assertTest(
    str_contains((string) ($designerObject['onChange'] ?? ''), 'IPSVIEWA_UpdateDesignerHandover('),
    'Selecting the first Symcon object does not refresh the Designer recommendation.'
);
assertTest(is_array($designerObjectHint), 'The first control recommendation is missing.');
assertTest(is_array($designerHandoverInitialInfo), 'The initial Designer handover explanation is missing.');
assertTest(
    str_contains($moduleSource, 'RegisterAttributeInteger(self::ATTRIBUTE_LAST_CREATED_VIEW_ID')
        && str_contains($moduleSource, 'RegisterAttributeInteger(self::ATTRIBUTE_DESIGNER_OBJECT_ID'),
    'The most recent Designer handover is not persisted.'
);
assertTest(
    str_contains($moduleSource, '$this->showDesignerHandover($mediaID);')
        && str_contains($moduleSource, 'applyDesignerHandoverToForm('),
    'A created View does not activate or restore the guided Designer handover.'
);
assertTest(is_array($existingViewPanel), 'The existing View panel has no stable form name.');
assertTest(($existingViewPanel['visible'] ?? true) === false, 'Existing Views must be hidden in quick start.');
assertTest(($existingViewPanel['type'] ?? '') === 'PopupButton', 'Existing View settings are not opened as a popup.');
assertTest(($existingViewPanel['popup']['closeCaption'] ?? '') === 'Close', 'The existing View popup has no close action.');
assertTest(is_array($templateSelect), 'The internal template selection is missing from the form.');
assertTest(($templateSelect['visible'] ?? true) === false, 'The internal template selection must be hidden in quick start.');
assertTest(is_array($styledCopyInfo), 'The styled copy explanation has no stable form name.');
assertTest(($styledCopyInfo['visible'] ?? true) === false, 'The styled copy explanation must be hidden in quick start.');
assertTest(
    str_contains($moduleSource, 'RegisterAttributeInteger(self::ATTRIBUTE_ASSISTANT_MODE'),
    'The selected assistant mode is not persisted.'
);
assertTest(
    str_contains($moduleSource, "'ExistingViewPopup'")
        && str_contains($moduleSource, "'ThemeDetailsPopup'")
        && str_contains($moduleSource, "'SaveStyledCopyButton'"),
    'The advanced mode does not control all advanced form areas.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), 'IPSVIEWA_CreateOrOverwriteView('),
    'The Create View button does not call the overwrite-aware public module method.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$OverwriteExistingView'),
    'The Create View button does not pass the explicit overwrite confirmation.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$Theme'),
    'The Create View button does not pass the selected theme.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$FullScreen'),
    'The Create View button does not pass the selected full-screen mode.'
);
assertTest(is_array($startGrid), 'The optional start grid selection is missing.');
assertTest(($startGrid['value'] ?? null) === 0, 'The start grid must be disabled by default.');
assertTest(
    array_column($startGrid['options'] ?? [], 'value') === [0, 2, 3],
    'The start grid does not offer the expected two- and three-column modes.'
);
assertTest(is_array($startGridInfo), 'The start grid explanation is missing.');
assertTest(
    str_contains((string) ($startGrid['onChange'] ?? ''), 'IPSVIEWA_UpdateStartGridPreview('),
    'Changing the start grid does not refresh the live preview.'
);
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), '$StartGrid'),
    'The Create View button does not pass the selected start grid.'
);
assertTest(
    str_contains($factorySource, '$fullScreen')
        && str_contains($factorySource, '$document->configure('),
    'The View factory does not forward the selected full-screen mode.'
);
assertTest(
    str_contains($factorySource, '$startGrid')
        && str_contains($moduleSource, '$StartGrid'),
    'The selected start grid is not forwarded to the IPSView document.'
);
assertTest(
    str_contains($moduleSource, 'RegisterAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID')
        && str_contains($moduleSource, '$this->previewStartGrid()'),
    'The selected grid arrangement is not retained across preview updates.'
);
assertTest(is_array($copyButton), 'The Save styled copy button is missing from the form.');
assertTest(($copyButton['name'] ?? '') === 'SaveStyledCopyButton', 'The styled copy button has no stable form name.');
assertTest(($copyButton['visible'] ?? true) === false, 'The styled copy button must be hidden in quick start.');
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
assertTest(
    ($fontFamilyMode['options'] ?? []) === [],
    'The static form must not duplicate the shared IPSView font catalogue.'
);
assertTest(
    str_contains($moduleSource, 'IPSViewTypography::fontFamilyOptions('),
    'The dynamic form does not populate fonts from the shared IPSView catalogue.'
);
assertTest(is_array($fontBoldMode), 'The font weight selection is missing.');
assertTest(($fontBoldMode['type'] ?? '') === 'Select', 'Font weight must remain a compact Select.');
assertTest(count($fontBoldMode['options'] ?? []) === 3, 'The font weight selection does not offer all modes.');
assertTest(is_array($fontItalicMode), 'The font style selection is missing.');
assertTest(($fontItalicMode['type'] ?? '') === 'Select', 'Font style must remain a compact Select.');
assertTest(count($fontItalicMode['options'] ?? []) === 3, 'The font style selection does not offer all modes.');
assertTest(is_array($fontUnderlineMode), 'The underline selection is missing.');
assertTest(count($fontUnderlineMode['options'] ?? []) === 3, 'The underline selection does not offer all modes.');
assertTest(
    str_contains((string) ($fontBoldMode['onChange'] ?? ''), 'IPSVIEWA_UpdateAppearancePreview('),
    'Changing the font weight does not refresh the appearance preview.'
);
$boldOption = array_values(array_filter(
    $fontBoldMode['options'] ?? [],
    static fn (array $option): bool => ($option['value'] ?? null) === 2
))[0] ?? null;
$italicOption = array_values(array_filter(
    $fontItalicMode['options'] ?? [],
    static fn (array $option): bool => ($option['value'] ?? null) === 2
))[0] ?? null;
assertTest(
    is_array($boldOption) && ($boldOption['enabled'] ?? null) === true,
    'The bold option cannot be disabled independently by Symcon 9.1.'
);
assertTest(
    is_array($italicOption) && ($italicOption['enabled'] ?? null) === true,
    'The italic option cannot be disabled independently by Symcon 9.1.'
);
assertTest(
    str_contains($moduleSource, "fontFormatOptions('Bold', \$capabilities['bold'])")
        && str_contains($moduleSource, "fontFormatOptions('Italic', \$capabilities['italic'])")
        && preg_match(
            "/'options',\\s*json_encode\\(\\s*\\\$this->fontFormatOptions\\('Bold'/",
            $moduleSource
        ) === 1
        && preg_match(
            "/'options',\\s*json_encode\\(\\s*\\\$this->fontFormatOptions\\('Italic'/",
            $moduleSource
        ) === 1
        && !str_contains($moduleSource, "UpdateFormField('FontBoldMode', 'enabled'")
        && !str_contains($moduleSource, "UpdateFormField('FontItalicMode', 'enabled'"),
    'Unavailable font cuts are not passed as JSON-encoded individual Select options.'
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
assertTest(is_array($backgroundPanel), 'The background-image popup is missing.');
assertTest(($backgroundPanel['type'] ?? '') === 'PopupButton', 'Background image settings are not opened as a popup.');
assertTest(is_array($backgroundMode), 'The background mode selection is missing.');
assertTest(count($backgroundMode['options'] ?? []) === 3, 'The background selection does not offer all modes.');
assertTest(is_array($backgroundFile), 'The local background file selection is missing.');
assertTest(($backgroundFile['extensions'] ?? '') === '.png,.jpg,.jpeg', 'The background file filter is incorrect.');
assertTest(is_array($backgroundLayout), 'The background layout selection is missing.');
assertTest(count($backgroundLayout['options'] ?? []) === 3, 'The background layout selection does not offer all modes.');
assertTest(is_array($backgroundScope), 'The background page-scope selection is missing.');
assertTest(count($backgroundScope['options'] ?? []) === 2, 'The background page-scope selection does not offer both modes.');
assertTest(
    str_contains((string) ($backgroundScope['onChange'] ?? ''), 'IPSVIEWA_UpdateBackgroundScope('),
    'Changing the background page scope is not persisted.'
);
assertTest(
    str_contains((string) ($backgroundFile['onChange'] ?? ''), 'IPSVIEWA_UpdateBackgroundPreview('),
    'Selecting a background image does not refresh the preview.'
);
assertTest(
    str_ends_with((string) ($backgroundFile['onChange'] ?? ''), ', true);'),
    'Changing the background image does not distinguish an explicit file removal.'
);
assertTest(
    str_ends_with((string) ($backgroundMode['onChange'] ?? ''), ', false);')
        && str_ends_with((string) ($backgroundLayout['onChange'] ?? ''), ', false);'),
    'Every background preview call must pass the explicit file-selection flag expected by Symcon.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateBackgroundPreview('),
    'The public background preview method is missing.'
);
assertTest(
    str_contains($moduleSource, 'bool $ImageSelectionChanged')
        && str_contains($moduleSource, 'storeBackgroundSettings(')
        && str_contains($moduleSource, '$imageSelectionChanged' . "\n" . '                ? $imageData'),
    'The background preview cannot clear a previously persisted image explicitly.'
);
assertTest(
    str_contains($moduleSource, 'public function UpdateBackgroundScope('),
    'The public background page-scope method is missing.'
);
assertTest(is_array($preview), 'The live theme preview is missing from the form.');
assertTest(($preview['image'] ?? null) === '', 'The dynamic preview placeholder must be empty in form.json.');
assertTest(($preview['width'] ?? '') === '100%', 'The live preview must fill its responsive panel.');
assertTest(($preview['center'] ?? false) === true, 'The live preview must be centered.');

$workspace = null;
$colorsPanel = null;
$previewPanel = null;
$backgroundPanelPosition = null;
$previewPanelPosition = null;
foreach ($actions as $position => $action) {
    if (($action['name'] ?? '') === 'ThemeWorkspace') {
        $workspace = $action;
    }
    if (($action['name'] ?? '') === 'ThemeDetailsPopup') {
        $colorsPanel = $action;
    }
    if (($action['name'] ?? '') === 'ThemePreviewPanel') {
        $previewPanel = $action;
        $previewPanelPosition = $position;
    }
    if (($action['name'] ?? '') === 'BackgroundImagePopup') {
        $backgroundPanelPosition = $position;
    }
}
assertTest(is_array($workspace) && ($workspace['type'] ?? '') === 'RowLayout', 'The responsive theme workspace is missing.');
assertTest(($colorsPanel['type'] ?? '') === 'PopupButton', 'The design details are not opened as a popup.');
assertTest(($colorsPanel['width'] ?? '') === '240px', 'The design popup button has an unexpected width.');
assertTest(($colorsPanel['visible'] ?? true) === false, 'Detailed design settings must be hidden in quick start.');
assertTest(($previewPanel['width'] ?? '') === '700px', 'The preview panel must have a compact responsive base width.');
assertTest(
    is_int($previewPanelPosition)
        && is_int($backgroundPanelPosition)
        && $previewPanelPosition < $backgroundPanelPosition,
    'The preview must remain between the design and background popup buttons.'
);
assertTest(
    str_contains($moduleSource, "\$item['popup']['items']"),
    'Dynamic initial form values cannot reach fields nested inside popups.'
);
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

<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../libs/IPSViewSharedStyleAdapter.php';

use Burki24\IPSViewAssistant\IPSViewEffects;
use Burki24\IPSViewAssistant\IPSViewSharedStyleAdapter;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;
use Burki24\SymconModuleHelper\IPSViewStyleProfileHelper;

$root = dirname(__DIR__);
$moduleSource = file_get_contents($root . '/IPSView Assistant/module.php');
$integrationSource = file_get_contents($root . '/libs/IPSViewSharedStyleIntegration.php');

assertTest(is_string($moduleSource), 'The module source could not be read.');
assertTest(is_string($integrationSource), 'The shared style integration source could not be read.');
assertTest(
    str_contains($moduleSource, 'use IPSViewStyleConfigurationHelper;')
        && str_contains($moduleSource, 'use IPSViewSharedStyleIntegration;'),
    'The module does not use the shared IPSView style integration traits.'
);
assertTest(
    str_contains($moduleSource, '$this->RegisterIPSViewStyleProperties();'),
    'The module does not register the shared IPSView style properties.'
);
assertTest(
    str_contains($moduleSource, '$this->ApplyIPSViewSharedStyleForm($form);'),
    'The dynamic form does not install the shared IPSView style mask.'
);
assertTest(
    !str_contains($integrationSource, "str_starts_with(\$name, 'IPSViewStyle') {"),
    'The shared form capture must not treat container names as property values.'
);
assertTest(
    str_contains($integrationSource, 'IPSViewControlThemeHelper::extract($decoded)')
        && str_contains($integrationSource, "'preserveNativeColorDetails'"),
    'IPSView media sources do not preserve their exact native color details.'
);
assertTest(
    str_contains($integrationSource, 'IPSViewAssistantWasStyleProfileImportSuccessful'),
    'Failed Style Profile imports are not guarded before shared properties are adopted.'
);
assertTest(
    str_contains($integrationSource, 'IPSViewAssistantPopulateSharedStyleValues($sharedItems, $fieldNames)'),
    'The action popup does not receive explicit values from the persisted shared style properties.'
);
assertTest(
    str_contains($integrationSource, '$sourceChanged = false;')
        && str_contains($integrationSource, "['IPSViewStyleSource', 'IPSViewStyleMediaID', 'IPSViewStyleProfileMediaID']"),
    'Shared style source changes are not tracked before ApplyChanges reloads the popup.'
);

assertTest(
    str_contains($integrationSource, 'IPSViewAssistantAttachNativeListOnEdit($sharedItems)')
        && str_contains($integrationSource, 'IPSVIEWA_ApplySharedNativeColorOverride($id')
        && str_contains($integrationSource, "\$item['onEdit'] = sprintf("),
    'Native IPSView list edits are not persisted immediately from the action popup.'
);
assertTest(
    str_contains($integrationSource, 'public function ApplySharedNativeColorOverride(')
        && str_contains($integrationSource, '$this->IPSViewStyleNativeOverrideProperties()')
        && str_contains($integrationSource, "'Field'    => \$field")
        && str_contains($integrationSource, 'IPS_ApplyChanges($this->InstanceID);'),
    'Edited native IPSView rows are not validated and persisted as complete override rows.'
);
assertTest(
    !str_contains($integrationSource, "\$isEditableList = \$type === 'List'"),
    'Native lists are still part of the generic preview capture and can overwrite persisted overrides.'
);

assertTest(
    str_contains($integrationSource, '$wasOverridden = isset($stored[$field]);')
        && str_contains($integrationSource, '$editedColor !== $inheritedColor')
        && str_contains($integrationSource, '$override = true;'),
    'Changing an inherited native IPSView color does not automatically enable its override.'
);
assertTest(
    str_contains($integrationSource, 'IPSViewAssistantNativeInheritedColor($field)')
        && str_contains($integrationSource, 'IPSViewControlThemeHelper::colorToHex($theme[\'colors\'][$field])'),
    'Native override auto-detection does not compare against the inherited semantic color.'
);
assertTest(
    str_contains($integrationSource, 'IPSViewAssistantRefreshNativeList($PropertyName)')
        && str_contains($integrationSource, 'json_encode($values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)'),
    'The native color list is not refreshed after automatic override activation.'
);
assertTest(
    !str_contains($integrationSource, "UpdateFormField(\$propertyName, 'values', \$values)"),
    'Native list values are still passed to UpdateFormField as an unsupported PHP array.'
);

$style = [
    'ViewBackground'            => '#102030',
    'PageBackground'            => '#203040',
    'LabelBackground'           => 'rgba(48, 64, 80, 0.75)',
    'ControlBackground'         => 'rgba(64, 80, 96, 0.80)',
    'ControlActiveBackground'   => 'rgba(80, 96, 112, 0.85)',
    'ControlInactiveBackground' => 'rgba(96, 112, 128, 0.65)',
    'Text'                      => '#F0F1F2',
    'TextActive'                => '#FFFFFF',
    'TextInactive'              => '#A0A1A2',
    'LabelText'                 => '#E0E1E2',
    'Icon'                      => '#D0D1D2',
    'Border'                    => 'rgba(112, 128, 144, 0.70)',
    'Line'                      => 'rgba(128, 144, 160, 0.60)',
    'PopupBackground'           => 'rgba(32, 48, 64, 0.90)',
    'PopupBorder'               => 'rgba(144, 160, 176, 0.55)',
    'Accent'                    => '#55CBB5',
    'Information'               => '#4A90E2',
    'Positive'                  => '#56C881',
    'Warning'                   => '#E6A93F',
    'Critical'                  => '#E36D6D',
    'FontFamily'                => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    'FontStyle'                 => 'regular',
    'FontSize'                  => 16.0,
    'FontScale'                 => 1.25,
    'BorderRadius'              => 9.5,
    'BorderWidth'               => 9.5,
    'LineWidth'                 => 2.5,
    'DisabledOpacity'           => 0.52,
    'Shadow'                    => '1px 2px 12px 3px rgba(10, 20, 30, 0.400)',
    'ViewBackgroundOpacity'     => 1.0,
    'PageBackgroundOpacity'     => 1.0,
    'LabelBackgroundOpacity'    => 0.75,
    'ControlBackgroundOpacity'  => 0.80,
    'ControlActiveOpacity'      => 0.85,
    'ControlInactiveOpacity'    => 0.65,
    'PopupBackgroundOpacity'    => 0.90,
    'BorderOpacity'             => 0.70,
    'LineOpacity'               => 0.60,
    'PopupBorderOpacity'        => 0.55,
    'ShadowOpacity'             => 0.40,
    'PopupShadowOpacity'        => 0.45,
];

$palette = IPSViewSharedStyleAdapter::palette($style);
assertTest(
    $palette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#102030'
        && $palette[IPSViewTheme::ROLE_SURFACE] === '#405060'
        && $palette[IPSViewTheme::ROLE_SECONDARY_TEXT] === '#A0A1A2',
    'The shared style is not mapped to the established Assistant palette correctly.'
);

$previewPalette = IPSViewSharedStyleAdapter::previewPalette($style);
assertTest(
    $previewPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#102030'
        && $previewPalette[IPSViewTheme::ROLE_PAGE_BACKGROUND] === '#203040'
        && $previewPalette[IPSViewTheme::ROLE_SURFACE] === IPSViewTheme::mix('#203040', '#405060', 0.80),
    'Role-specific shared opacity is not composited correctly for the Assistant preview.'
);
$previewEffects = IPSViewSharedStyleAdapter::previewEffects($style, 37);
assertTest(
    $previewEffects['transparencyMode'] === IPSViewEffects::TRANSPARENCY_OPAQUE
        && $previewEffects['transparencyPercent'] === 0,
    'The Assistant preview still collapses role-specific opacity into one global transparency value.'
);

$styleColors = [];
foreach (IPSViewControlThemeHelper::requiredStyleFields() as $styleField) {
    $styleColors[$styleField] = $styleField === 'ShadowColor'
        ? '#0A141E'
        : ($style[$styleField] ?? '#000000');
    if (str_starts_with((string) $styleColors[$styleField], 'rgba')) {
        preg_match(
            '/rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/',
            (string) $styleColors[$styleField],
            $matches
        );
        $styleColors[$styleField] = sprintf('#%02X%02X%02X', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
    }
}
$nativeTheme = IPSViewControlThemeHelper::fromStyleColors(
    $styleColors,
    ['SwitchTrackColorActive' => '#123456']
);
assertTest(
    count($nativeTheme['colors']) === 109,
    'The shared style adapter test theme does not contain all 109 known IPSView color fields.'
);

$profileStyle = IPSViewSharedStyleAdapter::profileStyle($style, $nativeTheme, 37, false);
assertTest(
    IPSViewStyleProfileHelper::normalizeStyle($profileStyle) === $profileStyle,
    'The shared style adapter does not create a canonical Style Profile V1 style.'
);
assertTest($profileStyle['LabelBackgroundOpacity'] === 75, 'Label opacity was not retained in the profile.');
assertTest($profileStyle['PopupShadowOpacity'] === 45, 'Popup shadow opacity was not retained in the profile.');
assertTest($profileStyle['GradientStrength'] === 37, 'Gradient strength was not retained in the profile.');
assertTest($profileStyle['BorderWidth'] === 9.5, 'The shared border width was unexpectedly clamped.');

$properties = IPSViewSharedStyleAdapter::propertyValuesFromProfileStyle($profileStyle);
assertTest($properties['IPSViewStyleSource'] === 0, 'Imported profiles must be adopted as a custom shared style.');
assertTest($properties['IPSViewStyleBorderWidth'] === 9.5, 'Imported border width was not restored exactly.');
assertTest($properties['IPSViewStyleGradientStrength'] === 37, 'Imported gradient strength was not restored.');

$document = new stdClass();
$document->DefaultFontFamily = 'Roboto Mono';
$document->DefaultFontSize = 11;
$document->DefaultBorderRadius = 6;
$document->DefaultBorderWidth = 1.5;
$document->LineWidth = 1.5;
$document->ShadowOffsetX = 0;
$document->ShadowOffsetY = 0;
$document->ShadowBlurRadius = 0;
$document->ShadowSpreadRadius = 0;
$document->CircleTrackEdgesRounded = false;
$document->ProgressbarTrackEdgesRounded = false;

IPSViewSharedStyleAdapter::applyToDocument(
    $document,
    $nativeTheme,
    $style,
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    37,
    false,
    true
);

assertTest(
    IPSViewControlThemeHelper::colorToHex($document->SwitchTrackColorActive) === '#123456',
    'A native IPSView color override was not applied.'
);
assertTest($document->ColorBack->A === 204, 'Control background opacity was not applied exactly.');
assertTest($document->ColorBack->Type === 1, 'The configured gradient was not applied to control backgrounds.');
assertTest($document->DefaultFontFamily === '', 'The shared system font did not restore the IPSView default font family.');
assertTest((float) $document->DefaultBorderWidth === 9.5, 'The native border width was not applied exactly.');
assertTest((float) $document->DefaultBorderRadius === 9.5, 'The native border radius was not applied exactly.');
assertTest((float) $document->LineWidth === 2.5, 'The native line width was not applied exactly.');
assertTest((float) $document->ShadowOffsetX === 1.0, 'The shared shadow X offset was not applied.');
assertTest((float) $document->ShadowOffsetY === 2.0, 'The shared shadow Y offset was not applied.');
assertTest((float) $document->ShadowBlurRadius === 12.0, 'The shared shadow blur was not applied.');
assertTest((float) $document->ShadowSpreadRadius === 3.0, 'The shared shadow spread was not applied.');

$transparent = clone $document;
IPSViewSharedStyleAdapter::applyToDocument(
    $transparent,
    $nativeTheme,
    $style,
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    0,
    true,
    true
);
assertTest($transparent->ColorView->A === 0, 'Transparent View background does not set ColorView alpha to zero.');

$mediaColor = IPSViewControlThemeHelper::createColor('#405060', 111, 1, '12', false, '', '#010203', 99);
$mediaDocument = new stdClass();
$mediaDocument->ColorBack = (object) $mediaColor;
$mediaDocument->DefaultFontFamily = 'Roboto';
$mediaDocument->DefaultFontSize = 11;
$mediaDocument->DefaultBorderRadius = 6;
$mediaDocument->DefaultBorderWidth = 1.5;
$mediaDocument->LineWidth = 1.5;
$mediaDocument->CircleTrackEdgesRounded = false;
$mediaDocument->ProgressbarTrackEdgesRounded = false;
IPSViewSharedStyleAdapter::applyToDocument(
    $mediaDocument,
    IPSViewControlThemeHelper::extract($mediaDocument),
    $style,
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    80,
    false,
    false,
    true
);
assertTest($mediaDocument->ColorBack->A === 111, 'Media-native color alpha was not preserved.');
assertTest($mediaDocument->ColorBack->Type === 1, 'Media-native gradient type was not preserved.');
assertTest($mediaDocument->ColorBack->R2 === 1, 'Media-native secondary gradient color was not preserved.');

$customFontStyle = $style;
$customFontStyle['FontFamily'] = 'Arial';
$customFontDocument = clone $document;
$customFontDocument->DefaultFontFamily = 'Roboto';
IPSViewSharedStyleAdapter::applyToDocument(
    $customFontDocument,
    $nativeTheme,
    $customFontStyle,
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    0,
    false,
    true
);
assertTest(
    $customFontDocument->DefaultFontFamily === 'Arial',
    'An unknown/custom native IPSView font family was reset instead of being applied exactly.'
);

fwrite(STDOUT, "Shared IPSView style integration tests passed.\n");

<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewStyleProfileExchange.php';

use Burki24\IPSViewAssistant\IPSViewEffects;
use Burki24\IPSViewAssistant\IPSViewShape;
use Burki24\IPSViewAssistant\IPSViewStyleProfileExchange;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewTypography;
use Burki24\SymconModuleHelper\IPSViewFontCatalogHelper;
use Burki24\SymconModuleHelper\IPSViewStyleProfileHelper;

$palette = IPSViewTheme::preset(IPSViewTheme::THEME_WARM);
$effects = [
    'shadowStyle'         => IPSViewEffects::SHADOW_STRONG,
    'transparencyMode'    => IPSViewEffects::TRANSPARENCY_CUSTOM,
    'transparencyPercent' => 20,
    'gradientStyle'       => IPSViewEffects::GRADIENT_MEDIUM,
    'gradientDirection'   => IPSViewEffects::GRADIENT_TO_LIGHTER,
];
$appearance = [
    'typographyStyle'    => IPSViewTypography::STYLE_CUSTOM,
    'fontFamilyMode'     => IPSViewTypography::FONT_ROBOTO_MONO,
    'customFontFamily'   => '',
    'customFontSize'     => 17,
    'fontBoldMode'       => IPSViewTypography::FORMAT_ON,
    'fontItalicMode'     => IPSViewTypography::FORMAT_ON,
    'fontUnderlineMode'  => IPSViewTypography::FORMAT_OFF,
    'cornerStyle'        => IPSViewShape::CORNER_CUSTOM,
    'customCornerRadius' => 7,
    'borderStyle'        => IPSViewShape::BORDER_CUSTOM,
    'customBorderWidth'  => 1.5,
];

$style = IPSViewStyleProfileExchange::styleFromEditor($palette, $effects, $appearance);
assertTest(count($style) === 46, 'The Assistant export does not contain all Style Profile V1 fields.');
assertTest(
    $style['ViewBackground'] === $palette[IPSViewTheme::ROLE_VIEW_BACKGROUND],
    'The View background was not mapped to the canonical profile.'
);
assertTest(
    $style['ControlBackground'] === $palette[IPSViewTheme::ROLE_SURFACE],
    'The Assistant surface was not mapped to the canonical control background.'
);
assertTest($style['ViewBackgroundOpacity'] === 80, 'The Assistant transparency was not exported correctly.');
assertTest($style['ShadowOpacity'] === 62, 'The strong shadow opacity was not exported correctly.');
assertTest(abs((float) $style['ShadowBlur'] - 14.0) < 0.001, 'The strong shadow blur was not exported correctly.');
assertTest($style['GradientStrength'] === 26, 'The medium gradient strength was not exported correctly.');
assertTest(
    $style['FontFamily'] === IPSViewFontCatalogHelper::FONT_ROBOTO_MONO,
    'The selected canonical font family was not exported.'
);
assertTest(
    $style['FontStyle'] === IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC,
    'The selected bold italic font cut was not exported.'
);
assertTest($style['FontSize'] === 17, 'The custom font size was not exported.');
assertTest(abs((float) $style['BorderRadius'] - 7.0) < 0.001, 'The custom corner radius was not exported.');
assertTest(abs((float) $style['BorderWidth'] - 1.5) < 0.001, 'The custom border width was not exported.');
assertTest(
    IPSViewStyleProfileHelper::normalizeStyle($style) === $style,
    'The exported style is not already normalized to Style Profile V1.'
);

$profile = IPSViewStyleProfileExchange::createProfile(
    'Warm control panel',
    $palette,
    $effects,
    $appearance,
    [
        'description' => 'Portable Assistant style',
        'createdBy'   => 'IPSView Assistant',
        'createdAt'   => '2026-08-30T18:00:00+02:00',
    ]
);
$json = IPSViewStyleProfileHelper::encode($profile);
assertTest(IPSViewStyleProfileHelper::isValidJson($json), 'The Assistant did not produce valid Style Profile V1 JSON.');
assertTest(
    !str_contains($json, 'shadowStyle')
        && !str_contains($json, 'fontFamilyMode')
        && !str_contains($json, 'typographyStyle')
        && !str_contains($json, 'THEME_WARM'),
    'The portable profile leaks Assistant-specific enum fields.'
);

assertTest(
    IPSViewStyleProfileExchange::decodeFileData($json) === trim($json),
    'Raw JSON from SelectFile was not decoded.'
);
assertTest(
    IPSViewStyleProfileExchange::decodeFileData(base64_encode($json)) === $json,
    'Base64 JSON from SelectFile was not decoded.'
);
assertTest(
    IPSViewStyleProfileExchange::decodeFileData('data:application/json;base64,' . base64_encode($json)) === $json,
    'A JSON data URI from SelectFile was not decoded.'
);
assertTest(
    IPSViewStyleProfileExchange::decodeFileData(
        'data:application/json;charset=utf-8;base64,' . base64_encode($json)
    ) === $json,
    'A parameterized JSON data URI from SelectFile was not decoded.'
);
assertTest(
    IPSViewStyleProfileExchange::decodeFileData(
        'data:application/json;charset=UTF-8;profile=ipsview;BASE64,' . base64_encode($json)
    ) === $json,
    'A JSON data URI with multiple parameters or uppercase BASE64 was not decoded.'
);

$customStyle = $style;
$customStyle['LabelBackground'] = '#123456';
$customStyle['LabelBackgroundOpacity'] = 71;
$customStyle['ControlActiveOpacity'] = 63;
$customStyle['PopupBackgroundOpacity'] = 88;
$customStyle['FontScale'] = 135;
$customStyle['LineWidth'] = 2.5;
$customStyle['DisabledOpacity'] = 64;
$customStyle['PopupShadowOpacity'] = 31;
$customStyle = IPSViewStyleProfileHelper::normalizeStyle($customStyle);
$customProfile = IPSViewStyleProfileHelper::create(
    'Lossless round trip',
    $customStyle,
    [
        'description' => 'Contains fields that the Assistant does not edit independently.',
        'createdBy'   => 'External editor',
        'createdAt'   => '2026-08-29T12:34:56+02:00',
    ]
);
$customJson = IPSViewStyleProfileHelper::encode($customProfile);
$state = IPSViewStyleProfileExchange::importJson($customJson);
assertTest(
    $state['editor']['effects']['gradientDirection'] === IPSViewEffects::GRADIENT_TO_DARKER,
    'The portable profile import must use the canonical darker gradient direction.'
);
assertTest(
    $state['editor']['appearance']['fontUnderlineMode'] === IPSViewTypography::FORMAT_OFF,
    'Underline must not be invented by the portable Style Profile import.'
);

$unchangedProfile = IPSViewStyleProfileExchange::createProfile(
    (string) $state['profile']['name'],
    $state['editor']['palette'],
    $state['editor']['effects'],
    $state['editor']['appearance'],
    [
        'description' => (string) $state['profile']['description'],
        'createdBy'   => (string) $state['profile']['createdBy'],
        'createdAt'   => (string) $state['profile']['createdAt'],
    ],
    $state
);
assertTest(
    $unchangedProfile['style'] === $customStyle,
    'An untouched imported profile did not retain its exact canonical style snapshot.'
);
assertTest(
    $unchangedProfile['style']['FontScale'] === 135
        && abs((float) $unchangedProfile['style']['LineWidth'] - 2.5) < 0.001
        && $unchangedProfile['style']['LabelBackgroundOpacity'] === 71,
    'Unexposed profile fields were lost during the no-edit round-trip.'
);

$editedPalette = $state['editor']['palette'];
$editedPalette[IPSViewTheme::ROLE_ACCENT] = '#1122CC';
$editedProfile = IPSViewStyleProfileExchange::createProfile(
    'Edited round trip',
    $editedPalette,
    $state['editor']['effects'],
    $state['editor']['appearance'],
    [],
    $state
);
assertTest($editedProfile['style']['Accent'] === '#1122CC', 'An edited imported accent was not exported.');
assertTest(
    $editedProfile['style']['LabelBackground'] === $state['editor']['palette'][IPSViewTheme::ROLE_SURFACE],
    'An edited profile did not rebuild the canonical surface mapping from visible Assistant semantics.'
);
assertTest(
    $editedProfile['style']['FontScale'] === 100,
    'An edited profile must rebuild hidden values from the Assistant canonical defaults.'
);
assertTest(IPSViewStyleProfileHelper::isValid($editedProfile), 'The edited round-trip profile is invalid.');

$systemStyle = $customStyle;
$systemStyle['FontFamily'] = IPSViewStyleProfileHelper::FONT_SYSTEM;
$systemStyle['FontStyle'] = IPSViewFontCatalogHelper::STYLE_ITALIC;
$systemProfile = IPSViewStyleProfileHelper::create('System font', $systemStyle);
$systemState = IPSViewStyleProfileExchange::importJson(IPSViewStyleProfileHelper::encode($systemProfile));
$systemRoundTrip = IPSViewStyleProfileExchange::createProfile(
    'System font',
    $systemState['editor']['palette'],
    $systemState['editor']['effects'],
    $systemState['editor']['appearance'],
    [],
    $systemState
);
assertTest(
    $systemRoundTrip['style']['FontFamily'] === IPSViewStyleProfileHelper::FONT_SYSTEM,
    'An untouched imported system font was not preserved.'
);
assertTest(
    $systemRoundTrip['style']['FontStyle'] === IPSViewFontCatalogHelper::STYLE_ITALIC,
    'An untouched imported system font style was not preserved.'
);

$reimported = IPSViewStyleProfileExchange::importJson(IPSViewStyleProfileHelper::encode($editedProfile));
assertTest(
    $reimported['profile']['style'] === $editedProfile['style'],
    'The edited exported profile cannot be imported again without changes.'
);

echo "IPSView Style Profile exchange tests passed.\n";

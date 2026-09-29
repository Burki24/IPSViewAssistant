<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewColorPaletteExchange.php';

use Burki24\IPSViewAssistant\IPSViewColorPaletteExchange;
use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;

$semanticColors = [];
foreach (IPSViewControlThemeHelper::styleFields() as $index => $field) {
    $value = (($index + 1) * 0x07111D) & 0xFFFFFF;
    $semanticColors[$field] = sprintf('#%06X', $value);
}

$nativeTheme = IPSViewControlThemeHelper::fromStyleColors($semanticColors);
$nativeColors = [];
foreach (IPSViewControlThemeHelper::fields() as $field) {
    $nativeColors[$field] = IPSViewControlThemeHelper::colorToHex($nativeTheme['colors'][$field]);
}
$nativeColors['SwitchTrackColorActive'] = '#123456';
$nativeColors['CalendarTodayHighlightColor'] = '#ABCDEF';

$json = IPSViewColorPaletteExchange::exportJson(
    'Test Palette',
    'All shared and native colors.',
    $semanticColors,
    $nativeColors,
    [
        'createdBy' => 'IPSViewAssistant tests',
        'createdAt' => '2026-09-29T11:30:00+02:00',
    ]
);
$palette = IPSViewColorPaletteExchange::importJson($json);
$flatNativeColors = IPSViewColorPaletteExchange::nativeColors($palette);

assertTest($palette['schema'] === IPSViewColorPaletteExchange::SCHEMA, 'The Color Palette schema is incorrect.');
assertTest($palette['version'] === 1, 'The Color Palette version is incorrect.');
assertTest($palette['name'] === 'Test Palette', 'The Color Palette name was not preserved.');
assertTest(
    count($palette['semanticColors']) === count(IPSViewControlThemeHelper::styleFields()),
    'The Color Palette does not contain every universal shared-style color.'
);
assertTest(count($palette['nativeColors']) === 15, 'The Color Palette does not contain all 15 native families.');
assertTest(count($flatNativeColors) === 109, 'The Color Palette does not contain all 109 native IPSView colors.');
assertTest(
    $flatNativeColors['SwitchTrackColorActive'] === '#123456',
    'A native IPSView color override was not preserved by the Color Palette.'
);
assertTest(
    $flatNativeColors['CalendarTodayHighlightColor'] === '#ABCDEF',
    'A native calendar color was not preserved by the Color Palette.'
);
$nativeOverrides = IPSViewColorPaletteExchange::nativeOverrides($palette);
assertTest(count($nativeOverrides) === 2, 'The Color Palette did not reduce native colors to the actual deviations.');
assertTest(
    $nativeOverrides['SwitchTrackColorActive'] === '#123456'
        && $nativeOverrides['CalendarTodayHighlightColor'] === '#ABCDEF',
    'The Color Palette native override derivation is incorrect.'
);
assertTest(
    !str_contains($json, 'FontFamily')
        && !str_contains($json, 'BorderRadius')
        && !str_contains($json, 'GradientStrength')
        && !str_contains($json, 'Opacity'),
    'The Color Palette unexpectedly contains non-color design settings.'
);
assertTest(
    IPSViewColorPaletteExchange::importJson($json) === IPSViewColorPaletteExchange::importJson($json),
    'Repeated Color Palette imports are not deterministic.'
);
assertTest(
    IPSViewColorPaletteExchange::decodeFileData(base64_encode($json)) === $json,
    'Base64 Color Palette file data was not decoded correctly.'
);
assertTest(
    IPSViewColorPaletteExchange::decodeFileData('data:application/json;base64,' . base64_encode($json)) === $json,
    'Color Palette data URI content was not decoded correctly.'
);

$invalid = $palette;
unset($invalid['nativeColors']['switch']['SwitchTrackColorActive']);
try {
    IPSViewColorPaletteExchange::normalize($invalid);
    failTest('An incomplete 109-field Color Palette was accepted.');
} catch (InvalidArgumentException) {
}

$invalid = $palette;
$invalid['semanticColors']['UnknownColor'] = '#FFFFFF';
try {
    IPSViewColorPaletteExchange::normalize($invalid);
    failTest('An unknown semantic Color Palette field was accepted.');
} catch (InvalidArgumentException) {
}

fwrite(STDOUT, "IPSView Color Palette exchange tests passed.\n");

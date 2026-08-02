<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewThemePreview.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;

/**
 * Converts one IPSView color object to #RRGGBB.
 */
function ipsViewColorToHex(stdClass $color): string
{
    return sprintf('#%02X%02X%02X', $color->R, $color->G, $color->B);
}

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';

$standardDocument = IPSViewDocument::fromTemplate($templatePath);
$standardDocument->configure('Standard', 10001, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$beforeStandard = $standardDocument->copy();
$standardPalette = $standardDocument->applyTheme(IPSViewTheme::THEME_STANDARD);
$afterStandard = $standardDocument->copy();

assertTest(
    json_encode($beforeStandard->ColorBack, JSON_THROW_ON_ERROR)
        === json_encode($afterStandard->ColorBack, JSON_THROW_ON_ERROR),
    'The IPSView Standard theme must retain the original template colors.'
);
assertTest(
    $standardPalette[IPSViewTheme::ROLE_ACCENT] === '#007AFF',
    'The IPSView Standard accent color is incorrect.'
);

$lightDocument = IPSViewDocument::fromTemplate($templatePath);
$lightDocument->configure('Light', 10002, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$lightDocument->applyTheme(IPSViewTheme::THEME_LIGHT);
$light = $lightDocument->copy();

assertTest(ipsViewColorToHex($light->ColorPage) === '#E9EEF5', 'The light View background is incorrect.');
assertTest(ipsViewColorToHex($light->ColorBack) === '#FFFFFF', 'The light surface color is incorrect.');
assertTest(ipsViewColorToHex($light->ColorText) === '#1F2937', 'The light primary text color is incorrect.');
assertTest(ipsViewColorToHex($light->SwitchTrackColorActive) === '#2563EB', 'The light accent color is incorrect.');
assertTest(ipsViewColorToHex($light->ColorBackOn) === '#16A34A', 'The light active color is incorrect.');
assertTest(ipsViewColorToHex($light->FlowLineColorNegative) === '#DC2626', 'The light error color is incorrect.');

$darkDocument = IPSViewDocument::fromTemplate($templatePath);
$darkDocument->configure('Dark', 10003, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$darkPalette = $darkDocument->applyTheme(IPSViewTheme::THEME_DARK);
$dark = $darkDocument->copy();

assertTest(ipsViewColorToHex($dark->ColorPage) === '#111827', 'The dark View background is incorrect.');
assertTest(ipsViewColorToHex($dark->ColorPopupBack) === '#1F2937', 'The dark page background is incorrect.');
assertTest(ipsViewColorToHex($dark->ColorBack) === '#273449', 'The dark surface color is incorrect.');
assertTest(ipsViewColorToHex($dark->ColorText) === '#F9FAFB', 'The dark primary text color is incorrect.');
assertTest(ipsViewColorToHex($dark->ColorBorder) === '#475569', 'The dark border color is incorrect.');
assertTest(ipsViewColorToHex($dark->ScheduleNowIndicatorColor) === '#F59E0B', 'The warning color is incorrect.');

$allowedColors = array_values($darkPalette);
$allowedColors[] = IPSViewTheme::mix($darkPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND], '#000000', 0.68);

foreach (get_object_vars($dark) as $property => $value) {
    if (!$value instanceof stdClass
        || !property_exists($value, 'R')
        || !property_exists($value, 'G')
        || !property_exists($value, 'B')) {
        continue;
    }

    assertTest(
        in_array(ipsViewColorToHex($value), $allowedColors, true),
        'The IPSView color property was not assigned to a semantic role: ' . $property
    );
}

$custom = IPSViewTheme::resolvePalette(
    IPSViewTheme::THEME_CUSTOM,
    [
        IPSViewTheme::ROLE_VIEW_BACKGROUND => 'abcdef',
        IPSViewTheme::ROLE_ACCENT          => '#123456',
        IPSViewTheme::ROLE_ERROR           => 'invalid',
    ]
);

assertTest($custom[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#ABCDEF', 'Custom colors are not normalized.');
assertTest($custom[IPSViewTheme::ROLE_ACCENT] === '#123456', 'Valid custom colors were changed.');
assertTest($custom[IPSViewTheme::ROLE_ERROR] === '#EF4444', 'Invalid custom colors do not use the fallback.');


$integerCustom = IPSViewTheme::resolvePalette(
    IPSViewTheme::THEME_CUSTOM,
    [
        IPSViewTheme::ROLE_VIEW_BACKGROUND => 0x102030,
        IPSViewTheme::ROLE_ACCENT          => 0xABCDEF,
    ]
);

assertTest(
    $integerCustom[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#102030',
    'Symcon color integers are not converted for the live preview.'
);
assertTest(
    $integerCustom[IPSViewTheme::ROLE_ACCENT] === '#ABCDEF',
    'Symcon color integers are not retained for custom Views.'
);
assertTest(
    IPSViewTheme::toFormColor('#ABCDEF') === 0xABCDEF,
    'Hexadecimal colors are not converted to SelectColor integers.'
);

$preview = IPSViewThemePreview::createDataUri($darkPalette);
assertTest(
    str_starts_with($preview, 'data:image/svg+xml;base64,'),
    'The theme preview is not an SVG data URI.'
);

$svg = base64_decode(substr($preview, strlen('data:image/svg+xml;base64,')), true);
assertTest(is_string($svg), 'The SVG preview could not be decoded.');
assertTest(str_contains($svg, '<svg'), 'The preview does not contain SVG markup.');
assertTest(str_contains($svg, '#111827'), 'The preview does not contain the selected View background.');
assertTest(str_contains($svg, '#3B82F6'), 'The preview does not contain the selected accent color.');

echo "IPSView theme tests passed.\n";

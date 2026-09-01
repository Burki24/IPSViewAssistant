<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewThemePreview.php';

use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;

$palette = IPSViewTheme::preset(IPSViewTheme::THEME_DARK);
$nativeColors = [
    'ColorView'                => '#111213',
    'ColorPage'                => '#212223',
    'ColorBack'                => '#313233',
    'ColorText'                => '#F1F2F3',
    'ColorTextOff'             => '#A1A2A3',
    'ColorBorder'              => '#414243',
    'SwitchTrackColorActive'   => '#123456',
    'SwitchThumbColorActive'   => '#234567',
    'SliderTrackColorActive'   => '#345678',
    'SliderTrackColorInactive' => '#EE5145',
    'SliderThumbColorInner'    => '#56789A',
    'SliderThumbColorOuter'    => '#6789AB'
];

$svg = IPSViewThemePreview::createSvg($palette, [], [], [], 0, false, $nativeColors);

assertTest(
    str_contains($svg, 'data-native-field="ColorView"')
        && str_contains($svg, '#111213'),
    'The preview does not use the native View color.'
);
assertTest(
    str_contains($svg, 'data-native-field="SwitchTrackColorActive"')
        && str_contains($svg, '#123456'),
    'The preview does not use SwitchTrackColorActive.'
);
assertTest(
    str_contains($svg, 'data-native-field="SwitchThumbColorActive"')
        && str_contains($svg, '#234567'),
    'The preview does not use SwitchThumbColorActive.'
);
assertTest(
    str_contains($svg, 'data-native-field="SliderTrackColorActive"')
        && str_contains($svg, '#345678'),
    'The preview does not use SliderTrackColorActive.'
);
assertTest(
    str_contains($svg, 'data-native-field="SliderTrackColorInactive"')
        && str_contains($svg, '#EE5145'),
    'The preview does not use SliderTrackColorInactive.'
);
assertTest(
    str_contains($svg, 'data-native-field="SliderThumbColorInner"')
        && str_contains($svg, '#56789A')
        && str_contains($svg, 'stroke="#6789AB"'),
    'The preview does not use the native slider thumb colors.'
);

$fallbackSvg = IPSViewThemePreview::createSvg($palette);
assertTest(
    str_contains($fallbackSvg, 'data-native-field="SwitchTrackColorActive"')
        && str_contains($fallbackSvg, $palette[IPSViewTheme::ROLE_ACCENT]),
    'The native SwitchTrackColorActive fallback does not inherit the accent color.'
);
assertTest(
    str_contains($fallbackSvg, 'data-native-field="SliderTrackColorInactive"')
        && str_contains($fallbackSvg, $palette[IPSViewTheme::ROLE_INACTIVE]),
    'The native SliderTrackColorInactive fallback does not inherit the inactive control color.'
);

$integrationSource = file_get_contents(dirname(__DIR__) . '/libs/IPSViewSharedStyleIntegration.php');
assertTest(is_string($integrationSource), 'The shared style integration source could not be read.');
assertTest(
    str_contains($integrationSource, 'IPSViewAssistantNativePreviewColors($snapshot[\'nativeTheme\'])')
        && str_contains($integrationSource, 'IPSViewControlThemeHelper::colorToHex($color)'),
    'The resolved native IPSView theme is not forwarded to the live preview.'
);

echo "Native IPSView preview color tests passed.\n";

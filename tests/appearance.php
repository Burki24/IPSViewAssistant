<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewThemePreview.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewShape;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;
use Burki24\IPSViewAssistant\IPSViewTypography;

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$sourceDocument = IPSViewDocument::fromTemplate($templatePath);
$sourceDocument->configure('Appearance', 14001, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$source = $sourceDocument->copy();
$source->Pages[0]->Controls = [
    (object) [
        'Type' => 'IPSTxtLabel',
        'Font' => (object) [
            'FontFamily' => '',
            'Size'       => 11,
            'isBold'     => true,
        ],
    ],
    (object) [
        'Type' => 'IPSTxtLabel',
        'Font' => (object) [
            'FontFamily' => 'Roboto',
            'Size'       => 16,
            'isBold'     => false,
        ],
    ],
];
$json = json_encode($source, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

$supportedFonts = [
    IPSViewTypography::FONT_ROBOTO         => 'Roboto',
    IPSViewTypography::FONT_ROBOTO_MONO    => 'RobotoMono',
    IPSViewTypography::FONT_DANCING_SCRIPT => 'DancingScript',
    IPSViewTypography::FONT_INDIE_FLOWER   => 'IndieFlower',
    IPSViewTypography::FONT_OPEN_SANS      => 'OpenSans',
    IPSViewTypography::FONT_PT_SANS        => 'PTSans',
    IPSViewTypography::FONT_BEBAS_NEUE     => 'BebasNeue',
    IPSViewTypography::FONT_SEGMENT_7      => 'Segment7',
];

foreach ($supportedFonts as $fontMode => $fontFamily) {
    $fontDocument = IPSViewDocument::fromJson($json);
    $fontDocument->applyThemeWithReport(
        IPSViewTheme::THEME_STANDARD,
        [],
        IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
        [],
        ['fontFamilyMode' => $fontMode]
    );

    assertTest(
        $fontDocument->copy()->DefaultFontFamily === $fontFamily,
        sprintf('The IPSView font %s was not applied correctly.', $fontFamily)
    );
}

$matchingDocument = IPSViewDocument::fromJson($json);
$matchingReport = $matchingDocument->applyThemeWithReport(
    IPSViewTheme::THEME_STANDARD,
    [],
    IPSViewTheme::SCOPE_MATCHING_CONTROLS,
    [],
    [
        'typographyStyle' => IPSViewTypography::STYLE_STANDARD,
        'fontFamilyMode'  => IPSViewTypography::FONT_ROBOTO_MONO,
        'cornerStyle'     => IPSViewShape::CORNER_ROUNDED,
        'borderStyle'     => IPSViewShape::BORDER_STRONG,
    ]
);
$matching = $matchingDocument->copy();

assertTest($matching->DefaultFontFamily === 'RobotoMono', 'The global font family was not updated.');
assertTest($matching->DefaultFontSize === 14, 'The standard base font size is incorrect.');
assertTest($matching->EventHeaderFontSize === 25, 'The global font hierarchy was not scaled proportionally.');
assertTest($matching->DefaultBorderRadius === 10, 'The rounded corner preset is incorrect.');
assertTest(abs($matching->DefaultBorderWidth - 3.0) < 0.001, 'The strong border preset is incorrect.');
assertTest($matching->CircleTrackEdgesRounded === true, 'Rounded circle tracks were not enabled.');
assertTest($matching->Pages[0]->Controls[0]->Font->FontFamily === 'RobotoMono', 'A matching default control font family was not updated.');
assertTest($matching->Pages[0]->Controls[0]->Font->Size === 14, 'A matching default control font size was not updated.');
assertTest($matching->Pages[0]->Controls[0]->Font->isBold === true, 'The control font weight was changed unexpectedly.');
assertTest($matching->Pages[0]->Controls[1]->Font->FontFamily === 'Roboto', 'An explicit control font family was changed by the recommended scope.');
assertTest($matching->Pages[0]->Controls[1]->Font->Size === 16, 'An explicit control font size was changed by the recommended scope.');
assertTest($matchingReport['globalTypographyApplied'] > 5, 'Global typography changes were not reported.');
assertTest($matchingReport['controlTypographyApplied'] === 1, 'The matching control-font report is incorrect.');
assertTest($matchingReport['globalShapeApplied'] >= 2, 'The form-language changes were not reported.');

$strongDocument = IPSViewDocument::fromJson($json);
$strongReport = $strongDocument->applyThemeWithReport(
    IPSViewTheme::THEME_STANDARD,
    [],
    IPSViewTheme::SCOPE_ALL_CONTROL_DEFAULTS,
    [],
    [
        'typographyStyle' => IPSViewTypography::STYLE_LARGE,
        'fontFamilyMode'  => IPSViewTypography::FONT_SEGMENT_7,
        'cornerStyle'     => IPSViewShape::CORNER_SQUARE,
        'borderStyle'     => IPSViewShape::BORDER_NONE,
    ]
);
$strong = $strongDocument->copy();

assertTest($strong->DefaultFontSize === 18, 'The large base font size is incorrect.');
assertTest($strong->Pages[0]->Controls[0]->Font->Size === 18, 'The strong scope did not scale a default control font.');
assertTest($strong->Pages[0]->Controls[1]->Font->Size === 26, 'The strong scope did not preserve the relative control font hierarchy.');
assertTest($strong->Pages[0]->Controls[1]->Font->FontFamily === 'Segment7', 'The strong scope did not standardize an explicit font family.');
assertTest($strong->DefaultBorderRadius === 0, 'The square corner preset is incorrect.');
assertTest(abs($strong->DefaultBorderWidth) < 0.001, 'The no-border preset is incorrect.');
assertTest($strong->CircleTrackEdgesRounded === false, 'Square circle tracks were not applied.');
assertTest($strongReport['controlTypographyApplied'] === 2, 'The strong control-font report is incorrect.');

$preserveDocument = IPSViewDocument::fromJson($json);
$beforePreserve = $preserveDocument->toJson();
$preserveDocument->applyThemeWithReport(
    IPSViewTheme::THEME_STANDARD,
    [],
    IPSViewTheme::SCOPE_ALL_CONTROL_DEFAULTS,
    [],
    []
);
$afterPreserve = $preserveDocument->toJson();
assertTest($beforePreserve === $afterPreserve, 'Preserve mode changed typography or form-language values.');

$preview = IPSViewThemePreview::createDataUri(
    IPSViewTheme::preset(IPSViewTheme::THEME_COOL),
    [],
    [
        'typographyStyle' => IPSViewTypography::STYLE_LARGE,
        'fontFamilyMode'  => IPSViewTypography::FONT_ROBOTO_MONO,
        'cornerStyle'     => IPSViewShape::CORNER_STRONG,
        'borderStyle'     => IPSViewShape::BORDER_STRONG,
    ]
);
$svg = base64_decode(substr($preview, strlen('data:image/svg+xml;base64,')), true);

assertTest(is_string($svg), 'The appearance preview could not be decoded.');
assertTest(str_contains($svg, 'font-family="RobotoMono, Arial, sans-serif"'), 'The preview does not show the selected font family.');
assertTest(str_contains($svg, 'rx="18"'), 'The preview does not show the strongly rounded corners.');
assertTest(str_contains($svg, 'stroke-width="3.0"'), 'The preview does not show the strong border.');

$extracted = $matchingDocument->extractAppearance();
assertTest($extracted['fontFamily'] === 'RobotoMono', 'The font family could not be extracted.');
assertTest($extracted['baseFontSize'] === 14, 'The base font size could not be extracted.');
assertTest($extracted['cornerRadius'] === 10, 'The corner radius could not be extracted.');
assertTest(abs($extracted['borderWidth'] - 3.0) < 0.001, 'The border width could not be extracted.');

echo "IPSView typography and form-language tests passed.\n";

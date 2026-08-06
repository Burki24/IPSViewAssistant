<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewThemePreview.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewEffects;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$document = IPSViewDocument::fromTemplate($templatePath);
$document->configure('Effects', 13001, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$report = $document->applyThemeWithReport(
    IPSViewTheme::THEME_DARK,
    [],
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    [
        'shadowStyle'         => IPSViewEffects::SHADOW_STRONG,
        'transparencyMode'    => IPSViewEffects::TRANSPARENCY_CUSTOM,
        'transparencyPercent' => 25,
        'gradientStyle'       => IPSViewEffects::GRADIENT_STRONG,
        'gradientDirection'   => IPSViewEffects::GRADIENT_TO_DARKER,
    ]
);
$copy = $document->copy();

assertTest($report['globalEffectsApplied'] > 20, 'General effects were not applied to the global fill colors.');
assertTest($report['controlEffectsApplied'] === 0, 'Global-only effects unexpectedly changed control backgrounds.');
assertTest($report['shadowChanged'] === true, 'The shadow change was not reported.');
assertTest($copy->ShadowSpreadRadius === 4, 'The strong shadow spread is incorrect.');
assertTest($copy->ShadowBlurRadius === 14, 'The strong shadow blur is incorrect.');
assertTest($copy->ShadowOffsetX === 7 && $copy->ShadowOffsetY === 7, 'The strong shadow offset is incorrect.');
assertTest($copy->ShadowColor->A === 190, 'The strong shadow opacity is incorrect.');
assertTest($copy->ColorBack->A === 191, 'The selected surface transparency is incorrect.');
assertTest($copy->ColorText->A === 255, 'General transparency must not change text opacity.');
assertTest($copy->ColorBack->Type === 1, 'A solid surface color was not converted to a gradient.');
assertTest(
    IPSViewTheme::colorObjectToHex($copy->ColorBack)
        !== sprintf('#%02X%02X%02X', $copy->ColorBack->R2, $copy->ColorBack->G2, $copy->ColorBack->B2),
    'The generated gradient does not contain a distinct secondary color.'
);

$noEffectsDocument = IPSViewDocument::fromTemplate($templatePath);
$noEffectsDocument->configure('No Effects', 13002, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$before = $noEffectsDocument->copy();
$noEffectsDocument->applyThemeWithReport(
    IPSViewTheme::THEME_STANDARD,
    [],
    IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
    [
        'shadowStyle'      => IPSViewEffects::SHADOW_NONE,
        'transparencyMode' => IPSViewEffects::TRANSPARENCY_OPAQUE,
        'gradientStyle'    => IPSViewEffects::GRADIENT_NONE,
    ]
);
$after = $noEffectsDocument->copy();

assertTest($after->ShadowBlurRadius === 0, 'Disabling shadows did not reset the blur radius.');
assertTest($after->ShadowColor->A === 0, 'Disabling shadows did not make the shadow transparent.');
assertTest($after->ColorBack->A === 255, 'Opaque mode did not reset the surface alpha value.');
assertTest($after->ColorBackOn->Type === 0, 'Disabling gradients did not flatten an existing gradient.');
assertTest($after->ColorText->A === $before->ColorText->A, 'Disabling fill effects changed text opacity.');

$lightGradient = IPSViewEffects::gradientColor(
    '#204060',
    [
        'gradientStyle'     => IPSViewEffects::GRADIENT_MEDIUM,
        'gradientDirection' => IPSViewEffects::GRADIENT_TO_LIGHTER,
    ]
);
assertTest($lightGradient !== '#204060', 'The lighter gradient direction did not derive a secondary shade.');

$preview = IPSViewThemePreview::createDataUri(
    IPSViewTheme::preset(IPSViewTheme::THEME_WATER),
    [
        'shadowStyle'         => IPSViewEffects::SHADOW_SUBTLE,
        'transparencyMode'    => IPSViewEffects::TRANSPARENCY_CUSTOM,
        'transparencyPercent' => 20,
        'gradientStyle'       => IPSViewEffects::GRADIENT_SUBTLE,
        'gradientDirection'   => IPSViewEffects::GRADIENT_TO_DARKER,
    ]
);
$svg = base64_decode(substr($preview, strlen('data:image/svg+xml;base64,')), true);

assertTest(is_string($svg), 'The effect preview could not be decoded.');
assertTest(str_contains($svg, 'linearGradient'), 'The preview does not show the selected gradients.');
assertTest(str_contains($svg, 'url(#checker)'), 'The preview does not visualize transparency.');
assertTest(str_contains($svg, 'flood-opacity="0.22"'), 'The preview does not show the selected shadow strength.');

echo "IPSView effect tests passed.\n";

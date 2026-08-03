<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';
require_once dirname(__DIR__) . '/libs/IPSViewThemePreview.php';

use Burki24\IPSViewAssistant\IPSViewBackground;
use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;

$png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$document = IPSViewDocument::fromTemplate($templatePath);
$document->configure('Background Test', 12345, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');

$document->applyTheme(
    IPSViewTheme::THEME_STANDARD,
    [],
    [],
    [],
    [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_TILE,
        'imageData' => $png,
    ]
);
$copy = $document->copy();

assertTest(count($copy->Images) === 1, 'The background image was not embedded.');
assertTest($copy->Images[0]->ImageType === -1, 'The IPSView image type is incorrect.');
assertTest($copy->Images[0]->Width === 1 && $copy->Images[0]->Height === 1, 'The image dimensions are incorrect.');
assertTest($copy->Pages[0]->BackgroundImage === $copy->Images[0]->ImageHash, 'The main page does not reference the embedded image.');
assertTest($copy->Pages[0]->BackgroundLayout === 'Tile', 'The selected image layout was not applied.');

$document->applyTheme(
    IPSViewTheme::THEME_STANDARD,
    [],
    [],
    [],
    [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_CENTER,
        'imageData' => 'data:image/png;base64,' . $png,
    ]
);
$reused = $document->copy();
assertTest(count($reused->Images) === 1, 'An identical image was embedded more than once.');
assertTest($reused->Pages[0]->BackgroundLayout === 'Center', 'The centered layout was not applied.');

$document->applyTheme(
    IPSViewTheme::THEME_STANDARD,
    [],
    [],
    [],
    ['mode' => IPSViewBackground::MODE_REMOVE]
);
$removed = $document->copy();
assertTest($removed->Pages[0]->BackgroundImage === 0, 'The main-page background was not removed.');
assertTest($removed->Pages[0]->BackgroundLayout === '', 'The removed background retained a layout.');
assertTest(count($removed->Images) === 1, 'Removing the background deleted a potentially shared image.');

$svg = IPSViewThemePreview::createSvg(
    IPSViewTheme::preset(IPSViewTheme::THEME_STANDARD),
    [],
    [],
    [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'imageData' => $png,
    ]
);
assertTest(str_contains($svg, 'data:image/png;base64,'), 'The background image is missing from the SVG preview.');
assertTest(str_contains($svg, 'preserveAspectRatio="none"'), 'The stretch layout is missing from the SVG preview.');

try {
    IPSViewBackground::preview([
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'imageData' => base64_encode('not an image'),
    ]);
    failTest('Invalid image data was accepted.');
} catch (InvalidArgumentException) {
}

echo "Background image tests passed.\n";

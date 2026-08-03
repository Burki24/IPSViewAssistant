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
$seed = $document->copy();
$secondPage = json_decode(json_encode($seed->Pages[0], JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
$thirdPage = json_decode(json_encode($seed->Pages[0], JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
$secondPage->PageName = 'Second';
$secondPage->BackgroundImage = 777;
$secondPage->BackgroundLayout = 'Center';
$thirdPage->PageName = 'Third';
$thirdPage->BackgroundImage = 888;
$thirdPage->BackgroundLayout = 'Stretch';
$seed->Pages[] = $secondPage;
$seed->Pages[] = $thirdPage;
$document = IPSViewDocument::fromJson(json_encode($seed, JSON_THROW_ON_ERROR));

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
assertTest($copy->Pages[1]->BackgroundImage === 777, 'The main-page scope changed another page.');
assertTest($copy->Pages[2]->BackgroundImage === 888, 'The main-page scope changed the third page.');

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
    [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'scope'     => IPSViewBackground::SCOPE_ALL_PAGES,
        'imageData' => $png,
    ]
);
$allPages = $document->copy();
foreach ($allPages->Pages as $page) {
    assertTest(
        $page->BackgroundImage === $allPages->Images[0]->ImageHash,
        'The all-pages scope did not assign the image to every page.'
    );
    assertTest($page->BackgroundLayout === 'Stretch', 'The all-pages scope did not apply the layout to every page.');
}
assertTest(count($allPages->Images) === 1, 'The all-pages scope embedded an image more than once.');

$document->applyTheme(
    IPSViewTheme::THEME_STANDARD,
    [],
    [],
    [],
    [
        'mode'  => IPSViewBackground::MODE_REMOVE,
        'scope' => IPSViewBackground::SCOPE_ALL_PAGES,
    ]
);
$removed = $document->copy();
foreach ($removed->Pages as $page) {
    assertTest($page->BackgroundImage === 0, 'The all-pages removal left a background image assigned.');
    assertTest($page->BackgroundLayout === '', 'The all-pages removal left a background layout assigned.');
}
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

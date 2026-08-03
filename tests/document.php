<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$cases = [
    [IPSViewDocument::ASPECT_RATIO_SQUARE, IPSViewDocument::ORIENTATION_LANDSCAPE, 1000, 1000, '1:1'],
    [IPSViewDocument::ASPECT_RATIO_SQUARE, IPSViewDocument::ORIENTATION_PORTRAIT, 1000, 1000, '1:1'],
    [IPSViewDocument::ASPECT_RATIO_4_3, IPSViewDocument::ORIENTATION_LANDSCAPE, 1024, 768, '4:3'],
    [IPSViewDocument::ASPECT_RATIO_4_3, IPSViewDocument::ORIENTATION_PORTRAIT, 768, 1024, '4:3'],
    [IPSViewDocument::ASPECT_RATIO_8_5, IPSViewDocument::ORIENTATION_LANDSCAPE, 1280, 800, '8:5'],
    [IPSViewDocument::ASPECT_RATIO_8_5, IPSViewDocument::ORIENTATION_PORTRAIT, 800, 1280, '8:5'],
    [IPSViewDocument::ASPECT_RATIO_9_5, IPSViewDocument::ORIENTATION_LANDSCAPE, 1440, 800, '9:5'],
    [IPSViewDocument::ASPECT_RATIO_9_5, IPSViewDocument::ORIENTATION_PORTRAIT, 800, 1440, '9:5'],
    [IPSViewDocument::ASPECT_RATIO_13_6, IPSViewDocument::ORIENTATION_LANDSCAPE, 1300, 600, '13:6'],
    [IPSViewDocument::ASPECT_RATIO_13_6, IPSViewDocument::ORIENTATION_PORTRAIT, 600, 1300, '13:6'],
    [IPSViewDocument::ASPECT_RATIO_16_9, IPSViewDocument::ORIENTATION_LANDSCAPE, 1360, 765, '16:9'],
    [IPSViewDocument::ASPECT_RATIO_16_9, IPSViewDocument::ORIENTATION_PORTRAIT, 765, 1360, '16:9'],
    [IPSViewDocument::ASPECT_RATIO_2_1, IPSViewDocument::ORIENTATION_LANDSCAPE, 1360, 680, '2:1'],
    [IPSViewDocument::ASPECT_RATIO_2_1, IPSViewDocument::ORIENTATION_PORTRAIT, 680, 1360, '2:1'],
];

foreach ($cases as [$ratio, $orientation, $expectedWidth, $expectedHeight, $ratioLabel]) {
    $document = IPSViewDocument::fromTemplate($templatePath);
    $document->configure('Test View', 12345, $ratio, $orientation, 'Startseite', true);
    $copy = $document->copy();
    $json = $document->toJson();

    assertTest($copy->Name === 'Test View', 'The View name was not applied.');
    assertTest($copy->ID === 12345, 'The media ID was not applied.');
    assertTest($copy->Width === $expectedWidth, 'The View width is incorrect.');
    assertTest($copy->Height === $expectedHeight, 'The View height is incorrect.');
    assertTest($copy->HardwareWidth === $expectedWidth, 'The hardware width is incorrect.');
    assertTest($copy->HardwareHeight === $expectedHeight, 'The hardware height is incorrect.');
    assertTest($copy->FullScreen === true, 'The full-screen setting was not applied.');
    assertTest(str_contains($copy->Hardware, '(' . $ratioLabel . ')'), 'The hardware ratio label is incorrect.');
    assertTest($copy->Pages[0]->PageName === 'Startseite', 'The main page name was not applied.');
    assertTest($copy->Pages[0]->Controls === [], 'The empty View unexpectedly contains controls.');
    assertTest($copy->Pages[0]->GridEnabled === false, 'The default start grid must be disabled.');
    assertTest($copy->Pages[0]->GridCellWidth === '', 'The disabled start grid must not define a cell width.');
    assertTest($copy->LicenseKey === '', 'The template contains a license key.');
    assertTest($copy->LicenseRegister === '', 'The template contains a registered user.');
    assertTest($copy->UsedIDs instanceof stdClass, 'UsedIDs must remain a JSON object.');
    assertTest($copy->GroupIDs instanceof stdClass, 'GroupIDs must remain a JSON object.');
    assertTest(str_contains($json, '"UsedIDs":{}'), 'UsedIDs was serialized as an array.');
    assertTest(str_contains($json, '"GroupIDs":{}'), 'GroupIDs was serialized as an array.');
}

$gridCases = [
    [IPSViewDocument::START_GRID_TWO_COLUMNS, '50%'],
    [IPSViewDocument::START_GRID_THREE_COLUMNS, '33.333333%'],
];

foreach ($gridCases as [$startGrid, $expectedCellWidth]) {
    $document = IPSViewDocument::fromTemplate($templatePath);
    $document->configure(
        'Grid View',
        12346,
        IPSViewDocument::ASPECT_RATIO_16_9,
        IPSViewDocument::ORIENTATION_LANDSCAPE,
        'Startseite',
        false,
        $startGrid
    );
    $copy = $document->copy();

    assertTest(count($copy->Pages) === 2, 'The start grid did not create one content page.');
    assertTest($copy->Pages[0]->GridEnabled === false, 'The unsupported main-page grid must remain disabled.');
    assertTest($copy->Pages[0]->GridCellWidth === '', 'The main page must not define a grid cell width.');
    assertTest(count($copy->Pages[0]->Controls) === 1, 'The main page must contain one page container.');
    assertTest(
        $copy->Pages[0]->Controls[0]->Type === 'IPSInlinePage',
        'The start grid does not use an IPSView page container.'
    );
    assertTest(
        $copy->Pages[0]->Controls[0]->Text1 === $copy->Pages[1]->PageName,
        'The page container does not reference the generated content page.'
    );
    assertTest($copy->Pages[0]->Controls[0]->Digits === 0, 'The page container has an incorrect container type.');
    assertTest($copy->Pages[0]->Controls[0]->Width === 1360, 'The page container does not fill the View width.');
    assertTest($copy->Pages[0]->Controls[0]->Height === 765, 'The page container does not fill the View height.');
    assertTest($copy->Pages[1]->IsMainPage === false, 'The grid content was incorrectly marked as a main page.');
    assertTest($copy->Pages[1]->IsInline === true, 'The grid content is not an embeddable standard page.');
    assertTest($copy->Pages[1]->GridEnabled === true, 'The selected start grid was not enabled.');
    assertTest(
        $copy->Pages[1]->GridCellWidth === $expectedCellWidth,
        'The selected start grid uses an incorrect relative cell width.'
    );
    assertTest($copy->Pages[1]->Controls === [], 'The start grid must not create placeholder controls.');
    assertTest($document->getControlCount() === 1, 'The start grid created controls beyond its page container.');
}

$invalidGridRejected = false;
try {
    $document = IPSViewDocument::fromTemplate($templatePath);
    $document->configure(
        'Invalid Grid',
        12347,
        IPSViewDocument::ASPECT_RATIO_16_9,
        IPSViewDocument::ORIENTATION_LANDSCAPE,
        'Startseite',
        false,
        99
    );
} catch (InvalidArgumentException) {
    $invalidGridRejected = true;
}
assertTest($invalidGridRejected, 'An unsupported start grid was accepted.');

echo "IPSView document tests passed.\n";

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
    [IPSViewDocument::ASPECT_RATIO_16_9, IPSViewDocument::ORIENTATION_LANDSCAPE, 1360, 765, '16:9'],
    [IPSViewDocument::ASPECT_RATIO_16_9, IPSViewDocument::ORIENTATION_PORTRAIT, 765, 1360, '16:9'],
];

foreach ($cases as [$ratio, $orientation, $expectedWidth, $expectedHeight, $ratioLabel]) {
    $document = IPSViewDocument::fromTemplate($templatePath);
    $document->configure('Test View', 12345, $ratio, $orientation, 'Startseite');
    $copy = $document->copy();
    $json = $document->toJson();

    assertTest($copy->Name === 'Test View', 'The View name was not applied.');
    assertTest($copy->ID === 12345, 'The media ID was not applied.');
    assertTest($copy->Width === $expectedWidth, 'The View width is incorrect.');
    assertTest($copy->Height === $expectedHeight, 'The View height is incorrect.');
    assertTest($copy->HardwareWidth === $expectedWidth, 'The hardware width is incorrect.');
    assertTest($copy->HardwareHeight === $expectedHeight, 'The hardware height is incorrect.');
    assertTest(str_contains($copy->Hardware, '(' . $ratioLabel . ')'), 'The hardware ratio label is incorrect.');
    assertTest($copy->Pages[0]->PageName === 'Startseite', 'The main page name was not applied.');
    assertTest($copy->Pages[0]->Controls === [], 'The empty View unexpectedly contains controls.');
    assertTest($copy->LicenseKey === '', 'The template contains a license key.');
    assertTest($copy->LicenseRegister === '', 'The template contains a registered user.');
    assertTest($copy->UsedIDs instanceof stdClass, 'UsedIDs must remain a JSON object.');
    assertTest($copy->GroupIDs instanceof stdClass, 'GroupIDs must remain a JSON object.');
    assertTest(str_contains($json, '"UsedIDs":{}'), 'UsedIDs was serialized as an array.');
    assertTest(str_contains($json, '"GroupIDs":{}'), 'GroupIDs was serialized as an array.');
}

echo "IPSView document tests passed.\n";

<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewTheme;

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$source = IPSViewDocument::fromTemplate($templatePath);
$source->configure('Existing View', 12001, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$sourceData = $source->copy();
$sourceData->LicenseRegister = 'Local Test User';
$sourceData->UnknownFutureField = (object) ['Enabled' => true];
$sourceData->Pages[0]->Controls = [
    (object) [
        'Type'     => 'IPSTxtLabel',
        'Name'     => 'Title',
        'Controls' => [
            (object) [
                'Type' => 'IPSTxtLabel',
                'Name' => 'Nested',
            ],
        ],
    ],
];

$document = IPSViewDocument::fromJson(
    json_encode($sourceData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
);
$palette = $document->extractThemePalette();

assertTest($document->getPageCount() === 1, 'The existing View page count is incorrect.');
assertTest($document->getControlCount() === 2, 'Nested existing View controls are not counted correctly.');
assertTest(
    $palette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#404040',
    'The existing View background is not extracted correctly.'
);
assertTest(
    $palette[IPSViewTheme::ROLE_ACCENT] === '#007AFF',
    'The existing View accent color is not extracted correctly.'
);

$document->prepareCopy('Existing View - Design Copy', 12002);
$document->applyTheme(
    IPSViewTheme::THEME_CUSTOM,
    [
        IPSViewTheme::ROLE_VIEW_BACKGROUND => '#102030',
        IPSViewTheme::ROLE_ACCENT          => '#ABCDEF',
    ]
);
$copy = $document->copy();

assertTest($copy->Name === 'Existing View - Design Copy', 'The copied View name was not updated.');
assertTest($copy->ID === 12002, 'The copied View media ID was not updated.');
assertTest($copy->LicenseRegister === 'Local Test User', 'Local license data was not preserved in the local copy.');
assertTest(
    ($copy->UnknownFutureField->Enabled ?? false) === true,
    'An unknown IPSView field was lost while creating a copy.'
);
assertTest(count($copy->Pages[0]->Controls ?? []) === 1, 'Existing controls were removed from the copied View.');
assertTest(
    IPSViewTheme::colorObjectToHex($copy->ColorPage) === '#102030',
    'The custom View background was not applied to the copied View.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($copy->SwitchTrackColorActive) === '#ABCDEF',
    'The custom accent was not applied to the copied View.'
);

$copyFactorySource = (string) file_get_contents(dirname(__DIR__) . '/libs/IPSViewCopyFactory.php');
assertTest(
    str_contains($copyFactorySource, 'IPS_GetMediaContent('),
    'The copy factory does not read the selected media content.'
);
assertTest(
    str_contains($copyFactorySource, 'base64_decode('),
    'The copy factory does not decode the selected media content.'
);
assertTest(
    str_contains($copyFactorySource, 'private const IPSVIEW_MEDIA_TYPE = 0;'),
    'The copy factory does not define the documented IPSView media type.'
);
assertTest(
    !str_contains($copyFactorySource, 'MEDIATYPE_DASHBOARD'),
    'The copy factory relies on a media type constant that is not available in every Symcon runtime.'
);
assertTest(
    str_contains($copyFactorySource, 'IPS_CreateMedia(self::IPSVIEW_MEDIA_TYPE)'),
    'The copy factory does not create an IPSView using its runtime-safe media type.'
);
assertTest(
    str_contains($copyFactorySource, 'prepareCopy('),
    'The copy factory does not prepare a separate media document.'
);

try {
    IPSViewDocument::fromJson('[]');
    failTest('A JSON array was accepted as an IPSView document.');
} catch (RuntimeException) {
}

echo "Existing IPSView tests passed.\n";

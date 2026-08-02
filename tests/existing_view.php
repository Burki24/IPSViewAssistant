<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewTheme;

/**
 * Creates a detached copy of one IPSView color object.
 */
function cloneIpsViewColor(stdClass $color): stdClass
{
    $copy = json_decode(
        json_encode($color, JSON_THROW_ON_ERROR),
        false,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!$copy instanceof stdClass) {
        failTest('An IPSView color object could not be copied.');
    }

    return $copy;
}

$templatePath = dirname(__DIR__) . '/libs/templates/empty-view.json';
$source = IPSViewDocument::fromTemplate($templatePath);
$source->configure('Existing View', 12001, IPSViewDocument::ASPECT_RATIO_16_9, 0, 'Main');
$sourceData = $source->copy();
$sourceData->LicenseRegister = 'Local Test User';
$sourceData->UnknownFutureField = (object) ['Enabled' => true];
$sourceData->Pages[0]->Controls = [
    (object) [
        'Type'         => 'IPSTxtLabel',
        'Name'         => 'Title',
        'BackColor1'   => cloneIpsViewColor($sourceData->ColorBack),
        'BackColor2'   => cloneIpsViewColor($sourceData->ColorBackOn),
        'ForeColor1'   => cloneIpsViewColor($sourceData->ColorText),
        'BorderColor1' => cloneIpsViewColor($sourceData->ColorBorder),
        'Controls'     => [
            (object) [
                'Type' => 'IPSTxtLabel',
                'Name' => 'Nested',
            ],
        ],
    ],
    (object) [
        'Type'         => 'IPSValueButton',
        'Name'         => 'Individual',
        'BackColor1'   => (object) ['R' => 18, 'G' => 52, 'B' => 86, 'Type' => 0],
        'BackColor2'   => (object) ['R' => 101, 'G' => 67, 'B' => 33, 'Type' => 0],
        'ForeColor1'   => (object) ['R' => 171, 'G' => 205, 'B' => 239, 'Type' => 0],
        'BorderColor1' => (object) ['R' => 34, 'G' => 68, 'B' => 102, 'Type' => 0],
    ],
    (object) [
        'Type'         => 'IPSVarLabel',
        'Name'         => 'Associations',
        'Associations' => [
            (object) [
                'BackColor' => cloneIpsViewColor($sourceData->ScheduleNowIndicatorColor),
                'ForeColor' => (object) ['R' => 17, 'G' => 34, 'B' => 51, 'Type' => 0],
            ],
        ],
    ],
];

$document = IPSViewDocument::fromJson(
    json_encode($sourceData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
);
$palette = $document->extractThemePalette();

assertTest($document->getPageCount() === 1, 'The existing View page count is incorrect.');
assertTest($document->getControlCount() === 4, 'Nested existing View controls are not counted correctly.');
assertTest(
    $palette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#404040',
    'The existing View background is not extracted correctly.'
);
assertTest(
    $palette[IPSViewTheme::ROLE_ACCENT] === '#007AFF',
    'The existing View accent color is not extracted correctly.'
);

$analysis = $document->analyzeThemeColors();
assertTest($analysis['globalColors'] === 107, 'The global IPSView color count is incorrect.');
assertTest($analysis['controlColorsTotal'] === 10, 'The direct control color count is incorrect.');
assertTest($analysis['matchingControlColors'] === 5, 'Matching control colors are not detected correctly.');
assertTest($analysis['allControlDefaults'] === 9, 'Basic control colors are not detected correctly.');
assertTest($analysis['individualControlColors'] === 5, 'Individual control colors are not counted correctly.');
assertTest($analysis['specialControlColors'] === 1, 'Special control colors are not protected correctly.');

$matchingDocument = IPSViewDocument::fromJson($document->toJson());
$matchingReport = $matchingDocument->applyThemeWithReport(
    IPSViewTheme::THEME_DARK,
    [],
    IPSViewTheme::SCOPE_MATCHING_CONTROLS
);
$matchingCopy = $matchingDocument->copy();

assertTest($matchingReport['controlColorsApplied'] === 5, 'The recommended design scope applied an incorrect number of colors.');
assertTest($matchingReport['controlColorsPreserved'] === 5, 'The recommended design scope did not preserve individual colors.');
assertTest(
    IPSViewTheme::colorObjectToHex($matchingCopy->Pages[0]->Controls[0]->BackColor1) === '#273449',
    'A matching control background was not assigned to the surface role.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($matchingCopy->Pages[0]->Controls[0]->BorderColor1) === '#475569',
    'A matching control border was not assigned to the border role.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($matchingCopy->Pages[0]->Controls[1]->BackColor1) === '#123456',
    'An individual control background was changed by the recommended design scope.'
);
assertTest(
    IPSViewTheme::colorObjectToHex(
        $matchingCopy->Pages[0]->Controls[2]->Associations[0]->BackColor
    ) === '#F59E0B',
    'A matching association warning color was not retained semantically.'
);
assertTest(
    IPSViewTheme::colorObjectToHex(
        $matchingCopy->Pages[0]->Controls[2]->Associations[0]->ForeColor
    ) === '#112233',
    'An individual association color was changed unexpectedly.'
);

$allDocument = IPSViewDocument::fromJson($document->toJson());
$allReport = $allDocument->applyThemeWithReport(
    IPSViewTheme::THEME_LIGHT,
    [],
    IPSViewTheme::SCOPE_ALL_CONTROL_DEFAULTS
);
$allCopy = $allDocument->copy();

assertTest($allReport['controlColorsApplied'] === 9, 'The strong design scope applied an incorrect number of colors.');
assertTest($allReport['controlColorsPreserved'] === 1, 'The strong design scope did not protect special colors.');
assertTest(
    IPSViewTheme::colorObjectToHex($allCopy->Pages[0]->Controls[1]->BackColor1) === '#FFFFFF',
    'The strong design scope did not standardize an individual control background.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($allCopy->Pages[0]->Controls[1]->BackColor2) === '#16A34A',
    'The strong design scope did not standardize a second control background.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($allCopy->Pages[0]->Controls[1]->ForeColor1) === '#1F2937',
    'The strong design scope did not standardize an individual control text color.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($allCopy->Pages[0]->Controls[1]->BorderColor1) === '#D0D5DD',
    'The strong design scope did not standardize an individual control border.'
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
assertTest(count($copy->Pages[0]->Controls ?? []) === 3, 'Existing controls were removed from the copied View.');
assertTest(
    IPSViewTheme::colorObjectToHex($copy->ColorPage) === '#102030',
    'The custom View background was not applied to the copied View.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($copy->SwitchTrackColorActive) === '#ABCDEF',
    'The custom accent was not applied to the copied View.'
);

$copy->Pages[0]->Controls[0]->ChangedLaterInDesigner = true;
$updatedDocument = IPSViewDocument::fromJson(
    json_encode($copy, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
);
$updatedDocument->applyTheme(
    IPSViewTheme::THEME_CUSTOM,
    [IPSViewTheme::ROLE_VIEW_BACKGROUND => '#304050']
);
$updatedCopy = $updatedDocument->copy();

assertTest(
    ($updatedCopy->Pages[0]->Controls[0]->ChangedLaterInDesigner ?? false) === true,
    'A later IPSView Designer change was lost during an in-place design update.'
);
assertTest(
    $updatedCopy->ID === 12002,
    'An in-place design update changed the existing copy media ID.'
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
assertTest(
    str_contains($copyFactorySource, 'public function update('),
    'The copy factory cannot update an existing design copy.'
);
assertTest(
    str_contains($copyFactorySource, 'public function findExistingTarget('),
    'The copy factory cannot resolve an existing same-name IPSView target.'
);
assertTest(
    str_contains($copyFactorySource, '$document = $this->loadDocument($targetMediaID);'),
    'The copy factory does not reload the current target before updating its design.'
);

try {
    IPSViewDocument::fromJson('[]');
    failTest('A JSON array was accepted as an IPSView document.');
} catch (RuntimeException) {
}

echo "Existing IPSView tests passed.\n";

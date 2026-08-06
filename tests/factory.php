<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';
require_once dirname(__DIR__) . '/libs/IPSViewEffects.php';
require_once dirname(__DIR__) . '/libs/IPSViewTypography.php';
require_once dirname(__DIR__) . '/libs/IPSViewShape.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';
require_once dirname(__DIR__) . '/libs/IPSViewStartCheck.php';
require_once dirname(__DIR__) . '/libs/IPSViewFactory.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewFactory;

$GLOBALS['factoryObjects'] = [
    100 => ['ObjectType' => 0, 'ObjectName' => 'Views', 'ParentID' => 0],
];
$GLOBALS['factoryChildren'] = [
    0   => [100],
    100 => [],
];
$GLOBALS['factoryMedia'] = [];
$GLOBALS['factoryNextID'] = 1000;
$GLOBALS['factoryFailNextContentWrite'] = false;
$GLOBALS['factoryMediaEvents'] = [];

function IPS_ObjectExists(int $objectID): bool
{
    return isset($GLOBALS['factoryObjects'][$objectID]);
}

/**
 * @return array<string, mixed>
 */
function IPS_GetObject(int $objectID): array
{
    return $GLOBALS['factoryObjects'][$objectID];
}

/**
 * @return list<int>
 */
function IPS_GetChildrenIDs(int $objectID): array
{
    return $GLOBALS['factoryChildren'][$objectID] ?? [];
}

function IPS_MediaExists(int $mediaID): bool
{
    return isset($GLOBALS['factoryMedia'][$mediaID]);
}

/**
 * @return array<string, mixed>
 */
function IPS_GetMedia(int $mediaID): array
{
    return $GLOBALS['factoryMedia'][$mediaID];
}

/**
 * @return list<string>
 */
function IPS_GetModuleList(): array
{
    return ['{DESIGNER}'];
}

/**
 * @return array<string, mixed>
 */
function IPS_GetModule(string $moduleID): array
{
    return ['ModuleName' => 'IPSView Designer'];
}

function IPS_CreateMedia(int $mediaType): int
{
    $mediaID = $GLOBALS['factoryNextID']++;
    $GLOBALS['factoryObjects'][$mediaID] = [
        'ObjectType' => 5,
        'ObjectName' => '',
        'ParentID'   => 0,
    ];
    $GLOBALS['factoryChildren'][0][] = $mediaID;
    $GLOBALS['factoryMedia'][$mediaID] = [
        'MediaType'    => $mediaType,
        'MediaFile'    => '',
        'MediaContent' => '',
    ];

    return $mediaID;
}

function IPS_SetName(int $objectID, string $name): void
{
    $GLOBALS['factoryObjects'][$objectID]['ObjectName'] = $name;
}

function IPS_SetParent(int $objectID, int $parentID): void
{
    $oldParentID = (int) $GLOBALS['factoryObjects'][$objectID]['ParentID'];
    $GLOBALS['factoryChildren'][$oldParentID] = array_values(array_filter(
        $GLOBALS['factoryChildren'][$oldParentID] ?? [],
        static fn (int $childID): bool => $childID !== $objectID
    ));
    $GLOBALS['factoryObjects'][$objectID]['ParentID'] = $parentID;
    $GLOBALS['factoryChildren'][$parentID][] = $objectID;
}

function IPS_GetKernelDir(): string
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR;
}

function IPS_SetMediaFile(int $mediaID, string $mediaFile, bool $isURL): bool
{
    $GLOBALS['factoryMedia'][$mediaID]['MediaFile'] = $mediaFile;

    return true;
}

function IPS_SetMediaContent(int $mediaID, string $content): bool
{
    if ($GLOBALS['factoryFailNextContentWrite']) {
        $GLOBALS['factoryFailNextContentWrite'] = false;

        return false;
    }

    $GLOBALS['factoryMedia'][$mediaID]['MediaContent'] = $content;

    return true;
}

function IPS_GetMediaContent(int $mediaID): string
{
    return (string) $GLOBALS['factoryMedia'][$mediaID]['MediaContent'];
}

function IPS_SendMediaEvent(int $mediaID): void
{
    $GLOBALS['factoryMediaEvents'][] = $mediaID;
}

function IPS_DeleteMedia(int $mediaID, bool $deleteFile): void
{
    $parentID = (int) ($GLOBALS['factoryObjects'][$mediaID]['ParentID'] ?? 0);
    $GLOBALS['factoryChildren'][$parentID] = array_values(array_filter(
        $GLOBALS['factoryChildren'][$parentID] ?? [],
        static fn (int $childID): bool => $childID !== $mediaID
    ));
    unset($GLOBALS['factoryObjects'][$mediaID], $GLOBALS['factoryMedia'][$mediaID]);
}

$factory = new IPSViewFactory(dirname(__DIR__) . '/libs/templates');
$mediaID = $factory->create(
    'Guided View',
    100,
    IPSViewDocument::ASPECT_RATIO_16_9,
    IPSViewDocument::ORIENTATION_LANDSCAPE,
    IPSViewFactory::TEMPLATE_EMPTY,
    'Main'
);

assertTest($mediaID === 1000, 'The factory did not return the created media ID.');
assertTest(count($GLOBALS['factoryMedia']) === 1, 'The factory created an unexpected number of media objects.');

$duplicateRejected = false;
try {
    $factory->create(
        'Guided View',
        100,
        IPSViewDocument::ASPECT_RATIO_16_9,
        IPSViewDocument::ORIENTATION_LANDSCAPE,
        IPSViewFactory::TEMPLATE_EMPTY,
        'Main'
    );
} catch (InvalidArgumentException) {
    $duplicateRejected = true;
}
assertTest($duplicateRejected, 'A same-name View was overwritten without explicit confirmation.');

$overwrittenID = $factory->create(
    'Guided View',
    100,
    IPSViewDocument::ASPECT_RATIO_4_3,
    IPSViewDocument::ORIENTATION_PORTRAIT,
    IPSViewFactory::TEMPLATE_EMPTY,
    'Replacement',
    startGrid: IPSViewDocument::START_GRID_TWO_COLUMNS,
    overwriteExisting: true
);
$overwrittenJson = base64_decode(IPS_GetMediaContent($mediaID), true);
$overwrittenDocument = is_string($overwrittenJson)
    ? IPSViewDocument::fromJson($overwrittenJson)->copy()
    : null;

assertTest($overwrittenID === $mediaID, 'Overwrite changed the existing View object ID.');
assertTest(count($GLOBALS['factoryMedia']) === 1, 'Overwrite created an additional media object.');
assertTest($overwrittenDocument instanceof stdClass, 'The overwritten View content is unreadable.');
assertTest($overwrittenDocument->ID === $mediaID, 'The overwritten document contains an incorrect media ID.');
assertTest($overwrittenDocument->Pages[0]->PageName === 'Replacement', 'The overwritten View retained its old main page.');

$contentBeforeFailedOverwrite = IPS_GetMediaContent($mediaID);
$GLOBALS['factoryFailNextContentWrite'] = true;
$overwriteFailed = false;
try {
    $factory->create(
        'Guided View',
        100,
        IPSViewDocument::ASPECT_RATIO_SQUARE,
        IPSViewDocument::ORIENTATION_LANDSCAPE,
        IPSViewFactory::TEMPLATE_EMPTY,
        'Failed replacement',
        overwriteExisting: true
    );
} catch (RuntimeException) {
    $overwriteFailed = true;
}

assertTest($overwriteFailed, 'A failed overwrite was reported as successful.');
assertTest(
    IPS_GetMediaContent($mediaID) === $contentBeforeFailedOverwrite,
    'A failed overwrite did not retain the previous View content.'
);

echo "IPSView factory tests passed.\n";

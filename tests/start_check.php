<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewBackground.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';
require_once dirname(__DIR__) . '/libs/IPSViewStartCheck.php';

use Burki24\IPSViewAssistant\IPSViewBackground;
use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewStartCheck;

$GLOBALS['startCheckObjects'] = [
    100 => ['ObjectType' => 0, 'ObjectName' => 'Views'],
    200 => ['ObjectType' => 1, 'ObjectName' => 'Not a category'],
    300 => ['ObjectType' => 5, 'ObjectName' => 'Existing View'],
    400 => ['ObjectType' => 1, 'ObjectName' => 'Conflicting Target'],
    500 => ['ObjectType' => 5, 'ObjectName' => 'Wrong Media'],
];
$GLOBALS['startCheckChildren'] = [
    0   => [],
    100 => [300, 400, 500],
];
$GLOBALS['startCheckMedia'] = [
    300 => ['MediaType' => 0],
    500 => ['MediaType' => 1],
];
$GLOBALS['startCheckModules'] = [
    '{DESIGNER}' => ['ModuleName' => 'IPSView Designer'],
];

function IPS_ObjectExists(int $objectID): bool
{
    return isset($GLOBALS['startCheckObjects'][$objectID]);
}

/**
 * @return array<string, mixed>
 */
function IPS_GetObject(int $objectID): array
{
    return $GLOBALS['startCheckObjects'][$objectID];
}

/**
 * @return list<int>
 */
function IPS_GetChildrenIDs(int $objectID): array
{
    return $GLOBALS['startCheckChildren'][$objectID] ?? [];
}

function IPS_MediaExists(int $mediaID): bool
{
    return isset($GLOBALS['startCheckMedia'][$mediaID]);
}

/**
 * @return array<string, mixed>
 */
function IPS_GetMedia(int $mediaID): array
{
    return $GLOBALS['startCheckMedia'][$mediaID];
}

/**
 * @return list<string>
 */
function IPS_GetModuleList(): array
{
    return array_keys($GLOBALS['startCheckModules']);
}

/**
 * @return array<string, mixed>
 */
function IPS_GetModule(string $moduleID): array
{
    return $GLOBALS['startCheckModules'][$moduleID];
}

/**
 * @param array<string, mixed> $background
 *
 * @return array{status: int, ready: bool, checks: list<string>, warnings: list<string>, errors: list<string>}
 */
function analyzeStartCheck(
    string $viewName = 'New View',
    int $targetCategoryID = 100,
    string $mainPageName = 'Main',
    int $aspectRatio = IPSViewDocument::ASPECT_RATIO_16_9,
    int $orientation = IPSViewDocument::ORIENTATION_LANDSCAPE,
    int $template = 0,
    int $startGrid = IPSViewDocument::START_GRID_NONE,
    array $background = [],
    bool $overwriteExisting = false
): array {
    return IPSViewStartCheck::analyze(
        $viewName,
        $targetCategoryID,
        $mainPageName,
        $aspectRatio,
        $orientation,
        $template,
        $startGrid,
        $background,
        $overwriteExisting
    );
}

$ready = analyzeStartCheck();
assertTest($ready['ready'], 'A valid View configuration was not reported as ready.');
assertTest($ready['status'] === IPSViewStartCheck::STATUS_READY, 'A valid start check is not green.');
assertTest($ready['errors'] === [], 'A valid start check contains errors.');
assertTest(
    in_array('IPSView Designer was detected.', $ready['checks'], true),
    'The installed IPSView Designer was not detected.'
);

$duplicate = analyzeStartCheck('Existing View');
assertTest(!$duplicate['ready'], 'A duplicate View name was not rejected.');
assertTest($duplicate['status'] === IPSViewStartCheck::STATUS_ERROR, 'A duplicate View name is not red.');
assertTest($duplicate['overwriteAvailable'], 'A safe same-name IPSView cannot be selected for overwrite.');
assertTest(
    in_array('A View with this name already exists in the target category. Enable overwrite to replace it.', $duplicate['errors'], true),
    'The duplicate-name error is missing.'
);

$overwrite = analyzeStartCheck('Existing View', overwriteExisting: true);
assertTest($overwrite['ready'], 'An explicitly confirmed safe overwrite is still blocked.');
assertTest(
    $overwrite['status'] === IPSViewStartCheck::STATUS_WARNING,
    'An explicitly confirmed overwrite does not produce a warning.'
);
assertTest(
    in_array('The existing View will be overwritten completely while its object ID is retained.', $overwrite['warnings'], true),
    'The destructive overwrite warning is missing.'
);

$conflictingTarget = analyzeStartCheck('Conflicting Target', overwriteExisting: true);
assertTest(!$conflictingTarget['ready'], 'A same-name non-IPSView object was accepted for overwrite.');
assertTest(!$conflictingTarget['overwriteAvailable'], 'Overwrite was offered for a non-IPSView target.');

$wrongMedia = analyzeStartCheck('Wrong Media', overwriteExisting: true);
assertTest(!$wrongMedia['ready'], 'A same-name non-IPSView medium was accepted for overwrite.');
assertTest(!$wrongMedia['overwriteAvailable'], 'Overwrite was offered for a non-IPSView medium.');

$GLOBALS['startCheckObjects'][301] = ['ObjectType' => 5, 'ObjectName' => 'Existing View'];
$GLOBALS['startCheckMedia'][301] = ['MediaType' => 0];
$GLOBALS['startCheckChildren'][100][] = 301;
$ambiguousTarget = analyzeStartCheck('Existing View', overwriteExisting: true);
assertTest(!$ambiguousTarget['ready'], 'Multiple same-name IPSViews were accepted for overwrite.');
assertTest(!$ambiguousTarget['overwriteAvailable'], 'Overwrite was offered for multiple same-name IPSViews.');
unset($GLOBALS['startCheckObjects'][301], $GLOBALS['startCheckMedia'][301]);
$GLOBALS['startCheckChildren'][100] = [300, 400, 500];

$invalid = analyzeStartCheck('', 200, '', 99, 99, 1, 99);
assertTest(count($invalid['errors']) === 7, 'The start check did not collect all invalid basic settings.');
assertTest(!$invalid['ready'], 'Invalid basic settings were reported as ready.');

$missingTarget = analyzeStartCheck('New View', 999);
assertTest(
    in_array('The selected target category does not exist.', $missingTarget['errors'], true),
    'A missing target category was not reported.'
);

$missingImage = analyzeStartCheck(
    background: [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'scope'     => IPSViewBackground::SCOPE_MAIN_PAGE,
        'imageData' => '',
    ]
);
assertTest(!$missingImage['ready'], 'A selected but missing background image did not block creation.');

$png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
$gridBackground = analyzeStartCheck(
    startGrid: IPSViewDocument::START_GRID_TWO_COLUMNS,
    background: [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'scope'     => IPSViewBackground::SCOPE_MAIN_PAGE,
        'imageData' => $png,
    ]
);
assertTest($gridBackground['ready'], 'A valid main-page background warning blocked creation.');
assertTest(
    $gridBackground['status'] === IPSViewStartCheck::STATUS_WARNING,
    'The start-grid background scope did not produce a yellow report.'
);

$allPageBackground = analyzeStartCheck(
    startGrid: IPSViewDocument::START_GRID_TWO_COLUMNS,
    background: [
        'mode'      => IPSViewBackground::MODE_FILE,
        'layout'    => IPSViewBackground::LAYOUT_STRETCH,
        'scope'     => IPSViewBackground::SCOPE_ALL_PAGES,
        'imageData' => $png,
    ]
);
assertTest(
    $allPageBackground['status'] === IPSViewStartCheck::STATUS_READY,
    'An all-pages background image still produces a start-grid warning.'
);

$GLOBALS['startCheckModules'] = [];
$missingDesigner = analyzeStartCheck();
assertTest($missingDesigner['ready'], 'A missing Designer incorrectly blocked View creation.');
assertTest(
    $missingDesigner['status'] === IPSViewStartCheck::STATUS_WARNING,
    'A missing Designer did not produce a yellow report.'
);

$exceptionThrown = false;
try {
    IPSViewStartCheck::assertReady($duplicate);
} catch (InvalidArgumentException) {
    $exceptionThrown = true;
}
assertTest($exceptionThrown, 'The mandatory creation guard accepted an invalid report.');

echo "IPSView start-check tests passed.\n";

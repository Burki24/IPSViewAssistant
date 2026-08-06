<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewDesignerHandover.php';

use Burki24\IPSViewAssistant\IPSViewDesignerHandover;

$cases = [
    [2, 0, 'Switch or Toggle-Button'],
    [2, 1, 'Variable Text, Value-Button or Slider'],
    [2, 2, 'Variable Text, Value-Button or Slider'],
    [2, 3, 'Variable Text control'],
    [3, null, 'Script-Button'],
    [5, null, 'Media-Image or Media-Stream'],
    [1, null, 'suitable variable below this object'],
];

foreach ($cases as [$objectType, $variableType, $expectedText]) {
    $recommendation = IPSViewDesignerHandover::recommendation($objectType, $variableType);
    assertTest(
        str_contains($recommendation, $expectedText),
        'The Designer handover recommendation does not match the selected Symcon object.'
    );
}

echo "IPSView Designer handover tests passed.\n";

<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewDocument.php';
require_once dirname(__DIR__) . '/libs/IPSViewUsageProfile.php';

use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewUsageProfile;

$profiles = [
    IPSViewUsageProfile::PROFILE_WALL_TABLET => [
        'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
        'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
        'fullScreen'  => true,
    ],
    IPSViewUsageProfile::PROFILE_TABLET => [
        'aspectRatio' => IPSViewDocument::ASPECT_RATIO_4_3,
        'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
        'fullScreen'  => true,
    ],
    IPSViewUsageProfile::PROFILE_SMARTPHONE => [
        'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
        'orientation' => IPSViewDocument::ORIENTATION_PORTRAIT,
        'fullScreen'  => true,
    ],
    IPSViewUsageProfile::PROFILE_BROWSER => [
        'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
        'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
        'fullScreen'  => false,
    ],
];

foreach ($profiles as $profile => $expected) {
    assertTest(IPSViewUsageProfile::isSelectable($profile), 'A ready-made usage profile is not selectable.');
    assertTest(IPSViewUsageProfile::resolve($profile) === $expected, 'A usage profile resolves to incorrect View settings.');
}

assertTest(
    IPSViewUsageProfile::isSelectable(IPSViewUsageProfile::PROFILE_CUSTOM),
    'The custom usage profile is not selectable.'
);
assertTest(!IPSViewUsageProfile::isSelectable(-1), 'An invalid usage profile is selectable.');
assertTest(!IPSViewUsageProfile::isSelectable(99), 'An invalid usage profile is selectable.');

echo "IPSView usage profile tests passed.\n";

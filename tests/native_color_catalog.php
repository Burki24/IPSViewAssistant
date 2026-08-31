<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/libs/IPSViewTheme.php';

use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;

$template = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/libs/templates/empty-view.json'),
    false,
    512,
    JSON_THROW_ON_ERROR
);
assertTest($template instanceof stdClass, 'The IPSView template must decode to an object.');
assertTest(
    count(IPSViewControlThemeHelper::fields()) === 109,
    'The Assistant must consume the complete 109-field native IPSView color catalogue.'
);
assertTest(
    !property_exists($template, 'ColorView'),
    'The empty-view fixture may omit ColorView when no explicit View color is stored.'
);

$missingView = unserialize(serialize($template));
assertTest($missingView instanceof stdClass, 'The IPSView template copy could not be created.');
$missingViewReport = IPSViewTheme::applyWithReport($missingView, IPSViewTheme::THEME_DARK);
assertTest(
    $missingViewReport['globalColorsApplied'] === 107,
    'Documents that omit ColorView must keep all existing native color fields themeable.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($missingView->ColorPage) === '#1F2937',
    'ColorPage must keep the semantic page background even when ColorView is absent.'
);
$missingViewPalette = IPSViewTheme::extract($missingView);
assertTest(
    $missingViewPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#111827',
    'Missing ColorView must fall back to the theme View color instead of reinterpreting ColorPage.'
);
assertTest(
    $missingViewPalette[IPSViewTheme::ROLE_PAGE_BACKGROUND] === '#1F2937',
    'Page background extraction must continue to use ColorPage.'
);

$current = unserialize(serialize($template));
assertTest($current instanceof stdClass, 'The current IPSView template copy could not be created.');
$current->ColorView = (object) IPSViewControlThemeHelper::createColor('#FF00FF');
$current->ColorPage = (object) IPSViewControlThemeHelper::createColor('#00FFFF');
$currentReport = IPSViewTheme::applyWithReport($current, IPSViewTheme::THEME_DARK);
assertTest(
    $currentReport['globalColorsApplied'] === 108,
    'Current IPSView documents with separate View and page colors must theme both native fields.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($current->ColorView) === '#111827',
    'ColorView must receive the semantic View background.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($current->ColorPage) === '#1F2937',
    'ColorPage must independently receive the semantic page background.'
);
$currentPalette = IPSViewTheme::extract($current);
assertTest(
    $currentPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#111827',
    'View background extraction must use ColorView.'
);
assertTest(
    $currentPalette[IPSViewTheme::ROLE_PAGE_BACKGROUND] === '#1F2937',
    'Page background extraction must use ColorPage.'
);

$themeSource = (string) file_get_contents(dirname(__DIR__) . '/libs/IPSViewTheme.php');
assertTest(
    str_contains($themeSource, 'IPSViewControlThemeHelper::presetRoleMappingForDocument'),
    'IPSViewTheme must delegate native global color mapping to the central helper.'
);

fwrite(STDOUT, "IPSView native color catalogue integration verified.\n");

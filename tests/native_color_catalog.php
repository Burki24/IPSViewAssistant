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
    'The current IPSView 6.5 template regression fixture must not contain legacy ColorView.'
);

$current = unserialize(serialize($template));
assertTest($current instanceof stdClass, 'The current IPSView template copy could not be created.');
$currentReport = IPSViewTheme::applyWithReport($current, IPSViewTheme::THEME_DARK);
assertTest(
    $currentReport['globalColorsApplied'] === 107,
    'Current IPSView 6.5 documents must keep all 107 existing native color fields themeable.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($current->ColorPage) === '#111827',
    'Current IPSView 6.5 ColorPage must remain the View background.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($current->ColorPopupBack) === '#1F2937',
    'Current IPSView 6.5 ColorPopupBack must remain the page/popup background.'
);
$currentPalette = IPSViewTheme::extract($current);
assertTest(
    $currentPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#111827',
    'Current IPSView 6.5 View background extraction must use ColorPage.'
);
assertTest(
    $currentPalette[IPSViewTheme::ROLE_PAGE_BACKGROUND] === '#1F2937',
    'Current IPSView 6.5 page background extraction must use ColorPopupBack.'
);

$legacy = unserialize(serialize($template));
assertTest($legacy instanceof stdClass, 'The legacy IPSView template copy could not be created.');
$legacy->ColorView = (object) IPSViewControlThemeHelper::createColor('#212121');
$legacyReport = IPSViewTheme::applyWithReport($legacy, IPSViewTheme::THEME_DARK);
assertTest(
    $legacyReport['globalColorsApplied'] === 108,
    'Legacy IPSView documents with ColorView must theme the additional native View color.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($legacy->ColorView) === '#111827',
    'Legacy ColorView must receive the semantic View background.'
);
assertTest(
    IPSViewTheme::colorObjectToHex($legacy->ColorPage) === '#1F2937',
    'Legacy ColorPage must receive the semantic page background when ColorView exists.'
);
$legacyPalette = IPSViewTheme::extract($legacy);
assertTest(
    $legacyPalette[IPSViewTheme::ROLE_VIEW_BACKGROUND] === '#111827',
    'Legacy View background extraction must use ColorView.'
);
assertTest(
    $legacyPalette[IPSViewTheme::ROLE_PAGE_BACKGROUND] === '#1F2937',
    'Legacy page background extraction must use ColorPage.'
);

$themeSource = (string) file_get_contents(dirname(__DIR__) . '/libs/IPSViewTheme.php');
assertTest(
    str_contains($themeSource, 'IPSViewControlThemeHelper::presetRoleMappingForDocument'),
    'IPSViewTheme must delegate native global color mapping to the central helper.'
);

fwrite(STDOUT, "IPSView native color catalogue integration verified.\n");

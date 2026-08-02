<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$requiredFiles = [
    '.gitmodules',
    '.github/workflows/tests.yml',
    '.github/workflows/style.yml',
    '.github/workflows/update-library-metadata.yml',
    'library.json',
    'IPSView Assistant/module.json',
    'IPSView Assistant/module.php',
    'IPSView Assistant/form.json',
    'IPSView Assistant/locale.json',
    'libs/IPSViewDocument.php',
    'libs/IPSViewFactory.php',
    'libs/templates/empty-view.json',
    'tests/stubs',
    '.style',
];

foreach ($requiredFiles as $file) {
    assertTest(file_exists($root . '/' . $file), 'Required repository path is missing: ' . $file);
}

$library = json_decode(
    (string) file_get_contents($root . '/library.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$module = json_decode(
    (string) file_get_contents($root . '/IPSView Assistant/module.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$template = json_decode(
    (string) file_get_contents($root . '/libs/templates/empty-view.json'),
    false,
    512,
    JSON_THROW_ON_ERROR
);

assertTest($library['compatibility']['version'] === '9.0', 'The minimum Symcon version must be 9.0.');
assertTest($module['name'] === 'IPSView Assistant', 'The module name is incorrect.');
assertTest($module['prefix'] === 'IPSVIEWA', 'The public module prefix is incorrect.');
assertTest($module['aliases'] === [], 'The module aliases field must be an empty array.');
assertTest(($template->LicenseKey ?? null) === '', 'The template must not contain a license key.');
assertTest(($template->LicenseRegister ?? null) === '', 'The template must not contain a registered user.');
assertTest(($template->UsedIDs ?? null) instanceof stdClass, 'UsedIDs must be a JSON object.');
assertTest(($template->GroupIDs ?? null) instanceof stdClass, 'GroupIDs must be a JSON object.');

$gitmodules = (string) file_get_contents($root . '/.gitmodules');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/StylePHP'), 'StylePHP submodule is missing.');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/SymconStubs'), 'SymconStubs submodule is missing.');

$testsWorkflow = (string) file_get_contents($root . '/.github/workflows/tests.yml');
$styleWorkflow = (string) file_get_contents($root . '/.github/workflows/style.yml');
assertTest(str_contains($testsWorkflow, 'Symcon_ModuleCI/php-tests@v1.0.0'), 'Shared tests are not pinned to v1.0.0.');
assertTest(str_contains($styleWorkflow, 'Symcon_ModuleCI/style@v1.0.0'), 'Shared style is not pinned to v1.0.0.');

echo "Repository structure tests passed.\n";

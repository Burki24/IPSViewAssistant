<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$requiredFiles = [
    '.gitmodules',
    '.helper-sync.json',
    '.github/workflows/tests.yml',
    '.github/workflows/style.yml',
    '.github/workflows/update-library-metadata.yml',
    'library.json',
    'IPSView Assistant/module.json',
    'IPSView Assistant/module.php',
    'IPSView Assistant/form.json',
    'IPSView Assistant/locale.json',
    'libs/helper/ConfigurationFormHelper.php',
    'libs/helper/manifest.json',
    'libs/IPSViewTheme.php',
    'libs/IPSViewEffects.php',
    'libs/IPSViewTypography.php',
    'libs/IPSViewShape.php',
    'libs/IPSViewThemePreview.php',
    'libs/IPSViewDocument.php',
    'libs/IPSViewCopyFactory.php',
    'libs/IPSViewFactory.php',
    'libs/templates/empty-view.json',
    'assets/fonts/NOTICE.md',
    'assets/fonts/Apache-2.0.txt',
    'assets/fonts/OFL-1.1.txt',
    'assets/fonts/ParaType-Free-Font-License.txt',
    'assets/fonts/preview-font-01-400.woff2',
    'assets/fonts/preview-font-01-700.woff2',
    'assets/fonts/preview-font-02-400.woff2',
    'assets/fonts/preview-font-02-700.woff2',
    'assets/fonts/preview-font-03-400.woff2',
    'assets/fonts/preview-font-03-700.woff2',
    'assets/fonts/preview-font-04-400.woff2',
    'assets/fonts/preview-font-05-400.woff2',
    'assets/fonts/preview-font-05-700.woff2',
    'assets/fonts/preview-font-06-400.woff2',
    'assets/fonts/preview-font-06-700.woff2',
    'assets/fonts/preview-font-07-400.woff2',
    'assets/fonts/preview-font-08-400.woff2',
    'tests/theme.php',
    'tests/effects.php',
    'tests/appearance.php',
    'tests/existing_view.php',
    'tests/stubs',
    '.style',
];

foreach ($requiredFiles as $file) {
    assertTest(file_exists($root . '/' . $file), 'Required repository path is missing: ' . $file);
}

$previewFonts = glob($root . '/assets/fonts/preview-font-*.woff2');
assertTest(is_array($previewFonts), 'The bundled preview fonts could not be enumerated.');
assertTest(count($previewFonts) === 13, 'The bundled preview font set is incomplete.');
foreach ($previewFonts as $previewFont) {
    $signature = file_get_contents($previewFont, false, null, 0, 4);
    assertTest($signature === 'wOF2', 'A bundled preview font is not a valid WOFF2 file.');
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

$form = json_decode(
    (string) file_get_contents($root . '/IPSView Assistant/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$formJson = json_encode($form, JSON_THROW_ON_ERROR);
assertTest(str_contains($formJson, 'ThemePreview'), 'The live theme preview is missing.');
assertTest(str_contains($formJson, 'ThemeWorkspace'), 'The responsive theme workspace is missing.');
assertTest(str_contains($formJson, 'ThemeColorsPanel'), 'The theme color panel is missing.');
assertTest(str_contains($formJson, 'ThemePreviewPanel'), 'The theme preview panel is missing.');
assertTest(str_contains($formJson, 'SelectColor'), 'The semantic color controls are missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_ApplyThemePreset'), 'The theme preset action is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateThemePreview'), 'The live preview action is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateEffectsPreview'), 'The general effect preview action is missing.');
assertTest(str_contains($formJson, 'ThemeEffectsPanel'), 'The general effects panel is missing.');
assertTest(str_contains($formJson, 'TypographyShapePanel'), 'The typography and form-language panel is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateAppearancePreview'), 'The appearance preview action is missing.');
assertTest(str_contains($formJson, 'SourceViewID'), 'The existing IPSView selector is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_LoadExistingView'), 'The existing View load action is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_CreateStyledCopy'), 'The styled copy action is missing.');

$gitmodules = (string) file_get_contents($root . '/.gitmodules');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/StylePHP'), 'StylePHP submodule is missing.');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/SymconStubs'), 'SymconStubs submodule is missing.');

$testsWorkflow = (string) file_get_contents($root . '/.github/workflows/tests.yml');
$styleWorkflow = (string) file_get_contents($root . '/.github/workflows/style.yml');
assertTest(str_contains($testsWorkflow, 'Symcon_ModuleCI/php-tests@v1.0.0'), 'Shared tests are not pinned to v1.0.0.');
assertTest(str_contains($styleWorkflow, 'Symcon_ModuleCI/style@v1.0.0'), 'Shared style is not pinned to v1.0.0.');

echo "Repository structure tests passed.\n";

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
    'libs/IPSViewDesignerHandover.php',
    'libs/IPSViewEffects.php',
    'libs/IPSViewTypography.php',
    'libs/IPSViewBackground.php',
    'libs/IPSViewShape.php',
    'libs/IPSViewThemePreview.php',
    'libs/IPSViewDocument.php',
    'libs/IPSViewUsageProfile.php',
    'libs/IPSViewCopyFactory.php',
    'libs/IPSViewFactory.php',
    'libs/templates/empty-view.json',
    'libs/fonts/NOTICE.md',
    'libs/fonts/Apache-2.0.txt',
    'libs/fonts/OFL-1.1.txt',
    'libs/fonts/ParaType-Free-Font-License.txt',
    'libs/fonts/BebasNeue-Regular.ttf',
    'libs/fonts/DancingScript-Bold.ttf',
    'libs/fonts/DancingScript-Regular.ttf',
    'libs/fonts/IndieFlower-Regular.ttf',
    'libs/fonts/OpenSans-Bold.ttf',
    'libs/fonts/OpenSans-BoldItalic.ttf',
    'libs/fonts/OpenSans-Regular.ttf',
    'libs/fonts/OpenSans-RegularItalic.ttf',
    'libs/fonts/PTSans-Bold.ttf',
    'libs/fonts/PTSans-BoldItalic.ttf',
    'libs/fonts/PTSans-Regular.ttf',
    'libs/fonts/PTSans-RegularItalic.ttf',
    'libs/fonts/Roboto-Bold.ttf',
    'libs/fonts/Roboto-BoldItalic.ttf',
    'libs/fonts/Roboto-Regular.ttf',
    'libs/fonts/Roboto-RegularItalic.ttf',
    'libs/fonts/RobotoMono-Bold.ttf',
    'libs/fonts/RobotoMono-BoldItalic.ttf',
    'libs/fonts/RobotoMono-Regular.ttf',
    'libs/fonts/RobotoMono-RegularItalic.ttf',
    'libs/fonts/Segment7-Regular.ttf',
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

assertTest(!is_dir($root . '/assets'), 'The library root must not contain an assets module directory.');

$obsoletePreviewFonts = glob($root . '/libs/fonts/preview-font-*.woff2');
assertTest(is_array($obsoletePreviewFonts), 'The obsolete preview fonts could not be enumerated.');
assertTest($obsoletePreviewFonts === [], 'Obsolete generated WOFF2 preview fonts must not be bundled.');

$previewTrueTypeFonts = glob($root . '/libs/fonts/*.ttf');
assertTest(is_array($previewTrueTypeFonts), 'The original preview font cuts could not be enumerated.');
assertTest(count($previewTrueTypeFonts) === 21, 'The original IPSView preview font set is incomplete.');
foreach ($previewTrueTypeFonts as $previewTrueTypeFont) {
    $signature = file_get_contents($previewTrueTypeFont, false, null, 0, 4);
    assertTest($signature === "\x00\x01\x00\x00", 'A bundled preview font is not a valid TrueType file.');
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
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateAssistantMode'), 'The assistant mode action is missing.');
assertTest(str_contains($formJson, 'AssistantModeInfo'), 'The assistant mode explanation is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateUsageProfile'), 'The usage profile action is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_MarkUsageProfileCustom'), 'The custom usage profile action is missing.');
assertTest(str_contains($formJson, 'UsageProfileInfo'), 'The usage profile explanation is missing.');
assertTest(str_contains($formJson, 'DesignerHandoverPanel'), 'The guided Designer handover is missing.');
assertTest(str_contains($formJson, 'OpenObjectButton'), 'The button for opening a created IPSView is missing.');
assertTest(str_contains($formJson, 'IPSVIEWA_UpdateDesignerHandover'), 'The Designer object recommendation action is missing.');

$gitmodules = (string) file_get_contents($root . '/.gitmodules');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/StylePHP'), 'StylePHP submodule is missing.');
assertTest(str_contains($gitmodules, 'url = https://github.com/symcon/SymconStubs'), 'SymconStubs submodule is missing.');

$testsWorkflow = (string) file_get_contents($root . '/.github/workflows/tests.yml');
$styleWorkflow = (string) file_get_contents($root . '/.github/workflows/style.yml');
assertTest(str_contains($testsWorkflow, 'Symcon_ModuleCI/php-tests@v1.0.0'), 'Shared tests are not pinned to v1.0.0.');
assertTest(str_contains($styleWorkflow, 'Symcon_ModuleCI/style@v1.0.0'), 'Shared style is not pinned to v1.0.0.');

echo "Repository structure tests passed.\n";

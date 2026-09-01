<?php

declare(strict_types=1);

$root = dirname(__DIR__);
if (!chdir($root)) {
    fwrite(STDERR, "Unable to switch to the repository root.\n");
    exit(1);
}

/**
 * Runs one repository-specific test command.
 */
function runTestCommand(string $label, string $command): void
{
    echo $label . "...\n";
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, $label . ' failed with exit code ' . $exitCode . ".\n");
        exit($exitCode);
    }
}

$commands = [
    ['Validate repository structure', 'php tests/validate_structure.php'],
    ['Verify vendored helpers', 'python3 tests/helper_integrity.py'],
    ['Test IPSView theme mapping', 'php tests/theme.php'],
    ['Test native IPSView color catalogue', 'php tests/native_color_catalog.php'],
    ['Test native IPSView preview colors', 'php tests/native_preview.php'],
    ['Test general IPSView effects', 'php tests/effects.php'],
    ['Test IPSView typography and form language', 'php tests/appearance.php'],
    ['Test IPSView Style Profile exchange', 'php tests/style_profile.php'],
    ['Test shared IPSView style integration', 'php tests/shared_style.php'],
    ['Test IPSView page backgrounds', 'php tests/background.php'],
    ['Test IPSView start check', 'php tests/start_check.php'],
    ['Test IPSView usage profiles', 'php tests/usage_profile.php'],
    ['Test IPSView Designer handover', 'php tests/designer_handover.php'],
    ['Test IPSView document generation', 'php tests/document.php'],
    ['Test IPSView creation and overwrite', 'php tests/factory.php'],
    ['Test existing IPSView handling', 'php tests/existing_view.php'],
    ['Test module integration', 'php tests/module.php'],
    ['Test library metadata updater', 'python3 tests/test_update_library_metadata.py'],
];

foreach ($commands as [$label, $command]) {
    runTestCommand($label, $command);
}

echo "All IPSViewAssistant tests passed.\n";

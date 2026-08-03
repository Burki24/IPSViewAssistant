<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use Throwable;

final class IPSViewStartCheck
{
    public const STATUS_READY = 0;
    public const STATUS_WARNING = 1;
    public const STATUS_ERROR = 2;

    /**
     * Collects blocking errors, non-blocking warnings and successful creation checks.
     *
     * @param array<string, mixed> $background
     *
     * @return array{status: int, ready: bool, checks: list<string>, warnings: list<string>, errors: list<string>}
     */
    public static function analyze(
        string $viewName,
        int $targetCategoryID,
        string $mainPageName,
        int $aspectRatio,
        int $orientation,
        int $template,
        int $startGrid,
        array $background = []
    ): array {
        $checks = [];
        $warnings = [];
        $errors = [];

        $viewNameValid = self::validateName(trim($viewName), 'View', $errors);
        $mainPageNameValid = self::validateName(trim($mainPageName), 'main page', $errors);
        if ($viewNameValid && $mainPageNameValid) {
            $checks[] = 'View name and main page name are valid.';
        }

        $targetValid = self::validateTargetCategory($targetCategoryID, $errors);
        if ($targetValid && $viewNameValid) {
            if (self::hasDuplicateViewName(trim($viewName), $targetCategoryID)) {
                $errors[] = 'A View with this name already exists in the target category.';
            } else {
                $checks[] = 'The target category is available and the View name is unused.';
            }
        }

        $formatValid = true;
        if (!in_array($aspectRatio, [
            IPSViewDocument::ASPECT_RATIO_SQUARE,
            IPSViewDocument::ASPECT_RATIO_4_3,
            IPSViewDocument::ASPECT_RATIO_16_9,
            IPSViewDocument::ASPECT_RATIO_8_5,
            IPSViewDocument::ASPECT_RATIO_9_5,
            IPSViewDocument::ASPECT_RATIO_13_6,
            IPSViewDocument::ASPECT_RATIO_2_1,
        ], true)) {
            $errors[] = 'The selected aspect ratio is not supported.';
            $formatValid = false;
        }

        if (!in_array($orientation, [
            IPSViewDocument::ORIENTATION_LANDSCAPE,
            IPSViewDocument::ORIENTATION_PORTRAIT,
        ], true)) {
            $errors[] = 'The selected orientation is not supported.';
            $formatValid = false;
        }

        if ($template !== 0) {
            $errors[] = 'The selected template is not supported.';
            $formatValid = false;
        }

        if (!in_array($startGrid, [
            IPSViewDocument::START_GRID_NONE,
            IPSViewDocument::START_GRID_TWO_COLUMNS,
            IPSViewDocument::START_GRID_THREE_COLUMNS,
        ], true)) {
            $errors[] = 'The selected start grid is not supported.';
            $formatValid = false;
        }

        if ($formatValid) {
            $checks[] = 'View format and start grid are plausible.';
        }

        self::checkBackground($background, $startGrid, $checks, $warnings, $errors);

        $designerAvailable = self::designerAvailable();
        if ($designerAvailable === true) {
            $checks[] = 'IPSView Designer was detected.';
        } elseif ($designerAvailable === false) {
            $warnings[] = 'IPSView Designer was not detected. The View can be created, but it cannot be edited until the Designer is installed.';
        } else {
            $warnings[] = 'IPSView Designer availability could not be checked automatically.';
        }

        $status = $errors !== []
            ? self::STATUS_ERROR
            : ($warnings !== [] ? self::STATUS_WARNING : self::STATUS_READY);

        return [
            'status'   => $status,
            'ready'    => $errors === [],
            'checks'   => $checks,
            'warnings' => $warnings,
            'errors'   => $errors,
        ];
    }

    /**
     * Stops View creation when a previously generated report contains errors.
     *
     * @param array{ready: bool, errors: list<string>} $report
     */
    public static function assertReady(array $report): void
    {
        if ($report['ready']) {
            return;
        }

        throw new InvalidArgumentException(
            $report['errors'][0] ?? 'The View configuration is not ready for creation.'
        );
    }

    /**
     * Appends a precise validation error for an empty or excessively long name.
     *
     * @param list<string> $errors
     */
    private static function validateName(string $name, string $field, array &$errors): bool
    {
        if ($name === '') {
            $errors[] = $field === 'View'
                ? 'The View name must not be empty.'
                : 'The main page name must not be empty.';

            return false;
        }

        if (strlen($name) > 128) {
            $errors[] = $field === 'View'
                ? 'The View name must not exceed 128 characters.'
                : 'The main page name must not exceed 128 characters.';

            return false;
        }

        return true;
    }

    /**
     * Accepts the Symcon root or an existing category as creation target.
     *
     * @param list<string> $errors
     */
    private static function validateTargetCategory(int $targetCategoryID, array &$errors): bool
    {
        if ($targetCategoryID === 0) {
            return true;
        }

        if (!IPS_ObjectExists($targetCategoryID)) {
            $errors[] = 'The selected target category does not exist.';

            return false;
        }

        $object = IPS_GetObject($targetCategoryID);
        if (($object['ObjectType'] ?? -1) !== 0) {
            $errors[] = 'The selected target object is not a category.';

            return false;
        }

        return true;
    }

    /**
     * Checks whether the target category already contains a same-name media object.
     */
    private static function hasDuplicateViewName(string $viewName, int $targetCategoryID): bool
    {
        foreach (IPS_GetChildrenIDs($targetCategoryID) as $childID) {
            $object = IPS_GetObject($childID);
            if (($object['ObjectType'] ?? -1) === 5 && ($object['ObjectName'] ?? '') === $viewName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates an optional background image and adds the grid-scope hint when needed.
     *
     * @param array<string, mixed> $background
     * @param list<string>         $checks
     * @param list<string>         $warnings
     * @param list<string>         $errors
     */
    private static function checkBackground(
        array $background,
        int $startGrid,
        array &$checks,
        array &$warnings,
        array &$errors
    ): void {
        try {
            $settings = IPSViewBackground::resolve($background);
            if ($settings['mode'] === IPSViewBackground::MODE_FILE) {
                if ($settings['imageData'] === '') {
                    $errors[] = 'A background image file is selected, but no image was provided.';

                    return;
                }

                IPSViewBackground::preview($settings);
                $checks[] = 'The selected background image is valid.';

                if (
                    $startGrid !== IPSViewDocument::START_GRID_NONE
                    && $settings['scope'] === IPSViewBackground::SCOPE_MAIN_PAGE
                ) {
                    $warnings[] = 'With a start grid, select all pages if the background image should also appear on the grid content page.';
                }

                return;
            }

            $checks[] = 'No new background image needs to be checked.';
        } catch (Throwable) {
            $errors[] = 'The selected background image is invalid.';
        }
    }

    /**
     * Detects IPSView Designer, returning null when the module API cannot be queried.
     */
    private static function designerAvailable(): ?bool
    {
        if (!function_exists('IPS_GetModuleList') || !function_exists('IPS_GetModule')) {
            return null;
        }

        try {
            foreach (IPS_GetModuleList() as $moduleID) {
                $module = IPS_GetModule($moduleID);
                $moduleName = preg_replace('/[^a-z0-9]/', '', strtolower((string) ($module['ModuleName'] ?? '')));
                if (str_contains($moduleName ?? '', 'ipsviewdesigner')) {
                    return true;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return false;
    }
}

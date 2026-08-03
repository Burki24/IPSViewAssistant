<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use stdClass;

final class IPSViewDocument
{
    public const ASPECT_RATIO_SQUARE = 0;
    public const ASPECT_RATIO_4_3 = 1;
    public const ASPECT_RATIO_16_9 = 2;
    public const ASPECT_RATIO_8_5 = 3;
    public const ASPECT_RATIO_9_5 = 4;
    public const ASPECT_RATIO_13_6 = 5;
    public const ASPECT_RATIO_2_1 = 6;

    public const ORIENTATION_LANDSCAPE = 0;
    public const ORIENTATION_PORTRAIT = 1;

    public const START_GRID_NONE = 0;
    public const START_GRID_TWO_COLUMNS = 2;
    public const START_GRID_THREE_COLUMNS = 3;

    private stdClass $document;

    private function __construct(stdClass $document)
    {
        $this->document = $document;
    }

    /**
     * Loads an IPSView document from a template file.
     */
    public static function fromTemplate(string $templatePath): self
    {
        $json = file_get_contents($templatePath);
        if ($json === false) {
            throw new RuntimeException('The IPSView template could not be read.');
        }

        return self::fromJson($json);
    }

    /**
     * Loads an IPSView document without converting JSON objects into PHP arrays.
     */
    public static function fromJson(string $json): self
    {
        try {
            $document = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The IPSView document contains invalid JSON.', 0, $exception);
        }

        if (!$document instanceof stdClass) {
            throw new RuntimeException('The IPSView document must contain a JSON object.');
        }

        return new self($document);
    }

    /**
     * Applies the values selected by the user to the empty IPSView document.
     */
    public function configure(
        string $viewName,
        int $mediaID,
        int $aspectRatio,
        int $orientation,
        string $mainPageName,
        bool $fullScreen = false,
        int $startGrid = self::START_GRID_NONE
    ): void {
        $viewName = trim($viewName);
        $mainPageName = trim($mainPageName);

        if ($viewName === '') {
            throw new InvalidArgumentException('The View name must not be empty.');
        }

        if ($mainPageName === '') {
            throw new InvalidArgumentException('The main page name must not be empty.');
        }

        if ($mediaID < 1) {
            throw new InvalidArgumentException('The media ID must be greater than zero.');
        }

        [$ratioLabel, $landscapeWidth, $landscapeHeight] = self::resolveAspectRatio($aspectRatio);
        [$orientationName, $width, $height] = self::resolveOrientation(
            $orientation,
            $landscapeWidth,
            $landscapeHeight
        );

        $this->document->Name = $viewName;
        $this->document->ID = $mediaID;
        $this->document->Client = 'Universal';
        $this->document->Hardware = sprintf('Universal %dx%d (%s)', $width, $height, $ratioLabel);
        $this->document->Orientation = $orientationName;
        $this->document->HardwareWidth = $width;
        $this->document->HardwareHeight = $height;
        $this->document->Width = $width;
        $this->document->Height = $height;
        $this->document->FullScreen = $fullScreen;
        $this->document->CurrentPageName = $mainPageName;
        $this->document->ItemID = 0;

        $this->removeLicenseData();
        $this->document->UsedIDs = new stdClass();
        $this->document->GroupIDs = new stdClass();

        $pages = $this->document->Pages ?? null;
        if (!is_array($pages) || !isset($pages[0]) || !$pages[0] instanceof stdClass) {
            throw new RuntimeException('The IPSView template does not contain a valid main page.');
        }

        $pages[0]->PageName = $mainPageName;
        $pages[0]->PageTitle = '';
        $pages[0]->Controls = [];
        $pages = $this->configureStartGrid($pages, $mainPageName, $width, $height, $startGrid);
        $this->document->Pages = $pages;
    }

    /**
     * Prepares a lossless copy of an existing View for a new media object.
     */
    public function prepareCopy(string $viewName, int $mediaID): void
    {
        $viewName = trim($viewName);

        if ($viewName === '') {
            throw new InvalidArgumentException('The View name must not be empty.');
        }

        if ($mediaID < 1) {
            throw new InvalidArgumentException('The media ID must be greater than zero.');
        }

        $this->document->Name = $viewName;
        $this->document->ID = $mediaID;
    }

    /**
     * Applies a semantic color theme to the IPSView defaults.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array<string, string> Effective palette
     */
    public function applyTheme(
        int $theme,
        array $customPalette = [],
        array $effects = [],
        array $appearance = [],
        array $background = []
    ): array {
        $palette = IPSViewTheme::apply($this->document, $theme, $customPalette);
        IPSViewEffects::apply(
            $this->document,
            $effects,
            IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
        );
        IPSViewTypography::apply(
            $this->document,
            $appearance,
            IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
        );
        IPSViewShape::apply($this->document, $appearance);
        IPSViewBackground::apply($this->document, $background);

        return $palette;
    }

    /**
     * Applies a semantic theme with the selected scope and returns a report.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array{
     *     palette: array<string, string>,
     *     scope: int,
     *     globalColorsApplied: int,
     *     controlColorsApplied: int,
     *     controlColorsPreserved: int,
     *     globalEffectsApplied: int,
     *     controlEffectsApplied: int,
     *     shadowChanged: bool,
     *     globalTypographyApplied: int,
     *     controlTypographyApplied: int,
     *     globalShapeApplied: int
     * }
     */
    public function applyThemeWithReport(
        int $theme,
        array $customPalette = [],
        int $scope = IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
        array $effects = [],
        array $appearance = [],
        array $background = []
    ): array {
        $themeReport = IPSViewTheme::applyWithReport(
            $this->document,
            $theme,
            $customPalette,
            $scope
        );
        $effectReport = IPSViewEffects::apply(
            $this->document,
            $effects,
            $scope
        );
        $typographyReport = IPSViewTypography::apply(
            $this->document,
            $appearance,
            $scope
        );
        $shapeReport = IPSViewShape::apply($this->document, $appearance);
        $backgroundChanged = IPSViewBackground::apply($this->document, $background);

        return [...$themeReport, ...$effectReport, ...$typographyReport, ...$shapeReport, 'backgroundChanged' => $backgroundChanged];
    }

    /**
     * Analyzes the available global and direct control colors.
     *
     * @return array{
     *     globalColors: int,
     *     controlColorsTotal: int,
     *     matchingControlColors: int,
     *     allControlDefaults: int,
     *     individualControlColors: int,
     *     specialControlColors: int
     * }
     */
    public function analyzeThemeColors(): array
    {
        return IPSViewTheme::analyze($this->document);
    }

    /**
     * Reads the current IPSView defaults into the semantic color model.
     *
     * @return array<string, string>
     */
    public function extractThemePalette(): array
    {
        return IPSViewTheme::extract($this->document);
    }

    /**
     * Reads the current global typography and shape defaults.
     *
     * @return array{
     *     fontFamily: string,
     *     baseFontSize: int,
     *     cornerRadius: int,
     *     borderWidth: float
     * }
     */
    public function extractAppearance(): array
    {
        return [
            ...IPSViewTypography::extract($this->document),
            ...IPSViewShape::extract($this->document),
        ];
    }

    /**
     * Reads the background image assigned to the main page.
     *
     * @return array{mode: int, layout: string, scope: int, imageData: string, width: int, height: int}
     */
    public function extractBackground(): array
    {
        return IPSViewBackground::extract($this->document);
    }

    /**
     * Returns the number of pages in the document.
     */
    public function getPageCount(): int
    {
        return is_array($this->document->Pages ?? null) ? count($this->document->Pages) : 0;
    }

    /**
     * Returns the number of controls in all nested control collections.
     */
    public function getControlCount(): int
    {
        return self::countControls($this->document);
    }

    /**
     * Serializes the document while preserving empty JSON objects as objects.
     */
    public function toJson(): string
    {
        try {
            return json_encode(
                $this->document,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException('The IPSView document could not be serialized.', 0, $exception);
        }
    }

    /**
     * Returns a detached copy for diagnostics and tests.
     */
    public function copy(): stdClass
    {
        try {
            $copy = json_decode($this->toJson(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The IPSView document could not be copied.', 0, $exception);
        }

        if (!$copy instanceof stdClass) {
            throw new RuntimeException('The copied IPSView document has an invalid root type.');
        }

        return $copy;
    }

    /**
     * Resolves the selected aspect ratio to its standard landscape dimensions.
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private static function resolveAspectRatio(int $aspectRatio): array
    {
        return match ($aspectRatio) {
            self::ASPECT_RATIO_SQUARE => ['1:1', 1000, 1000],
            self::ASPECT_RATIO_4_3    => ['4:3', 1024, 768],
            self::ASPECT_RATIO_16_9   => ['16:9', 1360, 765],
            self::ASPECT_RATIO_8_5    => ['8:5', 1280, 800],
            self::ASPECT_RATIO_9_5    => ['9:5', 1440, 800],
            self::ASPECT_RATIO_13_6   => ['13:6', 1300, 600],
            self::ASPECT_RATIO_2_1    => ['2:1', 1360, 680],
            default                   => throw new InvalidArgumentException('The selected aspect ratio is not supported.'),
        };
    }

    /**
     * Applies the selected orientation to the standard landscape dimensions.
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private static function resolveOrientation(int $orientation, int $width, int $height): array
    {
        return match ($orientation) {
            self::ORIENTATION_LANDSCAPE => ['Landscape', $width, $height],
            self::ORIENTATION_PORTRAIT  => ['Portrait', $height, $width],
            default                     => throw new InvalidArgumentException('The selected orientation is not supported.'),
        };
    }

    /**
     * Creates an optional grid-enabled content page and embeds it in the main page.
     *
     * IPSView supports its native grid mode on standard and popup pages, but not
     * directly on a main page. A page container is therefore the smallest valid
     * scaffold that makes the grid content visible without placeholder controls.
     *
     * @param list<stdClass> $pages
     *
     * @return list<stdClass>
     */
    private function configureStartGrid(
        array $pages,
        string $mainPageName,
        int $width,
        int $height,
        int $startGrid
    ): array {
        $cellWidth = match ($startGrid) {
            self::START_GRID_NONE          => '',
            self::START_GRID_TWO_COLUMNS   => '50%',
            self::START_GRID_THREE_COLUMNS => '33.333333%',
            default                        => throw new InvalidArgumentException('The selected start grid is not supported.'),
        };

        $pages[0]->GridEnabled = false;
        $pages[0]->GridCellWidth = '';

        if ($startGrid === self::START_GRID_NONE) {
            return $pages;
        }

        $contentPageName = $mainPageName . ' - Grid';
        $contentPage = clone $pages[0];
        $contentPage->PageName = $contentPageName;
        $contentPage->PageTitle = $contentPageName;
        $contentPage->Position = 10;
        $contentPage->IsMainPage = false;
        $contentPage->IsInline = true;
        $contentPage->PopupWidth = $width;
        $contentPage->PopupHeight = $height;
        $contentPage->GridEnabled = true;
        $contentPage->GridCellWidth = $cellWidth;
        $contentPage->Controls = [];

        $pages[0]->Controls = [self::createPageContainer($contentPageName, $width, $height)];
        $pages[] = $contentPage;
        $this->document->ItemID = 1;

        return $pages;
    }

    /**
     * Creates the single page container required to display the grid page.
     */
    private static function createPageContainer(string $pageName, int $width, int $height): stdClass
    {
        return (object) [
            'Type'           => 'IPSInlinePage',
            'ID'             => 0,
            'Name'           => '',
            'Text1'          => $pageName,
            'Image1'         => 0,
            'Image2'         => 0,
            'AutoSize'       => false,
            'Readonly'       => false,
            'AllowZoom'      => false,
            'Reverse'        => false,
            'Synchronize'    => false,
            'Digits'         => 0,
            'Associations'   => [],
            'WidgetDetail'   => '',
            'Width'          => $width,
            'Height'         => $height,
            'LocationX'      => 0,
            'LocationY'      => 0,
            'CR1'            => 0,
            'CR2'            => 0,
            'CR3'            => 0,
            'CR4'            => 0,
            'Font'           => (object) [
                'FontFamily' => '',
                'Size'       => 11,
            ],
            'Widget'         => (object) ['anchor_auto' => 'false'],
            'ItemID'         => 1,
            'ItemParentID'   => 0,
            'ItemName'       => 'IPSInlinePage1',
            'ItemIdx'        => 0,
            'ItemExpanded'   => false,
            'ItemLayer'      => 2,
            'ItemVisibility' => 0,
        ];
    }

    private static function countControls(mixed $value): int
    {
        if (is_array($value)) {
            $count = 0;

            foreach ($value as $item) {
                $count += self::countControls($item);
            }

            return $count;
        }

        if (!$value instanceof stdClass) {
            return 0;
        }

        $count = 0;

        foreach (get_object_vars($value) as $property => $child) {
            if ($property === 'Controls' && is_array($child)) {
                $count += count($child);
            }

            $count += self::countControls($child);
        }

        return $count;
    }

    /**
     * Ensures that no personal IPSView license data is copied into a new View.
     */
    private function removeLicenseData(): void
    {
        $this->document->LicenseKey = '';
        $this->document->LicenseRegister = '';
        $this->document->LicenseDemoDate = '';
        $this->document->LicenseDemoKey = '';
        $this->document->LicenseDemoCrypt = '';
        $this->document->LicenseType = '';
    }
}

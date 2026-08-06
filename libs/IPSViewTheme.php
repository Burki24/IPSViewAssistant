<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use stdClass;

final class IPSViewTheme
{
    public const THEME_STANDARD = 0;
    public const THEME_LIGHT = 1;
    public const THEME_DARK = 2;
    public const THEME_CUSTOM = 3;
    public const THEME_WARM = 4;
    public const THEME_COOL = 5;
    public const THEME_EARTHY = 6;
    public const THEME_WATER = 7;
    public const THEME_SUNNY = 8;

    public const SCOPE_GLOBAL_DEFAULTS = 0;
    public const SCOPE_MATCHING_CONTROLS = 1;
    public const SCOPE_ALL_CONTROL_DEFAULTS = 2;

    public const ROLE_VIEW_BACKGROUND = 'viewBackground';
    public const ROLE_PAGE_BACKGROUND = 'pageBackground';
    public const ROLE_SURFACE = 'surface';
    public const ROLE_PRIMARY_TEXT = 'primaryText';
    public const ROLE_SECONDARY_TEXT = 'secondaryText';
    public const ROLE_BORDER = 'border';
    public const ROLE_ACCENT = 'accent';
    public const ROLE_ACTIVE = 'active';
    public const ROLE_INACTIVE = 'inactive';
    public const ROLE_SUCCESS = 'success';
    public const ROLE_WARNING = 'warning';
    public const ROLE_ERROR = 'error';

    /**
     * Returns the semantic palette for one predefined theme.
     *
     * @return array<string, string>
     */
    public static function preset(int $theme): array
    {
        return match ($theme) {
            self::THEME_STANDARD => [
                self::ROLE_VIEW_BACKGROUND => '#404040',
                self::ROLE_PAGE_BACKGROUND => '#404040',
                self::ROLE_SURFACE         => '#606060',
                self::ROLE_PRIMARY_TEXT    => '#FFFFFF',
                self::ROLE_SECONDARY_TEXT  => '#A4A4A4',
                self::ROLE_BORDER          => '#7F7F7F',
                self::ROLE_ACCENT          => '#007AFF',
                self::ROLE_ACTIVE          => '#0ABE0A',
                self::ROLE_INACTIVE        => '#BE0A0A',
                self::ROLE_SUCCESS         => '#0ABE0A',
                self::ROLE_WARNING         => '#FF0000',
                self::ROLE_ERROR           => '#BE0A0A',
            ],
            self::THEME_LIGHT => [
                self::ROLE_VIEW_BACKGROUND => '#E9EEF5',
                self::ROLE_PAGE_BACKGROUND => '#F6F8FB',
                self::ROLE_SURFACE         => '#FFFFFF',
                self::ROLE_PRIMARY_TEXT    => '#1F2937',
                self::ROLE_SECONDARY_TEXT  => '#667085',
                self::ROLE_BORDER          => '#D0D5DD',
                self::ROLE_ACCENT          => '#2563EB',
                self::ROLE_ACTIVE          => '#16A34A',
                self::ROLE_INACTIVE        => '#98A2B3',
                self::ROLE_SUCCESS         => '#15803D',
                self::ROLE_WARNING         => '#D97706',
                self::ROLE_ERROR           => '#DC2626',
            ],
            self::THEME_DARK => [
                self::ROLE_VIEW_BACKGROUND => '#111827',
                self::ROLE_PAGE_BACKGROUND => '#1F2937',
                self::ROLE_SURFACE         => '#273449',
                self::ROLE_PRIMARY_TEXT    => '#F9FAFB',
                self::ROLE_SECONDARY_TEXT  => '#AEB8C7',
                self::ROLE_BORDER          => '#475569',
                self::ROLE_ACCENT          => '#3B82F6',
                self::ROLE_ACTIVE          => '#22C55E',
                self::ROLE_INACTIVE        => '#64748B',
                self::ROLE_SUCCESS         => '#22C55E',
                self::ROLE_WARNING         => '#F59E0B',
                self::ROLE_ERROR           => '#EF4444',
            ],
            self::THEME_WARM => [
                self::ROLE_VIEW_BACKGROUND => '#3B2420',
                self::ROLE_PAGE_BACKGROUND => '#4A2E27',
                self::ROLE_SURFACE         => '#5C3A31',
                self::ROLE_PRIMARY_TEXT    => '#FFF7ED',
                self::ROLE_SECONDARY_TEXT  => '#D6B8A8',
                self::ROLE_BORDER          => '#8A5A44',
                self::ROLE_ACCENT          => '#F59E0B',
                self::ROLE_ACTIVE          => '#E76F51',
                self::ROLE_INACTIVE        => '#8D6E63',
                self::ROLE_SUCCESS         => '#7BA05B',
                self::ROLE_WARNING         => '#F4A261',
                self::ROLE_ERROR           => '#D64545',
            ],
            self::THEME_COOL => [
                self::ROLE_VIEW_BACKGROUND => '#0F1B2D',
                self::ROLE_PAGE_BACKGROUND => '#17263A',
                self::ROLE_SURFACE         => '#21354D',
                self::ROLE_PRIMARY_TEXT    => '#F1F7FF',
                self::ROLE_SECONDARY_TEXT  => '#A9BCD0',
                self::ROLE_BORDER          => '#49647E',
                self::ROLE_ACCENT          => '#38BDF8',
                self::ROLE_ACTIVE          => '#2DD4BF',
                self::ROLE_INACTIVE        => '#64748B',
                self::ROLE_SUCCESS         => '#22C55E',
                self::ROLE_WARNING         => '#FBBF24',
                self::ROLE_ERROR           => '#F87171',
            ],
            self::THEME_EARTHY => [
                self::ROLE_VIEW_BACKGROUND => '#2D2A20',
                self::ROLE_PAGE_BACKGROUND => '#3A3528',
                self::ROLE_SURFACE         => '#4A4433',
                self::ROLE_PRIMARY_TEXT    => '#F4EBD0',
                self::ROLE_SECONDARY_TEXT  => '#C8B894',
                self::ROLE_BORDER          => '#766A4E',
                self::ROLE_ACCENT          => '#B08968',
                self::ROLE_ACTIVE          => '#7A9E5A',
                self::ROLE_INACTIVE        => '#8B7D6B',
                self::ROLE_SUCCESS         => '#6B8E4E',
                self::ROLE_WARNING         => '#D4A373',
                self::ROLE_ERROR           => '#B75D5D',
            ],
            self::THEME_WATER => [
                self::ROLE_VIEW_BACKGROUND => '#06283D',
                self::ROLE_PAGE_BACKGROUND => '#0B3A53',
                self::ROLE_SURFACE         => '#10546D',
                self::ROLE_PRIMARY_TEXT    => '#E6F7FF',
                self::ROLE_SECONDARY_TEXT  => '#9CC9D8',
                self::ROLE_BORDER          => '#347D91',
                self::ROLE_ACCENT          => '#00B4D8',
                self::ROLE_ACTIVE          => '#48CAE4',
                self::ROLE_INACTIVE        => '#5B7F8C',
                self::ROLE_SUCCESS         => '#2EC4B6',
                self::ROLE_WARNING         => '#FFD166',
                self::ROLE_ERROR           => '#EF476F',
            ],
            self::THEME_SUNNY => [
                self::ROLE_VIEW_BACKGROUND => '#FFF3B0',
                self::ROLE_PAGE_BACKGROUND => '#FFF9DB',
                self::ROLE_SURFACE         => '#FFFFFF',
                self::ROLE_PRIMARY_TEXT    => '#5B3A00',
                self::ROLE_SECONDARY_TEXT  => '#8A6A2B',
                self::ROLE_BORDER          => '#E7C86E',
                self::ROLE_ACCENT          => '#F59E0B',
                self::ROLE_ACTIVE          => '#84CC16',
                self::ROLE_INACTIVE        => '#C7B37A',
                self::ROLE_SUCCESS         => '#22C55E',
                self::ROLE_WARNING         => '#F97316',
                self::ROLE_ERROR           => '#DC2626',
            ],
            default => throw new InvalidArgumentException('The selected theme is not supported.'),
        };
    }

    /**
     * Returns a complete validated palette.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array<string, string>
     */
    public static function resolvePalette(int $theme, array $customPalette = []): array
    {
        if ($theme !== self::THEME_CUSTOM) {
            return self::preset($theme);
        }

        $defaults = self::preset(self::THEME_DARK);
        $palette = [];

        foreach ($defaults as $role => $fallback) {
            $value = $customPalette[$role] ?? $fallback;
            $palette[$role] = self::normalizeColor($value, $fallback);
        }

        return $palette;
    }

    /**
     * Applies one semantic palette to the IPSView default color properties.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array<string, string> Effective palette
     */
    public static function apply(stdClass $document, int $theme, array $customPalette = []): array
    {
        $report = self::applyWithReport(
            $document,
            $theme,
            $customPalette,
            self::SCOPE_GLOBAL_DEFAULTS
        );

        return $report['palette'];
    }

    /**
     * Applies a semantic palette with the selected design scope.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array{
     *     palette: array<string, string>,
     *     scope: int,
     *     globalColorsApplied: int,
     *     controlColorsApplied: int,
     *     controlColorsPreserved: int
     * }
     */
    public static function applyWithReport(
        stdClass $document,
        int $theme,
        array $customPalette = [],
        int $scope = self::SCOPE_GLOBAL_DEFAULTS
    ): array {
        self::validateScope($scope);

        $palette = self::resolvePalette($theme, $customPalette);
        $controlAnalysis = self::analyzeControlColors($document);
        $report = [
            'palette'                => $palette,
            'scope'                  => $scope,
            'globalColorsApplied'    => 0,
            'controlColorsApplied'   => 0,
            'controlColorsPreserved' => $controlAnalysis['total'],
        ];

        if ($theme === self::THEME_STANDARD) {
            return $report;
        }

        $sourceRoleSignatures = self::sourceRoleSignatures($document);

        foreach (self::propertyMapping() as $role => $properties) {
            foreach ($properties as $property) {
                if (self::applyColor($document, $property, $palette[$role])) {
                    ++$report['globalColorsApplied'];
                }
            }
        }

        $shadow = self::mix($palette[self::ROLE_VIEW_BACKGROUND], '#000000', 0.68);
        foreach (['ColorPopupShadow', 'ColorAssocShadow', 'ShadowColor'] as $property) {
            if (self::applyColor($document, $property, $shadow)) {
                ++$report['globalColorsApplied'];
            }
        }

        if ($scope === self::SCOPE_GLOBAL_DEFAULTS) {
            return $report;
        }

        $controlReport = self::applyControlColors(
            $document,
            $palette,
            $sourceRoleSignatures,
            $scope
        );
        $report['controlColorsApplied'] = $controlReport['applied'];
        $report['controlColorsPreserved'] = $controlReport['preserved'];

        return $report;
    }

    /**
     * Analyzes which direct control colors can be safely or comprehensively themed.
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
    public static function analyze(stdClass $document): array
    {
        $controlAnalysis = self::analyzeControlColors($document);
        $globalColors = 0;

        foreach (self::propertyMapping() as $properties) {
            foreach ($properties as $property) {
                if (self::isColorObject($document->{$property} ?? null)) {
                    ++$globalColors;
                }
            }
        }

        foreach (['ColorPopupShadow', 'ColorAssocShadow', 'ShadowColor'] as $property) {
            if (self::isColorObject($document->{$property} ?? null)) {
                ++$globalColors;
            }
        }

        return [
            'globalColors'            => $globalColors,
            'controlColorsTotal'      => $controlAnalysis['total'],
            'matchingControlColors'   => $controlAnalysis['matching'],
            'allControlDefaults'      => $controlAnalysis['all'],
            'individualControlColors' => $controlAnalysis['total'] - $controlAnalysis['matching'],
            'specialControlColors'    => $controlAnalysis['total'] - $controlAnalysis['all'],
        ];
    }

    /**
     * Extracts one representative color for every semantic role.
     *
     * @return array<string, string>
     */
    public static function extract(stdClass $document): array
    {
        $fallbacks = self::preset(self::THEME_DARK);
        $representatives = [
            self::ROLE_VIEW_BACKGROUND => 'ColorPage',
            self::ROLE_PAGE_BACKGROUND => 'ColorPopupBack',
            self::ROLE_SURFACE         => 'ColorBack',
            self::ROLE_PRIMARY_TEXT    => 'ColorText',
            self::ROLE_SECONDARY_TEXT  => 'ColorTextOff',
            self::ROLE_BORDER          => 'ColorBorder',
            self::ROLE_ACCENT          => 'SwitchTrackColorActive',
            self::ROLE_ACTIVE          => 'ColorBackOn',
            self::ROLE_INACTIVE        => 'ColorBackOff',
            self::ROLE_SUCCESS         => 'FlowLineColorPositive',
            self::ROLE_WARNING         => 'ScheduleNowIndicatorColor',
            self::ROLE_ERROR           => 'FlowLineColorNegative',
        ];

        $palette = [];

        foreach ($representatives as $role => $property) {
            $value = $document->{$property} ?? null;
            $palette[$role] = self::colorObjectToHex($value, $fallbacks[$role]);
        }

        return $palette;
    }

    /**
     * Converts an IPSView color object to #RRGGBB.
     */
    public static function colorObjectToHex(mixed $color, string $fallback = '#000000'): string
    {
        if (!$color instanceof stdClass
            || !property_exists($color, 'R')
            || !property_exists($color, 'G')
            || !property_exists($color, 'B')) {
            return self::normalizeColor($fallback);
        }

        foreach (['R', 'G', 'B'] as $component) {
            if (!is_int($color->{$component}) && !is_float($color->{$component})) {
                return self::normalizeColor($fallback);
            }
        }

        return sprintf(
            '#%02X%02X%02X',
            max(0, min(255, (int) $color->R)),
            max(0, min(255, (int) $color->G)),
            max(0, min(255, (int) $color->B))
        );
    }

    /**
     * Normalizes a Symcon color integer or hexadecimal value to #RRGGBB.
     */
    public static function normalizeColor(mixed $color, string $fallback = '#000000'): string
    {
        if (is_int($color) || is_float($color)) {
            $integerColor = (int) $color;

            if ($integerColor >= 0 && $integerColor <= 0xFFFFFF) {
                return sprintf('#%06X', $integerColor);
            }

            return self::normalizeColor($fallback);
        }

        if (!is_string($color)) {
            return self::normalizeColor($fallback);
        }

        $color = strtoupper(trim($color));
        $color = preg_replace('/^(#|0X)/', '', $color) ?? '';

        if (preg_match('/^[0-9A-F]{6}$/', $color) !== 1) {
            return self::normalizeColor($fallback);
        }

        return '#' . $color;
    }

    /**
     * Converts a hexadecimal color to the integer format used by SelectColor.
     */
    public static function toFormColor(string $color): int
    {
        return hexdec(substr(self::normalizeColor($color), 1));
    }

    /**
     * Mixes two colors by the given amount of the second color.
     */
    public static function mix(string $first, string $second, float $amount): string
    {
        [$firstRed, $firstGreen, $firstBlue] = self::toRgb($first);
        [$secondRed, $secondGreen, $secondBlue] = self::toRgb($second);
        $amount = max(0.0, min(1.0, $amount));

        return sprintf(
            '#%02X%02X%02X',
            (int) round($firstRed + (($secondRed - $firstRed) * $amount)),
            (int) round($firstGreen + (($secondGreen - $firstGreen) * $amount)),
            (int) round($firstBlue + (($secondBlue - $firstBlue) * $amount))
        );
    }

    /**
     * Returns the semantic-role mapping for all supported global color properties.
     *
     * @return array<string, list<string>>
     */
    private static function propertyMapping(): array
    {
        return [
            self::ROLE_VIEW_BACKGROUND => [
                'ColorPage',
            ],
            self::ROLE_PAGE_BACKGROUND => [
                'ColorPopupBack',
                'DialogBackColor',
                'CalendarDayBackColor',
                'CalendarOffBackColor',
            ],
            self::ROLE_SURFACE => [
                'ColorBack',
                'ColorBackLabel',
                'ShadowBackColor',
                'DialogButtonBackColor',
            ],
            self::ROLE_PRIMARY_TEXT => [
                'ColorText',
                'ColorTextOn',
                'ColorTextLabel',
                'ColorIcon',
                'ColorAssocTextOn',
                'ColorTabTextOn',
                'FlowTextColorPositive',
                'FlowTextColorNegative',
                'GaugeTickColor',
                'GaugeLabelColor',
                'GaugeKnobColor',
                'ChartDotFillColor',
                'ScheduleDayOfWeekColor',
                'ScheduleLegendColor',
                'ScheduleItemsColor',
                'EventHeaderColor',
                'EventTextColor',
                'EventTextColorOn',
                'EventIconColor',
                'CalendarHeaderFontColor',
                'CalendarTodayFontColor',
                'CalendarDayFontColor',
                'DialogHeaderTextColor',
                'DialogTextColor',
                'DialogButtonTextColorEnabled',
            ],
            self::ROLE_SECONDARY_TEXT => [
                'ColorTextOff',
                'ColorAssocTextOff',
                'ColorTabTextOff',
                'FlowTextColorNeutral',
                'FlowAnimationColorNeutral',
                'ChartScaleFontColor',
                'ScheduleTimeColor',
                'EventTextColorOff',
                'CalendarWeekNumberFontColor',
                'CalendarOffFontColor',
                'CalendarTimeFontColor',
                'DialogButtonTextColorDisabled',
            ],
            self::ROLE_BORDER => [
                'ColorBorder',
                'ColorLine',
                'ColorBorderLabel',
                'ColorPopupBorder',
                'ColorAssocBorder',
                'FlowBorderColorPositive',
                'FlowBorderColorNegative',
                'FlowBorderColorNeutral',
                'GaugeTrackColor',
                'ChartGridColor',
                'ChartScaleLineColor',
                'ChartDotBorderColor',
                'CalendarGridColor',
                'ShadowBorderColor',
                'GridLineColor',
                'SliderThumbColorOuter',
                'ProgressbarThumbColorOuter',
                'CircleThumbColorOuter',
            ],
            self::ROLE_ACCENT => [
                'SwitchTrackColorActive',
                'SwitchThumbColorActive',
                'SliderTrackColorActive',
                'SliderTickColorInactive',
                'SliderThumbColorInner',
                'ProgressbarTrackColorActive',
                'ProgressbarTickColorInactive',
                'ProgressbarThumbColorInner',
                'CircleTrackColorActive',
                'CircleTickColorInactive',
                'CircleThumbColorInner',
                'GaugeNeedleColor',
                'ChartGraphFillColor',
                'ChartGraphLineColor',
                'ChartGraphLineColorMin',
                'CalendarTodayHighlightColor',
                'DialogDateTimePrimaryColor',
            ],
            self::ROLE_ACTIVE => [
                'ColorBackOn',
                'ColorAssocBackOn',
                'ColorTabBackOn',
                'GaugeTrackPointerColor',
            ],
            self::ROLE_INACTIVE => [
                'ColorBackOff',
                'ColorAssocBackOff',
                'SwitchTrackColorInactive',
                'SwitchThumbColorInactive',
                'SliderTrackColorInactive',
                'SliderTickColorActive',
                'ProgressbarTrackColorInactive',
                'ProgressbarTickColorActive',
                'CircleTrackColorInactive',
                'CircleTickColorActive',
                'DialogDateTimeSecondaryColor',
                'FlowLineColorNeutral',
            ],
            self::ROLE_SUCCESS => [
                'FlowLineColorPositive',
                'FlowAnimationColorPositive',
            ],
            self::ROLE_WARNING => [
                'ScheduleNowFontColor',
                'ScheduleNowIndicatorColor',
            ],
            self::ROLE_ERROR => [
                'FlowLineColorNegative',
                'FlowAnimationColorNegative',
                'ChartGraphLineColorMax',
            ],
        ];
    }

    /**
     * @return array{
     *     total: int,
     *     matching: int,
     *     all: int
     * }
     */
    private static function analyzeControlColors(stdClass $document): array
    {
        $counters = [
            'total'    => 0,
            'matching' => 0,
            'all'      => 0,
            'applied'  => 0,
        ];

        self::walkControlColors(
            $document,
            self::sourceRoleSignatures($document),
            null,
            self::SCOPE_GLOBAL_DEFAULTS,
            $counters
        );

        return [
            'total'    => $counters['total'],
            'matching' => $counters['matching'],
            'all'      => $counters['all'],
        ];
    }

    /**
     * Recursively recolors eligible controls according to the selected scope.
     *
     * @param array<string, string>              $palette
     * @param array<string, array<string, true>> $sourceRoleSignatures
     *
     * @return array{applied: int, preserved: int}
     */
    private static function applyControlColors(
        stdClass $document,
        array $palette,
        array $sourceRoleSignatures,
        int $scope
    ): array {
        $counters = [
            'total'    => 0,
            'matching' => 0,
            'all'      => 0,
            'applied'  => 0,
        ];

        self::walkControlColors(
            $document,
            $sourceRoleSignatures,
            $palette,
            $scope,
            $counters
        );

        return [
            'applied'   => $counters['applied'],
            'preserved' => $counters['total'] - $counters['applied'],
        ];
    }

    /**
     * Walks nested controls, applying semantic colors and updating report counters.
     *
     * @param array<string, array<string, true>> $sourceRoleSignatures
     * @param array<string, string>|null         $palette
     * @param array{total: int, matching: int, all: int, applied: int} $counters
     */
    private static function walkControlColors(
        mixed $value,
        array $sourceRoleSignatures,
        ?array $palette,
        int $scope,
        array &$counters
    ): void {
        if (is_array($value)) {
            foreach ($value as $item) {
                self::walkControlColors(
                    $item,
                    $sourceRoleSignatures,
                    $palette,
                    $scope,
                    $counters
                );
            }

            return;
        }

        if (!$value instanceof stdClass) {
            return;
        }

        foreach (get_object_vars($value) as $property => $child) {
            if (!self::isColorObject($child) || !self::isSupportedControlColorProperty($property)) {
                self::walkControlColors(
                    $child,
                    $sourceRoleSignatures,
                    $palette,
                    $scope,
                    $counters
                );

                continue;
            }

            ++$counters['total'];
            $signature = self::colorObjectToHex($child);
            $matchingRole = self::resolveControlRole(
                $property,
                $signature,
                $sourceRoleSignatures,
                self::SCOPE_MATCHING_CONTROLS
            );
            $allRole = self::resolveControlRole(
                $property,
                $signature,
                $sourceRoleSignatures,
                self::SCOPE_ALL_CONTROL_DEFAULTS
            );

            if ($matchingRole !== null) {
                ++$counters['matching'];
            }

            if ($allRole !== null) {
                ++$counters['all'];
            }

            if ($palette === null || $scope === self::SCOPE_GLOBAL_DEFAULTS) {
                continue;
            }

            $role = $scope === self::SCOPE_MATCHING_CONTROLS ? $matchingRole : $allRole;
            if ($role === null) {
                continue;
            }

            self::applyColorObject($child, $palette[$role]);
            ++$counters['applied'];
        }
    }

    /**
     * Collects the old normalized color signatures for every semantic role.
     *
     * @return array<string, array<string, true>>
     */
    private static function sourceRoleSignatures(stdClass $document): array
    {
        $signatures = [];

        foreach (self::propertyMapping() as $role => $properties) {
            $signatures[$role] = [];

            foreach ($properties as $property) {
                $color = $document->{$property} ?? null;
                if (!self::isColorObject($color)) {
                    continue;
                }

                $signatures[$role][self::colorObjectToHex($color)] = true;
            }
        }

        return $signatures;
    }

    /**
     * Resolves the semantic role of one control property from its old color signature.
     *
     * @param array<string, array<string, true>> $sourceRoleSignatures
     */
    private static function resolveControlRole(
        string $property,
        string $signature,
        array $sourceRoleSignatures,
        int $scope
    ): ?string {
        $preferredRole = self::preferredControlRole($property);
        $matchingRoles = [];

        foreach ($sourceRoleSignatures as $role => $signatures) {
            if (isset($signatures[$signature])) {
                $matchingRoles[] = $role;
            }
        }

        if ($scope === self::SCOPE_ALL_CONTROL_DEFAULTS && $preferredRole !== null) {
            return $preferredRole;
        }

        if ($preferredRole !== null && in_array($preferredRole, $matchingRoles, true)) {
            return $preferredRole;
        }

        if (count($matchingRoles) === 1) {
            return $matchingRoles[0];
        }

        return null;
    }

    /**
     * Maps a known control color property to its preferred semantic role.
     */
    private static function preferredControlRole(string $property): ?string
    {
        if (preg_match('/^BorderColor\d+$/', $property) === 1) {
            return self::ROLE_BORDER;
        }

        if (preg_match('/^ForeColor\d+$/', $property) === 1) {
            return self::ROLE_PRIMARY_TEXT;
        }

        if ($property === 'BackColor1') {
            return self::ROLE_SURFACE;
        }

        if (preg_match('/^BackColor[2-9]\d*$/', $property) === 1) {
            return self::ROLE_ACTIVE;
        }

        return null;
    }

    /**
     * Reports whether a property is a supported IPSView control color slot.
     */
    private static function isSupportedControlColorProperty(string $property): bool
    {
        return preg_match('/^(BackColor|ForeColor|BackColor\d+|ForeColor\d+|BorderColor\d+)$/', $property) === 1;
    }

    /**
     * Reports whether a value is an IPSView color object with numeric RGB components.
     */
    private static function isColorObject(mixed $value): bool
    {
        if (!$value instanceof stdClass) {
            return false;
        }

        foreach (['R', 'G', 'B'] as $component) {
            if (!property_exists($value, $component)
                || (!is_int($value->{$component}) && !is_float($value->{$component}))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rejects unsupported design-scope values before applying a theme.
     */
    private static function validateScope(int $scope): void
    {
        if (!in_array(
            $scope,
            [
                self::SCOPE_GLOBAL_DEFAULTS,
                self::SCOPE_MATCHING_CONTROLS,
                self::SCOPE_ALL_CONTROL_DEFAULTS,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected design scope is not supported.');
        }
    }

    /**
     * Applies a color to one existing global property and reports whether it was found.
     */
    private static function applyColor(stdClass $document, string $property, string $hexColor): bool
    {
        if (!property_exists($document, $property) || !self::isColorObject($document->{$property})) {
            return false;
        }

        self::applyColorObject($document->{$property}, $hexColor);

        return true;
    }

    /**
     * Recolors an IPSView color object while preserving its secondary-lightness relation.
     */
    private static function applyColorObject(stdClass $color, string $hexColor): void
    {
        $originalPrimary = self::colorObjectToHex($color, $hexColor);
        $originalSecondary = self::secondaryColorToHex($color, $originalPrimary);
        [$red, $green, $blue] = self::toRgb($hexColor);
        $color->R = $red;
        $color->G = $green;
        $color->B = $blue;

        if (!self::hasSecondaryColor($color)) {
            return;
        }

        $secondColor = self::deriveSecondaryColor(
            $hexColor,
            $originalPrimary,
            $originalSecondary
        );
        [$secondRed, $secondGreen, $secondBlue] = self::toRgb($secondColor);
        $color->R2 = $secondRed;
        $color->G2 = $secondGreen;
        $color->B2 = $secondBlue;
    }

    /**
     * Converts the optional secondary RGB components to a normalized hex color.
     */
    private static function secondaryColorToHex(stdClass $color, string $fallback): string
    {
        if (!self::hasSecondaryColor($color)) {
            return self::normalizeColor($fallback);
        }

        return sprintf(
            '#%02X%02X%02X',
            max(0, min(255, (int) $color->R2)),
            max(0, min(255, (int) $color->G2)),
            max(0, min(255, (int) $color->B2))
        );
    }

    /**
     * Reports whether a color object contains numeric secondary RGB components.
     */
    private static function hasSecondaryColor(stdClass $color): bool
    {
        foreach (['R2', 'G2', 'B2'] as $component) {
            if (!property_exists($color, $component)
                || (!is_int($color->{$component}) && !is_float($color->{$component}))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Transfers the old primary-to-secondary luminance relation to a new color.
     */
    private static function deriveSecondaryColor(
        string $newPrimary,
        string $oldPrimary,
        string $oldSecondary
    ): string {
        $oldPrimaryLuminance = self::relativeLuminance($oldPrimary);
        $oldSecondaryLuminance = self::relativeLuminance($oldSecondary);
        $difference = $oldSecondaryLuminance - $oldPrimaryLuminance;

        if (abs($difference) < 0.005) {
            return self::normalizeColor($newPrimary);
        }

        if ($difference < 0.0) {
            $amount = 1.0 - ($oldSecondaryLuminance / max(0.001, $oldPrimaryLuminance));

            return self::mix($newPrimary, '#000000', min(0.65, max(0.0, $amount)));
        }

        $amount = $difference / max(0.001, 1.0 - $oldPrimaryLuminance);

        return self::mix($newPrimary, '#FFFFFF', min(0.65, max(0.0, $amount)));
    }

    /**
     * Calculates a simple weighted luminance value for a normalized color.
     */
    private static function relativeLuminance(string $color): float
    {
        [$red, $green, $blue] = self::toRgb($color);

        return (($red / 255) * 0.2126)
            + (($green / 255) * 0.7152)
            + (($blue / 255) * 0.0722);
    }

    /**
     * Converts a normalized hex color to integer RGB components.
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private static function toRgb(string $color): array
    {
        $color = self::normalizeColor($color);

        return [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2)),
        ];
    }
}

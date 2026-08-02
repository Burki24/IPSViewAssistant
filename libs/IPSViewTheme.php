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
     * The original IPSView Standard theme is retained byte-for-byte when the
     * standard preset is selected. Light, dark and custom themes are mapped to
     * all known top-level IPSView color defaults.
     *
     * @param array<string, mixed> $customPalette
     *
     * @return array<string, string> Effective palette
     */
    public static function apply(stdClass $document, int $theme, array $customPalette = []): array
    {
        $palette = self::resolvePalette($theme, $customPalette);

        if ($theme === self::THEME_STANDARD) {
            return $palette;
        }

        $mapping = self::propertyMapping();

        foreach ($mapping as $role => $properties) {
            foreach ($properties as $property) {
                self::applyColor($document, $property, $palette[$role]);
            }
        }

        $shadow = self::mix($palette[self::ROLE_VIEW_BACKGROUND], '#000000', 0.68);
        foreach (['ColorPopupShadow', 'ColorAssocShadow', 'ShadowColor'] as $property) {
            self::applyColor($document, $property, $shadow);
        }

        return $palette;
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

    private static function applyColor(stdClass $document, string $property, string $hexColor): void
    {
        if (!property_exists($document, $property) || !$document->{$property} instanceof stdClass) {
            return;
        }

        [$red, $green, $blue] = self::toRgb($hexColor);
        $color = $document->{$property};
        $color->R = $red;
        $color->G = $green;
        $color->B = $blue;
        $color->Type = 0;

        if (property_exists($color, 'R2')) {
            [$secondRed, $secondGreen, $secondBlue] = self::toRgb(self::mix($hexColor, '#000000', 0.22));
            $color->R2 = $secondRed;
            $color->G2 = $secondGreen;
            $color->B2 = $secondBlue;
        }
    }

    /**
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

<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use stdClass;

final class IPSViewTypography
{
    public const STYLE_PRESERVE = 0;
    public const STYLE_COMPACT = 1;
    public const STYLE_STANDARD = 2;
    public const STYLE_LARGE = 3;
    public const STYLE_CUSTOM = 4;

    public const FONT_PRESERVE = 0;
    public const FONT_ROBOTO = 1;
    public const FONT_ROBOTO_MONO = 2;
    public const FONT_DANCING_SCRIPT = 3;
    public const FONT_INDIE_FLOWER = 4;
    public const FONT_OPEN_SANS = 5;
    public const FONT_PT_SANS = 6;
    public const FONT_BEBAS_NEUE = 7;
    public const FONT_SEGMENT_7 = 8;

    /**
     * @var list<string>
     */
    private const GLOBAL_FONT_SIZE_PROPERTIES = [
        'DefaultFontSize',
        'AssocFontSize',
        'GaugeLabelFontSize',
        'ChartScaleFontSize',
        'ScheduleDayOfWeekFontSize',
        'ScheduleTimeFontSize',
        'ScheduleLegendFontSize',
        'ScheduleItemsFontSize',
        'ScheduleNowFontSize',
        'EventHeaderFontSize',
        'EventTextFontSize',
        'CalendarHeaderFontSize',
        'CalendarWeekNumberFontSize',
        'CalendarTodayFontSize',
        'CalendarDayFontSize',
        'CalendarOffFontSize',
        'CalendarTimeFontSize',
        'DialogHeaderTextSize',
        'DialogButtonTextSize',
    ];

    /**
     * Returns validated typography settings.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     typographyStyle: int,
     *     fontFamilyMode: int,
     *     customFontFamily: string,
     *     customFontSize: int
     * }
     */
    public static function resolve(array $settings = []): array
    {
        $resolved = [
            'typographyStyle' => (int) ($settings['typographyStyle'] ?? self::STYLE_PRESERVE),
            'fontFamilyMode'  => (int) ($settings['fontFamilyMode'] ?? self::FONT_PRESERVE),
            'customFontFamily'=> trim((string) ($settings['customFontFamily'] ?? '')),
            'customFontSize'  => max(8, min(32, (int) ($settings['customFontSize'] ?? 11))),
        ];

        if (!in_array(
            $resolved['typographyStyle'],
            [
                self::STYLE_PRESERVE,
                self::STYLE_COMPACT,
                self::STYLE_STANDARD,
                self::STYLE_LARGE,
                self::STYLE_CUSTOM,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected typography style is not supported.');
        }

        if (!in_array(
            $resolved['fontFamilyMode'],
            [
                self::FONT_PRESERVE,
                self::FONT_ROBOTO,
                self::FONT_ROBOTO_MONO,
                self::FONT_DANCING_SCRIPT,
                self::FONT_INDIE_FLOWER,
                self::FONT_OPEN_SANS,
                self::FONT_PT_SANS,
                self::FONT_BEBAS_NEUE,
                self::FONT_SEGMENT_7,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected font family mode is not supported.');
        }

        if (strlen($resolved['customFontFamily']) > 80) {
            throw new InvalidArgumentException('The detected font family must not exceed 80 characters.');
        }

        return $resolved;
    }

    /**
     * Applies global typography and optional safe control-font updates.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{globalTypographyApplied: int, controlTypographyApplied: int}
     */
    public static function apply(
        stdClass $document,
        array $settings = [],
        int $scope = IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
    ): array {
        $settings = self::resolve($settings);
        $previousBaseSize = max(1, (int) ($document->DefaultFontSize ?? 11));
        $previousFontFamily = (string) ($document->DefaultFontFamily ?? '');
        $targetBaseSize = self::resolveBaseSize($settings);
        $targetFontFamily = self::resolveFontFamily($settings);
        $scale = $targetBaseSize === null ? 1.0 : $targetBaseSize / $previousBaseSize;
        $globalChanges = 0;

        if ($targetBaseSize !== null) {
            foreach (self::GLOBAL_FONT_SIZE_PROPERTIES as $property) {
                if (!isset($document->{$property}) || !is_numeric($document->{$property})) {
                    continue;
                }

                $newSize = $property === 'DefaultFontSize'
                    ? $targetBaseSize
                    : self::scaleFontSize((int) $document->{$property}, $scale);

                if ((int) $document->{$property} === $newSize) {
                    continue;
                }

                $document->{$property} = $newSize;
                ++$globalChanges;
            }
        }

        if (
            $targetFontFamily !== null
            && (string) ($document->DefaultFontFamily ?? '') !== $targetFontFamily
        ) {
            $document->DefaultFontFamily = $targetFontFamily;
            ++$globalChanges;
        }

        $controlChanges = 0;
        if (
            $scope !== IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
            && ($targetBaseSize !== null || $targetFontFamily !== null)
        ) {
            $controlChanges = self::applyControlTypography(
                $document,
                $scope,
                $previousBaseSize,
                $previousFontFamily,
                $targetBaseSize,
                $targetFontFamily,
                $scale
            );
        }

        return [
            'globalTypographyApplied'  => $globalChanges,
            'controlTypographyApplied' => $controlChanges,
        ];
    }

    /**
     * Reads the current global typography values.
     *
     * @return array{fontFamily: string, baseFontSize: int}
     */
    public static function extract(stdClass $document): array
    {
        return [
            'fontFamily'  => (string) ($document->DefaultFontFamily ?? ''),
            'baseFontSize'=> max(8, min(32, (int) ($document->DefaultFontSize ?? 11))),
        ];
    }

    /**
     * Returns the effective preview typography.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{fontFamily: string, baseFontSize: int}
     */
    public static function preview(array $settings = []): array
    {
        $settings = self::resolve($settings);
        $fontFamily = self::resolveFontFamily($settings);
        $baseFontSize = self::resolveBaseSize($settings);

        if ($fontFamily === null) {
            $fontFamily = $settings['customFontFamily'];
        }

        return [
            'fontFamily'  => $fontFamily === '' ? 'Roboto' : $fontFamily,
            'baseFontSize'=> $baseFontSize ?? $settings['customFontSize'],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolveBaseSize(array $settings): ?int
    {
        return match ($settings['typographyStyle']) {
            self::STYLE_COMPACT  => 11,
            self::STYLE_STANDARD => 14,
            self::STYLE_LARGE    => 18,
            self::STYLE_CUSTOM   => $settings['customFontSize'],
            default              => null,
        };
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolveFontFamily(array $settings): ?string
    {
        return match ($settings['fontFamilyMode']) {
            self::FONT_ROBOTO         => 'Roboto',
            self::FONT_ROBOTO_MONO    => 'RobotoMono',
            self::FONT_DANCING_SCRIPT => 'DancingScript',
            self::FONT_INDIE_FLOWER   => 'IndieFlower',
            self::FONT_OPEN_SANS      => 'OpenSans',
            self::FONT_PT_SANS        => 'PTSans',
            self::FONT_BEBAS_NEUE     => 'BebasNeue',
            self::FONT_SEGMENT_7      => 'Segment7',
            default                   => null,
        };
    }

    private static function scaleFontSize(int $size, float $scale): int
    {
        return max(6, min(72, (int) round($size * $scale)));
    }

    private static function applyControlTypography(
        mixed $value,
        int $scope,
        int $previousBaseSize,
        string $previousFontFamily,
        ?int $targetBaseSize,
        ?string $targetFontFamily,
        float $scale
    ): int {
        if (is_array($value)) {
            $changes = 0;

            foreach ($value as $item) {
                $changes += self::applyControlTypography(
                    $item,
                    $scope,
                    $previousBaseSize,
                    $previousFontFamily,
                    $targetBaseSize,
                    $targetFontFamily,
                    $scale
                );
            }

            return $changes;
        }

        if (!$value instanceof stdClass) {
            return 0;
        }

        $changes = 0;
        $font = $value->Font ?? null;

        if ($font instanceof stdClass) {
            $fontChanged = self::applyFontObject(
                $font,
                $scope,
                $previousBaseSize,
                $previousFontFamily,
                $targetBaseSize,
                $targetFontFamily,
                $scale
            );

            if ($fontChanged) {
                ++$changes;
            }
        }

        foreach (get_object_vars($value) as $property => $child) {
            if ($property === 'Font') {
                continue;
            }

            $changes += self::applyControlTypography(
                $child,
                $scope,
                $previousBaseSize,
                $previousFontFamily,
                $targetBaseSize,
                $targetFontFamily,
                $scale
            );
        }

        return $changes;
    }

    private static function applyFontObject(
        stdClass $font,
        int $scope,
        int $previousBaseSize,
        string $previousFontFamily,
        ?int $targetBaseSize,
        ?string $targetFontFamily,
        float $scale
    ): bool {
        $changed = false;
        $currentFamily = (string) ($font->FontFamily ?? '');
        $currentSize = isset($font->Size) && is_numeric($font->Size)
            ? (int) $font->Size
            : null;

        if ($scope === IPSViewTheme::SCOPE_ALL_CONTROL_DEFAULTS) {
            if ($targetFontFamily !== null && $currentFamily !== $targetFontFamily) {
                $font->FontFamily = $targetFontFamily;
                $changed = true;
            }

            if ($targetBaseSize !== null && $currentSize !== null) {
                $newSize = self::scaleFontSize($currentSize, $scale);
                if ($newSize !== $currentSize) {
                    $font->Size = $newSize;
                    $changed = true;
                }
            }

            return $changed;
        }

        $matchesDefaultFamily = $currentFamily === '' || $currentFamily === $previousFontFamily;
        if (
            $targetFontFamily !== null
            && $matchesDefaultFamily
            && $currentFamily !== $targetFontFamily
        ) {
            $font->FontFamily = $targetFontFamily;
            $changed = true;
        }

        if (
            $targetBaseSize !== null
            && $currentSize === $previousBaseSize
            && $currentSize !== $targetBaseSize
        ) {
            $font->Size = $targetBaseSize;
            $changed = true;
        }

        return $changed;
    }
}

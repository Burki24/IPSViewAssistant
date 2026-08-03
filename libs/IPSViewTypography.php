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

    public const FORMAT_PRESERVE = 0;
    public const FORMAT_OFF = 1;
    public const FORMAT_ON = 2;

    /**
     * @var array<string, array{bold: bool, italic: bool}>
     */
    private const FONT_CAPABILITIES = [
        'Roboto'        => [
            'bold'   => true,
            'italic' => true,
        ],
        'RobotoMono'    => [
            'bold'   => true,
            'italic' => true,
        ],
        'DancingScript' => [
            'bold'   => true,
            'italic' => false,
        ],
        'IndieFlower'   => [
            'bold'   => false,
            'italic' => false,
        ],
        'OpenSans'      => [
            'bold'   => true,
            'italic' => true,
        ],
        'PTSans'        => [
            'bold'   => true,
            'italic' => true,
        ],
        'BebasNeue'     => [
            'bold'   => false,
            'italic' => false,
        ],
        'Segment7'      => [
            'bold'   => false,
            'italic' => false,
        ],
    ];

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
     *     customFontSize: int,
     *     fontBoldMode: int,
     *     fontItalicMode: int,
     *     fontUnderlineMode: int
     * }
     */
    public static function resolve(array $settings = []): array
    {
        $resolved = [
            'typographyStyle'   => (int) ($settings['typographyStyle'] ?? self::STYLE_PRESERVE),
            'fontFamilyMode'    => (int) ($settings['fontFamilyMode'] ?? self::FONT_PRESERVE),
            'customFontFamily'  => trim((string) ($settings['customFontFamily'] ?? '')),
            'customFontSize'    => max(8, min(32, (int) ($settings['customFontSize'] ?? 11))),
            'fontBoldMode'      => (int) ($settings['fontBoldMode'] ?? self::FORMAT_PRESERVE),
            'fontItalicMode'    => (int) ($settings['fontItalicMode'] ?? self::FORMAT_PRESERVE),
            'fontUnderlineMode' => (int) ($settings['fontUnderlineMode'] ?? self::FORMAT_PRESERVE),
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

        foreach (['fontBoldMode', 'fontItalicMode', 'fontUnderlineMode'] as $formatMode) {
            if (!in_array(
                $resolved[$formatMode],
                [self::FORMAT_PRESERVE, self::FORMAT_OFF, self::FORMAT_ON],
                true
            )) {
                throw new InvalidArgumentException('The selected font formatting mode is not supported.');
            }
        }

        if (strlen($resolved['customFontFamily']) > 80) {
            throw new InvalidArgumentException('The detected font family must not exceed 80 characters.');
        }

        $selectedFontFamily = self::resolveFontFamily($resolved);
        if ($selectedFontFamily !== null) {
            $capabilities = self::capabilitiesForFamily($selectedFontFamily);

            if (!$capabilities['bold']) {
                $resolved['fontBoldMode'] = self::FORMAT_OFF;
            }

            if (!$capabilities['italic']) {
                $resolved['fontItalicMode'] = self::FORMAT_OFF;
            }
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
        $targetBold = self::resolveFormatValue($settings['fontBoldMode']);
        $targetItalic = self::resolveFormatValue($settings['fontItalicMode']);
        $targetUnderline = self::resolveFormatValue($settings['fontUnderlineMode']);
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
            && (
                $targetBaseSize !== null
                || $targetFontFamily !== null
                || $targetBold !== null
                || $targetItalic !== null
                || $targetUnderline !== null
            )
        ) {
            $controlChanges = self::applyControlTypography(
                $document,
                $scope,
                $previousBaseSize,
                $previousFontFamily,
                $targetBaseSize,
                $targetFontFamily,
                $targetBold,
                $targetItalic,
                $targetUnderline,
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
            'fontFamily'   => (string) ($document->DefaultFontFamily ?? ''),
            'baseFontSize' => max(8, min(32, (int) ($document->DefaultFontSize ?? 11))),
        ];
    }

    /**
     * Returns the effective preview typography.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     fontFamily: string,
     *     baseFontSize: int,
     *     isBold: bool,
     *     isItalic: bool,
     *     isUnderline: bool
     * }
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
            'fontFamily'   => $fontFamily === '' ? 'Roboto' : $fontFamily,
            'baseFontSize' => $baseFontSize ?? $settings['customFontSize'],
            'isBold'       => self::resolveFormatValue($settings['fontBoldMode']) ?? false,
            'isItalic'     => self::resolveFormatValue($settings['fontItalicMode']) ?? false,
            'isUnderline'  => self::resolveFormatValue($settings['fontUnderlineMode']) ?? false,
        ];
    }

    /**
     * Returns the formatting options supported by the selected fixed IPSView font.
     * Unknown or preserved fonts remain unrestricted because their installed cuts are not known.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{bold: bool, italic: bool, underline: bool}
     */
    public static function selectedCapabilities(array $settings = []): array
    {
        $fontFamilyMode = (int) ($settings['fontFamilyMode'] ?? self::FONT_PRESERVE);
        if ($fontFamilyMode === self::FONT_PRESERVE) {
            return [
                'bold'      => true,
                'italic'    => true,
                'underline' => true,
            ];
        }

        $resolved = self::resolve($settings);
        $fontFamily = self::resolveFontFamily($resolved);
        $capabilities = $fontFamily === null
            ? ['bold' => true, 'italic' => true]
            : self::capabilitiesForFamily($fontFamily);

        return [
            ...$capabilities,
            'underline' => true,
        ];
    }

    /**
     * Resolves a typography-size preset to its effective base size.
     *
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
     * Resolves a font-family mode to the IPSView family name to apply.
     *
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

    /**
     * Returns the available bold and italic faces for one font family.
     *
     * @return array{bold: bool, italic: bool}
     */
    private static function capabilitiesForFamily(string $fontFamily): array
    {
        return self::FONT_CAPABILITIES[$fontFamily] ?? [
            'bold'   => true,
            'italic' => true,
        ];
    }

    /**
     * Converts a preserve/off/on format mode to null, false or true.
     */
    private static function resolveFormatValue(int $mode): ?bool
    {
        return match ($mode) {
            self::FORMAT_OFF => false,
            self::FORMAT_ON  => true,
            default          => null,
        };
    }

    /**
     * Scales and clamps a control font size to the supported range.
     */
    private static function scaleFontSize(int $size, float $scale): int
    {
        return max(6, min(72, (int) round($size * $scale)));
    }

    /**
     * Recursively updates eligible control fonts and returns the change count.
     */
    private static function applyControlTypography(
        mixed $value,
        int $scope,
        int $previousBaseSize,
        string $previousFontFamily,
        ?int $targetBaseSize,
        ?string $targetFontFamily,
        ?bool $targetBold,
        ?bool $targetItalic,
        ?bool $targetUnderline,
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
                    $targetBold,
                    $targetItalic,
                    $targetUnderline,
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
                $targetBold,
                $targetItalic,
                $targetUnderline,
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
                $targetBold,
                $targetItalic,
                $targetUnderline,
                $scale
            );
        }

        return $changes;
    }

    /**
     * Applies the selected family, size and formatting to one control font.
     */
    private static function applyFontObject(
        stdClass $font,
        int $scope,
        int $previousBaseSize,
        string $previousFontFamily,
        ?int $targetBaseSize,
        ?string $targetFontFamily,
        ?bool $targetBold,
        ?bool $targetItalic,
        ?bool $targetUnderline,
        float $scale
    ): bool {
        $changed = false;
        $currentFamily = (string) ($font->FontFamily ?? '');
        $currentSize = isset($font->Size) && is_numeric($font->Size)
            ? (int) $font->Size
            : null;
        $matchesDefaultFamily = $currentFamily === '' || $currentFamily === $previousFontFamily;

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

            return self::applyFontFormatting(
                $font,
                $targetBold,
                $targetItalic,
                $targetUnderline
            ) || $changed;
        }

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

        if ($matchesDefaultFamily) {
            $changed = self::applyFontFormatting(
                $font,
                $targetBold,
                $targetItalic,
                $targetUnderline
            ) || $changed;
        }

        return $changed;
    }

    /**
     * Applies explicitly selected bold, italic and underline flags.
     */
    private static function applyFontFormatting(
        stdClass $font,
        ?bool $targetBold,
        ?bool $targetItalic,
        ?bool $targetUnderline
    ): bool {
        $changed = false;

        foreach (
            [
                'isBold'      => $targetBold,
                'isItalic'    => $targetItalic,
                'isUnderline' => $targetUnderline,
            ] as $property => $targetValue
        ) {
            if ($targetValue === null || (bool) ($font->{$property} ?? false) === $targetValue) {
                continue;
            }

            $font->{$property} = $targetValue;
            $changed = true;
        }

        return $changed;
    }
}

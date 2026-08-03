<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use stdClass;

final class IPSViewEffects
{
    public const SHADOW_PRESERVE = 0;
    public const SHADOW_NONE = 1;
    public const SHADOW_SUBTLE = 2;
    public const SHADOW_MEDIUM = 3;
    public const SHADOW_STRONG = 4;

    public const TRANSPARENCY_PRESERVE = 0;
    public const TRANSPARENCY_OPAQUE = 1;
    public const TRANSPARENCY_CUSTOM = 2;

    public const GRADIENT_PRESERVE = 0;
    public const GRADIENT_NONE = 1;
    public const GRADIENT_SUBTLE = 2;
    public const GRADIENT_MEDIUM = 3;
    public const GRADIENT_STRONG = 4;

    public const GRADIENT_TO_DARKER = 0;
    public const GRADIENT_TO_LIGHTER = 1;

    /**
     * @var list<string>
     */
    private const GLOBAL_FILL_PROPERTIES = [
        'ColorPage',
        'ColorPopupBack',
        'DialogBackColor',
        'CalendarDayBackColor',
        'CalendarOffBackColor',
        'ColorBack',
        'ColorBackLabel',
        'ShadowBackColor',
        'DialogButtonBackColor',
        'ColorBackOn',
        'ColorAssocBackOn',
        'ColorTabBackOn',
        'GaugeTrackPointerColor',
        'ColorBackOff',
        'ColorAssocBackOff',
        'SwitchTrackColorActive',
        'SwitchTrackColorInactive',
        'SliderTrackColorActive',
        'SliderTrackColorInactive',
        'ProgressbarTrackColorActive',
        'ProgressbarTrackColorInactive',
        'CircleTrackColorActive',
        'CircleTrackColorInactive',
        'ChartGraphFillColor',
        'CalendarTodayHighlightColor',
        'DialogDateTimePrimaryColor',
        'DialogDateTimeSecondaryColor',
    ];

    /**
     * Returns validated effect settings.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     shadowStyle: int,
     *     transparencyMode: int,
     *     transparencyPercent: int,
     *     gradientStyle: int,
     *     gradientDirection: int
     * }
     */
    public static function resolve(array $settings = []): array
    {
        $resolved = [
            'shadowStyle'         => (int) ($settings['shadowStyle'] ?? self::SHADOW_PRESERVE),
            'transparencyMode'    => (int) ($settings['transparencyMode'] ?? self::TRANSPARENCY_PRESERVE),
            'transparencyPercent' => max(0, min(100, (int) ($settings['transparencyPercent'] ?? 15))),
            'gradientStyle'       => (int) ($settings['gradientStyle'] ?? self::GRADIENT_PRESERVE),
            'gradientDirection'   => (int) ($settings['gradientDirection'] ?? self::GRADIENT_TO_DARKER),
        ];

        if (!in_array(
            $resolved['shadowStyle'],
            [
                self::SHADOW_PRESERVE,
                self::SHADOW_NONE,
                self::SHADOW_SUBTLE,
                self::SHADOW_MEDIUM,
                self::SHADOW_STRONG,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected shadow style is not supported.');
        }

        if (!in_array(
            $resolved['transparencyMode'],
            [
                self::TRANSPARENCY_PRESERVE,
                self::TRANSPARENCY_OPAQUE,
                self::TRANSPARENCY_CUSTOM,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected transparency mode is not supported.');
        }

        if (!in_array(
            $resolved['gradientStyle'],
            [
                self::GRADIENT_PRESERVE,
                self::GRADIENT_NONE,
                self::GRADIENT_SUBTLE,
                self::GRADIENT_MEDIUM,
                self::GRADIENT_STRONG,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected gradient style is not supported.');
        }

        if (!in_array(
            $resolved['gradientDirection'],
            [
                self::GRADIENT_TO_DARKER,
                self::GRADIENT_TO_LIGHTER,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected gradient direction is not supported.');
        }

        return $resolved;
    }

    /**
     * Applies general shadow, transparency and gradient settings.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     globalEffectsApplied: int,
     *     controlEffectsApplied: int,
     *     shadowChanged: bool
     * }
     */
    public static function apply(
        stdClass $document,
        array $settings = [],
        int $scope = IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
    ): array {
        $settings = self::resolve($settings);
        $globalSignatures = self::globalFillSignatures($document);
        $report = [
            'globalEffectsApplied'  => 0,
            'controlEffectsApplied' => 0,
            'shadowChanged'         => false,
        ];

        if ($settings['shadowStyle'] !== self::SHADOW_PRESERVE) {
            self::applyShadow($document, $settings['shadowStyle']);
            $report['shadowChanged'] = true;
        }

        if (
            $settings['transparencyMode'] === self::TRANSPARENCY_PRESERVE
            && $settings['gradientStyle'] === self::GRADIENT_PRESERVE
        ) {
            return $report;
        }

        foreach (self::GLOBAL_FILL_PROPERTIES as $property) {
            $color = $document->{$property} ?? null;

            if (!$color instanceof stdClass || !self::isColorObject($color)) {
                continue;
            }

            if (self::applyColorEffects($color, $settings)) {
                ++$report['globalEffectsApplied'];
            }
        }

        if ($scope === IPSViewTheme::SCOPE_GLOBAL_DEFAULTS) {
            return $report;
        }

        $report['controlEffectsApplied'] = self::applyControlEffects(
            $document,
            $settings,
            $scope,
            $globalSignatures
        );

        return $report;
    }

    /**
     * Returns the preview opacity from zero to one.
     *
     * @param array<string, mixed> $settings
     */
    public static function previewOpacity(array $settings = []): float
    {
        $settings = self::resolve($settings);

        return match ($settings['transparencyMode']) {
            self::TRANSPARENCY_OPAQUE => 1.0,
            self::TRANSPARENCY_CUSTOM => 1.0 - ($settings['transparencyPercent'] / 100),
            default                   => 1.0,
        };
    }

    /**
     * Returns the secondary color for a preview or IPSView gradient.
     *
     * @param array<string, mixed> $settings
     */
    public static function gradientColor(string $primary, array $settings = []): string
    {
        $settings = self::resolve($settings);
        $amount = match ($settings['gradientStyle']) {
            self::GRADIENT_SUBTLE => 0.12,
            self::GRADIENT_MEDIUM => 0.26,
            self::GRADIENT_STRONG => 0.42,
            default               => 0.0,
        };

        if ($amount <= 0.0) {
            return IPSViewTheme::normalizeColor($primary);
        }

        $target = $settings['gradientDirection'] === self::GRADIENT_TO_LIGHTER
            ? '#FFFFFF'
            : '#000000';

        return IPSViewTheme::mix($primary, $target, $amount);
    }

    /**
     * Returns whether the preview should render a generated gradient.
     *
     * @param array<string, mixed> $settings
     */
    public static function hasGeneratedGradient(array $settings = []): bool
    {
        $settings = self::resolve($settings);

        return in_array(
            $settings['gradientStyle'],
            [
                self::GRADIENT_SUBTLE,
                self::GRADIENT_MEDIUM,
                self::GRADIENT_STRONG,
            ],
            true
        );
    }

    /**
     * Resolves the shadow geometry used by the SVG design preview.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{offset: int, blur: int, spread: int, opacity: float}
     */
    public static function previewShadow(array $settings = []): array
    {
        $settings = self::resolve($settings);

        return match ($settings['shadowStyle']) {
            self::SHADOW_NONE => [
                'offset'  => 0,
                'blur'    => 0,
                'spread'  => 0,
                'opacity' => 0.0,
            ],
            self::SHADOW_SUBTLE => [
                'offset'  => 2,
                'blur'    => 4,
                'spread'  => 1,
                'opacity' => 0.22,
            ],
            self::SHADOW_STRONG => [
                'offset'  => 7,
                'blur'    => 14,
                'spread'  => 4,
                'opacity' => 0.62,
            ],
            self::SHADOW_MEDIUM => [
                'offset'  => 4,
                'blur'    => 8,
                'spread'  => 2,
                'opacity' => 0.42,
            ],
            default => [
                'offset'  => 4,
                'blur'    => 8,
                'spread'  => 2,
                'opacity' => 0.42,
            ],
        };
    }

    /**
     * Applies one supported shadow preset to the global IPSView defaults.
     */
    private static function applyShadow(stdClass $document, int $shadowStyle): void
    {
        $specification = match ($shadowStyle) {
            self::SHADOW_NONE => [
                'spread' => 0,
                'blur'   => 0,
                'offset' => 0,
                'alpha'  => 0,
            ],
            self::SHADOW_SUBTLE => [
                'spread' => 1,
                'blur'   => 4,
                'offset' => 2,
                'alpha'  => 80,
            ],
            self::SHADOW_MEDIUM => [
                'spread' => 2,
                'blur'   => 8,
                'offset' => 4,
                'alpha'  => 130,
            ],
            self::SHADOW_STRONG => [
                'spread' => 4,
                'blur'   => 14,
                'offset' => 7,
                'alpha'  => 190,
            ],
            default => throw new InvalidArgumentException('The selected shadow style is not supported.'),
        };

        $document->ShadowSpreadRadius = $specification['spread'];
        $document->ShadowBlurRadius = $specification['blur'];
        $document->ShadowOffsetX = $specification['offset'];
        $document->ShadowOffsetY = $specification['offset'];

        $viewBackground = IPSViewTheme::colorObjectToHex(
            $document->ColorPage ?? null,
            '#000000'
        );
        $shadowColor = IPSViewTheme::mix($viewBackground, '#000000', 0.82);

        foreach (['ColorPopupShadow', 'ColorAssocShadow', 'ShadowColor'] as $property) {
            $color = $document->{$property} ?? null;

            if (!$color instanceof stdClass || !self::isColorObject($color)) {
                continue;
            }

            [$red, $green, $blue] = self::toRgb($shadowColor);
            $color->A = $specification['alpha'];
            $color->R = $red;
            $color->G = $green;
            $color->B = $blue;
            $color->Type = 0;

            if (property_exists($color, 'A2')) {
                $color->A2 = $specification['alpha'];
            }
        }
    }

    /**
     * Applies transparency and gradient settings to one compatible color object.
     *
     * @param array<string, mixed> $settings
     */
    private static function applyColorEffects(stdClass $color, array $settings): bool
    {
        $before = json_encode($color);

        self::applyTransparency($color, $settings);
        self::applyGradient($color, $settings);

        return $before !== json_encode($color);
    }

    /**
     * Applies the selected alpha value while preserving secondary-color consistency.
     *
     * @param array<string, mixed> $settings
     */
    private static function applyTransparency(stdClass $color, array $settings): void
    {
        if ($settings['transparencyMode'] === self::TRANSPARENCY_PRESERVE) {
            return;
        }

        $alpha = $settings['transparencyMode'] === self::TRANSPARENCY_OPAQUE
            ? 255
            : (int) round(255 * (1 - ($settings['transparencyPercent'] / 100)));

        $color->A = max(0, min(255, $alpha));

        if (property_exists($color, 'A2')) {
            $color->A2 = $color->A;
        }
    }

    /**
     * Applies or removes the generated secondary color used by IPSView gradients.
     *
     * @param array<string, mixed> $settings
     */
    private static function applyGradient(stdClass $color, array $settings): void
    {
        if ($settings['gradientStyle'] === self::GRADIENT_PRESERVE) {
            return;
        }

        if ($settings['gradientStyle'] === self::GRADIENT_NONE) {
            $color->Type = 0;

            if (self::hasSecondaryColor($color)) {
                $color->R2 = $color->R;
                $color->G2 = $color->G;
                $color->B2 = $color->B;

                if (property_exists($color, 'A2')) {
                    $color->A2 = (int) ($color->A ?? 255);
                }
            }

            return;
        }

        if (!property_exists($color, 'Type') || (int) $color->Type === 0) {
            $color->Type = 1;
        }

        $primary = IPSViewTheme::colorObjectToHex($color);
        $secondary = self::gradientColor($primary, $settings);
        [$secondRed, $secondGreen, $secondBlue] = self::toRgb($secondary);
        $color->R2 = $secondRed;
        $color->G2 = $secondGreen;
        $color->B2 = $secondBlue;
        $color->A2 = (int) ($color->A ?? 255);
    }

    /**
     * Collects the normalized colors of all global fill properties.
     *
     * @return array<string, true>
     */
    private static function globalFillSignatures(stdClass $document): array
    {
        $signatures = [];

        foreach (self::GLOBAL_FILL_PROPERTIES as $property) {
            $color = $document->{$property} ?? null;

            if (!$color instanceof stdClass || !self::isColorObject($color)) {
                continue;
            }

            $signatures[IPSViewTheme::colorObjectToHex($color)] = true;
        }

        return $signatures;
    }

    /**
     * Recursively applies effects to eligible control backgrounds.
     *
     * @param array<string, mixed> $settings
     * @param array<string, true>  $globalSignatures
     */
    private static function applyControlEffects(
        mixed $value,
        array $settings,
        int $scope,
        array $globalSignatures
    ): int {
        if (is_array($value)) {
            $applied = 0;

            foreach ($value as $item) {
                $applied += self::applyControlEffects(
                    $item,
                    $settings,
                    $scope,
                    $globalSignatures
                );
            }

            return $applied;
        }

        if (!$value instanceof stdClass) {
            return 0;
        }

        $applied = 0;

        foreach (get_object_vars($value) as $property => $child) {
            if (
                $child instanceof stdClass
                && self::isColorObject($child)
                && preg_match('/^BackColor\d+$/', $property) === 1
            ) {
                $matchesGlobal = isset(
                    $globalSignatures[IPSViewTheme::colorObjectToHex($child)]
                );

                if (
                    $scope === IPSViewTheme::SCOPE_ALL_CONTROL_DEFAULTS
                    || ($scope === IPSViewTheme::SCOPE_MATCHING_CONTROLS && $matchesGlobal)
                ) {
                    if (self::applyColorEffects($child, $settings)) {
                        ++$applied;
                    }
                }

                continue;
            }

            $applied += self::applyControlEffects(
                $child,
                $settings,
                $scope,
                $globalSignatures
            );
        }

        return $applied;
    }

    /**
     * Reports whether a color object contains a complete secondary RGB color.
     */
    private static function hasSecondaryColor(stdClass $color): bool
    {
        foreach (['R2', 'G2', 'B2'] as $component) {
            if (!property_exists($color, $component)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reports whether an object contains numeric primary RGB components.
     */
    private static function isColorObject(stdClass $color): bool
    {
        foreach (['R', 'G', 'B'] as $component) {
            if (
                !property_exists($color, $component)
                || (!is_int($color->{$component}) && !is_float($color->{$component}))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts a normalized hex color to integer RGB components.
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private static function toRgb(string $color): array
    {
        $color = IPSViewTheme::normalizeColor($color);

        return [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2)),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use stdClass;

final class IPSViewShape
{
    public const CORNER_PRESERVE = 0;
    public const CORNER_SQUARE = 1;
    public const CORNER_SUBTLE = 2;
    public const CORNER_ROUNDED = 3;
    public const CORNER_STRONG = 4;
    public const CORNER_CUSTOM = 5;

    public const BORDER_PRESERVE = 0;
    public const BORDER_NONE = 1;
    public const BORDER_THIN = 2;
    public const BORDER_STANDARD = 3;
    public const BORDER_STRONG = 4;
    public const BORDER_CUSTOM = 5;

    /**
     * Returns validated form-shape settings.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     cornerStyle: int,
     *     customCornerRadius: int,
     *     borderStyle: int,
     *     customBorderWidth: float
     * }
     */
    public static function resolve(array $settings = []): array
    {
        $resolved = [
            'cornerStyle'       => (int) ($settings['cornerStyle'] ?? self::CORNER_PRESERVE),
            'customCornerRadius'=> max(0, min(40, (int) ($settings['customCornerRadius'] ?? 6))),
            'borderStyle'       => (int) ($settings['borderStyle'] ?? self::BORDER_PRESERVE),
            'customBorderWidth' => max(0.0, min(8.0, (float) ($settings['customBorderWidth'] ?? 1.5))),
        ];

        if (!in_array(
            $resolved['cornerStyle'],
            [
                self::CORNER_PRESERVE,
                self::CORNER_SQUARE,
                self::CORNER_SUBTLE,
                self::CORNER_ROUNDED,
                self::CORNER_STRONG,
                self::CORNER_CUSTOM,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected corner style is not supported.');
        }

        if (!in_array(
            $resolved['borderStyle'],
            [
                self::BORDER_PRESERVE,
                self::BORDER_NONE,
                self::BORDER_THIN,
                self::BORDER_STANDARD,
                self::BORDER_STRONG,
                self::BORDER_CUSTOM,
            ],
            true
        )) {
            throw new InvalidArgumentException('The selected border style is not supported.');
        }

        return $resolved;
    }

    /**
     * Applies the global IPSView corner and border defaults.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{globalShapeApplied: int}
     */
    public static function apply(stdClass $document, array $settings = []): array
    {
        $settings = self::resolve($settings);
        $cornerRadius = self::resolveCornerRadius($settings);
        $borderWidth = self::resolveBorderWidth($settings);
        $changes = 0;

        if ($cornerRadius !== null) {
            if ((int) ($document->DefaultBorderRadius ?? -1) !== $cornerRadius) {
                $document->DefaultBorderRadius = $cornerRadius;
                ++$changes;
            }

            $roundedTracks = $cornerRadius > 0;
            foreach (['CircleTrackEdgesRounded', 'ProgressbarTrackEdgesRounded'] as $property) {
                if ((bool) ($document->{$property} ?? false) === $roundedTracks) {
                    continue;
                }

                $document->{$property} = $roundedTracks;
                ++$changes;
            }
        }

        if (
            $borderWidth !== null
            && abs((float) ($document->DefaultBorderWidth ?? -1.0) - $borderWidth) > 0.001
        ) {
            $document->DefaultBorderWidth = $borderWidth;
            ++$changes;
        }

        return ['globalShapeApplied' => $changes];
    }

    /**
     * Reads the current global shape defaults.
     *
     * @return array{cornerRadius: int, borderWidth: float}
     */
    public static function extract(stdClass $document): array
    {
        return [
            'cornerRadius'=> max(0, min(40, (int) ($document->DefaultBorderRadius ?? 6))),
            'borderWidth' => max(0.0, min(8.0, (float) ($document->DefaultBorderWidth ?? 1.5))),
        ];
    }

    /**
     * Returns the effective preview geometry.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{cornerRadius: int, borderWidth: float}
     */
    public static function preview(array $settings = []): array
    {
        $settings = self::resolve($settings);

        return [
            'cornerRadius'=> self::resolveCornerRadius($settings) ?? $settings['customCornerRadius'],
            'borderWidth' => self::resolveBorderWidth($settings) ?? $settings['customBorderWidth'],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolveCornerRadius(array $settings): ?int
    {
        return match ($settings['cornerStyle']) {
            self::CORNER_SQUARE  => 0,
            self::CORNER_SUBTLE  => 4,
            self::CORNER_ROUNDED => 10,
            self::CORNER_STRONG  => 18,
            self::CORNER_CUSTOM  => $settings['customCornerRadius'],
            default              => null,
        };
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function resolveBorderWidth(array $settings): ?float
    {
        return match ($settings['borderStyle']) {
            self::BORDER_NONE     => 0.0,
            self::BORDER_THIN     => 1.0,
            self::BORDER_STANDARD => 1.5,
            self::BORDER_STRONG   => 3.0,
            self::BORDER_CUSTOM   => $settings['customBorderWidth'],
            default               => null,
        };
    }
}

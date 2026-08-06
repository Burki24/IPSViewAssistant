<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;

final class IPSViewUsageProfile
{
    public const PROFILE_WALL_TABLET = 0;
    public const PROFILE_TABLET = 1;
    public const PROFILE_SMARTPHONE = 2;
    public const PROFILE_BROWSER = 3;
    public const PROFILE_CUSTOM = 4;

    /**
     * Resolves a ready-made profile to the corresponding View settings.
     *
     * @return array{aspectRatio: int, orientation: int, fullScreen: bool}
     */
    public static function resolve(int $profile): array
    {
        return match ($profile) {
            self::PROFILE_WALL_TABLET => [
                'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
                'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
                'fullScreen'  => true,
            ],
            self::PROFILE_TABLET => [
                'aspectRatio' => IPSViewDocument::ASPECT_RATIO_4_3,
                'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
                'fullScreen'  => true,
            ],
            self::PROFILE_SMARTPHONE => [
                'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
                'orientation' => IPSViewDocument::ORIENTATION_PORTRAIT,
                'fullScreen'  => true,
            ],
            self::PROFILE_BROWSER => [
                'aspectRatio' => IPSViewDocument::ASPECT_RATIO_16_9,
                'orientation' => IPSViewDocument::ORIENTATION_LANDSCAPE,
                'fullScreen'  => false,
            ],
            default => throw new InvalidArgumentException('The selected usage profile is not supported.'),
        };
    }

    /**
     * Reports whether a profile value can be selected in the assistant form.
     */
    public static function isSelectable(int $profile): bool
    {
        return $profile >= self::PROFILE_WALL_TABLET
            && $profile <= self::PROFILE_CUSTOM;
    }
}

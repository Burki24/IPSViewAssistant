<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

final class IPSViewDesignerHandover
{
    private const OBJECT_TYPE_VARIABLE = 2;
    private const OBJECT_TYPE_SCRIPT = 3;
    private const OBJECT_TYPE_MEDIA = 5;

    private const VARIABLE_TYPE_BOOLEAN = 0;
    private const VARIABLE_TYPE_INTEGER = 1;
    private const VARIABLE_TYPE_FLOAT = 2;
    private const VARIABLE_TYPE_STRING = 3;

    public static function recommendation(int $objectType, ?int $variableType = null): string
    {
        if ($objectType === self::OBJECT_TYPE_VARIABLE) {
            return match ($variableType) {
                self::VARIABLE_TYPE_BOOLEAN => 'Start with a Switch or Toggle-Button.',
                self::VARIABLE_TYPE_INTEGER,
                self::VARIABLE_TYPE_FLOAT  => 'Start with a Variable Text, Value-Button or Slider; the Repository may also offer a suitable combined control.',
                self::VARIABLE_TYPE_STRING => 'Start with a Variable Text control.',
                default                    => 'Use the Repository ID search to display the controls supported for this variable.',
            };
        }

        return match ($objectType) {
            self::OBJECT_TYPE_SCRIPT => 'Start with a Script-Button.',
            self::OBJECT_TYPE_MEDIA  => 'Start with a Media-Image or Media-Stream control.',
            default                  => 'Select a suitable variable below this object for the simplest first control.',
        };
    }
}

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

    public const ORIENTATION_LANDSCAPE = 0;
    public const ORIENTATION_PORTRAIT = 1;

    private stdClass $document;

    private function __construct(stdClass $document)
    {
        $this->document = $document;
    }

    /**
     * Loads an IPSView document without converting JSON objects into PHP arrays.
     */
    public static function fromTemplate(string $templatePath): self
    {
        $json = file_get_contents($templatePath);
        if ($json === false) {
            throw new RuntimeException('The IPSView template could not be read.');
        }

        try {
            $document = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The IPSView template contains invalid JSON.', 0, $exception);
        }

        if (!$document instanceof stdClass) {
            throw new RuntimeException('The IPSView template must contain a JSON object.');
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
        string $mainPageName
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
        $this->document->Pages = $pages;
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
            default                  => throw new InvalidArgumentException('The selected aspect ratio is not supported.'),
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

<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class IPSViewFactory
{
    public const TEMPLATE_EMPTY = 0;

    public function __construct(private readonly string $templateDirectory)
    {
    }

    /**
     * Creates and initializes one IPSView media object.
     */
    public function create(
        string $viewName,
        int $targetCategoryID,
        int $aspectRatio,
        int $orientation,
        int $template,
        string $mainPageName,
        int $theme = IPSViewTheme::THEME_STANDARD,
        array $customPalette = [],
        array $effects = [],
        array $appearance = [],
        array $background = [],
        bool $fullScreen = false
    ): int {
        $viewName = trim($viewName);
        $mainPageName = trim($mainPageName);

        $this->validateName($viewName, 'View');
        $this->validateName($mainPageName, 'main page');
        $this->validateTargetCategory($targetCategoryID);
        $this->ensureUniqueViewName($viewName, $targetCategoryID);

        if ($template !== self::TEMPLATE_EMPTY) {
            throw new InvalidArgumentException('The selected template is not supported.');
        }

        $mediaID = IPS_CreateMedia(0);

        try {
            IPS_SetName($mediaID, $viewName);
            IPS_SetParent($mediaID, $targetCategoryID);

            $document = IPSViewDocument::fromTemplate($this->templateDirectory . '/empty-view.json');
            $document->configure($viewName, $mediaID, $aspectRatio, $orientation, $mainPageName, $fullScreen);
            $document->applyTheme($theme, $customPalette, $effects, $appearance, $background);

            $mediaFile = IPS_GetKernelDir()
                . 'media'
                . DIRECTORY_SEPARATOR
                . $mediaID
                . '.ipsView';

            if (!IPS_SetMediaFile($mediaID, $mediaFile, false)) {
                throw new RuntimeException('The media file could not be assigned.');
            }

            if (!IPS_SetMediaContent($mediaID, base64_encode($document->toJson()))) {
                throw new RuntimeException('The IPSView content could not be written.');
            }

            IPS_SendMediaEvent($mediaID);

            return $mediaID;
        } catch (Throwable $exception) {
            if (IPS_MediaExists($mediaID)) {
                IPS_DeleteMedia($mediaID, true);
            }

            throw $exception;
        }
    }

    /**
     * Rejects empty and excessively long object names.
     */
    private function validateName(string $name, string $field): void
    {
        if ($name === '') {
            throw new InvalidArgumentException(sprintf('The %s name must not be empty.', $field));
        }

        if (strlen($name) > 128) {
            throw new InvalidArgumentException(sprintf('The %s name must not exceed 128 characters.', $field));
        }
    }

    /**
     * Accepts the Symcon root or a normal category as destination.
     */
    private function validateTargetCategory(int $targetCategoryID): void
    {
        if ($targetCategoryID === 0) {
            return;
        }

        if (!IPS_ObjectExists($targetCategoryID)) {
            throw new InvalidArgumentException('The selected target category does not exist.');
        }

        $object = IPS_GetObject($targetCategoryID);
        if (($object['ObjectType'] ?? -1) !== 0) {
            throw new InvalidArgumentException('The selected target object is not a category.');
        }
    }

    /**
     * Prevents a second IPSView with the same name in the target category.
     */
    private function ensureUniqueViewName(string $viewName, int $targetCategoryID): void
    {
        foreach (IPS_GetChildrenIDs($targetCategoryID) as $childID) {
            $object = IPS_GetObject($childID);
            if (($object['ObjectType'] ?? -1) !== 5) {
                continue;
            }

            if (($object['ObjectName'] ?? '') === $viewName) {
                throw new InvalidArgumentException(
                    sprintf('An IPSView named "%s" already exists in the selected category.', $viewName)
                );
            }
        }
    }
}

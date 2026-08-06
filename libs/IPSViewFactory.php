<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use RuntimeException;
use Throwable;

final class IPSViewFactory
{
    public const TEMPLATE_EMPTY = 0;

    /**
     * Uses the supplied directory to locate the bundled empty-View template.
     */
    public function __construct(private readonly string $templateDirectory)
    {
    }

    /**
     * Creates and initializes one IPSView media object.
     *
     * @param array<string, mixed> $customPalette
     * @param array<string, mixed> $effects
     * @param array<string, mixed> $appearance
     * @param array<string, mixed> $background
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
        bool $fullScreen = false,
        int $startGrid = IPSViewDocument::START_GRID_NONE,
        bool $overwriteExisting = false
    ): int {
        $viewName = trim($viewName);
        $mainPageName = trim($mainPageName);
        IPSViewStartCheck::assertReady(IPSViewStartCheck::analyze(
            $viewName,
            $targetCategoryID,
            $mainPageName,
            $aspectRatio,
            $orientation,
            $template,
            $startGrid,
            $background,
            $overwriteExisting
        ));

        $existingMediaID = $overwriteExisting
            ? IPSViewStartCheck::findExistingView($viewName, $targetCategoryID)
            : null;
        if ($existingMediaID !== null) {
            return $this->overwrite(
                $existingMediaID,
                $viewName,
                $aspectRatio,
                $orientation,
                $template,
                $mainPageName,
                $theme,
                $customPalette,
                $effects,
                $appearance,
                $background,
                $fullScreen,
                $startGrid
            );
        }

        $mediaID = IPS_CreateMedia(0);

        try {
            IPS_SetName($mediaID, $viewName);
            IPS_SetParent($mediaID, $targetCategoryID);

            $document = $this->buildDocument(
                $viewName,
                $mediaID,
                $aspectRatio,
                $orientation,
                $template,
                $mainPageName,
                $theme,
                $customPalette,
                $effects,
                $appearance,
                $background,
                $fullScreen,
                $startGrid
            );

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
     * Replaces one explicitly selected same-name IPSView while retaining its object ID.
     *
     * @param array<string, mixed> $customPalette
     * @param array<string, mixed> $effects
     * @param array<string, mixed> $appearance
     * @param array<string, mixed> $background
     */
    private function overwrite(
        int $mediaID,
        string $viewName,
        int $aspectRatio,
        int $orientation,
        int $template,
        string $mainPageName,
        int $theme,
        array $customPalette,
        array $effects,
        array $appearance,
        array $background,
        bool $fullScreen,
        int $startGrid
    ): int {
        $previousContent = IPS_GetMediaContent($mediaID);
        $document = $this->buildDocument(
            $viewName,
            $mediaID,
            $aspectRatio,
            $orientation,
            $template,
            $mainPageName,
            $theme,
            $customPalette,
            $effects,
            $appearance,
            $background,
            $fullScreen,
            $startGrid
        );

        try {
            if (!IPS_SetMediaContent($mediaID, base64_encode($document->toJson()))) {
                throw new RuntimeException('The existing IPSView content could not be overwritten.');
            }

            IPS_SendMediaEvent($mediaID);

            return $mediaID;
        } catch (Throwable $exception) {
            try {
                IPS_SetMediaContent($mediaID, $previousContent);
                IPS_SendMediaEvent($mediaID);
            } catch (Throwable) {
            }

            throw $exception;
        }
    }

    /**
     * Builds the configured IPSView document shared by creation and explicit overwrite.
     *
     * @param array<string, mixed> $customPalette
     * @param array<string, mixed> $effects
     * @param array<string, mixed> $appearance
     * @param array<string, mixed> $background
     */
    private function buildDocument(
        string $viewName,
        int $mediaID,
        int $aspectRatio,
        int $orientation,
        int $template,
        string $mainPageName,
        int $theme,
        array $customPalette,
        array $effects,
        array $appearance,
        array $background,
        bool $fullScreen,
        int $startGrid
    ): IPSViewDocument {
        if ($template !== self::TEMPLATE_EMPTY) {
            throw new RuntimeException('The selected template is not supported.');
        }

        $document = IPSViewDocument::fromTemplate($this->templateDirectory . '/empty-view.json');
        $document->configure(
            $viewName,
            $mediaID,
            $aspectRatio,
            $orientation,
            $mainPageName,
            $fullScreen,
            $startGrid
        );
        $document->applyTheme($theme, $customPalette, $effects, $appearance, $background);

        return $document;
    }

}

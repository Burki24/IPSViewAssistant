<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

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
        bool $fullScreen = false,
        int $startGrid = IPSViewDocument::START_GRID_NONE
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
            $background
        ));

        $mediaID = IPS_CreateMedia(0);

        try {
            IPS_SetName($mediaID, $viewName);
            IPS_SetParent($mediaID, $targetCategoryID);

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

}

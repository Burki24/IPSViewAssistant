<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class IPSViewCopyFactory
{
    private const IPSVIEW_MEDIA_TYPE = 0;

    /**
     * @var array{
     *     palette: array<string, string>,
     *     scope: int,
     *     globalColorsApplied: int,
     *     controlColorsApplied: int,
     *     controlColorsPreserved: int
     * }|null
     */
    private ?array $lastThemeReport = null;

    /**
     * Reads one existing IPSView and returns its editable design information.
     *
     * @return array{
     *     name: string,
     *     parentID: int,
     *     pageCount: int,
     *     controlCount: int,
     *     palette: array<string, string>,
     *     designAnalysis: array{
     *         globalColors: int,
     *         controlColorsTotal: int,
     *         matchingControlColors: int,
     *         allControlDefaults: int,
     *         individualControlColors: int,
     *         specialControlColors: int
     *     }
     * }
     */
    public function inspect(int $sourceMediaID): array
    {
        $document = $this->loadDocument($sourceMediaID);
        $object = IPS_GetObject($sourceMediaID);

        return [
            'name'         => (string) ($object['ObjectName'] ?? ''),
            'parentID'     => (int) ($object['ParentID'] ?? 0),
            'pageCount'    => $document->getPageCount(),
            'controlCount'   => $document->getControlCount(),
            'palette'        => $document->extractThemePalette(),
            'designAnalysis' => $document->analyzeThemeColors(),
        ];
    }

    /**
     * Creates a styled copy while preserving all pages, controls and unknown fields.
     *
     * @param array<string, mixed> $customPalette
     */
    public function create(
        int $sourceMediaID,
        string $copyName,
        int $targetCategoryID,
        int $theme,
        array $customPalette = [],
        int $scope = IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
    ): int {
        $copyName = trim($copyName);

        $this->validateName($copyName);
        $this->validateTargetCategory($targetCategoryID);

        if ($this->findExistingTarget($copyName, $targetCategoryID) !== null) {
            throw new InvalidArgumentException(
                sprintf('An IPSView named "%s" already exists in the selected category.', $copyName)
            );
        }

        $document = $this->loadDocument($sourceMediaID);
        $mediaID = IPS_CreateMedia(self::IPSVIEW_MEDIA_TYPE);

        try {
            IPS_SetName($mediaID, $copyName);
            IPS_SetParent($mediaID, $targetCategoryID);

            $document->prepareCopy($copyName, $mediaID);
            $this->lastThemeReport = $document->applyThemeWithReport(
                $theme,
                $customPalette,
                $scope
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
     * Updates the global design defaults of an existing IPSView in place.
     *
     * The current target content is read first, so pages, controls and later
     * changes made in the IPSView Designer remain untouched.
     *
     * @param array<string, mixed> $customPalette
     */
    public function update(
        int $targetMediaID,
        int $theme,
        array $customPalette = [],
        int $scope = IPSViewTheme::SCOPE_GLOBAL_DEFAULTS
    ): int {
        $document = $this->loadDocument($targetMediaID);
        $this->lastThemeReport = $document->applyThemeWithReport(
            $theme,
            $customPalette,
            $scope
        );

        if (!IPS_SetMediaContent($targetMediaID, base64_encode($document->toJson()))) {
            throw new RuntimeException('The IPSView content could not be written.');
        }

        IPS_SendMediaEvent($targetMediaID);

        return $targetMediaID;
    }

    /**
     * Returns the report of the most recent create or update operation.
     *
     * @return array{
     *     palette: array<string, string>,
     *     scope: int,
     *     globalColorsApplied: int,
     *     controlColorsApplied: int,
     *     controlColorsPreserved: int
     * }|null
     */
    public function getLastThemeReport(): ?array
    {
        return $this->lastThemeReport;
    }

    public function findExistingTarget(string $copyName, int $targetCategoryID): ?int
    {
        $copyName = trim($copyName);
        $this->validateName($copyName);
        $this->validateTargetCategory($targetCategoryID);
        $matchingObjects = [];

        foreach (IPS_GetChildrenIDs($targetCategoryID) as $childID) {
            $object = IPS_GetObject($childID);

            if ((string) ($object['ObjectName'] ?? '') === $copyName) {
                $matchingObjects[] = [
                    'id'   => $childID,
                    'type' => (int) ($object['ObjectType'] ?? -1),
                ];
            }
        }

        if ($matchingObjects === []) {
            return null;
        }

        if (count($matchingObjects) > 1) {
            throw new InvalidArgumentException(
                sprintf('More than one object named "%s" exists in the selected category.', $copyName)
            );
        }

        $matchingObject = $matchingObjects[0];
        if ($matchingObject['type'] !== 5 || !IPS_MediaExists($matchingObject['id'])) {
            throw new InvalidArgumentException(
                sprintf('An object named "%s" exists, but it is not an IPSView media object.', $copyName)
            );
        }

        $media = IPS_GetMedia($matchingObject['id']);
        if ((int) ($media['MediaType'] ?? -1) !== self::IPSVIEW_MEDIA_TYPE) {
            throw new InvalidArgumentException(
                sprintf('A media object named "%s" exists, but it is not an IPSView.', $copyName)
            );
        }

        return $matchingObject['id'];
    }

    private function loadDocument(int $sourceMediaID): IPSViewDocument
    {
        if ($sourceMediaID < 1 || !IPS_MediaExists($sourceMediaID)) {
            throw new InvalidArgumentException('The selected source medium does not exist.');
        }

        $media = IPS_GetMedia($sourceMediaID);
        if ((int) ($media['MediaType'] ?? -1) !== self::IPSVIEW_MEDIA_TYPE) {
            throw new InvalidArgumentException('The selected source medium is not an IPSView.');
        }

        $encodedContent = IPS_GetMediaContent($sourceMediaID);
        $json = base64_decode($encodedContent, true);

        if ($json === false || trim($json) === '') {
            throw new RuntimeException('The selected IPSView does not contain readable content.');
        }

        return IPSViewDocument::fromJson($json);
    }

    private function validateName(string $copyName): void
    {
        if ($copyName === '') {
            throw new InvalidArgumentException('The copy name must not be empty.');
        }

        if (strlen($copyName) > 128) {
            throw new InvalidArgumentException('The copy name must not exceed 128 characters.');
        }
    }

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

}

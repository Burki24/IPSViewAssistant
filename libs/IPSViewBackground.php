<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;
use RuntimeException;
use stdClass;

final class IPSViewBackground
{
    public const MODE_PRESERVE = 0;
    public const MODE_REMOVE = 1;
    public const MODE_FILE = 2;

    public const SCOPE_MAIN_PAGE = 0;
    public const SCOPE_ALL_PAGES = 1;

    public const LAYOUT_TILE = 'Tile';
    public const LAYOUT_CENTER = 'Center';
    public const LAYOUT_STRETCH = 'Stretch';

    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

    /**
     * Normalizes and validates a background-image selection.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{mode: int, layout: string, scope: int, imageData: string}
     */
    public static function resolve(array $settings = []): array
    {
        $mode = (int) ($settings['mode'] ?? self::MODE_PRESERVE);
        if (!in_array($mode, [self::MODE_PRESERVE, self::MODE_REMOVE, self::MODE_FILE], true)) {
            throw new InvalidArgumentException('The selected background image mode is invalid.');
        }

        $layout = (string) ($settings['layout'] ?? self::LAYOUT_STRETCH);
        if (!in_array($layout, [self::LAYOUT_TILE, self::LAYOUT_CENTER, self::LAYOUT_STRETCH], true)) {
            throw new InvalidArgumentException('The selected background image layout is invalid.');
        }

        $scope = (int) ($settings['scope'] ?? self::SCOPE_MAIN_PAGE);
        if (!in_array($scope, [self::SCOPE_MAIN_PAGE, self::SCOPE_ALL_PAGES], true)) {
            throw new InvalidArgumentException('The selected background image page scope is invalid.');
        }

        return [
            'mode'      => $mode,
            'layout'    => $layout,
            'scope'     => $scope,
            'imageData' => trim((string) ($settings['imageData'] ?? '')),
        ];
    }

    /**
     * Applies the selection to the main page or all pages.
     *
     * @param array<string, mixed> $settings
     */
    public static function apply(stdClass $document, array $settings = []): bool
    {
        $settings = self::resolve($settings);
        if ($settings['mode'] === self::MODE_PRESERVE) {
            return false;
        }

        $pages = self::targetPages($document, $settings['scope']);
        if ($settings['mode'] === self::MODE_REMOVE) {
            $changed = false;
            foreach ($pages as $page) {
                $changed = $changed
                    || (int) ($page->BackgroundImage ?? 0) !== 0
                    || (string) ($page->BackgroundLayout ?? '') !== '';
                $page->BackgroundImage = 0;
                $page->BackgroundLayout = '';
            }

            return $changed;
        }

        $image = self::decodeImage($settings['imageData']);
        $images = $document->Images ?? [];
        if (!is_array($images)) {
            throw new RuntimeException('The IPSView image collection is invalid.');
        }

        $imageHash = self::findMatchingHash($images, $image['base64']);
        if ($imageHash === null) {
            $imageHash = self::createHash($image['binary'], $images);
            $images[] = (object) [
                'ImageData' => $image['base64'],
                'ImageHash' => $imageHash,
                'ImageType' => -1,
                'Height'    => $image['height'],
                'Width'     => $image['width'],
            ];
            $document->Images = $images;
        }

        $changed = false;
        foreach ($pages as $page) {
            $changed = $changed
                || (int) ($page->BackgroundImage ?? 0) !== $imageHash
                || (string) ($page->BackgroundLayout ?? '') !== $settings['layout'];
            $page->BackgroundImage = $imageHash;
            $page->BackgroundLayout = $settings['layout'];
        }

        return $changed;
    }

    /**
     * Reads the image referenced by the main page without changing the document.
     *
     * @return array{mode: int, layout: string, scope: int, imageData: string, width: int, height: int}
     */
    public static function extract(stdClass $document): array
    {
        $page = self::mainPage($document);
        $hash = (int) ($page->BackgroundImage ?? 0);
        $layout = (string) ($page->BackgroundLayout ?? '');

        if ($hash === 0) {
            return [
                'mode'      => self::MODE_PRESERVE,
                'layout'    => self::LAYOUT_STRETCH,
                'scope'     => self::SCOPE_MAIN_PAGE,
                'imageData' => '',
                'width'     => 0,
                'height'    => 0,
            ];
        }

        foreach ($document->Images ?? [] as $image) {
            if (!$image instanceof stdClass || (int) ($image->ImageHash ?? 0) !== $hash) {
                continue;
            }

            $data = trim((string) ($image->ImageData ?? ''));
            $mime = self::detectMimeFromBase64($data);

            return [
                'mode'      => self::MODE_PRESERVE,
                'layout'    => in_array($layout, [self::LAYOUT_TILE, self::LAYOUT_CENTER, self::LAYOUT_STRETCH], true)
                    ? $layout
                    : self::LAYOUT_STRETCH,
                'scope'     => self::SCOPE_MAIN_PAGE,
                'imageData' => $mime === null ? '' : 'data:' . $mime . ';base64,' . $data,
                'width'     => (int) ($image->Width ?? 0),
                'height'    => (int) ($image->Height ?? 0),
            ];
        }

        return [
            'mode'      => self::MODE_PRESERVE,
            'layout'    => self::LAYOUT_STRETCH,
            'scope'     => self::SCOPE_MAIN_PAGE,
            'imageData' => '',
            'width'     => 0,
            'height'    => 0,
        ];
    }

    /**
     * Decodes the selected image for use in the SVG design preview.
     *
     * @param array<string, mixed> $settings
     *
     * @return array{dataUri: string, layout: string, width: int, height: int}|null
     */
    public static function preview(array $settings = []): ?array
    {
        $settings = self::resolve($settings);
        if ($settings['mode'] === self::MODE_REMOVE || $settings['imageData'] === '') {
            return null;
        }

        $image = self::decodeImage($settings['imageData']);

        return [
            'dataUri' => 'data:' . $image['mime'] . ';base64,' . $image['base64'],
            'layout'  => $settings['layout'],
            'width'   => $image['width'],
            'height'  => $image['height'],
        ];
    }

    /**
     * Returns the first page or rejects a malformed IPSView document.
     */
    private static function mainPage(stdClass $document): stdClass
    {
        $pages = $document->Pages ?? null;
        if (!is_array($pages) || !isset($pages[0]) || !$pages[0] instanceof stdClass) {
            throw new RuntimeException('The IPSView document does not contain a valid main page.');
        }

        return $pages[0];
    }

    /**
     * Resolves and validates the pages affected by the selected scope.
     *
     * @return list<stdClass>
     */
    private static function targetPages(stdClass $document, int $scope): array
    {
        $pages = $document->Pages ?? null;
        if (!is_array($pages) || !isset($pages[0]) || !$pages[0] instanceof stdClass) {
            throw new RuntimeException('The IPSView document does not contain a valid main page.');
        }

        if ($scope === self::SCOPE_MAIN_PAGE) {
            return [$pages[0]];
        }

        foreach ($pages as $page) {
            if (!$page instanceof stdClass) {
                throw new RuntimeException('The IPSView document contains an invalid page.');
            }
        }

        return $pages;
    }

    /**
     * Validates and decodes one Base64-encoded PNG or JPEG image.
     *
     * @return array{binary: string, base64: string, mime: string, width: int, height: int}
     */
    private static function decodeImage(string $encoded): array
    {
        $encoded = trim($encoded);
        if (preg_match('/^data:([^;,]+);base64,(.*)$/s', $encoded, $matches) === 1) {
            $encoded = $matches[2];
        }
        $encoded = preg_replace('/\s+/', '', $encoded) ?? '';
        $binary = base64_decode($encoded, true);
        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('The selected background image does not contain valid Base64 data.');
        }
        if (strlen($binary) > self::MAX_IMAGE_BYTES) {
            throw new InvalidArgumentException('The selected background image must not exceed 10 MB.');
        }

        $info = getimagesizefromstring($binary);
        $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
        if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
            throw new InvalidArgumentException('Only valid PNG and JPEG background images are supported.');
        }

        return [
            'binary' => $binary,
            'base64' => base64_encode($binary),
            'mime'   => $mime,
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }

    /**
     * Reuses the hash of an identical image already embedded in the View.
     *
     * @param list<mixed> $images
     */
    private static function findMatchingHash(array $images, string $base64): ?int
    {
        foreach ($images as $image) {
            if ($image instanceof stdClass && hash_equals((string) ($image->ImageData ?? ''), $base64)) {
                return (int) ($image->ImageHash ?? 0);
            }
        }

        return null;
    }

    /**
     * Creates a deterministic positive image hash that is unused in the View.
     *
     * @param list<mixed> $images
     */
    private static function createHash(string $binary, array $images): int
    {
        $used = [];
        foreach ($images as $image) {
            if ($image instanceof stdClass) {
                $used[(int) ($image->ImageHash ?? 0)] = true;
            }
        }

        $hash = unpack('N', substr(hash('sha256', $binary, true), 0, 4))[1] & 0x7FFFFFFF;
        $hash = max(1, $hash);
        while (isset($used[$hash])) {
            $hash = $hash === 0x7FFFFFFF ? 1 : $hash + 1;
        }

        return $hash;
    }

    /**
     * Returns the supported image MIME type or null for unreadable image data.
     */
    private static function detectMimeFromBase64(string $encoded): ?string
    {
        try {
            return self::decodeImage($encoded)['mime'];
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}

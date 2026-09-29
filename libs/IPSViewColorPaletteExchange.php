<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;
use InvalidArgumentException;
use JsonException;

require_once __DIR__ . '/helper/IPSViewControlThemeHelper.php';

/**
 * Provides the validated Color Palette V1 exchange format for IPSView Assistant.
 *
 * A palette contains only colors: all semantic shared-style colors and the complete
 * 109-field native IPSView color catalogue grouped by the 15 canonical families.
 */
final class IPSViewColorPaletteExchange
{
    public const SCHEMA = 'burki24.ipsview-color-palette';
    public const VERSION = 1;

    private const MAX_JSON_BYTES = 1048576;
    private const MAX_NAME_LENGTH = 128;
    private const MAX_DESCRIPTION_LENGTH = 2048;
    private const MAX_CREATED_BY_LENGTH = 128;
    private const MAX_CREATED_AT_LENGTH = 64;

    /**
     * Creates one canonical Color Palette V1 document.
     *
     * @param string              $name Palette name.
     * @param string              $description Optional palette description.
     * @param array<string,mixed> $semanticColors Semantic shared-style colors.
     * @param array<string,mixed> $nativeColors Flat native IPSView color map.
     * @param array<string,mixed> $metadata Optional origin metadata.
     *
     * @return array<string,mixed> Canonical palette document.
     */
    public static function create(
        string $name,
        string $description,
        array $semanticColors,
        array $nativeColors,
        array $metadata = []
    ): array {
        $palette = [
            'schema'         => self::SCHEMA,
            'version'        => self::VERSION,
            'name'           => $name,
            'description'    => $description,
            'createdBy'      => $metadata['createdBy'] ?? 'IPSView Assistant',
            'createdAt'      => $metadata['createdAt'] ?? date(DATE_ATOM),
            'semanticColors' => $semanticColors,
            'nativeColors'   => self::groupNativeColors($nativeColors),
        ];

        return self::normalize($palette);
    }

    /**
     * Encodes one Color Palette V1 document as deterministic UTF-8 JSON.
     *
     * @param string              $name Palette name.
     * @param string              $description Optional palette description.
     * @param array<string,mixed> $semanticColors Semantic shared-style colors.
     * @param array<string,mixed> $nativeColors Flat native IPSView color map.
     * @param array<string,mixed> $metadata Optional origin metadata.
     *
     * @return string Canonical palette JSON.
     */
    public static function exportJson(
        string $name,
        string $description,
        array $semanticColors,
        array $nativeColors,
        array $metadata = []
    ): string {
        return json_encode(
            self::create($name, $description, $semanticColors, $nativeColors, $metadata),
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR
        ) . "\n";
    }

    /**
     * Decodes and validates one Color Palette V1 JSON document.
     *
     * @param string $json Encoded palette JSON.
     *
     * @return array<string,mixed> Canonical palette document.
     */
    public static function importJson(string $json): array
    {
        if (strlen($json) > self::MAX_JSON_BYTES) {
            throw new InvalidArgumentException('The IPSView color palette exceeds the supported size.');
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('The IPSView color palette contains invalid JSON.', 0, $exception);
        }
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('The IPSView color palette must contain a JSON object.');
        }

        return self::normalize($decoded);
    }

    /**
     * Decodes content returned by a Symcon SelectFile field.
     *
     * @param string $fileData Raw JSON, Base64 data or a Base64 data URI.
     *
     * @return string Readable palette JSON.
     */
    public static function decodeFileData(string $fileData): string
    {
        $fileData = trim($fileData);
        if ($fileData === '') {
            throw new InvalidArgumentException('The selected color palette file is empty.');
        }
        if (strlen($fileData) > (self::MAX_JSON_BYTES * 2)) {
            throw new InvalidArgumentException('The selected color palette file exceeds the supported size.');
        }

        if (preg_match('/^data:[^,]*;base64,(.*)$/is', $fileData, $matches) === 1) {
            $fileData = $matches[1];
        }

        if (str_starts_with(ltrim($fileData), '{')) {
            self::importJson($fileData);

            return $fileData;
        }

        $compact = preg_replace('/\s+/', '', $fileData) ?? '';
        $decoded = base64_decode($compact, true);
        if ($decoded === false || !str_starts_with(ltrim($decoded), '{')) {
            throw new InvalidArgumentException('The selected file does not contain a readable IPSView color palette.');
        }
        self::importJson($decoded);

        return $decoded;
    }

    /**
     * Returns all 109 native colors from one canonical palette as a flat map.
     *
     * @param array<string,mixed> $palette Canonical or importable palette document.
     *
     * @return array<string,string> Native field => #RRGGBB.
     */
    public static function nativeColors(array $palette): array
    {
        $palette = self::normalize($palette);
        $flat = [];

        foreach (IPSViewControlThemeHelper::families() as $family => $fields) {
            foreach ($fields as $field) {
                $flat[$field] = $palette['nativeColors'][$family][$field];
            }
        }

        return $flat;
    }

    /**
     * Returns only native colors that differ from their imported semantic source color.
     *
     * @param array<string,mixed> $palette Canonical or importable palette document.
     *
     * @return array<string,string> Native field => #RRGGBB override map.
     */
    public static function nativeOverrides(array $palette): array
    {
        $palette = self::normalize($palette);
        $nativeColors = self::nativeColors($palette);
        $inheritedTheme = IPSViewControlThemeHelper::fromStyleColors($palette['semanticColors']);
        $overrides = [];

        foreach (IPSViewControlThemeHelper::fields() as $field) {
            $inheritedColor = IPSViewControlThemeHelper::colorToHex($inheritedTheme['colors'][$field]);
            if ($nativeColors[$field] !== $inheritedColor) {
                $overrides[$field] = $nativeColors[$field];
            }
        }

        return $overrides;
    }

    /**
     * Validates and canonicalizes one Color Palette V1 document.
     *
     * @param array<string,mixed> $palette Palette document.
     *
     * @return array<string,mixed> Canonical palette document.
     */
    public static function normalize(array $palette): array
    {
        if (($palette['schema'] ?? null) !== self::SCHEMA) {
            throw new InvalidArgumentException('Unsupported IPSView color palette schema.');
        }
        if (($palette['version'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('Unsupported IPSView color palette version.');
        }

        $name = self::normalizeText($palette['name'] ?? null, 'name', self::MAX_NAME_LENGTH, false);
        $description = self::normalizeText(
            $palette['description'] ?? '',
            'description',
            self::MAX_DESCRIPTION_LENGTH,
            true
        );
        $createdBy = self::normalizeText(
            $palette['createdBy'] ?? 'IPSView Assistant',
            'createdBy',
            self::MAX_CREATED_BY_LENGTH,
            false
        );
        $createdAt = self::normalizeText(
            $palette['createdAt'] ?? '',
            'createdAt',
            self::MAX_CREATED_AT_LENGTH,
            false
        );

        $semanticColors = self::normalizeSemanticColors($palette['semanticColors'] ?? null);
        $nativeColors = self::normalizeNativeColors($palette['nativeColors'] ?? null);

        return [
            'schema'         => self::SCHEMA,
            'version'        => self::VERSION,
            'name'           => $name,
            'description'    => $description,
            'createdBy'      => $createdBy,
            'createdAt'      => $createdAt,
            'semanticColors' => $semanticColors,
            'nativeColors'   => $nativeColors,
        ];
    }

    /**
     * Groups one complete flat native color map by the canonical IPSView families.
     *
     * @param array<string,mixed> $nativeColors Flat native field => color map.
     *
     * @return array<string,array<string,string>> Canonically grouped native colors.
     */
    private static function groupNativeColors(array $nativeColors): array
    {
        $grouped = [];
        foreach (IPSViewControlThemeHelper::families() as $family => $fields) {
            $grouped[$family] = [];
            foreach ($fields as $field) {
                if (!array_key_exists($field, $nativeColors)) {
                    throw new InvalidArgumentException('The IPSView color palette is missing native color ' . $field . '.');
                }
                $grouped[$family][$field] = self::normalizeHexColor($nativeColors[$field], $field);
            }
        }

        $knownFields = array_flip(IPSViewControlThemeHelper::fields());
        foreach ($nativeColors as $field => $_color) {
            if (!is_string($field) || !isset($knownFields[$field])) {
                throw new InvalidArgumentException('The IPSView color palette contains an unknown native color field.');
            }
        }

        return $grouped;
    }

    /**
     * Validates the semantic shared-style colors in canonical order.
     *
     * @param mixed $colors Semantic color map.
     *
     * @return array<string,string> Canonical semantic colors.
     */
    private static function normalizeSemanticColors(mixed $colors): array
    {
        if (!is_array($colors)) {
            throw new InvalidArgumentException('IPSView color palette semanticColors must be an object/map.');
        }

        $required = IPSViewControlThemeHelper::styleFields();
        $requiredMap = array_flip($required);
        $normalized = [];
        foreach ($required as $field) {
            if (!array_key_exists($field, $colors)) {
                throw new InvalidArgumentException('The IPSView color palette is missing semantic color ' . $field . '.');
            }
            $normalized[$field] = self::normalizeHexColor($colors[$field], $field);
        }
        foreach ($colors as $field => $_color) {
            if (!is_string($field) || !isset($requiredMap[$field])) {
                throw new InvalidArgumentException('The IPSView color palette contains an unknown semantic color field.');
            }
        }

        return $normalized;
    }

    /**
     * Validates all native color families and their complete current field set.
     *
     * @param mixed $families Grouped native color map.
     *
     * @return array<string,array<string,string>> Canonical native colors.
     */
    private static function normalizeNativeColors(mixed $families): array
    {
        if (!is_array($families)) {
            throw new InvalidArgumentException('IPSView color palette nativeColors must be an object/map.');
        }

        $definitions = IPSViewControlThemeHelper::families();
        $normalized = [];
        foreach ($definitions as $family => $fields) {
            $colors = $families[$family] ?? null;
            if (!is_array($colors)) {
                throw new InvalidArgumentException('The IPSView color palette is missing native family ' . $family . '.');
            }

            $fieldMap = array_flip($fields);
            $normalized[$family] = [];
            foreach ($fields as $field) {
                if (!array_key_exists($field, $colors)) {
                    throw new InvalidArgumentException('The IPSView color palette is missing native color ' . $field . '.');
                }
                $normalized[$family][$field] = self::normalizeHexColor($colors[$field], $field);
            }
            foreach ($colors as $field => $_color) {
                if (!is_string($field) || !isset($fieldMap[$field])) {
                    throw new InvalidArgumentException(
                        'The IPSView color palette contains an unknown native color in family ' . $family . '.'
                    );
                }
            }
        }

        $familyMap = array_flip(array_keys($definitions));
        foreach ($families as $family => $_colors) {
            if (!is_string($family) || !isset($familyMap[$family])) {
                throw new InvalidArgumentException('The IPSView color palette contains an unknown native color family.');
            }
        }

        return $normalized;
    }

    /**
     * Validates and normalizes one six-digit RGB color.
     *
     * @param mixed  $color Color value.
     * @param string $field Field name used for error reporting.
     *
     * @return string Uppercase #RRGGBB color.
     */
    private static function normalizeHexColor(mixed $color, string $field): string
    {
        if (!is_string($color) || preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            throw new InvalidArgumentException('Invalid IPSView color palette value for ' . $field . '.');
        }

        return strtoupper($color);
    }

    /**
     * Validates one bounded text field.
     *
     * @param mixed  $value Text value.
     * @param string $field Field name used for error reporting.
     * @param int    $maximumLength Maximum accepted byte length.
     * @param bool   $allowEmpty Whether an empty value is allowed.
     *
     * @return string Normalized text.
     */
    private static function normalizeText(mixed $value, string $field, int $maximumLength, bool $allowEmpty): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('IPSView color palette ' . $field . ' must be a string.');
        }

        $value = trim($value);
        if (!$allowEmpty && $value === '') {
            throw new InvalidArgumentException('IPSView color palette ' . $field . ' must not be empty.');
        }
        if (strlen($value) > $maximumLength) {
            throw new InvalidArgumentException('IPSView color palette ' . $field . ' is too long.');
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use Burki24\SymconModuleHelper\IPSViewFontCatalogHelper;
use Burki24\SymconModuleHelper\IPSViewStyleProfileHelper;
use InvalidArgumentException;

require_once __DIR__ . '/helper/IPSViewFontCatalogHelper.php';
require_once __DIR__ . '/helper/IPSViewStyleProfileHelper.php';
require_once __DIR__ . '/IPSViewEffects.php';
require_once __DIR__ . '/IPSViewShape.php';
require_once __DIR__ . '/IPSViewTheme.php';
require_once __DIR__ . '/IPSViewTypography.php';

/**
 * Converts between the Assistant editor model and portable Style Profile V1 documents.
 */
final class IPSViewStyleProfileExchange
{
    private const DEFAULT_DISABLED_OPACITY = 52;
    private const DEFAULT_FONT_SCALE = 100;

    /**
     * Creates a validated portable profile from the currently effective Assistant editor values.
     *
     * When an unchanged imported state is supplied, the exact canonical style snapshot is retained.
     * This makes import/export round-trips lossless even for profile fields that the Assistant does
     * not expose independently. As soon as the editor values are changed, a fresh canonical style is
     * generated from the visible Assistant semantics.
     *
     * @param array<string,mixed> $palette
     * @param array<string,mixed> $effects
     * @param array<string,mixed> $appearance
     * @param array<string,mixed> $metadata
     * @param array<string,mixed>|null $importState
     *
     * @return array<string,mixed>
     */
    public static function createProfile(
        string $name,
        array $palette,
        array $effects,
        array $appearance,
        array $metadata = [],
        ?array $importState = null
    ): array {
        $editor = self::normalizeEditor($palette, $effects, $appearance);
        $style = self::matchesImportedEditor($editor, $importState)
            ? $importState['profile']['style']
            : self::styleFromEditor($editor['palette'], $editor['effects'], $editor['appearance']);

        return IPSViewStyleProfileHelper::create($name, $style, $metadata);
    }

    /**
     * Encodes a profile from the Assistant editor as deterministic UTF-8 JSON.
     *
     * @param array<string,mixed> $palette
     * @param array<string,mixed> $effects
     * @param array<string,mixed> $appearance
     * @param array<string,mixed> $metadata
     * @param array<string,mixed>|null $importState
     */
    public static function exportJson(
        string $name,
        array $palette,
        array $effects,
        array $appearance,
        array $metadata = [],
        ?array $importState = null
    ): string {
        return IPSViewStyleProfileHelper::encode(
            self::createProfile($name, $palette, $effects, $appearance, $metadata, $importState)
        );
    }

    /**
     * Decodes a Style Profile V1 and prepares the corresponding Assistant editor state.
     *
     * @return array{profile: array<string,mixed>, editor: array{palette: array<string,string>, effects: array<string,int>, appearance: array<string,mixed>}}
     */
    public static function importJson(string $json): array
    {
        $profile = IPSViewStyleProfileHelper::decode($json);

        return [
            'profile' => $profile,
            'editor'  => self::editorFromStyle($profile['style'])
        ];
    }

    /**
     * Decodes content returned by a Symcon SelectFile field.
     *
     * SelectFile can provide raw text, Base64 content or a data URI depending on
     * the client. All three forms are accepted, while arbitrary binary data is rejected.
     */
    public static function decodeFileData(string $fileData): string
    {
        $fileData = trim($fileData);
        if ($fileData === '') {
            throw new InvalidArgumentException('The selected style profile file is empty.');
        }

        if (preg_match('/^data:[^;,]+;base64,(.*)$/s', $fileData, $matches) === 1) {
            $fileData = $matches[1];
        }

        if (str_starts_with(ltrim($fileData), '{')) {
            return $fileData;
        }

        $compact = preg_replace('/\s+/', '', $fileData) ?? '';
        $decoded = base64_decode($compact, true);
        if ($decoded === false || !str_starts_with(ltrim($decoded), '{')) {
            throw new InvalidArgumentException('The selected file does not contain a readable IPSView style profile.');
        }

        return $decoded;
    }

    /**
     * Creates the canonical Style Profile V1 style snapshot from Assistant semantics.
     *
     * @param array<string,mixed> $palette
     * @param array<string,mixed> $effects
     * @param array<string,mixed> $appearance
     *
     * @return array<string,string|int|float>
     */
    public static function styleFromEditor(array $palette, array $effects, array $appearance): array
    {
        $editor = self::normalizeEditor($palette, $effects, $appearance);
        $palette = $editor['palette'];
        $effects = $editor['effects'];
        $appearance = $editor['appearance'];
        $opacity = (int) round(IPSViewEffects::previewOpacity($effects) * 100);
        $shadow = IPSViewEffects::previewShadow($effects);
        $typography = IPSViewTypography::preview($appearance);
        $shape = IPSViewShape::preview($appearance);
        $fontFamily = IPSViewFontCatalogHelper::normalizeFamily($typography['fontFamily']);
        $fontFamily = $fontFamily ?? IPSViewStyleProfileHelper::FONT_SYSTEM;
        $fontStyle = self::fontStyle($typography['isBold'], $typography['isItalic'], $fontFamily);
        $shadowColor = IPSViewTheme::mix($palette[IPSViewTheme::ROLE_VIEW_BACKGROUND], '#000000', 0.68);
        $gradientStrength = match ($effects['gradientStyle']) {
            IPSViewEffects::GRADIENT_SUBTLE => 12,
            IPSViewEffects::GRADIENT_MEDIUM => 26,
            IPSViewEffects::GRADIENT_STRONG => 42,
            default                         => 0
        };

        return IPSViewStyleProfileHelper::normalizeStyle([
            'ViewBackground'            => $palette[IPSViewTheme::ROLE_VIEW_BACKGROUND],
            'PageBackground'            => $palette[IPSViewTheme::ROLE_PAGE_BACKGROUND],
            'LabelBackground'           => $palette[IPSViewTheme::ROLE_SURFACE],
            'ControlBackground'         => $palette[IPSViewTheme::ROLE_SURFACE],
            'ControlActiveBackground'   => $palette[IPSViewTheme::ROLE_ACTIVE],
            'ControlInactiveBackground' => $palette[IPSViewTheme::ROLE_INACTIVE],
            'Text'                      => $palette[IPSViewTheme::ROLE_PRIMARY_TEXT],
            'TextActive'                => $palette[IPSViewTheme::ROLE_PRIMARY_TEXT],
            'TextInactive'              => $palette[IPSViewTheme::ROLE_SECONDARY_TEXT],
            'LabelText'                 => $palette[IPSViewTheme::ROLE_PRIMARY_TEXT],
            'Icon'                      => $palette[IPSViewTheme::ROLE_PRIMARY_TEXT],
            'Border'                    => $palette[IPSViewTheme::ROLE_BORDER],
            'Line'                      => $palette[IPSViewTheme::ROLE_BORDER],
            'PopupBackground'           => $palette[IPSViewTheme::ROLE_PAGE_BACKGROUND],
            'PopupBorder'               => $palette[IPSViewTheme::ROLE_BORDER],
            'Accent'                    => $palette[IPSViewTheme::ROLE_ACCENT],
            'Information'               => $palette[IPSViewTheme::ROLE_ACCENT],
            'Positive'                  => $palette[IPSViewTheme::ROLE_SUCCESS],
            'Warning'                   => $palette[IPSViewTheme::ROLE_WARNING],
            'Critical'                  => $palette[IPSViewTheme::ROLE_ERROR],
            'ShadowColor'               => $shadowColor,
            'ViewBackgroundOpacity'     => $opacity,
            'PageBackgroundOpacity'     => $opacity,
            'LabelBackgroundOpacity'    => $opacity,
            'ControlBackgroundOpacity'  => $opacity,
            'ControlActiveOpacity'      => $opacity,
            'ControlInactiveOpacity'    => $opacity,
            'PopupBackgroundOpacity'    => $opacity,
            'BorderOpacity'             => 100,
            'LineOpacity'               => 100,
            'PopupBorderOpacity'        => 100,
            'ShadowOpacity'             => (int) round($shadow['opacity'] * 100),
            'PopupShadowOpacity'        => (int) round($shadow['opacity'] * 100),
            'FontFamily'                => $fontFamily,
            'FontStyle'                 => $fontStyle,
            'FontSize'                  => $typography['baseFontSize'],
            'FontScale'                 => self::DEFAULT_FONT_SCALE,
            'BorderRadius'              => (float) $shape['cornerRadius'],
            'BorderWidth'               => (float) $shape['borderWidth'],
            'LineWidth'                 => (float) $shape['borderWidth'],
            'ShadowBlur'                => (float) $shadow['blur'],
            'ShadowSpread'              => (float) $shadow['spread'],
            'ShadowOffsetX'             => 0.0,
            'ShadowOffsetY'             => (float) $shadow['offset'],
            'DisabledOpacity'           => self::DEFAULT_DISABLED_OPACITY,
            'GradientStrength'          => $gradientStrength
        ]);
    }

    /**
     * Maps a normalized Style Profile V1 style snapshot into the Assistant editor model.
     *
     * @param array<string,mixed> $style
     *
     * @return array{palette: array<string,string>, effects: array<string,int>, appearance: array<string,mixed>}
     */
    public static function editorFromStyle(array $style): array
    {
        $style = IPSViewStyleProfileHelper::normalizeStyle($style);
        $palette = [
            IPSViewTheme::ROLE_VIEW_BACKGROUND => $style['ViewBackground'],
            IPSViewTheme::ROLE_PAGE_BACKGROUND => $style['PageBackground'],
            IPSViewTheme::ROLE_SURFACE         => $style['ControlBackground'],
            IPSViewTheme::ROLE_PRIMARY_TEXT    => $style['Text'],
            IPSViewTheme::ROLE_SECONDARY_TEXT  => $style['TextInactive'],
            IPSViewTheme::ROLE_BORDER          => $style['Border'],
            IPSViewTheme::ROLE_ACCENT          => $style['Accent'],
            IPSViewTheme::ROLE_ACTIVE          => $style['ControlActiveBackground'],
            IPSViewTheme::ROLE_INACTIVE        => $style['ControlInactiveBackground'],
            IPSViewTheme::ROLE_SUCCESS         => $style['Positive'],
            IPSViewTheme::ROLE_WARNING         => $style['Warning'],
            IPSViewTheme::ROLE_ERROR           => $style['Critical']
        ];
        $effects = self::effectsFromStyle($style);
        $appearance = self::appearanceFromStyle($style);

        return self::normalizeEditor($palette, $effects, $appearance);
    }

    /**
     * Returns a stable JSON-ready state used for a lossless no-edit round-trip.
     *
     * @param array<string,mixed> $profile
     *
     * @return array{profile: array<string,mixed>, editor: array{palette: array<string,string>, effects: array<string,int>, appearance: array<string,mixed>}}
     */
    public static function importState(array $profile): array
    {
        $profile = IPSViewStyleProfileHelper::normalize($profile);

        return [
            'profile' => $profile,
            'editor'  => self::editorFromStyle($profile['style'])
        ];
    }

    /**
     * @param array<string,mixed> $palette
     * @param array<string,mixed> $effects
     * @param array<string,mixed> $appearance
     *
     * @return array{palette: array<string,string>, effects: array<string,int>, appearance: array<string,mixed>}
     */
    private static function normalizeEditor(array $palette, array $effects, array $appearance): array
    {
        $typography = IPSViewTypography::resolve($appearance);
        $shape = IPSViewShape::resolve($appearance);

        return [
            'palette'    => IPSViewTheme::resolvePalette(IPSViewTheme::THEME_CUSTOM, $palette),
            'effects'    => IPSViewEffects::resolve($effects),
            'appearance' => [...$typography, ...$shape]
        ];
    }

    /** @param array<string,mixed>|null $importState */
    private static function matchesImportedEditor(array $editor, ?array $importState): bool
    {
        if (!is_array($importState)) {
            return false;
        }

        $profile = $importState['profile'] ?? null;
        $importedEditor = $importState['editor'] ?? null;
        if (!is_array($profile) || !is_array($importedEditor)) {
            return false;
        }

        try {
            $normalizedProfile = IPSViewStyleProfileHelper::normalize($profile);
            $normalizedImportedEditor = self::normalizeEditor(
                is_array($importedEditor['palette'] ?? null) ? $importedEditor['palette'] : [],
                is_array($importedEditor['effects'] ?? null) ? $importedEditor['effects'] : [],
                is_array($importedEditor['appearance'] ?? null) ? $importedEditor['appearance'] : []
            );
        } catch (InvalidArgumentException) {
            return false;
        }

        return $editor === $normalizedImportedEditor
            && IPSViewStyleProfileHelper::isValid($normalizedProfile);
    }

    /** @param array<string,mixed> $style */
    private static function effectsFromStyle(array $style): array
    {
        $fillOpacities = [
            (int) $style['ViewBackgroundOpacity'],
            (int) $style['PageBackgroundOpacity'],
            (int) $style['LabelBackgroundOpacity'],
            (int) $style['ControlBackgroundOpacity'],
            (int) $style['ControlActiveOpacity'],
            (int) $style['ControlInactiveOpacity'],
            (int) $style['PopupBackgroundOpacity']
        ];
        $opacity = (int) round(array_sum($fillOpacities) / count($fillOpacities));
        $gradientStrength = (int) $style['GradientStrength'];

        return IPSViewEffects::resolve([
            'shadowStyle'         => self::shadowStyleFromStyle($style),
            'transparencyMode'    => $opacity >= 100
                ? IPSViewEffects::TRANSPARENCY_OPAQUE
                : IPSViewEffects::TRANSPARENCY_CUSTOM,
            'transparencyPercent' => 100 - $opacity,
            'gradientStyle'       => match (true) {
                $gradientStrength <= 0  => IPSViewEffects::GRADIENT_NONE,
                $gradientStrength <= 18 => IPSViewEffects::GRADIENT_SUBTLE,
                $gradientStrength <= 34 => IPSViewEffects::GRADIENT_MEDIUM,
                default                 => IPSViewEffects::GRADIENT_STRONG
            },
            'gradientDirection'   => IPSViewEffects::GRADIENT_TO_DARKER
        ]);
    }

    /** @param array<string,mixed> $style */
    private static function appearanceFromStyle(array $style): array
    {
        $fontFamily = (string) $style['FontFamily'];
        $fontMode = self::fontFamilyMode($fontFamily);
        $fontStyle = (string) $style['FontStyle'];
        $bold = in_array($fontStyle, [
            IPSViewFontCatalogHelper::STYLE_BOLD,
            IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC
        ], true);
        $italic = in_array($fontStyle, [
            IPSViewFontCatalogHelper::STYLE_ITALIC,
            IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC
        ], true);
        $customFontFamily = $fontFamily === IPSViewStyleProfileHelper::FONT_SYSTEM
            ? IPSViewFontCatalogHelper::FONT_ROBOTO
            : $fontFamily;

        return [
            'typographyStyle'    => IPSViewTypography::STYLE_CUSTOM,
            'fontFamilyMode'     => $fontMode,
            'customFontFamily'   => $customFontFamily,
            'customFontSize'     => (int) $style['FontSize'],
            'fontBoldMode'       => $bold ? IPSViewTypography::FORMAT_ON : IPSViewTypography::FORMAT_OFF,
            'fontItalicMode'     => $italic ? IPSViewTypography::FORMAT_ON : IPSViewTypography::FORMAT_OFF,
            'fontUnderlineMode'  => IPSViewTypography::FORMAT_OFF,
            'cornerStyle'        => IPSViewShape::CORNER_CUSTOM,
            'customCornerRadius' => (int) round((float) $style['BorderRadius']),
            'borderStyle'        => IPSViewShape::BORDER_CUSTOM,
            'customBorderWidth'  => (float) $style['BorderWidth']
        ];
    }

    /** @param array<string,mixed> $style */
    private static function shadowStyleFromStyle(array $style): int
    {
        $target = [
            'offset'  => (float) $style['ShadowOffsetY'],
            'blur'    => (float) $style['ShadowBlur'],
            'spread'  => (float) $style['ShadowSpread'],
            'opacity' => (float) $style['ShadowOpacity'] / 100
        ];
        $bestStyle = IPSViewEffects::SHADOW_MEDIUM;
        $bestDistance = INF;

        foreach ([
            IPSViewEffects::SHADOW_NONE,
            IPSViewEffects::SHADOW_SUBTLE,
            IPSViewEffects::SHADOW_MEDIUM,
            IPSViewEffects::SHADOW_STRONG
        ] as $shadowStyle) {
            $candidate = IPSViewEffects::previewShadow(['shadowStyle' => $shadowStyle]);
            $distance = (($target['offset'] - $candidate['offset']) ** 2)
                + (($target['blur'] - $candidate['blur']) ** 2)
                + (($target['spread'] - $candidate['spread']) ** 2)
                + ((($target['opacity'] - $candidate['opacity']) * 20) ** 2);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestStyle = $shadowStyle;
            }
        }

        return $bestStyle;
    }

    private static function fontFamilyMode(string $fontFamily): int
    {
        $fontFamily = IPSViewFontCatalogHelper::normalizeFamily($fontFamily);

        return match ($fontFamily) {
            IPSViewFontCatalogHelper::FONT_ROBOTO         => IPSViewTypography::FONT_ROBOTO,
            IPSViewFontCatalogHelper::FONT_ROBOTO_MONO    => IPSViewTypography::FONT_ROBOTO_MONO,
            IPSViewFontCatalogHelper::FONT_DANCING_SCRIPT => IPSViewTypography::FONT_DANCING_SCRIPT,
            IPSViewFontCatalogHelper::FONT_INDIE_FLOWER   => IPSViewTypography::FONT_INDIE_FLOWER,
            IPSViewFontCatalogHelper::FONT_OPEN_SANS      => IPSViewTypography::FONT_OPEN_SANS,
            IPSViewFontCatalogHelper::FONT_PT_SANS        => IPSViewTypography::FONT_PT_SANS,
            IPSViewFontCatalogHelper::FONT_BEBAS_NEUE     => IPSViewTypography::FONT_BEBAS_NEUE,
            IPSViewFontCatalogHelper::FONT_SEGMENT_7      => IPSViewTypography::FONT_SEGMENT_7,
            default                                       => IPSViewTypography::FONT_ROBOTO
        };
    }

    private static function fontStyle(bool $bold, bool $italic, string $fontFamily): string
    {
        $fontStyle = match (true) {
            $bold && $italic => IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC,
            $bold            => IPSViewFontCatalogHelper::STYLE_BOLD,
            $italic          => IPSViewFontCatalogHelper::STYLE_ITALIC,
            default          => IPSViewFontCatalogHelper::STYLE_REGULAR
        };

        if ($fontFamily === IPSViewStyleProfileHelper::FONT_SYSTEM) {
            return $fontStyle;
        }

        return IPSViewFontCatalogHelper::normalizeStyle(
            $fontFamily,
            $fontStyle,
            IPSViewFontCatalogHelper::STYLE_REGULAR
        ) ?? IPSViewFontCatalogHelper::STYLE_REGULAR;
    }
}

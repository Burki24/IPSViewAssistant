<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;
use Burki24\SymconModuleHelper\IPSViewFontCatalogHelper;
use Burki24\SymconModuleHelper\IPSViewStyleProfileHelper;
use InvalidArgumentException;
use stdClass;

require_once __DIR__ . '/helper/IPSViewControlThemeHelper.php';
require_once __DIR__ . '/helper/IPSViewFontCatalogHelper.php';
require_once __DIR__ . '/helper/IPSViewStyleProfileHelper.php';
require_once __DIR__ . '/IPSViewEffects.php';
require_once __DIR__ . '/IPSViewShape.php';
require_once __DIR__ . '/IPSViewTheme.php';
require_once __DIR__ . '/IPSViewTypography.php';

/**
 * Bridges the shared IPSView style contract to the Assistant's existing editor and document pipeline.
 */
final class IPSViewSharedStyleAdapter
{
    /** @var array<string,string> */
    private const PALETTE_MAPPING = [
        IPSViewTheme::ROLE_VIEW_BACKGROUND => 'ViewBackground',
        IPSViewTheme::ROLE_PAGE_BACKGROUND => 'PageBackground',
        IPSViewTheme::ROLE_SURFACE         => 'ControlBackground',
        IPSViewTheme::ROLE_PRIMARY_TEXT    => 'Text',
        IPSViewTheme::ROLE_SECONDARY_TEXT  => 'TextInactive',
        IPSViewTheme::ROLE_BORDER          => 'Border',
        IPSViewTheme::ROLE_ACCENT          => 'Accent',
        IPSViewTheme::ROLE_ACTIVE          => 'ControlActiveBackground',
        IPSViewTheme::ROLE_INACTIVE        => 'ControlInactiveBackground',
        IPSViewTheme::ROLE_SUCCESS         => 'Positive',
        IPSViewTheme::ROLE_WARNING         => 'Warning',
        IPSViewTheme::ROLE_ERROR           => 'Critical'
    ];

    /** @var array<string,int> */
    private const FONT_MODES = [
        IPSViewFontCatalogHelper::FONT_ROBOTO         => IPSViewTypography::FONT_ROBOTO,
        IPSViewFontCatalogHelper::FONT_ROBOTO_MONO    => IPSViewTypography::FONT_ROBOTO_MONO,
        IPSViewFontCatalogHelper::FONT_DANCING_SCRIPT => IPSViewTypography::FONT_DANCING_SCRIPT,
        IPSViewFontCatalogHelper::FONT_INDIE_FLOWER   => IPSViewTypography::FONT_INDIE_FLOWER,
        IPSViewFontCatalogHelper::FONT_OPEN_SANS      => IPSViewTypography::FONT_OPEN_SANS,
        IPSViewFontCatalogHelper::FONT_PT_SANS        => IPSViewTypography::FONT_PT_SANS,
        IPSViewFontCatalogHelper::FONT_BEBAS_NEUE     => IPSViewTypography::FONT_BEBAS_NEUE,
        IPSViewFontCatalogHelper::FONT_SEGMENT_7      => IPSViewTypography::FONT_SEGMENT_7
    ];

    /** @var array<string,string> */
    private const OPACITY_STYLE_FIELDS = [
        'ViewBackground'            => 'ViewBackgroundOpacity',
        'PageBackground'            => 'PageBackgroundOpacity',
        'LabelBackground'           => 'LabelBackgroundOpacity',
        'ControlBackground'         => 'ControlBackgroundOpacity',
        'ControlActiveBackground'   => 'ControlActiveOpacity',
        'ControlInactiveBackground' => 'ControlInactiveOpacity',
        'PopupBackground'           => 'PopupBackgroundOpacity',
        'Border'                    => 'BorderOpacity',
        'Line'                      => 'LineOpacity',
        'PopupBorder'               => 'PopupBorderOpacity',
        'ShadowColor'               => 'ShadowOpacity'
    ];

    /** @var list<string> */
    private const GRADIENT_FIELDS = [
        'ColorView',
        'ColorPage',
        'ColorPopupBack',
        'DialogBackColor',
        'CalendarDayBackColor',
        'CalendarOffBackColor',
        'ColorBack',
        'ColorBackLabel',
        'ShadowBackColor',
        'DialogButtonBackColor',
        'ColorBackOn',
        'ColorAssocBackOn',
        'ColorTabBackOn',
        'GaugeTrackPointerColor',
        'ColorBackOff',
        'ColorAssocBackOff',
        'SwitchTrackColorActive',
        'SwitchTrackColorInactive',
        'SliderTrackColorActive',
        'SliderTrackColorInactive',
        'ProgressbarTrackColorActive',
        'ProgressbarTrackColorInactive',
        'CircleTrackColorActive',
        'CircleTrackColorInactive',
        'ChartGraphFillColor',
        'CalendarTodayHighlightColor',
        'DialogDateTimePrimaryColor',
        'DialogDateTimeSecondaryColor'
    ];

    /** @var array<string,string> */
    private const PROFILE_COLOR_PROPERTIES = [
        'ViewBackground'            => 'IPSViewStyleViewBackgroundColor',
        'PageBackground'            => 'IPSViewStylePageBackgroundColor',
        'LabelBackground'           => 'IPSViewStyleLabelBackgroundColor',
        'ControlBackground'         => 'IPSViewStyleControlBackgroundColor',
        'ControlActiveBackground'   => 'IPSViewStyleControlActiveBackgroundColor',
        'ControlInactiveBackground' => 'IPSViewStyleControlInactiveBackgroundColor',
        'Text'                      => 'IPSViewStyleTextColor',
        'TextActive'                => 'IPSViewStyleTextActiveColor',
        'TextInactive'              => 'IPSViewStyleTextInactiveColor',
        'LabelText'                 => 'IPSViewStyleLabelTextColor',
        'Icon'                      => 'IPSViewStyleIconColor',
        'Border'                    => 'IPSViewStyleBorderColor',
        'Line'                      => 'IPSViewStyleLineColor',
        'PopupBackground'           => 'IPSViewStylePopupBackgroundColor',
        'PopupBorder'               => 'IPSViewStylePopupBorderColor',
        'Accent'                    => 'IPSViewStyleAccentColor',
        'Information'               => 'IPSViewStyleInformationColor',
        'Positive'                  => 'IPSViewStylePositiveColor',
        'Warning'                   => 'IPSViewStyleWarningColor',
        'Critical'                  => 'IPSViewStyleCriticalColor',
        'ShadowColor'               => 'IPSViewStyleShadowColor'
    ];

    /** @var array<string,string> */
    private const PROFILE_OPACITY_PROPERTIES = [
        'ViewBackgroundOpacity'     => 'IPSViewStyleViewBackgroundOpacity',
        'PageBackgroundOpacity'     => 'IPSViewStylePageBackgroundOpacity',
        'LabelBackgroundOpacity'    => 'IPSViewStyleLabelBackgroundOpacity',
        'ControlBackgroundOpacity'  => 'IPSViewStyleControlBackgroundOpacity',
        'ControlActiveOpacity'      => 'IPSViewStyleControlActiveBackgroundOpacity',
        'ControlInactiveOpacity'    => 'IPSViewStyleControlInactiveBackgroundOpacity',
        'PopupBackgroundOpacity'    => 'IPSViewStylePopupBackgroundOpacity',
        'BorderOpacity'             => 'IPSViewStyleBorderOpacity',
        'LineOpacity'               => 'IPSViewStyleLineOpacity',
        'PopupBorderOpacity'        => 'IPSViewStylePopupBorderOpacity',
        'ShadowOpacity'             => 'IPSViewStyleShadowOpacity',
        'PopupShadowOpacity'        => 'IPSViewStylePopupShadowOpacity'
    ];

    /**
     * Converts a resolved shared style to the Assistant's established semantic palette.
     *
     * @param array<string,string|float> $style
     *
     * @return array<string,string>
     */
    public static function palette(array $style): array
    {
        $palette = [];
        foreach (self::PALETTE_MAPPING as $role => $styleField) {
            $palette[$role] = self::color($style, $styleField);
        }

        return $palette;
    }

    /**
     * Converts shared effects to the legacy preview model. Exact values are applied separately.
     *
     * @param array<string,string|float> $style
     *
     * @return array<string,int>
     */
    public static function effects(array $style, int $gradientStrength = 0): array
    {
        $gradientStrength = max(0, min(80, $gradientStrength));
        $gradientStyle = match (true) {
            $gradientStrength <= 0  => IPSViewEffects::GRADIENT_NONE,
            $gradientStrength <= 20 => IPSViewEffects::GRADIENT_SUBTLE,
            $gradientStrength <= 45 => IPSViewEffects::GRADIENT_MEDIUM,
            default                 => IPSViewEffects::GRADIENT_STRONG
        };
        $fillOpacities = [];
        foreach ([
            'ViewBackgroundOpacity',
            'PageBackgroundOpacity',
            'LabelBackgroundOpacity',
            'ControlBackgroundOpacity',
            'ControlActiveOpacity',
            'ControlInactiveOpacity',
            'PopupBackgroundOpacity'
        ] as $field) {
            $fillOpacities[] = max(0.0, min(1.0, (float) ($style[$field] ?? 1.0)));
        }
        $opacity = array_sum($fillOpacities) / count($fillOpacities);
        [$offsetX, $offsetY, $blur, $spread] = self::shadowGeometry($style);
        unset($offsetX, $spread);
        $shadowOpacity = max(0.0, min(1.0, (float) ($style['ShadowOpacity'] ?? 0.0)));
        $shadowStyle = match (true) {
            $shadowOpacity <= 0.01 || $blur <= 0.01 => IPSViewEffects::SHADOW_NONE,
            $shadowOpacity <= 0.28 && $blur <= 6.0 && abs($offsetY) <= 3.0 => IPSViewEffects::SHADOW_SUBTLE,
            $shadowOpacity <= 0.50 && $blur <= 14.0 => IPSViewEffects::SHADOW_MEDIUM,
            default => IPSViewEffects::SHADOW_STRONG
        };

        return [
            'shadowStyle'         => $shadowStyle,
            'transparencyMode'    => $opacity >= 0.999
                ? IPSViewEffects::TRANSPARENCY_OPAQUE
                : IPSViewEffects::TRANSPARENCY_CUSTOM,
            'transparencyPercent' => (int) round((1.0 - $opacity) * 100),
            'gradientStyle'       => $gradientStyle,
            'gradientDirection'   => IPSViewEffects::GRADIENT_TO_DARKER
        ];
    }

    /**
     * Converts shared typography and shape values to the Assistant's application model.
     *
     * @param array<string,string|float> $style
     *
     * @return array<string,mixed>
     */
    public static function appearance(array $style): array
    {
        $fontFamily = trim((string) ($style['FontFamily'] ?? ''));
        $normalizedFont = IPSViewFontCatalogHelper::normalizeFamily($fontFamily);
        $fontMode = $normalizedFont === null
            ? IPSViewTypography::FONT_PRESERVE
            : self::FONT_MODES[$normalizedFont];
        $fontStyle = (string) ($style['FontStyle'] ?? IPSViewFontCatalogHelper::STYLE_REGULAR);
        $fontStyle = $normalizedFont === null
            ? IPSViewFontCatalogHelper::STYLE_REGULAR
            : (IPSViewFontCatalogHelper::normalizeStyle($normalizedFont, $fontStyle) ?? IPSViewFontCatalogHelper::STYLE_REGULAR);
        $bold = in_array($fontStyle, [IPSViewFontCatalogHelper::STYLE_BOLD, IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC], true);
        $italic = in_array($fontStyle, [IPSViewFontCatalogHelper::STYLE_ITALIC, IPSViewFontCatalogHelper::STYLE_BOLD_ITALIC], true);
        $boldMode = $normalizedFont === null
            ? IPSViewTypography::FORMAT_PRESERVE
            : ($bold ? IPSViewTypography::FORMAT_ON : IPSViewTypography::FORMAT_OFF);
        $italicMode = $normalizedFont === null
            ? IPSViewTypography::FORMAT_PRESERVE
            : ($italic ? IPSViewTypography::FORMAT_ON : IPSViewTypography::FORMAT_OFF);
        $fontScale = max(0.6, min(2.0, (float) ($style['FontScale'] ?? 1.0)));
        $fontSize = (int) round(max(8.0, min(32.0, (float) ($style['FontSize'] ?? 16.0) * $fontScale)));

        return [
            'typographyStyle'    => IPSViewTypography::STYLE_CUSTOM,
            'fontFamilyMode'     => $fontMode,
            'customFontFamily'   => $normalizedFont ?? $fontFamily,
            'customFontSize'     => $fontSize,
            'fontBoldMode'       => $boldMode,
            'fontItalicMode'     => $italicMode,
            'fontUnderlineMode'  => IPSViewTypography::FORMAT_PRESERVE,
            'cornerStyle'        => IPSViewShape::CORNER_CUSTOM,
            'customCornerRadius' => (int) round(max(0.0, min(40.0, (float) ($style['BorderRadius'] ?? 8.0)))),
            'borderStyle'        => IPSViewShape::BORDER_CUSTOM,
            'customBorderWidth'  => max(0.0, min(8.0, (float) ($style['BorderWidth'] ?? 1.0)))
        ];
    }

    /**
     * Creates the exact portable Style Profile V1 style snapshot from a resolved shared style.
     *
     * @param array<string,string|float> $style
     * @param array<string,mixed>        $nativeTheme
     *
     * @return array<string,string|int|float>
     */
    public static function profileStyle(
        array $style,
        array $nativeTheme,
        int $gradientStrength,
        bool $transparentBackground = false
    ): array {
        $colors = [
            'ViewBackground',
            'PageBackground',
            'LabelBackground',
            'ControlBackground',
            'ControlActiveBackground',
            'ControlInactiveBackground',
            'Text',
            'TextActive',
            'TextInactive',
            'LabelText',
            'Icon',
            'Border',
            'Line',
            'PopupBackground',
            'PopupBorder',
            'Accent',
            'Information',
            'Positive',
            'Warning',
            'Critical'
        ];
        $profile = [];
        foreach ($colors as $field) {
            $profile[$field] = self::color($style, $field);
        }

        $shadowColor = $nativeTheme['colors']['ShadowColor'] ?? null;
        $profile['ShadowColor'] = $shadowColor === null
            ? '#000000'
            : IPSViewControlThemeHelper::colorToHex($shadowColor);

        foreach (self::PROFILE_OPACITY_PROPERTIES as $profileField => $_propertyName) {
            $styleField = $profileField;
            $value = (float) ($style[$styleField] ?? 1.0);
            $profile[$profileField] = (int) round(max(0.0, min(1.0, $value)) * 100);
        }
        if ($transparentBackground) {
            $profile['ViewBackgroundOpacity'] = 0;
        }

        $fontFamily = IPSViewFontCatalogHelper::normalizeFamily((string) ($style['FontFamily'] ?? ''));
        $profile['FontFamily'] = $fontFamily ?? IPSViewStyleProfileHelper::FONT_SYSTEM;
        $profile['FontStyle'] = (string) ($style['FontStyle'] ?? IPSViewFontCatalogHelper::STYLE_REGULAR);
        $profile['FontSize'] = (int) round(max(8.0, min(32.0, (float) ($style['FontSize'] ?? 16.0))));
        $profile['FontScale'] = (int) round(max(0.6, min(2.0, (float) ($style['FontScale'] ?? 1.0))) * 100);
        $profile['BorderRadius'] = max(0.0, min(40.0, (float) ($style['BorderRadius'] ?? 8.0)));
        $profile['BorderWidth'] = max(0.0, min(10.0, (float) ($style['BorderWidth'] ?? 1.0)));
        $profile['LineWidth'] = max(0.0, min(10.0, (float) ($style['LineWidth'] ?? 1.0)));
        [$offsetX, $offsetY, $blur, $spread] = self::shadowGeometry($style);
        $profile['ShadowBlur'] = $blur;
        $profile['ShadowSpread'] = $spread;
        $profile['ShadowOffsetX'] = $offsetX;
        $profile['ShadowOffsetY'] = $offsetY;
        $profile['DisabledOpacity'] = (int) round(max(0.1, min(1.0, (float) ($style['DisabledOpacity'] ?? 0.52))) * 100);
        $profile['GradientStrength'] = max(0, min(80, $gradientStrength));

        return IPSViewStyleProfileHelper::normalizeStyle($profile);
    }

    /**
     * Maps one normalized Style Profile V1 style snapshot to all shared custom-style properties.
     *
     * @param array<string,mixed> $profileStyle
     *
     * @return array<string,int|float|string|bool>
     */
    public static function propertyValuesFromProfileStyle(array $profileStyle): array
    {
        $style = IPSViewStyleProfileHelper::normalizeStyle($profileStyle);
        $properties = [
            'IPSViewStyleSource'                => 0,
            'IPSViewStyleMediaID'               => 0,
            'IPSViewStyleProfileMediaID'        => 0,
            'IPSViewStylePreset'                => 'standard',
            'IPSViewStyleTransparentBackground' => false
        ];
        foreach (self::PROFILE_COLOR_PROPERTIES as $styleField => $propertyName) {
            $properties[$propertyName] = hexdec(substr((string) $style[$styleField], 1));
        }
        foreach (self::PROFILE_OPACITY_PROPERTIES as $styleField => $propertyName) {
            $properties[$propertyName] = (int) $style[$styleField];
        }

        $properties['IPSViewStyleFontFamily'] = $style['FontFamily'] === IPSViewStyleProfileHelper::FONT_SYSTEM
            ? ''
            : (string) $style['FontFamily'];
        $properties['IPSViewStyleFontStyle'] = (string) $style['FontStyle'];
        $properties['IPSViewStyleBaseFontSize'] = (int) $style['FontSize'];
        $properties['IPSViewStyleFontScale'] = (int) $style['FontScale'];
        $properties['IPSViewStyleBorderRadius'] = (float) $style['BorderRadius'];
        $properties['IPSViewStyleBorderWidth'] = (float) $style['BorderWidth'];
        $properties['IPSViewStyleLineWidth'] = (float) $style['LineWidth'];
        $properties['IPSViewStyleShadowBlur'] = (float) $style['ShadowBlur'];
        $properties['IPSViewStyleShadowSpread'] = (float) $style['ShadowSpread'];
        $properties['IPSViewStyleShadowOffsetX'] = (float) $style['ShadowOffsetX'];
        $properties['IPSViewStyleShadowOffsetY'] = (float) $style['ShadowOffsetY'];
        $properties['IPSViewStyleDisabledOpacity'] = (int) $style['DisabledOpacity'];
        $properties['IPSViewStyleGradientStrength'] = (int) $style['GradientStrength'];

        return $properties;
    }

    /**
     * Applies the complete shared native theme plus exact native effects.
     *
     * Native colors are written first. For generated shared styles, exact opacity and gradients are
     * applied afterwards. IPSView-media sources can preserve their native alpha and gradient details.
     *
     * @param array<string,mixed>        $nativeTheme
     * @param array<string,string|float> $style
     *
     * @return array<string,int|bool>
     */
    public static function applyToDocument(
        stdClass $document,
        array $nativeTheme,
        array $style,
        int $scope,
        int $gradientStrength = 0,
        bool $transparentBackground = false,
        bool $createMissing = false,
        bool $preserveNativeColorDetails = false
    ): array {
        $nativeReport = IPSViewControlThemeHelper::apply($document, $nativeTheme, $createMissing);
        $effectReport = IPSViewEffects::apply(
            $document,
            [
                'shadowStyle'         => IPSViewEffects::SHADOW_PRESERVE,
                'transparencyMode'    => IPSViewEffects::TRANSPARENCY_PRESERVE,
                'transparencyPercent' => 0,
                'gradientStyle'       => IPSViewEffects::GRADIENT_PRESERVE,
                'gradientDirection'   => IPSViewEffects::GRADIENT_TO_DARKER
            ],
            $scope
        );
        if ($preserveNativeColorDetails) {
            if ($transparentBackground) {
                self::applyTransparentViewBackground($document);
            }
        } else {
            self::applyExactOpacity($document, $style, $transparentBackground);
            self::applyExactGradient($document, $gradientStrength);
        }
        $shadowChanged = self::applyExactShadow($document, $style);
        $appearance = self::appearance($style);
        $typographyReport = IPSViewTypography::apply($document, $appearance, $scope);
        $shapeReport = IPSViewShape::apply($document, $appearance);
        $exactTypographyChanged = self::applyExactTypography($document, $style);
        $exactShapeChanged = self::applyExactShape($document, $style);
        $lineWidthChanged = self::applyLineWidth($document, $style);

        return [
            ...$nativeReport,
            ...$effectReport,
            ...$typographyReport,
            ...$shapeReport,
            'shadowChanged'          => $effectReport['shadowChanged'] || $shadowChanged,
            'exactTypographyChanged' => $exactTypographyChanged,
            'exactShapeChanged'      => $exactShapeChanged,
            'lineWidthChanged'       => $lineWidthChanged
        ];
    }

    /** @param array<string,string|float> $style */
    private static function applyExactTypography(stdClass $document, array $style): bool
    {
        $fontFamily = trim((string) ($style['FontFamily'] ?? ''));
        $normalizedFont = IPSViewFontCatalogHelper::normalizeFamily($fontFamily);
        if ($normalizedFont !== null) {
            return false;
        }

        $nativeFontFamily = self::isSystemFontFamily($fontFamily) ? '' : $fontFamily;
        if ((string) ($document->DefaultFontFamily ?? '') === $nativeFontFamily) {
            return false;
        }

        $document->DefaultFontFamily = $nativeFontFamily;

        return true;
    }

    private static function isSystemFontFamily(string $fontFamily): bool
    {
        $normalized = strtolower(trim($fontFamily));
        if ($normalized === '' || $normalized === 'system') {
            return true;
        }

        return str_contains($normalized, '-apple-system')
            || str_contains($normalized, 'blinkmacsystemfont')
            || str_contains($normalized, 'segoe ui');
    }

    /** @param array<string,string|float> $style */
    private static function applyExactShape(stdClass $document, array $style): bool
    {
        $changed = false;
        $radius = max(0.0, min(40.0, (float) ($style['BorderRadius'] ?? 8.0)));
        $borderWidth = max(0.0, min(10.0, (float) ($style['BorderWidth'] ?? 1.0)));

        if (!property_exists($document, 'DefaultBorderRadius')
            || abs((float) $document->DefaultBorderRadius - $radius) > 0.001) {
            $document->DefaultBorderRadius = $radius;
            $changed = true;
        }
        if (!property_exists($document, 'DefaultBorderWidth')
            || abs((float) $document->DefaultBorderWidth - $borderWidth) > 0.001) {
            $document->DefaultBorderWidth = $borderWidth;
            $changed = true;
        }

        $roundedTracks = $radius > 0.0;
        foreach (['CircleTrackEdgesRounded', 'ProgressbarTrackEdgesRounded'] as $property) {
            if ((bool) ($document->{$property} ?? false) === $roundedTracks) {
                continue;
            }
            $document->{$property} = $roundedTracks;
            $changed = true;
        }

        return $changed;
    }

    /** @param array<string,string|float> $style */
    private static function applyExactOpacity(
        stdClass $document,
        array $style,
        bool $transparentBackground
    ): void {
        foreach (IPSViewControlThemeHelper::catalog() as $field => $definition) {
            $styleField = (string) ($definition['styleField'] ?? '');
            $opacityField = self::OPACITY_STYLE_FIELDS[$styleField] ?? null;
            if ($opacityField === null || !array_key_exists($opacityField, $style)) {
                continue;
            }

            $color = $document->{$field} ?? null;
            if (!$color instanceof stdClass) {
                continue;
            }

            $alpha = (int) round(max(0.0, min(1.0, (float) $style[$opacityField])) * 255);
            if ($field === 'ColorView' && $transparentBackground) {
                $alpha = 0;
            }
            $color->A = $alpha;
            if (property_exists($color, 'A2')) {
                $color->A2 = $alpha;
            }
        }

        $popupShadow = $document->ColorPopupShadow ?? null;
        if ($popupShadow instanceof stdClass && array_key_exists('PopupShadowOpacity', $style)) {
            $alpha = (int) round(max(0.0, min(1.0, (float) $style['PopupShadowOpacity'])) * 255);
            $popupShadow->A = $alpha;
            if (property_exists($popupShadow, 'A2')) {
                $popupShadow->A2 = $alpha;
            }
        }
    }


    private static function applyTransparentViewBackground(stdClass $document): void
    {
        $color = $document->ColorView ?? null;
        if (!$color instanceof stdClass || !self::isColorObject($color)) {
            return;
        }

        $color->A = 0;
        if (property_exists($color, 'A2')) {
            $color->A2 = 0;
        }
    }

    private static function applyExactGradient(stdClass $document, int $gradientStrength): void
    {
        $strength = max(0, min(80, $gradientStrength)) / 100;
        foreach (self::GRADIENT_FIELDS as $field) {
            $color = $document->{$field} ?? null;
            if (!$color instanceof stdClass || !self::isColorObject($color)) {
                continue;
            }

            if ($strength <= 0.0) {
                $color->Type = 0;
                if (self::hasSecondaryColor($color)) {
                    $color->A2 = (int) ($color->A ?? 255);
                    $color->R2 = (int) $color->R;
                    $color->G2 = (int) $color->G;
                    $color->B2 = (int) $color->B;
                }
                continue;
            }

            $color->Type = 1;
            $color->A2 = (int) ($color->A ?? 255);
            $color->R2 = (int) round((float) $color->R * (1 - $strength));
            $color->G2 = (int) round((float) $color->G * (1 - $strength));
            $color->B2 = (int) round((float) $color->B * (1 - $strength));
        }
    }

    /** @param array<string,string|float> $style */
    private static function applyExactShadow(stdClass $document, array $style): bool
    {
        [$offsetX, $offsetY, $blur, $spread] = self::shadowGeometry($style);
        $document->ShadowOffsetX = $offsetX;
        $document->ShadowOffsetY = $offsetY;
        $document->ShadowBlurRadius = $blur;
        $document->ShadowSpreadRadius = $spread;

        return true;
    }

    /** @param array<string,string|float> $style @return array{0:float,1:float,2:float,3:float} */
    private static function shadowGeometry(array $style): array
    {
        $shadow = trim((string) ($style['Shadow'] ?? ''));
        if ($shadow !== '' && preg_match(
            '/^(-?[0-9]+(?:\.[0-9]+)?)px\s+(-?[0-9]+(?:\.[0-9]+)?)px\s+([0-9]+(?:\.[0-9]+)?)px\s+(-?[0-9]+(?:\.[0-9]+)?)px\s+/',
            $shadow,
            $matches
        ) === 1) {
            return [(float) $matches[1], (float) $matches[2], (float) $matches[3], (float) $matches[4]];
        }

        return [0.0, 8.0, 18.0, 0.0];
    }

    /** @param array<string,string|float> $style */
    private static function applyLineWidth(stdClass $document, array $style): bool
    {
        if (!array_key_exists('LineWidth', $style)) {
            return false;
        }

        $lineWidth = max(0.0, min(10.0, (float) $style['LineWidth']));
        if (property_exists($document, 'LineWidth') && abs((float) $document->LineWidth - $lineWidth) <= 0.001) {
            return false;
        }

        $document->LineWidth = $lineWidth;

        return true;
    }

    private static function isColorObject(stdClass $color): bool
    {
        return property_exists($color, 'R')
            && property_exists($color, 'G')
            && property_exists($color, 'B')
            && is_numeric($color->R)
            && is_numeric($color->G)
            && is_numeric($color->B);
    }

    private static function hasSecondaryColor(stdClass $color): bool
    {
        return property_exists($color, 'R2')
            || property_exists($color, 'G2')
            || property_exists($color, 'B2')
            || property_exists($color, 'A2');
    }

    /** @param array<string,string|float> $style */
    private static function color(array $style, string $field): string
    {
        $value = $style[$field] ?? null;
        if (!is_string($value)) {
            throw new InvalidArgumentException('Resolved shared IPSView style is missing color field ' . $field . '.');
        }

        if (preg_match('/#([0-9a-f]{6})/i', $value, $matches) === 1) {
            return '#' . strtoupper($matches[1]);
        }

        if (preg_match(
            '/rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})/i',
            $value,
            $matches
        ) === 1) {
            return sprintf('#%02X%02X%02X', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        throw new InvalidArgumentException('Resolved shared IPSView style color ' . $field . ' is invalid.');
    }
}

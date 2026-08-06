<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use InvalidArgumentException;

final class IPSViewThemePreview
{
    /**
     * @var array<string, array{
     *     alias: string,
     *     fallback: string,
     *     faces: array<string, array{
     *         filename: string,
     *         mime: string,
     *         format: string
     *     }>
     * }>
     */
    private const PREVIEW_FONTS = [
        'Roboto'        => [
            'alias'    => 'IPSViewPreviewFont01',
            'fallback' => 'sans-serif',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'Roboto-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '400-italic' => [
                    'filename' => 'Roboto-RegularItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-normal' => [
                    'filename' => 'Roboto-Bold.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-italic' => [
                    'filename' => 'Roboto-BoldItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'RobotoMono'    => [
            'alias'    => 'IPSViewPreviewFont02',
            'fallback' => 'monospace',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'RobotoMono-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '400-italic' => [
                    'filename' => 'RobotoMono-RegularItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-normal' => [
                    'filename' => 'RobotoMono-Bold.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-italic' => [
                    'filename' => 'RobotoMono-BoldItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'DancingScript' => [
            'alias'    => 'IPSViewPreviewFont03',
            'fallback' => 'cursive',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'DancingScript-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-normal' => [
                    'filename' => 'DancingScript-Bold.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'IndieFlower'   => [
            'alias'    => 'IPSViewPreviewFont04',
            'fallback' => 'cursive',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'IndieFlower-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'OpenSans'      => [
            'alias'    => 'IPSViewPreviewFont05',
            'fallback' => 'sans-serif',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'OpenSans-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '400-italic' => [
                    'filename' => 'OpenSans-RegularItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-normal' => [
                    'filename' => 'OpenSans-Bold.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-italic' => [
                    'filename' => 'OpenSans-BoldItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'PTSans'        => [
            'alias'    => 'IPSViewPreviewFont06',
            'fallback' => 'sans-serif',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'PTSans-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '400-italic' => [
                    'filename' => 'PTSans-RegularItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-normal' => [
                    'filename' => 'PTSans-Bold.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
                '700-italic' => [
                    'filename' => 'PTSans-BoldItalic.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'BebasNeue'     => [
            'alias'    => 'IPSViewPreviewFont07',
            'fallback' => 'sans-serif',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'BebasNeue-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
        'Segment7'      => [
            'alias'    => 'IPSViewPreviewFont08',
            'fallback' => 'monospace',
            'faces'    => [
                '400-normal' => [
                    'filename' => 'Segment7-Regular.ttf',
                    'mime'     => 'font/ttf',
                    'format'   => 'truetype',
                ],
            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    private static array $previewFontData = [];

    /**
     * Creates a self-contained SVG data URI for the configuration form.
     *
     * @param array<string, string> $palette
     * @param array<string, mixed>  $effects
     * @param array<string, mixed>  $appearance
     * @param array<string, mixed>  $background
     */
    public static function createDataUri(
        array $palette,
        array $effects = [],
        array $appearance = [],
        array $background = [],
        int $startGrid = 0
    ): string {
        $svg = self::createSvg($palette, $effects, $appearance, $background, $startGrid);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Creates the self-contained SVG used by the configuration-form preview.
     *
     * @param array<string, string> $palette
     * @param array<string, mixed>  $effects
     * @param array<string, mixed>  $appearance
     * @param array<string, mixed>  $background
     */
    public static function createSvg(
        array $palette,
        array $effects = [],
        array $appearance = [],
        array $background = [],
        int $startGrid = 0
    ): string {
        $view = self::color($palette, IPSViewTheme::ROLE_VIEW_BACKGROUND);
        $page = self::color($palette, IPSViewTheme::ROLE_PAGE_BACKGROUND);
        $surface = self::color($palette, IPSViewTheme::ROLE_SURFACE);
        $primary = self::color($palette, IPSViewTheme::ROLE_PRIMARY_TEXT);
        $secondary = self::color($palette, IPSViewTheme::ROLE_SECONDARY_TEXT);
        $border = self::color($palette, IPSViewTheme::ROLE_BORDER);
        $accent = self::color($palette, IPSViewTheme::ROLE_ACCENT);
        $active = self::color($palette, IPSViewTheme::ROLE_ACTIVE);
        $inactive = self::color($palette, IPSViewTheme::ROLE_INACTIVE);
        $success = self::color($palette, IPSViewTheme::ROLE_SUCCESS);
        $warning = self::color($palette, IPSViewTheme::ROLE_WARNING);
        $error = self::color($palette, IPSViewTheme::ROLE_ERROR);
        $effects = IPSViewEffects::resolve($effects);
        $typography = IPSViewTypography::preview($appearance);
        $shape = IPSViewShape::preview($appearance);
        $fontScale = max(0.75, min(1.45, $typography['baseFontSize'] / 14));
        $fontDefinition = self::fontDefinition(
            $typography['fontFamily'],
            $typography['isBold'],
            $typography['isItalic']
        );
        $fontFamily = htmlspecialchars(
            self::fontStack($typography['fontFamily']),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );
        $fontWeight = $typography['isBold'] ? 700 : 400;
        $fontStyle = $typography['isItalic'] ? 'italic' : 'normal';
        $textDecoration = $typography['isUnderline'] ? 'underline' : 'none';
        $fontAttributes = sprintf(
            'font-family="%s" font-weight="%d" font-style="%s" text-decoration="%s"',
            $fontFamily,
            $fontWeight,
            $fontStyle,
            $textDecoration
        );
        $cornerRadius = max(0, min(24, $shape['cornerRadius']));
        $smallRadius = $cornerRadius === 0 ? 0 : max(2, min(12, $cornerRadius));
        $borderWidth = number_format(max(0.0, min(8.0, $shape['borderWidth'])), 1, '.', '');
        $opacity = number_format(IPSViewEffects::previewOpacity($effects), 2, '.', '');
        $shadow = IPSViewTheme::mix($view, '#000000', 0.82);
        $shadowSettings = IPSViewEffects::previewShadow($effects);
        $shadowOffset = $shadowSettings['offset'];
        $shadowBlur = $shadowSettings['blur'];
        $shadowOpacity = number_format($shadowSettings['opacity'], 2, '.', '');
        $backgroundPreview = IPSViewBackground::preview($background);
        $backgroundDefinition = self::backgroundDefinition($backgroundPreview);
        $backgroundElement = self::backgroundElement($backgroundPreview);

        $definitions = [
            self::gradientDefinition('viewFill', $view, $effects),
            self::gradientDefinition('pageFill', $page, $effects),
            self::gradientDefinition('surfaceFill', $surface, $effects),
            self::gradientDefinition('accentFill', $accent, $effects),
            self::gradientDefinition('activeFill', $active, $effects),
            self::gradientDefinition('inactiveFill', $inactive, $effects),
            self::gradientDefinition('successFill', $success, $effects),
            self::gradientDefinition('warningFill', $warning, $effects),
            self::gradientDefinition('errorFill', $error, $effects),
        ];
        $gradientDefinitions = implode("\n", array_filter($definitions));
        $viewFill = self::fill('viewFill', $view, $effects);
        $pageFill = self::fill('pageFill', $page, $effects);
        $surfaceFill = self::fill('surfaceFill', $surface, $effects);
        $accentFill = self::fill('accentFill', $accent, $effects);
        $activeFill = self::fill('activeFill', $active, $effects);
        $inactiveFill = self::fill('inactiveFill', $inactive, $effects);
        $successFill = self::fill('successFill', $success, $effects);
        $warningFill = self::fill('warningFill', $warning, $effects);
        $errorFill = self::fill('errorFill', $error, $effects);
        $titleSize = self::fontSize(20, $fontScale);
        $subtitleSize = self::fontSize(13, $fontScale);
        $valueSize = self::fontSize(22, $fontScale);
        $statusSize = self::fontSize(12, $fontScale);
        $sectionSize = self::fontSize(18, $fontScale);
        $layout = self::previewLayout($startGrid);
        [$lightsX, $lightsY, $lightsWidth, $lightsHeight] = $layout['lights'];
        [$shuttersX, $shuttersY, $shuttersWidth, $shuttersHeight] = $layout['shutters'];
        [$securityX, $securityY, $securityWidth, $securityHeight] = $layout['security'];
        [$typographyX, $typographyY, $typographyWidth, $typographyHeight] = $layout['typography'];

        $lightsTextX = $lightsX + 22;
        $lightsLabelY = $lightsY + 32;
        $lightsValueY = $lightsY + 63;
        $lightsSwitchX = $lightsX + $lightsWidth - 80;
        $lightsSwitchY = $lightsY + 46;
        $lightsSwitchCircleX = $lightsSwitchX + 39;
        $lightsSwitchCircleY = $lightsSwitchY + 15;
        $lightsStatusY = $lightsY + $lightsHeight - 21;

        $shuttersTextX = $shuttersX + 22;
        $shuttersLabelY = $shuttersY + 32;
        $shuttersValueY = $shuttersY + 63;
        $shuttersProgressY = $shuttersY + 82;
        $shuttersProgressWidth = $shuttersWidth - 52;
        $shuttersActiveWidth = round($shuttersProgressWidth * 0.65, 1);
        $shuttersHandleX = $shuttersTextX + $shuttersActiveWidth;
        $shuttersHandleY = $shuttersProgressY + 4;

        $securityTextX = $securityX + 22;
        $securityLabelY = $securityY + 32;
        $securityValueY = $securityY + 63;
        $securityButtonY = $securityY + $securityHeight - 35;
        $securityButtonTotalWidth = $securityWidth - 54;
        $securitySuccessWidth = (int) round($securityButtonTotalWidth * 0.42);
        $securityWarningWidth = $securityButtonTotalWidth - $securitySuccessWidth;
        $securityWarningX = $securityTextX + $securitySuccessWidth + 10;
        $securitySuccessTextX = $securityTextX + ($securitySuccessWidth / 2);
        $securityWarningTextX = $securityWarningX + ($securityWarningWidth / 2);
        $securityButtonTextY = $securityButtonY + 17;

        $typographyTextX = $typographyX + 22;
        $typographyTitleY = $typographyY + 31;
        $typographyDescriptionY = $typographyY + 57;
        $compactTypography = $typographyWidth < 500;
        $typographyDescription = $compactTypography
            ? 'Global design basics'
            : 'The Assistant prepares global basics; detailed layout remains in IPSView Designer.';
        $typographyCircleSpacing = $compactTypography ? 30 : 45;
        $typographyCircleRadius = $compactTypography ? 10 : 12;
        $typographyCircleY = $compactTypography
            ? $typographyY + $typographyHeight - 22
            : $typographyY + 48;
        $typographyCircle4X = $typographyX + $typographyWidth - 47;
        $typographyCircle3X = $typographyCircle4X - $typographyCircleSpacing;
        $typographyCircle2X = $typographyCircle3X - $typographyCircleSpacing;
        $typographyCircle1X = $typographyCircle2X - $typographyCircleSpacing;
        $gridGuides = self::gridGuides($startGrid, $accent);
        $gridBadge = $startGrid === 0
            ? ''
            : sprintf(
                '<text x="590" y="62" text-anchor="middle" fill="%s" %s font-size="%s">%d-COLUMN START GRID</text>',
                $secondary,
                $fontAttributes,
                $subtitleSize,
                $startGrid
            );

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="920" height="420" viewBox="0 0 920 420" data-start-grid="{$startGrid}">
  <defs>
    {$fontDefinition}
    {$backgroundDefinition}
    <pattern id="checker" width="20" height="20" patternUnits="userSpaceOnUse">
      <rect width="20" height="20" fill="#E5E7EB"/>
      <rect width="10" height="10" fill="#F8FAFC"/>
      <rect x="10" y="10" width="10" height="10" fill="#F8FAFC"/>
    </pattern>
    {$gradientDefinitions}
    <filter id="shadow" x="-25%" y="-25%" width="150%" height="170%">
      <feDropShadow dx="{$shadowOffset}" dy="{$shadowOffset}" stdDeviation="{$shadowBlur}" flood-color="{$shadow}" flood-opacity="{$shadowOpacity}"/>
    </filter>
  </defs>
  <rect width="920" height="420" rx="{$cornerRadius}" fill="url(#checker)"/>
  <rect width="920" height="420" rx="{$cornerRadius}" fill="{$viewFill}" fill-opacity="{$opacity}"/>
  <rect x="22" y="22" width="876" height="376" rx="{$cornerRadius}" fill="{$pageFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
  {$backgroundElement}
  {$gridGuides}
  <rect x="22" y="22" width="876" height="68" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}"/>
  <rect x="22" y="72" width="876" height="18" fill="{$surfaceFill}" fill-opacity="{$opacity}"/>
  <circle cx="60" cy="56" r="17" fill="{$accentFill}" fill-opacity="{$opacity}"/>
  <path d="M52 56h16M60 48v16" stroke="{$primary}" stroke-width="3" stroke-linecap="round"/>
  <text x="90" y="52" fill="{$primary}" {$fontAttributes} font-size="{$titleSize}">Design preview</text>
  <text x="90" y="73" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">Colors, typography, shapes and effects</text>
  {$gridBadge}
  <text x="824" y="62" fill="{$primary}" {$fontAttributes} font-size="{$sectionSize}">21.5 °C</text>

  <g filter="url(#shadow)">
    <rect x="{$lightsX}" y="{$lightsY}" width="{$lightsWidth}" height="{$lightsHeight}" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="{$lightsTextX}" y="{$lightsLabelY}" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">LIGHTS</text>
    <text x="{$lightsTextX}" y="{$lightsValueY}" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">Living room</text>
    <rect x="{$lightsSwitchX}" y="{$lightsSwitchY}" width="54" height="30" rx="{$smallRadius}" fill="{$activeFill}" fill-opacity="{$opacity}"/>
    <circle cx="{$lightsSwitchCircleX}" cy="{$lightsSwitchCircleY}" r="11" fill="{$primary}"/>
    <text x="{$lightsTextX}" y="{$lightsStatusY}" fill="{$success}" {$fontAttributes} font-size="{$subtitleSize}">Active</text>
  </g>

  <g filter="url(#shadow)">
    <rect x="{$shuttersX}" y="{$shuttersY}" width="{$shuttersWidth}" height="{$shuttersHeight}" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="{$shuttersTextX}" y="{$shuttersLabelY}" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">SHUTTERS</text>
    <text x="{$shuttersTextX}" y="{$shuttersValueY}" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">65 %</text>
    <rect x="{$shuttersTextX}" y="{$shuttersProgressY}" width="{$shuttersProgressWidth}" height="8" rx="{$smallRadius}" fill="{$inactiveFill}" fill-opacity="{$opacity}"/>
    <rect x="{$shuttersTextX}" y="{$shuttersProgressY}" width="{$shuttersActiveWidth}" height="8" rx="{$smallRadius}" fill="{$accentFill}" fill-opacity="{$opacity}"/>
    <circle cx="{$shuttersHandleX}" cy="{$shuttersHandleY}" r="11" fill="{$accentFill}" fill-opacity="{$opacity}" stroke="{$surface}" stroke-width="4"/>
  </g>

  <g filter="url(#shadow)">
    <rect x="{$securityX}" y="{$securityY}" width="{$securityWidth}" height="{$securityHeight}" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="{$securityTextX}" y="{$securityLabelY}" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">SECURITY</text>
    <text x="{$securityTextX}" y="{$securityValueY}" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">Home</text>
    <rect x="{$securityTextX}" y="{$securityButtonY}" width="{$securitySuccessWidth}" height="24" rx="{$smallRadius}" fill="{$successFill}" fill-opacity="{$opacity}"/>
    <text x="{$securitySuccessTextX}" y="{$securityButtonTextY}" text-anchor="middle" fill="{$primary}" {$fontAttributes} font-size="{$statusSize}">OK</text>
    <rect x="{$securityWarningX}" y="{$securityButtonY}" width="{$securityWarningWidth}" height="24" rx="{$smallRadius}" fill="{$warningFill}" fill-opacity="{$opacity}"/>
    <text x="{$securityWarningTextX}" y="{$securityButtonTextY}" text-anchor="middle" fill="{$primary}" {$fontAttributes} font-size="{$statusSize}">NOTICE</text>
  </g>

  <rect x="{$typographyX}" y="{$typographyY}" width="{$typographyWidth}" height="{$typographyHeight}" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
  <text x="{$typographyTextX}" y="{$typographyTitleY}" fill="{$primary}" {$fontAttributes} font-size="{$sectionSize}">Typography and form language</text>
  <text x="{$typographyTextX}" y="{$typographyDescriptionY}" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">{$typographyDescription}</text>
  <circle cx="{$typographyCircle1X}" cy="{$typographyCircleY}" r="{$typographyCircleRadius}" fill="{$successFill}" fill-opacity="{$opacity}"/>
  <circle cx="{$typographyCircle2X}" cy="{$typographyCircleY}" r="{$typographyCircleRadius}" fill="{$warningFill}" fill-opacity="{$opacity}"/>
  <circle cx="{$typographyCircle3X}" cy="{$typographyCircleY}" r="{$typographyCircleRadius}" fill="{$errorFill}" fill-opacity="{$opacity}"/>
  <circle cx="{$typographyCircle4X}" cy="{$typographyCircleY}" r="{$typographyCircleRadius}" fill="{$inactiveFill}" fill-opacity="{$opacity}"/>
</svg>
SVG;
    }

    /**
     * @return array{
     *     lights: array{int, int, int, int},
     *     shutters: array{int, int, int, int},
     *     security: array{int, int, int, int},
     *     typography: array{int, int, int, int}
     * }
     */
    private static function previewLayout(int $startGrid): array
    {
        return match ($startGrid) {
            0 => [
                'lights'     => [48, 118, 250, 116],
                'shutters'   => [335, 118, 250, 116],
                'security'   => [622, 118, 250, 116],
                'typography' => [48, 268, 824, 96],
            ],
            2 => [
                'lights'     => [48, 118, 404, 105],
                'shutters'   => [468, 118, 404, 105],
                'security'   => [48, 244, 404, 105],
                'typography' => [468, 244, 404, 105],
            ],
            3 => [
                'lights'     => [48, 118, 264, 116],
                'shutters'   => [328, 118, 264, 116],
                'security'   => [608, 118, 264, 116],
                'typography' => [48, 268, 824, 96],
            ],
            default => throw new InvalidArgumentException('The selected preview start grid is not supported.'),
        };
    }

    /**
     * Draws optional column guides matching the selected start-grid layout.
     */
    private static function gridGuides(int $startGrid, string $accent): string
    {
        if ($startGrid === 0) {
            return '';
        }

        $columnWidth = $startGrid === 2 ? 404 : 264;
        $guides = [];
        for ($column = 0; $column < $startGrid; ++$column) {
            $x = 48 + ($column * ($columnWidth + 16));
            $guides[] = sprintf(
                '<rect x="%d" y="106" width="%d" height="258" rx="6"/>',
                $x,
                $columnWidth
            );
        }

        return sprintf(
            '<g id="startGridGuides" data-columns="%d" fill="none" stroke="%s" stroke-width="2" stroke-dasharray="7 6" opacity="0.42">%s</g>',
            $startGrid,
            $accent,
            implode('', $guides)
        );
    }

    /**
     * Creates the SVG pattern definition required by a tiled background image.
     *
     * @param array{dataUri: string, layout: string, width: int, height: int}|null $background
     */
    private static function backgroundDefinition(?array $background): string
    {
        if ($background === null || $background['layout'] !== IPSViewBackground::LAYOUT_TILE) {
            return '';
        }

        $width = max(1, $background['width']);
        $height = max(1, $background['height']);
        $dataUri = htmlspecialchars($background['dataUri'], ENT_QUOTES | ENT_XML1, 'UTF-8');

        return sprintf(
            '<pattern id="backgroundImage" width="%d" height="%d" patternUnits="userSpaceOnUse"><image href="%s" width="%d" height="%d"/></pattern>',
            $width,
            $height,
            $dataUri,
            $width,
            $height
        );
    }

    /**
     * Creates the SVG element for the selected background-image layout.
     *
     * @param array{dataUri: string, layout: string, width: int, height: int}|null $background
     */
    private static function backgroundElement(?array $background): string
    {
        if ($background === null) {
            return '';
        }

        if ($background['layout'] === IPSViewBackground::LAYOUT_TILE) {
            return '<rect x="22" y="22" width="876" height="376" fill="url(#backgroundImage)"/>';
        }

        $dataUri = htmlspecialchars($background['dataUri'], ENT_QUOTES | ENT_XML1, 'UTF-8');
        if ($background['layout'] === IPSViewBackground::LAYOUT_CENTER) {
            $width = max(1, $background['width']);
            $height = max(1, $background['height']);
            $x = 22 + (876 - $width) / 2;
            $y = 22 + (376 - $height) / 2;

            return sprintf(
                '<image href="%s" x="%s" y="%s" width="%d" height="%d"/>',
                $dataUri,
                number_format($x, 1, '.', ''),
                number_format($y, 1, '.', ''),
                $width,
                $height
            );
        }

        return sprintf(
            '<image href="%s" x="22" y="22" width="876" height="376" preserveAspectRatio="none"/>',
            $dataUri
        );
    }

    /**
     * Embeds the closest bundled font face required by the current preview.
     */
    private static function fontDefinition(
        string $fontFamily,
        bool $isBold,
        bool $isItalic
    ): string {
        $font = self::PREVIEW_FONTS[$fontFamily] ?? null;
        if ($font === null) {
            return '';
        }

        $requestedWeight = $isBold ? 700 : 400;
        $requestedStyle = $isItalic ? 'italic' : 'normal';
        $face = self::resolveFontFace($font['faces'], $requestedWeight, $requestedStyle);
        if ($face === null) {
            return '';
        }

        $rule = sprintf(
            '@font-face { font-family: "%s"; src: url("data:%s;base64,%s") format("%s"); font-style: %s; font-weight: %d; font-display: block; }',
            $font['alias'],
            $face['source']['mime'],
            $face['source']['data'],
            $face['source']['format'],
            $face['style'],
            $face['weight']
        );

        return '<style type="text/css"><![CDATA[' . $rule . ']]></style>';
    }

    /**
     * Selects the closest available bundled font face for the requested style.
     *
     * @param array<string, array{
     *     filename: string,
     *     mime: string,
     *     format: string
     * }> $faces
     *
     * @return array{
     *     source: array{data: string, mime: string, format: string},
     *     weight: int,
     *     style: string
     * }|null
     */
    private static function resolveFontFace(
        array $faces,
        int $requestedWeight,
        string $requestedStyle
    ): ?array {
        $keys = [
            $requestedWeight . '-' . $requestedStyle,
            $requestedWeight . '-normal',
            '400-' . $requestedStyle,
            '400-normal',
        ];

        foreach (array_unique($keys) as $key) {
            if (!isset($faces[$key])) {
                continue;
            }

            $source = self::resolveFontSource($faces[$key]);
            if ($source === null) {
                continue;
            }

            [$weight, $style] = explode('-', $key, 2);

            return [
                'source' => $source,
                'weight' => (int) $weight,
                'style'  => $style,
            ];
        }

        return null;
    }

    /**
     * Resolves one bundled font file to an embeddable data source.
     *
     * @param array{
     *     filename: string,
     *     mime: string,
     *     format: string
     * } $face
     *
     * @return array{data: string, mime: string, format: string}|null
     */
    private static function resolveFontSource(array $face): ?array
    {
        $fontData = self::fontData($face['filename']);
        if ($fontData === '') {
            return null;
        }

        return [
            'data'   => $fontData,
            'mime'   => $face['mime'],
            'format' => $face['format'],
        ];
    }

    /**
     * Returns the CSS font stack for a bundled or user-provided family.
     */
    private static function fontStack(string $fontFamily): string
    {
        $font = self::PREVIEW_FONTS[$fontFamily] ?? null;
        if ($font === null) {
            return $fontFamily . ', Arial, sans-serif';
        }

        return $font['fallback'] === 'sans-serif'
            ? $font['alias'] . ', Arial, sans-serif'
            : $font['alias'] . ', ' . $font['fallback'];
    }

    /**
     * Loads and caches one bundled preview font as Base64 data.
     */
    private static function fontData(string $filename): string
    {
        if (isset(self::$previewFontData[$filename])) {
            return self::$previewFontData[$filename];
        }

        $path = __DIR__ . '/fonts/' . $filename;
        $contents = is_file($path) ? file_get_contents($path) : false;
        self::$previewFontData[$filename] = is_string($contents)
            ? base64_encode($contents)
            : '';

        return self::$previewFontData[$filename];
    }

    /**
     * Creates one SVG linear-gradient definition when gradients are enabled.
     *
     * @param array<string, mixed> $effects
     */
    private static function gradientDefinition(
        string $id,
        string $color,
        array $effects
    ): string {
        if (!IPSViewEffects::hasGeneratedGradient($effects)) {
            return '';
        }

        $second = IPSViewEffects::gradientColor($color, $effects);

        return sprintf(
            '<linearGradient id="%s" x1="0%%" y1="0%%" x2="0%%" y2="100%%"><stop offset="0%%" stop-color="%s"/><stop offset="100%%" stop-color="%s"/></linearGradient>',
            $id,
            $color,
            $second
        );
    }

    /**
     * Returns either a solid color or the matching generated gradient reference.
     *
     * @param array<string, mixed> $effects
     */
    private static function fill(string $id, string $color, array $effects): string
    {
        return IPSViewEffects::hasGeneratedGradient($effects)
            ? sprintf('url(#%s)', $id)
            : $color;
    }

    /**
     * Scales and clamps one font size for the fixed preview canvas.
     */
    private static function fontSize(int $baseSize, float $scale): int
    {
        return max(9, min(30, (int) round($baseSize * $scale)));
    }

    /**
     * Resolves one semantic palette role to a normalized preview color.
     *
     * @param array<string, string> $palette
     */
    private static function color(array $palette, string $role): string
    {
        return IPSViewTheme::normalizeColor((string) ($palette[$role] ?? '#000000'));
    }
}

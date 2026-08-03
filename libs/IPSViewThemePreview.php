<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

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
     */
    public static function createDataUri(
        array $palette,
        array $effects = [],
        array $appearance = []
    ): string {
        $svg = self::createSvg($palette, $effects, $appearance);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * @param array<string, string> $palette
     * @param array<string, mixed>  $effects
     * @param array<string, mixed>  $appearance
     */
    public static function createSvg(
        array $palette,
        array $effects = [],
        array $appearance = []
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

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="920" height="420" viewBox="0 0 920 420">
  <defs>
    {$fontDefinition}
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
  <rect x="22" y="22" width="876" height="68" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}"/>
  <rect x="22" y="72" width="876" height="18" fill="{$surfaceFill}" fill-opacity="{$opacity}"/>
  <circle cx="60" cy="56" r="17" fill="{$accentFill}" fill-opacity="{$opacity}"/>
  <path d="M52 56h16M60 48v16" stroke="{$primary}" stroke-width="3" stroke-linecap="round"/>
  <text x="90" y="52" fill="{$primary}" {$fontAttributes} font-size="{$titleSize}">Design preview</text>
  <text x="90" y="73" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">Colors, typography, shapes and effects</text>
  <text x="824" y="62" fill="{$primary}" {$fontAttributes} font-size="{$sectionSize}">21.5 °C</text>

  <g filter="url(#shadow)">
    <rect x="48" y="118" width="250" height="116" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="70" y="150" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">LIGHTS</text>
    <text x="70" y="181" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">Living room</text>
    <rect x="218" y="164" width="54" height="30" rx="{$smallRadius}" fill="{$activeFill}" fill-opacity="{$opacity}"/>
    <circle cx="257" cy="179" r="11" fill="{$primary}"/>
    <text x="70" y="213" fill="{$success}" {$fontAttributes} font-size="{$subtitleSize}">Active</text>
  </g>

  <g filter="url(#shadow)">
    <rect x="335" y="118" width="250" height="116" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="357" y="150" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">SHUTTERS</text>
    <text x="357" y="181" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">65 %</text>
    <rect x="357" y="202" width="198" height="8" rx="{$smallRadius}" fill="{$inactiveFill}" fill-opacity="{$opacity}"/>
    <rect x="357" y="202" width="129" height="8" rx="{$smallRadius}" fill="{$accentFill}" fill-opacity="{$opacity}"/>
    <circle cx="486" cy="206" r="11" fill="{$accentFill}" fill-opacity="{$opacity}" stroke="{$surface}" stroke-width="4"/>
  </g>

  <g filter="url(#shadow)">
    <rect x="622" y="118" width="250" height="116" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
    <text x="644" y="150" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">SECURITY</text>
    <text x="644" y="181" fill="{$primary}" {$fontAttributes} font-size="{$valueSize}">Home</text>
    <rect x="644" y="199" width="82" height="24" rx="{$smallRadius}" fill="{$successFill}" fill-opacity="{$opacity}"/>
    <text x="685" y="216" text-anchor="middle" fill="{$primary}" {$fontAttributes} font-size="{$statusSize}">OK</text>
    <rect x="736" y="199" width="106" height="24" rx="{$smallRadius}" fill="{$warningFill}" fill-opacity="{$opacity}"/>
    <text x="789" y="216" text-anchor="middle" fill="{$primary}" {$fontAttributes} font-size="{$statusSize}">NOTICE</text>
  </g>

  <rect x="48" y="268" width="824" height="96" rx="{$cornerRadius}" fill="{$surfaceFill}" fill-opacity="{$opacity}" stroke="{$border}" stroke-width="{$borderWidth}"/>
  <text x="70" y="299" fill="{$primary}" {$fontAttributes} font-size="{$sectionSize}">Typography and form language</text>
  <text x="70" y="325" fill="{$secondary}" {$fontAttributes} font-size="{$subtitleSize}">The Assistant prepares global basics; detailed layout remains in IPSView Designer.</text>
  <circle cx="690" cy="316" r="12" fill="{$successFill}" fill-opacity="{$opacity}"/>
  <circle cx="735" cy="316" r="12" fill="{$warningFill}" fill-opacity="{$opacity}"/>
  <circle cx="780" cy="316" r="12" fill="{$errorFill}" fill-opacity="{$opacity}"/>
  <circle cx="825" cy="316" r="12" fill="{$inactiveFill}" fill-opacity="{$opacity}"/>
</svg>
SVG;
    }

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
     * @param array<string, mixed> $effects
     */
    private static function fill(string $id, string $color, array $effects): string
    {
        return IPSViewEffects::hasGeneratedGradient($effects)
            ? sprintf('url(#%s)', $id)
            : $color;
    }

    private static function fontSize(int $baseSize, float $scale): int
    {
        return max(9, min(30, (int) round($baseSize * $scale)));
    }

    /**
     * @param array<string, string> $palette
     */
    private static function color(array $palette, string $role): string
    {
        return IPSViewTheme::normalizeColor((string) ($palette[$role] ?? '#000000'));
    }
}

<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

final class IPSViewThemePreview
{
    /**
     * Creates a self-contained SVG data URI for the configuration form.
     *
     * @param array<string, string> $palette
     */
    public static function createDataUri(array $palette): string
    {
        $svg = self::createSvg($palette);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * @param array<string, string> $palette
     */
    public static function createSvg(array $palette): string
    {
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
        $shadow = IPSViewTheme::mix($view, '#000000', 0.72);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="920" height="420" viewBox="0 0 920 420">
  <defs>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="160%">
      <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="{$shadow}" flood-opacity="0.42"/>
    </filter>
  </defs>
  <rect width="920" height="420" rx="24" fill="{$view}"/>
  <rect x="22" y="22" width="876" height="376" rx="18" fill="{$page}" stroke="{$border}" stroke-width="2"/>
  <rect x="22" y="22" width="876" height="68" rx="18" fill="{$surface}"/>
  <rect x="22" y="72" width="876" height="18" fill="{$surface}"/>
  <circle cx="60" cy="56" r="17" fill="{$accent}"/>
  <path d="M52 56h16M60 48v16" stroke="{$primary}" stroke-width="3" stroke-linecap="round"/>
  <text x="90" y="52" fill="{$primary}" font-family="Arial, sans-serif" font-size="20" font-weight="700">Design preview</text>
  <text x="90" y="73" fill="{$secondary}" font-family="Arial, sans-serif" font-size="13">Semantic colors applied consistently</text>
  <text x="824" y="62" fill="{$primary}" font-family="Arial, sans-serif" font-size="18" font-weight="700">21.5 °C</text>

  <g filter="url(#shadow)">
    <rect x="48" y="118" width="250" height="116" rx="16" fill="{$surface}" stroke="{$border}"/>
    <text x="70" y="150" fill="{$secondary}" font-family="Arial, sans-serif" font-size="13">LIGHTS</text>
    <text x="70" y="181" fill="{$primary}" font-family="Arial, sans-serif" font-size="22" font-weight="700">Living room</text>
    <rect x="218" y="164" width="54" height="30" rx="15" fill="{$active}"/>
    <circle cx="257" cy="179" r="11" fill="{$primary}"/>
    <text x="70" y="213" fill="{$success}" font-family="Arial, sans-serif" font-size="13" font-weight="700">Active</text>
  </g>

  <g filter="url(#shadow)">
    <rect x="335" y="118" width="250" height="116" rx="16" fill="{$surface}" stroke="{$border}"/>
    <text x="357" y="150" fill="{$secondary}" font-family="Arial, sans-serif" font-size="13">SHUTTERS</text>
    <text x="357" y="181" fill="{$primary}" font-family="Arial, sans-serif" font-size="22" font-weight="700">65 %</text>
    <rect x="357" y="202" width="198" height="8" rx="4" fill="{$inactive}"/>
    <rect x="357" y="202" width="129" height="8" rx="4" fill="{$accent}"/>
    <circle cx="486" cy="206" r="11" fill="{$accent}" stroke="{$surface}" stroke-width="4"/>
  </g>

  <g filter="url(#shadow)">
    <rect x="622" y="118" width="250" height="116" rx="16" fill="{$surface}" stroke="{$border}"/>
    <text x="644" y="150" fill="{$secondary}" font-family="Arial, sans-serif" font-size="13">SECURITY</text>
    <text x="644" y="181" fill="{$primary}" font-family="Arial, sans-serif" font-size="22" font-weight="700">Home</text>
    <rect x="644" y="199" width="82" height="24" rx="12" fill="{$success}"/>
    <text x="685" y="216" text-anchor="middle" fill="{$primary}" font-family="Arial, sans-serif" font-size="12" font-weight="700">OK</text>
    <rect x="736" y="199" width="106" height="24" rx="12" fill="{$warning}"/>
    <text x="789" y="216" text-anchor="middle" fill="{$primary}" font-family="Arial, sans-serif" font-size="12" font-weight="700">NOTICE</text>
  </g>

  <rect x="48" y="268" width="824" height="96" rx="16" fill="{$surface}" stroke="{$border}"/>
  <text x="70" y="299" fill="{$primary}" font-family="Arial, sans-serif" font-size="18" font-weight="700">Status colors</text>
  <text x="70" y="325" fill="{$secondary}" font-family="Arial, sans-serif" font-size="13">The same roles are used for switches, sliders, dialogs, charts and calendars.</text>
  <circle cx="690" cy="316" r="12" fill="{$success}"/>
  <circle cx="735" cy="316" r="12" fill="{$warning}"/>
  <circle cx="780" cy="316" r="12" fill="{$error}"/>
  <circle cx="825" cy="316" r="12" fill="{$inactive}"/>
</svg>
SVG;
    }

    /**
     * @param array<string, string> $palette
     */
    private static function color(array $palette, string $role): string
    {
        return IPSViewTheme::normalizeColor((string) ($palette[$role] ?? '#000000'));
    }
}

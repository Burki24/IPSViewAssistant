<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/ConfigurationFormHelper.php';
require_once __DIR__ . '/../libs/IPSViewEffects.php';
require_once __DIR__ . '/../libs/IPSViewTheme.php';
require_once __DIR__ . '/../libs/IPSViewThemePreview.php';
require_once __DIR__ . '/../libs/IPSViewDocument.php';
require_once __DIR__ . '/../libs/IPSViewCopyFactory.php';
require_once __DIR__ . '/../libs/IPSViewFactory.php';
require_once __DIR__ . '/../libs/IPSViewShape.php';
require_once __DIR__ . '/../libs/IPSViewTypography.php';

use Burki24\IPSViewAssistant\IPSViewCopyFactory;
use Burki24\IPSViewAssistant\IPSViewEffects;
use Burki24\IPSViewAssistant\IPSViewFactory;
use Burki24\IPSViewAssistant\IPSViewShape;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;
use Burki24\IPSViewAssistant\IPSViewTypography;
use Burki24\SymconModuleHelper\ConfigurationFormHelper;

class IPSViewAssistant extends IPSModuleStrict
{
    use ConfigurationFormHelper;

    private const ATTRIBUTE_MANAGED_COPIES = 'ManagedCopies';

    /**
     * @var array<string, string>
     */
    private const FORM_COLOR_FIELDS = [
        IPSViewTheme::ROLE_VIEW_BACKGROUND => 'ViewBackgroundColor',
        IPSViewTheme::ROLE_PAGE_BACKGROUND => 'PageBackgroundColor',
        IPSViewTheme::ROLE_SURFACE         => 'SurfaceColor',
        IPSViewTheme::ROLE_PRIMARY_TEXT    => 'PrimaryTextColor',
        IPSViewTheme::ROLE_SECONDARY_TEXT  => 'SecondaryTextColor',
        IPSViewTheme::ROLE_BORDER          => 'BorderColor',
        IPSViewTheme::ROLE_ACCENT          => 'AccentColor',
        IPSViewTheme::ROLE_ACTIVE          => 'ActiveColor',
        IPSViewTheme::ROLE_INACTIVE        => 'InactiveColor',
        IPSViewTheme::ROLE_SUCCESS         => 'SuccessColor',
        IPSViewTheme::ROLE_WARNING         => 'WarningColor',
        IPSViewTheme::ROLE_ERROR           => 'ErrorColor',
    ];

    /**
     * Initializes the assistant instance.
     */
    public function Create(): void
    {
        parent::Create();

        $this->RegisterAttributeString(self::ATTRIBUTE_MANAGED_COPIES, '[]');
    }

    /**
     * Applies the instance configuration.
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->SetStatus(IS_ACTIVE);
    }

    /**
     * Builds the dynamic assistant form from the shared configuration form helper.
     */
    public function GetConfigurationForm(): string
    {
        $form = $this->LoadConfigurationForm();
        $palette = IPSViewTheme::preset(IPSViewTheme::THEME_STANDARD);

        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->setConfigurationFormField(
                $form,
                $field,
                'value',
                IPSViewTheme::toFormColor($palette[$role])
            );
        }

        $this->setConfigurationFormField(
            $form,
            'ThemePreview',
            'image',
            IPSViewThemePreview::createDataUri($palette, IPSViewEffects::resolve(), [])
        );

        return $this->EncodeConfigurationForm($form);
    }

    /**
     * Creates a ready-initialized IPSView media object from the assistant form.
     */
    public function CreateView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        string $MainPageName,
        int $Theme = IPSViewTheme::THEME_STANDARD,
        string $ThemePalette = '',
        string $Effects = '',
        string $Appearance = ''
    ): string {
        try {
            $factory = new IPSViewFactory(__DIR__ . '/../libs/templates');
            $mediaID = $factory->create(
                $ViewName,
                $TargetCategoryID,
                $AspectRatio,
                $Orientation,
                $Template,
                $MainPageName,
                $Theme,
                $this->decodePalette($ThemePalette),
                $this->decodeEffects($Effects),
                $this->decodeAppearance($Appearance)
            );

            return sprintf(
                $this->Translate('The IPSView "%s" was created successfully with object ID %d.'),
                trim($ViewName),
                $mediaID
            );
        } catch (Throwable $exception) {
            $this->SendDebug('CreateView', $exception->getMessage(), 0);

            return sprintf(
                $this->Translate('The IPSView could not be created: %s'),
                $exception->getMessage()
            );
        }
    }

    /**
     * Loads the design of an existing IPSView into the semantic color fields.
     */
    public function LoadExistingView(int $SourceViewID, string $Effects = '', string $Appearance = ''): void
    {
        try {
            $factory = new IPSViewCopyFactory();
            $sourceInspection = $factory->inspect($SourceViewID);
            $copyName = trim($sourceInspection['name']) . ' - ' . $this->Translate('Design copy');
            $copyTargetCategoryID = $sourceInspection['parentID'];
            $copyMediaID = $this->findPreferredManagedCopy($SourceViewID);

            if ($copyMediaID === null) {
                $copyMediaID = $factory->findExistingTarget($copyName, $copyTargetCategoryID);

                if ($copyMediaID === $SourceViewID) {
                    $copyMediaID = null;
                }

                if ($copyMediaID !== null) {
                    $this->rememberManagedCopy($SourceViewID, $copyMediaID);
                }
            }

            $paletteInspection = $sourceInspection;
            $status = sprintf(
                $this->Translate('Loaded "%s": %d pages and %d controls. The original remains unchanged.'),
                $sourceInspection['name'],
                $sourceInspection['pageCount'],
                $sourceInspection['controlCount']
            );

            if ($copyMediaID !== null) {
                $copyInspection = $factory->inspect($copyMediaID);
                $copyName = $copyInspection['name'];
                $copyTargetCategoryID = $copyInspection['parentID'];
                $paletteInspection = $copyInspection;
                $status .= ' ' . sprintf(
                    $this->Translate('The existing design copy "%s" (ID %d) will be updated on the next save.'),
                    $copyName,
                    $copyMediaID
                );
            }

            $analysis = $paletteInspection['designAnalysis'];
            $status .= ' ' . sprintf(
                $this->Translate('%d global design values, %d matching control colors and %d individual or special colors were found.'),
                $analysis['globalColors'],
                $analysis['matchingControlColors'],
                $analysis['individualControlColors']
            );

            $appearanceInspection = $paletteInspection['appearance'];
            $appearance = $this->decodeAppearance($Appearance);
            $appearance['typographyStyle'] = IPSViewTypography::STYLE_PRESERVE;
            $appearance['fontFamilyMode'] = IPSViewTypography::FONT_PRESERVE;
            $appearance['customFontFamily'] = $appearanceInspection['fontFamily'];
            $appearance['customFontSize'] = $appearanceInspection['baseFontSize'];
            $appearance['cornerStyle'] = IPSViewShape::CORNER_PRESERVE;
            $appearance['customCornerRadius'] = $appearanceInspection['cornerRadius'];
            $appearance['borderStyle'] = IPSViewShape::BORDER_PRESERVE;
            $appearance['customBorderWidth'] = $appearanceInspection['borderWidth'];
            $status .= ' ' . sprintf(
                $this->Translate('Current basics: font %s at %d px, corner radius %d px and border width %.1f px.'),
                $appearanceInspection['fontFamily'] === ''
                    ? $this->Translate('system default')
                    : $appearanceInspection['fontFamily'],
                $appearanceInspection['baseFontSize'],
                $appearanceInspection['cornerRadius'],
                $appearanceInspection['borderWidth']
            );

            $palette = $paletteInspection['palette'];
            $this->UpdateFormField('Theme', 'value', IPSViewTheme::THEME_CUSTOM);
            $this->UpdateFormField('TypographyStyle', 'value', IPSViewTypography::STYLE_PRESERVE);
            $this->UpdateFormField('FontFamilyMode', 'value', IPSViewTypography::FONT_PRESERVE);
            $this->UpdateFormField('CustomFontFamily', 'value', $appearanceInspection['fontFamily']);
            $this->UpdateFormField('CustomFontSize', 'value', $appearanceInspection['baseFontSize']);
            $this->UpdateFormField('CornerStyle', 'value', IPSViewShape::CORNER_PRESERVE);
            $this->UpdateFormField('CustomCornerRadius', 'value', $appearanceInspection['cornerRadius']);
            $this->UpdateFormField('BorderStyle', 'value', IPSViewShape::BORDER_PRESERVE);
            $this->UpdateFormField('CustomBorderWidth', 'value', $appearanceInspection['borderWidth']);
            $this->updateColorFields($palette);
            $this->UpdateFormField('ThemePreview', 'image', IPSViewThemePreview::createDataUri($palette, $this->decodeEffects($Effects), $appearance));
            $this->UpdateFormField('CopyViewName', 'value', $copyName);
            $this->UpdateFormField('CopyTargetCategoryID', 'value', $copyTargetCategoryID);
            $this->UpdateFormField('ExistingViewStatus', 'caption', $status);
        } catch (Throwable $exception) {
            $this->SendDebug('LoadExistingView', $exception->getMessage(), 0);
            $this->UpdateFormField(
                'ExistingViewStatus',
                'caption',
                sprintf($this->Translate('The existing IPSView could not be loaded: %s'), $exception->getMessage())
            );
        }
    }

    /**
     * Creates a styled copy of an existing IPSView without changing the source.
     */
    public function CreateStyledCopy(
        int $SourceViewID,
        string $CopyViewName,
        int $CopyTargetCategoryID,
        int $Theme,
        string $ThemePalette = '',
        int $DesignScope = IPSViewTheme::SCOPE_MATCHING_CONTROLS,
        string $Effects = '',
        string $Appearance = ''
    ): string {
        try {
            $factory = new IPSViewCopyFactory();
            $copyName = trim($CopyViewName);
            $targetMediaID = $this->findManagedCopy(
                $SourceViewID,
                $copyName,
                $CopyTargetCategoryID
            );

            if ($targetMediaID === null) {
                $targetMediaID = $factory->findExistingTarget($copyName, $CopyTargetCategoryID);
            }

            if ($targetMediaID !== null) {
                if ($targetMediaID === $SourceViewID) {
                    throw new RuntimeException(
                        $this->Translate('The source IPSView cannot be used as its own design copy.')
                    );
                }

                $factory->update(
                    $targetMediaID,
                    $Theme,
                    $this->decodePalette($ThemePalette),
                    $DesignScope,
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance)
                );
                $this->rememberManagedCopy($SourceViewID, $targetMediaID);
                $reportText = $this->formatThemeReport($factory->getLastThemeReport());
                $this->UpdateFormField(
                    'ExistingViewStatus',
                    'caption',
                    sprintf(
                        $this->Translate('The design copy "%s" (ID %d) was updated. The original remains unchanged.'),
                        $copyName,
                        $targetMediaID
                    ) . $reportText
                );

                return sprintf(
                    $this->Translate('The styled IPSView copy "%s" was updated successfully with object ID %d.'),
                    $copyName,
                    $targetMediaID
                ) . $reportText;
            }

            $mediaID = $factory->create(
                $SourceViewID,
                $copyName,
                $CopyTargetCategoryID,
                $Theme,
                $this->decodePalette($ThemePalette),
                $DesignScope,
                $this->decodeEffects($Effects),
                $this->decodeAppearance($Appearance)
            );
            $this->rememberManagedCopy($SourceViewID, $mediaID);
            $reportText = $this->formatThemeReport($factory->getLastThemeReport());
            $this->UpdateFormField(
                'ExistingViewStatus',
                'caption',
                sprintf(
                    $this->Translate('The design copy "%s" (ID %d) was created and will be updated on future saves.'),
                    $copyName,
                    $mediaID
                ) . $reportText
            );

            return sprintf(
                $this->Translate('The styled IPSView copy "%s" was created successfully with object ID %d.'),
                $copyName,
                $mediaID
            ) . $reportText;
        } catch (Throwable $exception) {
            $this->SendDebug('CreateStyledCopy', $exception->getMessage(), 0);

            return sprintf(
                $this->Translate('The styled IPSView copy could not be saved: %s'),
                $exception->getMessage()
            );
        }
    }

    /**
     * Loads one preset into the semantic color fields and refreshes the preview.
     */
    public function ApplyThemePreset(
        int $Theme,
        string $ThemePalette = '',
        string $Effects = '',
        string $Appearance = ''
    ): void {
        try {
            $palette = IPSViewTheme::resolvePalette($Theme, $this->decodePalette($ThemePalette));
            $this->UpdateFormField('Theme', 'value', $Theme);
            $this->updateColorFields($palette);
            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance)
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('ApplyThemePreset', $exception->getMessage(), 0);
        }
    }

    /**
     * Switches to a custom theme and refreshes the live preview.
     */
    public function UpdateThemePreview(
        string $ThemePalette,
        string $Effects = '',
        string $Appearance = ''
    ): void {
        try {
            $palette = IPSViewTheme::resolvePalette(
                IPSViewTheme::THEME_CUSTOM,
                $this->decodePalette($ThemePalette)
            );

            $this->UpdateFormField('Theme', 'value', IPSViewTheme::THEME_CUSTOM);
            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance)
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateThemePreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Refreshes the preview after changing general visual effects.
     */
    public function UpdateEffectsPreview(
        string $ThemePalette,
        string $Effects = '',
        string $Appearance = ''
    ): void {
        try {
            $palette = IPSViewTheme::resolvePalette(
                IPSViewTheme::THEME_CUSTOM,
                $this->decodePalette($ThemePalette)
            );

            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance)
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateEffectsPreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Refreshes the preview after changing typography or form language.
     */
    public function UpdateAppearancePreview(
        string $ThemePalette,
        string $Effects = '',
        string $Appearance = ''
    ): void {
        try {
            $palette = IPSViewTheme::resolvePalette(
                IPSViewTheme::THEME_CUSTOM,
                $this->decodePalette($ThemePalette)
            );

            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance)
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateAppearancePreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Updates one named field in the nested configuration form definition.
     *
     * @param array<string, mixed> $form
     */
    private function setConfigurationFormField(
        array &$form,
        string $name,
        string $property,
        mixed $value
    ): void {
        $actions = &$form['actions'];

        if (!is_array($actions) || !$this->setConfigurationFormFieldInItems($actions, $name, $property, $value)) {
            throw new RuntimeException(sprintf('Configuration form field "%s" was not found.', $name));
        }
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function setConfigurationFormFieldInItems(
        array &$items,
        string $name,
        string $property,
        mixed $value
    ): bool {
        foreach ($items as &$item) {
            if (($item['name'] ?? '') === $name) {
                $item[$property] = $value;

                return true;
            }

            if (isset($item['items']) && is_array($item['items'])) {
                if ($this->setConfigurationFormFieldInItems($item['items'], $name, $property, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findManagedCopy(
        int $sourceMediaID,
        string $copyName,
        int $targetCategoryID
    ): ?int {
        foreach (array_reverse($this->readManagedCopies()) as $managedCopy) {
            if ($managedCopy['sourceMediaID'] !== $sourceMediaID) {
                continue;
            }

            $targetMediaID = $managedCopy['targetMediaID'];
            $object = IPS_GetObject($targetMediaID);

            if (
                (string) ($object['ObjectName'] ?? '') === $copyName
                && (int) ($object['ParentID'] ?? -1) === $targetCategoryID
            ) {
                return $targetMediaID;
            }
        }

        return null;
    }

    private function findPreferredManagedCopy(int $sourceMediaID): ?int
    {
        foreach (array_reverse($this->readManagedCopies()) as $managedCopy) {
            if ($managedCopy['sourceMediaID'] === $sourceMediaID) {
                return $managedCopy['targetMediaID'];
            }
        }

        return null;
    }

    private function rememberManagedCopy(int $sourceMediaID, int $targetMediaID): void
    {
        $managedCopies = array_values(
            array_filter(
                $this->readManagedCopies(),
                static fn (array $managedCopy): bool => $managedCopy['targetMediaID'] !== $targetMediaID
            )
        );
        $managedCopies[] = [
            'sourceMediaID' => $sourceMediaID,
            'targetMediaID' => $targetMediaID,
        ];

        $this->WriteAttributeString(
            self::ATTRIBUTE_MANAGED_COPIES,
            json_encode($managedCopies, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return list<array{sourceMediaID: int, targetMediaID: int}>
     */
    private function readManagedCopies(): array
    {
        try {
            $decoded = json_decode(
                $this->ReadAttributeString(self::ATTRIBUTE_MANAGED_COPIES),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            $decoded = [];
        }

        if (!is_array($decoded)) {
            $decoded = [];
        }

        $managedCopies = [];

        foreach ($decoded as $managedCopy) {
            if (!is_array($managedCopy)) {
                continue;
            }

            $sourceMediaID = (int) ($managedCopy['sourceMediaID'] ?? 0);
            $targetMediaID = (int) ($managedCopy['targetMediaID'] ?? 0);

            if ($sourceMediaID < 1 || $targetMediaID < 1 || !IPS_MediaExists($targetMediaID)) {
                continue;
            }

            $media = IPS_GetMedia($targetMediaID);
            if ((int) ($media['MediaType'] ?? -1) !== 0) {
                continue;
            }

            $managedCopies[] = [
                'sourceMediaID' => $sourceMediaID,
                'targetMediaID' => $targetMediaID,
            ];
        }

        return $managedCopies;
    }

    /**
     * @param array{
     *     palette: array<string, string>,
     *     scope: int,
     *     globalColorsApplied: int,
     *     controlColorsApplied: int,
     *     controlColorsPreserved: int,
     *     globalEffectsApplied: int,
     *     controlEffectsApplied: int,
     *     shadowChanged: bool,
     *     globalTypographyApplied: int,
     *     controlTypographyApplied: int,
     *     globalShapeApplied: int
     * }|null $report
     */
    private function formatThemeReport(?array $report): string
    {
        if ($report === null) {
            return '';
        }

        $reportText = ' ' . sprintf(
            $this->Translate('%d global design values and %d control colors were applied; %d individual or special colors were preserved.'),
            $report['globalColorsApplied'],
            $report['controlColorsApplied'],
            $report['controlColorsPreserved']
        );

        if (
            $report['globalEffectsApplied'] > 0
            || $report['controlEffectsApplied'] > 0
            || $report['shadowChanged']
        ) {
            $reportText .= ' ' . sprintf(
                $this->Translate('%d global fills and %d control backgrounds received the selected effects; shadow settings were %s.'),
                $report['globalEffectsApplied'],
                $report['controlEffectsApplied'],
                $report['shadowChanged']
                    ? $this->Translate('updated')
                    : $this->Translate('preserved')
            );
        }

        if (
            $report['globalTypographyApplied'] > 0
            || $report['controlTypographyApplied'] > 0
            || $report['globalShapeApplied'] > 0
        ) {
            $reportText .= ' ' . sprintf(
                $this->Translate('%d global typography settings and %d control fonts were updated; %d form-language settings were applied.'),
                $report['globalTypographyApplied'],
                $report['controlTypographyApplied'],
                $report['globalShapeApplied']
            );
        }

        return $reportText;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePalette(string $paletteJson): array
    {
        if (trim($paletteJson) === '') {
            return [];
        }

        $palette = json_decode($paletteJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($palette)) {
            throw new RuntimeException('The theme palette must be a JSON object.');
        }

        return $palette;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeEffects(string $effectsJson): array
    {
        if (trim($effectsJson) === '') {
            return IPSViewEffects::resolve();
        }

        $effects = json_decode($effectsJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($effects)) {
            throw new RuntimeException('The design effects must be a JSON object.');
        }

        return IPSViewEffects::resolve($effects);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAppearance(string $appearanceJson): array
    {
        if (trim($appearanceJson) === '') {
            return [...IPSViewTypography::resolve(), ...IPSViewShape::resolve()];
        }

        $appearance = json_decode($appearanceJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($appearance)) {
            throw new RuntimeException('The typography and form settings must be a JSON object.');
        }

        return [
            ...IPSViewTypography::resolve($appearance),
            ...IPSViewShape::resolve($appearance),
        ];
    }

    /**
     * @param array<string, string> $palette
     */
    private function updateColorFields(array $palette): void
    {
        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->UpdateFormField($field, 'value', IPSViewTheme::toFormColor($palette[$role]));
        }
    }
}

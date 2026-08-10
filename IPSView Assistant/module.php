<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/ConfigurationFormHelper.php';
require_once __DIR__ . '/../libs/IPSViewBackground.php';
require_once __DIR__ . '/../libs/IPSViewDesignerHandover.php';
require_once __DIR__ . '/../libs/IPSViewEffects.php';
require_once __DIR__ . '/../libs/IPSViewTheme.php';
require_once __DIR__ . '/../libs/IPSViewThemePreview.php';
require_once __DIR__ . '/../libs/IPSViewDocument.php';
require_once __DIR__ . '/../libs/IPSViewCopyFactory.php';
require_once __DIR__ . '/../libs/IPSViewStartCheck.php';
require_once __DIR__ . '/../libs/IPSViewFactory.php';
require_once __DIR__ . '/../libs/IPSViewShape.php';
require_once __DIR__ . '/../libs/IPSViewTypography.php';
require_once __DIR__ . '/../libs/IPSViewUsageProfile.php';

use Burki24\IPSViewAssistant\IPSViewBackground;
use Burki24\IPSViewAssistant\IPSViewCopyFactory;
use Burki24\IPSViewAssistant\IPSViewDesignerHandover;
use Burki24\IPSViewAssistant\IPSViewDocument;
use Burki24\IPSViewAssistant\IPSViewEffects;
use Burki24\IPSViewAssistant\IPSViewFactory;
use Burki24\IPSViewAssistant\IPSViewShape;
use Burki24\IPSViewAssistant\IPSViewStartCheck;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;
use Burki24\IPSViewAssistant\IPSViewTypography;
use Burki24\IPSViewAssistant\IPSViewUsageProfile;
use Burki24\SymconModuleHelper\ConfigurationFormHelper;

class IPSViewAssistant extends IPSModuleStrict
{
    use ConfigurationFormHelper;

    private const ASSISTANT_MODE_QUICK_START = 0;
    private const ASSISTANT_MODE_ADVANCED = 1;

    private const ATTRIBUTE_ASSISTANT_MODE = 'AssistantMode';
    private const ATTRIBUTE_MANAGED_COPIES = 'ManagedCopies';
    private const ATTRIBUTE_BACKGROUND_IMAGE = 'BackgroundImage';
    private const ATTRIBUTE_BACKGROUND_MODE = 'BackgroundMode';
    private const ATTRIBUTE_BACKGROUND_LAYOUT = 'BackgroundLayout';
    private const ATTRIBUTE_BACKGROUND_SCOPE = 'BackgroundScope';
    private const ATTRIBUTE_PREVIEW_START_GRID = 'PreviewStartGrid';
    private const ATTRIBUTE_LAST_CREATED_VIEW_ID = 'LastCreatedViewID';
    private const ATTRIBUTE_DESIGNER_OBJECT_ID = 'DesignerObjectID';

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

        $this->RegisterAttributeInteger(self::ATTRIBUTE_ASSISTANT_MODE, self::ASSISTANT_MODE_QUICK_START);
        $this->RegisterAttributeString(self::ATTRIBUTE_MANAGED_COPIES, '[]');
        $this->RegisterAttributeString(self::ATTRIBUTE_BACKGROUND_IMAGE, '');
        $this->RegisterAttributeInteger(self::ATTRIBUTE_BACKGROUND_MODE, IPSViewBackground::MODE_PRESERVE);
        $this->RegisterAttributeString(self::ATTRIBUTE_BACKGROUND_LAYOUT, IPSViewBackground::LAYOUT_STRETCH);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE, IPSViewBackground::SCOPE_MAIN_PAGE);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID, IPSViewDocument::START_GRID_NONE);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_LAST_CREATED_VIEW_ID, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_DESIGNER_OBJECT_ID, 1);
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
        $startGrid = $this->previewStartGrid();
        $background = $this->backgroundSettings();
        $preview = IPSViewThemePreview::createDataUri(
            $palette,
            IPSViewEffects::resolve(),
            [],
            $background,
            $startGrid
        );

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
            $preview
        );
        $this->setConfigurationFormField($form, 'QuickStartPreview', 'image', $preview);
        $this->setConfigurationFormField($form, 'StartGrid', 'value', $startGrid);
        $this->setConfigurationFormField($form, 'QuickStartGrid', 'value', $startGrid);
        $this->setConfigurationFormField($form, 'BackgroundImageMode', 'value', $background['mode']);
        $this->setConfigurationFormField($form, 'BackgroundImageLayout', 'value', $background['layout']);
        $this->setConfigurationFormField($form, 'BackgroundImageScope', 'value', $background['scope']);
        $this->setConfigurationFormField($form, 'QuickStartBackgroundMode', 'value', $background['mode']);
        $this->setConfigurationFormField($form, 'QuickStartBackgroundLayout', 'value', $background['layout']);
        $this->setConfigurationFormField($form, 'QuickStartBackgroundScope', 'value', $background['scope']);
        $this->setConfigurationFormField(
            $form,
            'BackgroundImageFile',
            'visible',
            $background['mode'] === IPSViewBackground::MODE_FILE
        );
        $this->setConfigurationFormField(
            $form,
            'BackgroundImageLayout',
            'visible',
            $background['mode'] === IPSViewBackground::MODE_FILE
        );
        $this->setConfigurationFormField(
            $form,
            'BackgroundImageScope',
            'visible',
            $background['mode'] !== IPSViewBackground::MODE_PRESERVE
        );
        $this->setConfigurationFormField(
            $form,
            'QuickStartBackgroundFile',
            'visible',
            $background['mode'] === IPSViewBackground::MODE_FILE
        );
        $this->setConfigurationFormField(
            $form,
            'QuickStartBackgroundLayout',
            'visible',
            $background['mode'] === IPSViewBackground::MODE_FILE
        );
        $this->setConfigurationFormField(
            $form,
            'QuickStartBackgroundScope',
            'visible',
            $background['mode'] !== IPSViewBackground::MODE_PRESERVE
        );
        $assistantMode = $this->normalizeAssistantMode(
            $this->ReadAttributeInteger(self::ATTRIBUTE_ASSISTANT_MODE)
        );
        $this->setConfigurationFormField($form, 'AssistantMode', 'value', $assistantMode);
        $this->applyAssistantModeToForm($form, $assistantMode);
        $this->applyDesignerHandoverToForm($form);
        $startCheck = $this->startCheck(
            'Neue IPSView',
            0,
            'Hauptseite',
            IPSViewDocument::ASPECT_RATIO_16_9,
            IPSViewDocument::ORIENTATION_LANDSCAPE,
            IPSViewFactory::TEMPLATE_EMPTY,
            $startGrid
        );
        $this->applyStartCheckToForm($form, $startCheck);
        $this->applyQuickStartCheckToForm($form, $startCheck);

        return $this->EncodeConfigurationForm($form);
    }

    /**
     * Switches between the reduced quick-start form and all advanced functions.
     */
    public function UpdateAssistantMode(int $Mode): void
    {
        $mode = $this->normalizeAssistantMode($Mode);
        $advanced = $mode === self::ASSISTANT_MODE_ADVANCED;
        $this->WriteAttributeInteger(self::ATTRIBUTE_ASSISTANT_MODE, $mode);

        foreach ($this->advancedModeFields() as $field) {
            $this->UpdateFormField($field, 'visible', $advanced);
        }

        foreach ($this->quickStartModeFields() as $field) {
            $this->UpdateFormField($field, 'visible', !$advanced);
        }

        $this->UpdateFormField('AssistantMode', 'value', $mode);
        $this->UpdateFormField('AssistantModeInfo', 'caption', $this->assistantModeInfo($mode));
        $this->UpdateFormField('ThemeDescription', 'caption', $this->themeDescription($mode));
    }

    /**
     * Applies one ready-made device profile to the basic View settings.
     */
    public function UpdateUsageProfile(int $Profile): void
    {
        $profile = $this->normalizeUsageProfile($Profile);
        $this->UpdateFormField('UsageProfile', 'value', $profile);
        $this->UpdateFormField('UsageProfileInfo', 'caption', $this->usageProfileInfo($profile));

        if ($profile === IPSViewUsageProfile::PROFILE_CUSTOM) {
            return;
        }

        $settings = IPSViewUsageProfile::resolve($profile);
        $this->UpdateFormField('AspectRatio', 'value', $settings['aspectRatio']);
        $this->UpdateFormField('Orientation', 'value', $settings['orientation']);
        $this->UpdateFormField('FullScreen', 'value', $settings['fullScreen']);
    }

    /**
     * Marks manually adjusted View settings as a custom usage profile.
     */
    public function MarkUsageProfileCustom(): void
    {
        $profile = IPSViewUsageProfile::PROFILE_CUSTOM;
        $this->UpdateFormField('UsageProfile', 'value', $profile);
        $this->UpdateFormField('UsageProfileInfo', 'caption', $this->usageProfileInfo($profile));
    }

    /**
     * Applies one ready-made device profile inside the quick-start wizard.
     */
    public function UpdateQuickStartUsageProfile(int $Profile): void
    {
        $profile = $this->normalizeUsageProfile($Profile);
        $this->UpdateFormField('QuickStartUsageProfile', 'value', $profile);
        $this->UpdateFormField('QuickStartUsageProfileInfo', 'caption', $this->usageProfileInfo($profile));

        if ($profile === IPSViewUsageProfile::PROFILE_CUSTOM) {
            return;
        }

        $settings = IPSViewUsageProfile::resolve($profile);
        $this->UpdateFormField('QuickStartAspectRatio', 'value', $settings['aspectRatio']);
        $this->UpdateFormField('QuickStartOrientation', 'value', $settings['orientation']);
        $this->UpdateFormField('QuickStartFullScreen', 'value', $settings['fullScreen']);
    }

    /**
     * Marks manually adjusted quick-start dimensions as a custom usage profile.
     */
    public function MarkQuickStartUsageProfileCustom(): void
    {
        $profile = IPSViewUsageProfile::PROFILE_CUSTOM;
        $this->UpdateFormField('QuickStartUsageProfile', 'value', $profile);
        $this->UpdateFormField('QuickStartUsageProfileInfo', 'caption', $this->usageProfileInfo($profile));
    }

    /**
     * Invalidates a previous overwrite decision after changing its identity fields.
     */
    public function ResetQuickStartOverwrite(): void
    {
        $this->UpdateFormField('QuickStartOverwriteExistingView', 'visible', false);
        $this->UpdateFormField('QuickStartOverwriteExistingView', 'value', false);
        $this->UpdateFormField('QuickStartOverwriteExistingViewInfo', 'visible', false);
        $this->UpdateFormField(
            'QuickStartCheckStatus',
            'caption',
            $this->Translate('The start check is being prepared.')
        );
    }

    /**
     * Updates the recommendation for the first object to place in IPSView Designer.
     */
    public function UpdateDesignerHandover(int $ObjectID): void
    {
        $objectID = $ObjectID > 1 && IPS_ObjectExists($ObjectID) ? $ObjectID : 1;
        $this->WriteAttributeInteger(self::ATTRIBUTE_DESIGNER_OBJECT_ID, $objectID);
        $this->UpdateFormField('DesignerObjectID', 'value', $objectID);
        $this->UpdateFormField('DesignerObjectHint', 'caption', $this->designerObjectHint($objectID));
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
        string $Appearance = '',
        bool $FullScreen = false,
        int $StartGrid = IPSViewDocument::START_GRID_NONE
    ): string {
        return $this->CreateOrOverwriteView(
            $ViewName,
            $TargetCategoryID,
            $AspectRatio,
            $Orientation,
            $Template,
            $MainPageName,
            $Theme,
            $ThemePalette,
            $Effects,
            $Appearance,
            $FullScreen,
            $StartGrid,
            false
        );
    }

    /**
     * Creates a new IPSView or explicitly overwrites one unambiguous same-name IPSView.
     */
    public function CreateOrOverwriteView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        string $MainPageName,
        int $Theme,
        string $ThemePalette,
        string $Effects,
        string $Appearance,
        bool $FullScreen,
        int $StartGrid,
        bool $OverwriteExistingView
    ): string {
        try {
            $startCheck = $this->startCheck(
                $ViewName,
                $TargetCategoryID,
                $MainPageName,
                $AspectRatio,
                $Orientation,
                $Template,
                $StartGrid,
                $OverwriteExistingView
            );
            $this->showStartCheck($startCheck, $OverwriteExistingView);
            IPSViewStartCheck::assertReady($startCheck);
            $wasOverwritten = $OverwriteExistingView && $startCheck['overwriteAvailable'];

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
                $this->decodeAppearance($Appearance),
                $this->backgroundSettings(),
                $FullScreen,
                $StartGrid,
                $OverwriteExistingView
            );
            $this->WriteAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID, $StartGrid);
            $this->WriteAttributeInteger(self::ATTRIBUTE_LAST_CREATED_VIEW_ID, $mediaID);
            $this->WriteAttributeInteger(self::ATTRIBUTE_DESIGNER_OBJECT_ID, 1);
            $this->showDesignerHandover($mediaID);

            return $wasOverwritten
                ? sprintf(
                    $this->Translate('The IPSView "%s" was overwritten successfully while retaining object ID %d.'),
                    trim($ViewName),
                    $mediaID
                )
                : sprintf(
                    $this->Translate('The IPSView "%s" was created successfully with object ID %d.'),
                    trim($ViewName),
                    $mediaID
                );
        } catch (Throwable $exception) {
            $this->SendDebug('CreateOrOverwriteView', $exception->getMessage(), 0);

            return sprintf(
                $this->Translate('The IPSView could not be created: %s'),
                $this->Translate($exception->getMessage())
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
            $appearance['fontBoldMode'] = IPSViewTypography::FORMAT_PRESERVE;
            $appearance['fontItalicMode'] = IPSViewTypography::FORMAT_PRESERVE;
            $appearance['fontUnderlineMode'] = IPSViewTypography::FORMAT_PRESERVE;
            $appearance['cornerStyle'] = IPSViewShape::CORNER_PRESERVE;
            $appearance['customCornerRadius'] = $appearanceInspection['cornerRadius'];
            $appearance['borderStyle'] = IPSViewShape::BORDER_PRESERVE;
            $appearance['customBorderWidth'] = $appearanceInspection['borderWidth'];
            $status .= ' ' . sprintf(
                $this->Translate('Current basics: font %s at %d px, corner radius %d px and border width %.1f px.'),
                $appearanceInspection['fontFamily'] === ''
                    ? $this->Translate('IPSView default (Roboto)')
                    : $appearanceInspection['fontFamily'],
                $appearanceInspection['baseFontSize'],
                $appearanceInspection['cornerRadius'],
                $appearanceInspection['borderWidth']
            );

            $palette = $paletteInspection['palette'];
            $background = $paletteInspection['background'];
            $this->WriteAttributeInteger(self::ATTRIBUTE_BACKGROUND_MODE, IPSViewBackground::MODE_PRESERVE);
            $this->WriteAttributeString(self::ATTRIBUTE_BACKGROUND_LAYOUT, $background['layout']);
            $this->WriteAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE, IPSViewBackground::SCOPE_MAIN_PAGE);
            $this->WriteAttributeString(self::ATTRIBUTE_BACKGROUND_IMAGE, $background['imageData']);
            $this->UpdateFormField('Theme', 'value', IPSViewTheme::THEME_CUSTOM);
            $this->UpdateFormField('TypographyStyle', 'value', IPSViewTypography::STYLE_PRESERVE);
            $this->UpdateFormField('FontFamilyMode', 'value', IPSViewTypography::FONT_PRESERVE);
            $this->UpdateFormField('CustomFontFamily', 'value', $appearanceInspection['fontFamily']);
            $this->UpdateFormField('CustomFontSize', 'value', $appearanceInspection['baseFontSize']);
            $this->updateFontStyleFields($appearance);
            $this->UpdateFormField('CornerStyle', 'value', IPSViewShape::CORNER_PRESERVE);
            $this->UpdateFormField('CustomCornerRadius', 'value', $appearanceInspection['cornerRadius']);
            $this->UpdateFormField('BorderStyle', 'value', IPSViewShape::BORDER_PRESERVE);
            $this->UpdateFormField('CustomBorderWidth', 'value', $appearanceInspection['borderWidth']);
            $this->updateColorFields($palette);
            $this->UpdateFormField('BackgroundImageMode', 'value', IPSViewBackground::MODE_PRESERVE);
            $this->UpdateFormField('BackgroundImageFile', 'visible', false);
            $this->UpdateFormField('BackgroundImageLayout', 'value', $background['layout']);
            $this->UpdateFormField('BackgroundImageLayout', 'visible', false);
            $this->UpdateFormField('BackgroundImageScope', 'value', IPSViewBackground::SCOPE_MAIN_PAGE);
            $this->UpdateFormField('BackgroundImageScope', 'visible', false);
            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $appearance,
                    $this->backgroundSettings(),
                    $this->previewStartGrid()
                )
            );
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
                    $this->decodeAppearance($Appearance),
                    $this->backgroundSettings()
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
                $this->decodeAppearance($Appearance),
                $this->backgroundSettings()
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
                    $this->decodeAppearance($Appearance),
                    $this->backgroundSettings(),
                    $this->previewStartGrid()
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
                    $this->decodeAppearance($Appearance),
                    $this->backgroundSettings(),
                    $this->previewStartGrid()
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
                    $this->decodeAppearance($Appearance),
                    $this->backgroundSettings(),
                    $this->previewStartGrid()
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
            $appearance = $this->decodeAppearance($Appearance);
            $this->updateFontStyleFields($appearance);

            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    $palette,
                    $this->decodeEffects($Effects),
                    $appearance,
                    $this->backgroundSettings(),
                    $this->previewStartGrid()
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateAppearancePreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Changes the sample-card arrangement to match the selected start grid.
     */
    public function UpdateStartGridPreview(
        int $StartGrid,
        string $ThemePalette = '',
        string $Effects = '',
        string $Appearance = ''
    ): void {
        try {
            $startGrid = $this->normalizeStartGrid($StartGrid);
            $palette = IPSViewTheme::resolvePalette(
                IPSViewTheme::THEME_CUSTOM,
                $this->decodePalette($ThemePalette)
            );
            $preview = IPSViewThemePreview::createDataUri(
                $palette,
                $this->decodeEffects($Effects),
                $this->decodeAppearance($Appearance),
                $this->backgroundSettings(),
                $startGrid
            );

            $this->WriteAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID, $startGrid);
            $this->UpdateFormField('ThemePreview', 'image', $preview);
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateStartGridPreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Refreshes the design preview inside the quick-start wizard.
     */
    public function UpdateQuickStartPreview(int $Theme, int $StartGrid): void
    {
        try {
            $theme = $this->normalizeQuickStartTheme($Theme);
            $startGrid = $this->normalizeStartGrid($StartGrid);
            $this->WriteAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID, $startGrid);
            $this->UpdateFormField('QuickStartTheme', 'value', $theme);
            $this->UpdateFormField('QuickStartGrid', 'value', $startGrid);
            $this->UpdateFormField(
                'QuickStartPreview',
                'image',
                $this->createQuickStartPreview($theme, $startGrid, $this->backgroundSettings())
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateQuickStartPreview', $exception->getMessage(), 0);
        }
    }

    /**
     * Rechecks whether the current form values are ready for creating a View.
     */
    public function UpdateStartCheck(
        string $ViewName,
        int $TargetCategoryID,
        string $MainPageName,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        int $StartGrid
    ): void {
        $this->UpdateStartCheckWithOverwrite(
            $ViewName,
            $TargetCategoryID,
            $MainPageName,
            $AspectRatio,
            $Orientation,
            $Template,
            $StartGrid,
            false
        );
    }

    /**
     * Rechecks the form and includes the user's explicit overwrite decision.
     */
    public function UpdateStartCheckWithOverwrite(
        string $ViewName,
        int $TargetCategoryID,
        string $MainPageName,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        int $StartGrid,
        bool $OverwriteExistingView
    ): void {
        try {
            $this->showStartCheck($this->startCheck(
                $ViewName,
                $TargetCategoryID,
                $MainPageName,
                $AspectRatio,
                $Orientation,
                $Template,
                $StartGrid,
                $OverwriteExistingView
            ), $OverwriteExistingView);
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateStartCheckWithOverwrite', $exception->getMessage(), 0);
            $this->UpdateFormField(
                'StartCheckStatus',
                'caption',
                sprintf(
                    $this->Translate('🔴 The start check could not be completed: %s'),
                    $exception->getMessage()
                )
            );
            $this->UpdateFormField('CreateViewButton', 'enabled', false);
            $this->UpdateFormField('OverwriteExistingView', 'visible', false);
            $this->UpdateFormField('OverwriteExistingViewInfo', 'visible', false);
        }
    }

    /**
     * Rechecks the values collected by the quick-start wizard.
     */
    public function UpdateQuickStartCheck(
        string $ViewName,
        int $TargetCategoryID,
        string $MainPageName,
        int $AspectRatio,
        int $Orientation,
        int $StartGrid,
        bool $OverwriteExistingView
    ): void {
        try {
            $this->showQuickStartCheck($this->startCheck(
                $ViewName,
                $TargetCategoryID,
                $MainPageName,
                $AspectRatio,
                $Orientation,
                IPSViewFactory::TEMPLATE_EMPTY,
                $StartGrid,
                $OverwriteExistingView
            ), $OverwriteExistingView);
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateQuickStartCheck', $exception->getMessage(), 0);
            $this->UpdateFormField(
                'QuickStartCheckStatus',
                'caption',
                sprintf(
                    $this->Translate('🔴 The start check could not be completed: %s'),
                    $exception->getMessage()
                )
            );
            $this->UpdateFormField('QuickStartOverwriteExistingView', 'visible', false);
            $this->UpdateFormField('QuickStartOverwriteExistingViewInfo', 'visible', false);
        }
    }

    /**
     * Validates the final native wizard page before Symcon runs its confirmation action.
     */
    public function ValidateQuickStartCreation(
        string $ViewName,
        int $TargetCategoryID,
        string $MainPageName,
        int $AspectRatio,
        int $Orientation,
        int $StartGrid,
        bool $OverwriteExistingView
    ): string {
        try {
            $report = $this->startCheck(
                $ViewName,
                $TargetCategoryID,
                $MainPageName,
                $AspectRatio,
                $Orientation,
                IPSViewFactory::TEMPLATE_EMPTY,
                $StartGrid,
                $OverwriteExistingView
            );
            $this->showQuickStartCheck($report, $OverwriteExistingView);

            return $report['ready']
                ? ''
                : $this->Translate(
                    $report['errors'][0] ?? 'The View configuration is not ready for creation.'
                );
        } catch (Throwable $exception) {
            $this->SendDebug('ValidateQuickStartCreation', $exception->getMessage(), 0);

            return sprintf(
                $this->Translate('The start check could not be completed: %s'),
                $exception->getMessage()
            );
        }
    }

    /**
     * Creates an empty IPSView from the compact wizard selections.
     */
    public function CreateQuickStartView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        string $MainPageName,
        int $Theme,
        bool $FullScreen,
        int $StartGrid,
        bool $OverwriteExistingView
    ): string {
        $this->UpdateQuickStartCheck(
            $ViewName,
            $TargetCategoryID,
            $MainPageName,
            $AspectRatio,
            $Orientation,
            $StartGrid,
            $OverwriteExistingView
        );

        return $this->CreateOrOverwriteView(
            $ViewName,
            $TargetCategoryID,
            $AspectRatio,
            $Orientation,
            IPSViewFactory::TEMPLATE_EMPTY,
            $MainPageName,
            $this->normalizeQuickStartTheme($Theme),
            '',
            '',
            '',
            $FullScreen,
            $this->normalizeStartGrid($StartGrid),
            $OverwriteExistingView
        );
    }

    /**
     * Stores the local background selection and refreshes the design preview.
     *
     * An empty image clears the persisted upload only when the file selection itself changed.
     */
    public function UpdateBackgroundPreview(
        string $ImageData,
        int $Mode,
        string $Layout,
        string $ThemePalette,
        string $Effects = '',
        string $Appearance = '',
        bool $ImageSelectionChanged
    ): void {
        try {
            $settings = $this->storeBackgroundSettings(
                $ImageData,
                $Mode,
                $Layout,
                $this->ReadAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE),
                $ImageSelectionChanged
            );
            $fileVisible = $settings['mode'] === IPSViewBackground::MODE_FILE;
            $this->UpdateFormField('BackgroundImageMode', 'value', $settings['mode']);
            $this->UpdateFormField('BackgroundImageFile', 'visible', $fileVisible);
            $this->UpdateFormField('BackgroundImageLayout', 'visible', $fileVisible);
            $this->UpdateFormField(
                'BackgroundImageScope',
                'visible',
                $settings['mode'] !== IPSViewBackground::MODE_PRESERVE
            );
            $this->UpdateFormField(
                'BackgroundImageStatus',
                'caption',
                $this->Translate(
                    $settings['mode'] === IPSViewBackground::MODE_FILE && $settings['imageData'] === ''
                        ? 'Please select a PNG or JPEG background image.'
                        : 'Background image changes are applied to the selected pages. PNG and JPEG files up to 10 MB are embedded directly in the IPSView.'
                )
            );
            $this->UpdateFormField(
                'ThemePreview',
                'image',
                IPSViewThemePreview::createDataUri(
                    IPSViewTheme::resolvePalette(IPSViewTheme::THEME_CUSTOM, $this->decodePalette($ThemePalette)),
                    $this->decodeEffects($Effects),
                    $this->decodeAppearance($Appearance),
                    $settings,
                    $this->previewStartGrid()
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateBackgroundPreview', $exception->getMessage(), 0);
            $this->UpdateFormField('BackgroundImageStatus', 'caption', $exception->getMessage());
        }
    }

    /**
     * Stores a background selection from the wizard and refreshes its preview.
     */
    public function UpdateQuickStartBackground(
        string $ImageData,
        int $Mode,
        string $Layout,
        int $Scope,
        int $Theme,
        int $StartGrid,
        bool $ImageSelectionChanged
    ): void {
        try {
            $settings = $this->storeBackgroundSettings(
                $ImageData,
                $Mode,
                $Layout,
                $Scope,
                $ImageSelectionChanged
            );
            $fileVisible = $settings['mode'] === IPSViewBackground::MODE_FILE;
            $this->UpdateFormField('QuickStartBackgroundMode', 'value', $settings['mode']);
            $this->UpdateFormField('QuickStartBackgroundFile', 'visible', $fileVisible);
            $this->UpdateFormField('QuickStartBackgroundLayout', 'visible', $fileVisible);
            $this->UpdateFormField(
                'QuickStartBackgroundScope',
                'visible',
                $settings['mode'] !== IPSViewBackground::MODE_PRESERVE
            );
            $this->UpdateFormField(
                'QuickStartBackgroundStatus',
                'caption',
                $this->Translate(
                    $settings['mode'] === IPSViewBackground::MODE_FILE && $settings['imageData'] === ''
                        ? 'Please select a PNG or JPEG background image.'
                        : 'Background image changes are applied to the selected pages. PNG and JPEG files up to 10 MB are embedded directly in the IPSView.'
                )
            );
            $this->UpdateFormField(
                'QuickStartPreview',
                'image',
                $this->createQuickStartPreview(
                    $this->normalizeQuickStartTheme($Theme),
                    $this->normalizeStartGrid($StartGrid),
                    $settings
                )
            );
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateQuickStartBackground', $exception->getMessage(), 0);
            $this->UpdateFormField('QuickStartBackgroundStatus', 'caption', $exception->getMessage());
        }
    }

    /**
     * Selects whether background changes affect only the main page or all pages.
     */
    public function UpdateBackgroundScope(int $Scope): void
    {
        try {
            $settings = IPSViewBackground::resolve(['scope' => $Scope]);
            $this->WriteAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE, $settings['scope']);
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateBackgroundScope', $exception->getMessage(), 0);
            $this->UpdateFormField('BackgroundImageStatus', 'caption', $exception->getMessage());
        }
    }

    /**
     * Updates the font-format fields for the selected IPSView font family.
     *
     * @param array<string, mixed> $appearance
     */
    private function updateFontStyleFields(array $appearance): void
    {
        $appearance = IPSViewTypography::resolve($appearance);
        $capabilities = IPSViewTypography::selectedCapabilities($appearance);

        $this->UpdateFormField(
            'FontBoldMode',
            'options',
            json_encode(
                $this->fontFormatOptions('Bold', $capabilities['bold']),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            )
        );
        $this->UpdateFormField('FontBoldMode', 'value', $appearance['fontBoldMode']);
        $this->UpdateFormField(
            'FontItalicMode',
            'options',
            json_encode(
                $this->fontFormatOptions('Italic', $capabilities['italic']),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            )
        );
        $this->UpdateFormField('FontItalicMode', 'value', $appearance['fontItalicMode']);
        $this->UpdateFormField('FontUnderlineMode', 'value', $appearance['fontUnderlineMode']);
    }

    /**
     * Builds one font-format Select while disabling only an unavailable active style.
     *
     * @return list<array{caption: string, value: int, enabled?: bool}>
     */
    private function fontFormatOptions(string $activeCaption, bool $activeEnabled): array
    {
        return [
            [
                'caption' => $this->Translate('Preserve existing'),
                'value'   => IPSViewTypography::FORMAT_PRESERVE,
            ],
            [
                'caption' => $this->Translate('Normal'),
                'value'   => IPSViewTypography::FORMAT_OFF,
            ],
            [
                'caption' => $this->Translate($activeCaption),
                'value'   => IPSViewTypography::FORMAT_ON,
                'enabled' => $activeEnabled,
            ],
        ];
    }

    /**
     * Applies the selected assistant mode to the initially rendered form.
     *
     * @param array<string, mixed> $form
     */
    private function applyAssistantModeToForm(array &$form, int $mode): void
    {
        $advanced = $mode === self::ASSISTANT_MODE_ADVANCED;

        foreach ($this->advancedModeFields() as $field) {
            $this->setConfigurationFormField($form, $field, 'visible', $advanced);
        }

        foreach ($this->quickStartModeFields() as $field) {
            $this->setConfigurationFormField($form, $field, 'visible', !$advanced);
        }

        $this->setConfigurationFormField(
            $form,
            'AssistantModeInfo',
            'caption',
            $this->assistantModeInfo($mode)
        );
        $this->setConfigurationFormField(
            $form,
            'ThemeDescription',
            'caption',
            $this->themeDescription($mode)
        );
    }

    /**
     * Returns the stable field names that are visible only in advanced mode.
     *
     * @return list<string>
     */
    private function advancedModeFields(): array
    {
        return [
            'ViewSettingsPanel',
            'DesignPanel',
            'StartCheckPanel',
            'CreateViewButton',
            'Template',
            'ExistingViewPopup',
            'ThemeDetailsPopup',
            'SaveStyledCopyButton',
            'StyledCopyInfo',
        ];
    }

    /**
     * Returns the stable field names that are visible only in quick-start mode.
     *
     * @return list<string>
     */
    private function quickStartModeFields(): array
    {
        return [
            'QuickStartWizardPopup',
        ];
    }

    /**
     * Falls back to quick start when an unknown assistant mode is supplied.
     */
    private function normalizeAssistantMode(int $mode): int
    {
        return $mode === self::ASSISTANT_MODE_ADVANCED
            ? self::ASSISTANT_MODE_ADVANCED
            : self::ASSISTANT_MODE_QUICK_START;
    }

    /**
     * Returns the localized explanation for the selected assistant mode.
     */
    private function assistantModeInfo(int $mode): string
    {
        if ($mode === self::ASSISTANT_MODE_ADVANCED) {
            return $this->Translate('Advanced mode shows all design details and the functions for existing IPSViews.');
        }

        return $this->Translate('Quick start opens a four-step wizard for the settings needed by a first IPSView. All detailed design and copy functions remain available in Advanced mode.');
    }

    /**
     * Returns the mode-specific explanation shown above the theme selection.
     */
    private function themeDescription(int $mode): string
    {
        if ($mode === self::ASSISTANT_MODE_ADVANCED) {
            return $this->Translate('Choose a preset or adjust the semantic colors below. A manual color change automatically switches the theme to Custom.');
        }

        return $this->Translate('Choose a ready-made design preset. Detailed colors, effects and typography are available in Advanced mode.');
    }

    /**
     * Falls back to the wall-tablet profile for unknown profile values.
     */
    private function normalizeUsageProfile(int $profile): int
    {
        return IPSViewUsageProfile::isSelectable($profile)
            ? $profile
            : IPSViewUsageProfile::PROFILE_WALL_TABLET;
    }

    /**
     * Falls back to the standard theme because custom colors belong to advanced mode.
     */
    private function normalizeQuickStartTheme(int $theme): int
    {
        return in_array(
            $theme,
            [
                IPSViewTheme::THEME_STANDARD,
                IPSViewTheme::THEME_LIGHT,
                IPSViewTheme::THEME_DARK,
                IPSViewTheme::THEME_WARM,
                IPSViewTheme::THEME_COOL,
                IPSViewTheme::THEME_EARTHY,
                IPSViewTheme::THEME_WATER,
                IPSViewTheme::THEME_SUNNY,
            ],
            true
        ) ? $theme : IPSViewTheme::THEME_STANDARD;
    }

    /**
     * Reads and validates the start-grid arrangement retained for the preview.
     */
    private function previewStartGrid(): int
    {
        return $this->normalizeStartGrid(
            $this->ReadAttributeInteger(self::ATTRIBUTE_PREVIEW_START_GRID)
        );
    }

    /**
     * Falls back to no grid for an unsupported start-grid value.
     */
    private function normalizeStartGrid(int $startGrid): int
    {
        return in_array(
            $startGrid,
            [
                IPSViewDocument::START_GRID_NONE,
                IPSViewDocument::START_GRID_TWO_COLUMNS,
                IPSViewDocument::START_GRID_THREE_COLUMNS,
            ],
            true
        ) ? $startGrid : IPSViewDocument::START_GRID_NONE;
    }

    /**
     * Returns the localized device and dimension summary for a usage profile.
     */
    private function usageProfileInfo(int $profile): string
    {
        return $this->Translate(match ($profile) {
            IPSViewUsageProfile::PROFILE_TABLET     => '4:3 landscape, 1024 x 768 logical pixels, full screen. Recommended for a portable tablet.',
            IPSViewUsageProfile::PROFILE_SMARTPHONE => '16:9 portrait, 765 x 1360 logical pixels, full screen. Recommended for a phone.',
            IPSViewUsageProfile::PROFILE_BROWSER    => '16:9 landscape, 1360 x 765 logical pixels, window mode. Recommended for use in a browser.',
            IPSViewUsageProfile::PROFILE_CUSTOM     => 'Aspect ratio, orientation and full-screen mode can be selected freely.',
            default                                 => '16:9 landscape, 1360 x 765 logical pixels, full screen. Recommended for a permanently installed control panel.',
        });
    }

    /**
     * Restores the handover for the most recently created View when the form is reopened.
     *
     * @param array<string, mixed> $form
     */
    private function applyDesignerHandoverToForm(array &$form): void
    {
        $mediaID = $this->ReadAttributeInteger(self::ATTRIBUTE_LAST_CREATED_VIEW_ID);
        $visible = $mediaID > 0 && IPS_MediaExists($mediaID);

        $this->setConfigurationFormField($form, 'DesignerHandoverPanel', 'visible', $visible);
        $this->setConfigurationFormField($form, 'DesignerHandoverInitialInfo', 'visible', !$visible);

        if (!$visible) {
            return;
        }

        $objectID = $this->ReadAttributeInteger(self::ATTRIBUTE_DESIGNER_OBJECT_ID);
        if ($objectID > 1 && !IPS_ObjectExists($objectID)) {
            $objectID = 1;
        }

        $this->setConfigurationFormField(
            $form,
            'DesignerHandoverTitle',
            'caption',
            $this->designerHandoverTitle($mediaID)
        );
        $this->setConfigurationFormField($form, 'DesignerObjectID', 'value', $objectID);
        $this->setConfigurationFormField(
            $form,
            'DesignerObjectHint',
            'caption',
            $this->designerObjectHint($objectID)
        );
    }

    /**
     * Reveals and initializes the guided Designer handover after View creation.
     */
    private function showDesignerHandover(int $mediaID): void
    {
        $this->UpdateFormField('DesignerHandoverPanel', 'visible', true);
        $this->UpdateFormField('DesignerHandoverPanel', 'expanded', true);
        $this->UpdateFormField('DesignerHandoverInitialInfo', 'visible', false);
        $this->UpdateFormField('DesignerHandoverTitle', 'caption', $this->designerHandoverTitle($mediaID));
        $this->UpdateFormField('DesignerObjectID', 'value', 1);
        $this->UpdateFormField('DesignerObjectHint', 'caption', $this->designerObjectHint(1));
    }

    /**
     * Builds the localized object-tree location of the newly created View.
     */
    private function designerHandoverTitle(int $mediaID): string
    {
        $object = IPS_GetObject($mediaID);

        return sprintf(
            $this->Translate('Created IPSView "%s" (object ID %d) at "%s".'),
            (string) ($object['ObjectName'] ?? ''),
            $mediaID,
            IPS_GetLocation($mediaID)
        );
    }

    /**
     * Builds a localized first-control recommendation for a Symcon object.
     */
    private function designerObjectHint(int $objectID): string
    {
        if ($objectID <= 1 || !IPS_ObjectExists($objectID)) {
            return $this->Translate('Select a variable, script or media object to receive a suitable starting recommendation.');
        }

        $object = IPS_GetObject($objectID);
        $objectType = (int) ($object['ObjectType'] ?? -1);
        $variableType = $objectType === 2
            ? (int) (IPS_GetVariable($objectID)['VariableType'] ?? -1)
            : null;
        $recommendation = IPSViewDesignerHandover::recommendation($objectType, $variableType);

        return sprintf(
            $this->Translate('Object ID %d ("%s"): %s'),
            $objectID,
            (string) ($object['ObjectName'] ?? ''),
            $this->Translate($recommendation)
        );
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
     * Recursively updates one named field in a list of nested form items.
     *
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

            if (isset($item['popup']['items']) && is_array($item['popup']['items'])) {
                if ($this->setConfigurationFormFieldInItems($item['popup']['items'], $name, $property, $value)) {
                    return true;
                }
            }

            if (isset($item['popup']['pages']) && is_array($item['popup']['pages'])) {
                if ($this->setConfigurationFormFieldInItems($item['popup']['pages'], $name, $property, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Finds the managed design copy matching source, name and target category.
     */
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

    /**
     * Returns the most recently registered, still valid copy for a source View.
     */
    private function findPreferredManagedCopy(int $sourceMediaID): ?int
    {
        foreach (array_reverse($this->readManagedCopies()) as $managedCopy) {
            if ($managedCopy['sourceMediaID'] === $sourceMediaID) {
                return $managedCopy['targetMediaID'];
            }
        }

        return null;
    }

    /**
     * Persists the relation between a source View and its managed design copy.
     */
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
     * Reads the managed-copy registry and filters invalid entries.
     *
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
     * Converts a theme report into the localized result text shown to the user.
     *
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
     *     globalShapeApplied: int,
     *     backgroundChanged: bool
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
     * Decodes the semantic color palette received from the configuration form.
     *
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
     * Decodes and validates the general visual effects received from the form.
     *
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
     * Decodes and validates the typography and shape settings received from the form.
     *
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
     * Returns the validated background-image settings persisted by the instance.
     *
     * @return array{mode: int, layout: string, scope: int, imageData: string}
     */
    private function backgroundSettings(): array
    {
        return IPSViewBackground::resolve([
            'mode'      => $this->ReadAttributeInteger(self::ATTRIBUTE_BACKGROUND_MODE),
            'layout'    => $this->ReadAttributeString(self::ATTRIBUTE_BACKGROUND_LAYOUT),
            'scope'     => $this->ReadAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE),
            'imageData' => $this->ReadAttributeString(self::ATTRIBUTE_BACKGROUND_IMAGE),
        ]);
    }

    /**
     * Validates and persists one background-image selection.
     *
     * @return array{mode: int, layout: string, scope: int, imageData: string}
     */
    private function storeBackgroundSettings(
        string $imageData,
        int $mode,
        string $layout,
        int $scope,
        bool $imageSelectionChanged
    ): array {
        $storedImage = $this->ReadAttributeString(self::ATTRIBUTE_BACKGROUND_IMAGE);
        $settings = IPSViewBackground::resolve([
            'mode'      => $mode,
            'layout'    => $layout,
            'scope'     => $scope,
            'imageData' => $imageSelectionChanged
                ? $imageData
                : ($imageData !== '' ? $imageData : $storedImage),
        ]);
        IPSViewBackground::preview($settings);
        $this->WriteAttributeString(self::ATTRIBUTE_BACKGROUND_IMAGE, $settings['imageData']);
        $this->WriteAttributeInteger(self::ATTRIBUTE_BACKGROUND_MODE, $settings['mode']);
        $this->WriteAttributeString(self::ATTRIBUTE_BACKGROUND_LAYOUT, $settings['layout']);
        $this->WriteAttributeInteger(self::ATTRIBUTE_BACKGROUND_SCOPE, $settings['scope']);

        return $settings;
    }

    /**
     * Creates the wizard preview from preset-only design settings.
     *
     * @param array{mode: int, layout: string, scope: int, imageData: string} $background
     */
    private function createQuickStartPreview(int $theme, int $startGrid, array $background): string
    {
        return IPSViewThemePreview::createDataUri(
            IPSViewTheme::preset($theme),
            IPSViewEffects::resolve(),
            [],
            $background,
            $startGrid
        );
    }

    /**
     * Analyzes the current creation inputs together with the persisted background settings.
     *
     * @return array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>}
     */
    private function startCheck(
        string $viewName,
        int $targetCategoryID,
        string $mainPageName,
        int $aspectRatio,
        int $orientation,
        int $template,
        int $startGrid,
        bool $overwriteExisting = false
    ): array {
        return IPSViewStartCheck::analyze(
            $viewName,
            $targetCategoryID,
            $mainPageName,
            $aspectRatio,
            $orientation,
            $template,
            $startGrid,
            $this->backgroundSettings(),
            $overwriteExisting
        );
    }

    /**
     * Writes a start-check report to the live form and enables creation when ready.
     *
     * @param array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>} $report
     */
    private function showStartCheck(array $report, bool $overwriteExisting = false): void
    {
        $this->UpdateFormField('StartCheckStatus', 'caption', $this->startCheckCaption($report));
        $this->UpdateFormField('CreateViewButton', 'enabled', $report['ready']);
        $this->UpdateFormField('OverwriteExistingView', 'visible', $report['overwriteAvailable']);
        $this->UpdateFormField('OverwriteExistingView', 'value', $overwriteExisting);
        $this->UpdateFormField('OverwriteExistingViewInfo', 'visible', $report['overwriteAvailable']);
    }

    /**
     * Writes a start-check report to the quick-start wizard.
     *
     * @param array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>} $report
     */
    private function showQuickStartCheck(array $report, bool $overwriteExisting = false): void
    {
        $this->UpdateFormField('QuickStartCheckStatus', 'caption', $this->startCheckCaption($report));
        $this->UpdateFormField('QuickStartOverwriteExistingView', 'visible', $report['overwriteAvailable']);
        $this->UpdateFormField('QuickStartOverwriteExistingView', 'value', $overwriteExisting);
        $this->UpdateFormField('QuickStartOverwriteExistingViewInfo', 'visible', $report['overwriteAvailable']);
    }

    /**
     * Applies the initial start-check report to the form definition before rendering.
     *
     * @param array<string, mixed>                                                                              $form
     * @param array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>} $report
     */
    private function applyStartCheckToForm(array &$form, array $report): void
    {
        $this->setConfigurationFormField($form, 'StartCheckStatus', 'caption', $this->startCheckCaption($report));
        $this->setConfigurationFormField($form, 'CreateViewButton', 'enabled', $report['ready']);
        $this->setConfigurationFormField($form, 'OverwriteExistingView', 'visible', $report['overwriteAvailable']);
        $this->setConfigurationFormField($form, 'OverwriteExistingView', 'value', false);
        $this->setConfigurationFormField($form, 'OverwriteExistingViewInfo', 'visible', $report['overwriteAvailable']);
    }

    /**
     * Applies the initial start-check report to the quick-start wizard.
     *
     * @param array<string, mixed>                                                                              $form
     * @param array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>} $report
     */
    private function applyQuickStartCheckToForm(array &$form, array $report): void
    {
        $this->setConfigurationFormField(
            $form,
            'QuickStartCheckStatus',
            'caption',
            $this->startCheckCaption($report)
        );
        $this->setConfigurationFormField(
            $form,
            'QuickStartOverwriteExistingView',
            'visible',
            $report['overwriteAvailable']
        );
        $this->setConfigurationFormField($form, 'QuickStartOverwriteExistingView', 'value', false);
        $this->setConfigurationFormField(
            $form,
            'QuickStartOverwriteExistingViewInfo',
            'visible',
            $report['overwriteAvailable']
        );
    }

    /**
     * Formats one start-check report as a localized multiline traffic-light summary.
     *
     * @param array{status: int, ready: bool, overwriteAvailable: bool, checks: list<string>, warnings: list<string>, errors: list<string>} $report
     */
    private function startCheckCaption(array $report): string
    {
        if ($report['status'] === IPSViewStartCheck::STATUS_ERROR) {
            $title = $this->Translate('🔴 Not ready yet');
            $messages = [...$report['errors'], ...$report['warnings']];
        } elseif ($report['status'] === IPSViewStartCheck::STATUS_WARNING) {
            $title = $this->Translate('🟡 Ready with a note');
            $messages = [...$report['warnings'], ...$report['checks']];
        } else {
            $title = $this->Translate('🟢 Ready to create');
            $messages = $report['checks'];
        }

        return implode("\n", [
            $title,
            ...array_map(
                fn (string $message): string => '• ' . $this->Translate($message),
                $messages
            ),
        ]);
    }

    /**
     * Writes a resolved semantic palette back to all configuration color fields.
     *
     * @param array<string, string> $palette
     */
    private function updateColorFields(array $palette): void
    {
        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->UpdateFormField($field, 'value', IPSViewTheme::toFormColor($palette[$role]));
        }
    }
}

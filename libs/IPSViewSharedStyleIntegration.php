<?php

declare(strict_types=1);

namespace Burki24\IPSViewAssistant;

use Burki24\SymconModuleHelper\IPSViewControlThemeHelper;
use Burki24\SymconModuleHelper\IPSViewStyleProfileHelper;
use JsonException;
use RuntimeException;
use stdClass;
use Throwable;

require_once __DIR__ . '/helper/IPSViewStyleProfileHelper.php';
require_once __DIR__ . '/IPSViewColorPaletteExchange.php';
require_once __DIR__ . '/IPSViewSharedStyleAdapter.php';

/**
 * Integrates the shared IPSView style configuration into the IPSView Assistant workflow.
 *
 * The static legacy editor remains in form.json as a hidden compatibility bridge for established
 * Assistant methods. The visible configuration, persisted values and native overrides are provided
 * by IPSViewStyleConfigurationHelper so every consumer can use the same IPSView design mask.
 */
trait IPSViewSharedStyleIntegration
{
    /** @var list<string> */
    private const IPSVIEW_ASSISTANT_SHARED_STRING_PROPERTIES = [
        'IPSViewStylePreset',
        'IPSViewStyleFontFamily',
        'IPSViewStyleFontStyle'
    ];

    /** @var list<string> */
    private const IPSVIEW_ASSISTANT_SHARED_FLOAT_PROPERTIES = [
        'IPSViewStyleBorderRadius',
        'IPSViewStyleBorderWidth',
        'IPSViewStyleLineWidth',
        'IPSViewStyleShadowBlur',
        'IPSViewStyleShadowSpread',
        'IPSViewStyleShadowOffsetX',
        'IPSViewStyleShadowOffsetY'
    ];

    /**
     * @param string $Configuration JSON object containing the visible shared style field values.
     */
    public function ApplySharedStyleConfiguration(string $Configuration): void
    {
        try {
            $configuration = json_decode($Configuration, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The shared IPSView style configuration is invalid.', 0, $exception);
        }
        if (!is_array($configuration)) {
            throw new RuntimeException('The shared IPSView style configuration must be an object.');
        }

        $allowed = array_flip($this->IPSViewAssistantSharedStyleFieldNames($this->IPSViewStyleFormItems('240px')));
        $changed = false;
        $sourceChanged = false;

        foreach ($configuration as $propertyName => $value) {
            if (!is_string($propertyName) || !isset($allowed[$propertyName])) {
                continue;
            }

            $normalized = $this->IPSViewAssistantNormalizeSharedProperty($propertyName, $value);
            $previous = $this->IPSViewAssistantReadSharedProperty($propertyName);
            if ($previous !== $normalized) {
                $changed = true;
                if (in_array(
                    $propertyName,
                    ['IPSViewStyleSource', 'IPSViewStyleMediaID', 'IPSViewStyleProfileMediaID'],
                    true
                )) {
                    $sourceChanged = true;
                }
            }
            IPS_SetProperty($this->InstanceID, $propertyName, $normalized);
        }

        if ($changed) {
            $this->clearStyleProfileImportState();
        }
        IPS_ApplyChanges($this->InstanceID);

        if ($sourceChanged) {
            $this->ReloadForm();

            return;
        }

        $this->RefreshSharedStylePreview();
    }

    /**
     * @param string $PropertyName Native override property that owns the edited color family.
     * @param string $Row JSON-encoded edited native IPSView color row.
     */
    public function ApplySharedNativeColorOverride(string $PropertyName, string $Row): void
    {
        if ($this->IPSViewStyleSource() !== self::IPSVIEW_STYLE_SOURCE_CUSTOM) {
            throw new RuntimeException('Native IPSView color overrides can only be edited in the custom style.');
        }

        $propertyMap = $this->IPSViewStyleNativeOverrideProperties();
        $family = array_search($PropertyName, $propertyMap, true);
        if (!is_string($family)) {
            throw new RuntimeException('The native IPSView color group is not supported.');
        }

        try {
            $editedRow = json_decode($Row, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The edited native IPSView color row is invalid.', 0, $exception);
        }
        if (!is_array($editedRow)) {
            throw new RuntimeException('The edited native IPSView color row must be an object.');
        }

        $field = isset($editedRow['Field']) && is_string($editedRow['Field'])
            ? trim($editedRow['Field'])
            : '';
        $definition = $field === '' ? null : IPSViewControlThemeHelper::definition($field);
        if ($definition === null || ($definition['family'] ?? null) !== $family) {
            throw new RuntimeException('The edited IPSView field does not belong to the selected color group.');
        }

        $stored = $this->IPSViewAssistantNativeOverrideRows($PropertyName, $family);
        $wasOverridden = isset($stored[$field]);
        $inheritedColor = $this->IPSViewAssistantNativeInheritedColor($field);
        $editedColor = array_key_exists('Color', $editedRow)
            ? max(0, min(0xFFFFFF, (int) $editedRow['Color']))
            : $inheritedColor;
        $override = $this->IPSViewAssistantNativeOverrideEnabled($editedRow['Override'] ?? false);
        if (!$wasOverridden && !$override && $editedColor !== $inheritedColor) {
            $override = true;
        }

        if ($override) {
            $stored[$field] = [
                'Override' => true,
                'Field'    => $field,
                'Color'    => $editedColor
            ];
        } else {
            unset($stored[$field]);
        }

        $ordered = [];
        foreach (IPSViewControlThemeHelper::families()[$family] ?? [] as $familyField) {
            if (isset($stored[$familyField])) {
                $ordered[] = $stored[$familyField];
            }
        }

        IPS_SetProperty(
            $this->InstanceID,
            $PropertyName,
            json_encode($ordered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
        $this->clearStyleProfileImportState();
        IPS_ApplyChanges($this->InstanceID);
        $this->IPSViewAssistantRefreshNativeList($PropertyName);
        $this->RefreshSharedStylePreview();
    }

    /**
     * Clears the imported Style Profile baseline so the next export uses the current Assistant values.
     */
    public function ClearSharedStyleProfileBaseline(): void
    {
        $this->clearStyleProfileImportState();
    }

    /**
     * Reloads the configuration form after shared style changes.
     */
    public function ReloadSharedStyleForm(): void
    {
        $this->ReloadForm();
    }

    /**
     * Rebuilds the preview from the resolved shared style and native IPSView theme.
     */
    public function RefreshSharedStylePreview(): void
    {
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $style = $snapshot['style'];
        $palette = IPSViewSharedStyleAdapter::previewPalette($style);
        $effects = IPSViewSharedStyleAdapter::previewEffects($style, $snapshot['gradientStrength']);
        $appearance = IPSViewSharedStyleAdapter::appearance($style);
        $nativeColors = $this->IPSViewAssistantNativePreviewColors($snapshot['nativeTheme']);
        $preview = IPSViewThemePreview::createDataUri(
            $palette,
            $effects,
            $appearance,
            $this->backgroundSettings(),
            $this->previewStartGrid(),
            $snapshot['transparentBackground'],
            $nativeColors
        );

        $this->IPSViewAssistantSynchronizeLegacyStyleFields($palette, $effects, $appearance);
        $this->UpdateFormField('ThemePreview', 'image', $preview);
    }

    /**
     * @param string $ViewName Name of the target IPSView media object.
     * @param int $TargetCategoryID Symcon category that receives the target object.
     * @param int $AspectRatio IPSView aspect-ratio identifier.
     * @param int $Orientation IPSView orientation identifier.
     * @param int $Template IPSView template identifier.
     * @param string $MainPageName Name of the initial main page.
     * @param bool $FullScreen Whether the generated View should start in fullscreen mode.
     * @param int $StartGrid Optional start-grid mode.
     *
     * @return string Human-readable result of the View creation.
     */
    public function CreateSharedStyleView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        string $MainPageName,
        bool $FullScreen = false,
        int $StartGrid = IPSViewDocument::START_GRID_NONE
    ): string {
        return $this->CreateOrOverwriteSharedStyleView(
            $ViewName,
            $TargetCategoryID,
            $AspectRatio,
            $Orientation,
            $Template,
            $MainPageName,
            $FullScreen,
            $StartGrid,
            false
        );
    }

    /**
     * @param string $ViewName Name of the target IPSView media object.
     * @param int $TargetCategoryID Symcon category that receives the target object.
     * @param int $AspectRatio IPSView aspect-ratio identifier.
     * @param int $Orientation IPSView orientation identifier.
     * @param int $Template IPSView template identifier.
     * @param string $MainPageName Name of the initial main page.
     * @param bool $FullScreen Whether the generated View should start in fullscreen mode.
     * @param int $StartGrid Optional start-grid mode.
     * @param bool $OverwriteExistingView Whether one unambiguous same-name IPSView may be overwritten.
     *
     * @return string Human-readable result of the create/overwrite operation.
     */
    public function CreateOrOverwriteSharedStyleView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        string $MainPageName,
        bool $FullScreen,
        int $StartGrid,
        bool $OverwriteExistingView
    ): string {
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $palette = IPSViewSharedStyleAdapter::palette($snapshot['style']);
        $effects = IPSViewSharedStyleAdapter::effects($snapshot['style'], $snapshot['gradientStrength']);
        $appearance = IPSViewSharedStyleAdapter::appearance($snapshot['style']);
        $report = $this->startCheck(
            $ViewName,
            $TargetCategoryID,
            $MainPageName,
            $AspectRatio,
            $Orientation,
            $Template,
            $StartGrid,
            $OverwriteExistingView
        );
        $wasOverwritten = $OverwriteExistingView && $report['overwriteAvailable'];
        $result = $this->CreateOrOverwriteView(
            $ViewName,
            $TargetCategoryID,
            $AspectRatio,
            $Orientation,
            $Template,
            $MainPageName,
            IPSViewTheme::THEME_CUSTOM,
            $this->IPSViewAssistantEncodeActionValue($palette),
            $this->IPSViewAssistantEncodeActionValue($effects),
            $this->IPSViewAssistantEncodeActionValue($appearance),
            $FullScreen,
            $StartGrid,
            $OverwriteExistingView
        );

        $mediaID = max(0, $this->ReadAttributeInteger(self::ATTRIBUTE_LAST_CREATED_VIEW_ID));
        if ($mediaID <= 0 || !$this->IPSViewAssistantIsCreateSuccess($result, $ViewName, $mediaID, $wasOverwritten)) {
            return $result;
        }

        try {
            $this->IPSViewAssistantFinalizeSharedStyleMedia(
                $mediaID,
                IPSViewTheme::SCOPE_GLOBAL_DEFAULTS,
                $snapshot,
                true
            );
        } catch (Throwable $exception) {
            $this->SendDebug('CreateOrOverwriteSharedStyleView', $exception->getMessage(), 0);

            return $result . ' ' . $this->Translate('Warning') . ': ' . $exception->getMessage();
        }

        return $result;
    }

    /**
     * @param int $SourceViewID Media object ID of the source IPSView.
     * @param string $CopyViewName Name of the styled copy.
     * @param int $TargetCategoryID Symcon category that receives the target object.
     * @param int $Scope Scope used when applying the shared design to existing controls.
     *
     * @return string Human-readable result of the shared-style copy operation.
     */
    public function CreateSharedStyledCopy(
        int $SourceViewID,
        string $CopyViewName,
        int $TargetCategoryID,
        int $Scope = IPSViewTheme::SCOPE_MATCHING_CONTROLS
    ): string {
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $palette = IPSViewSharedStyleAdapter::palette($snapshot['style']);
        $effects = IPSViewSharedStyleAdapter::effects($snapshot['style'], $snapshot['gradientStrength']);
        $appearance = IPSViewSharedStyleAdapter::appearance($snapshot['style']);
        $copyName = trim($CopyViewName);
        $targetCategoryID = $TargetCategoryID === 1 ? 0 : $TargetCategoryID;
        $factory = new IPSViewCopyFactory();
        $targetBefore = $this->findManagedCopy($SourceViewID, $copyName, $targetCategoryID);
        if ($targetBefore === null) {
            $targetBefore = $factory->findExistingTarget($copyName, $targetCategoryID);
        }

        $result = $this->CreateStyledCopy(
            $SourceViewID,
            $CopyViewName,
            $targetCategoryID,
            IPSViewTheme::THEME_CUSTOM,
            $this->IPSViewAssistantEncodeActionValue($palette),
            $Scope,
            $this->IPSViewAssistantEncodeActionValue($effects),
            $this->IPSViewAssistantEncodeActionValue($appearance)
        );
        $targetMediaID = $this->findManagedCopy($SourceViewID, $copyName, $targetCategoryID);
        if ($targetMediaID === null || !$this->IPSViewAssistantIsCopySuccess(
            $result,
            $copyName,
            $targetMediaID,
            $targetBefore !== null
        )) {
            return $result;
        }

        try {
            $this->IPSViewAssistantFinalizeSharedStyleMedia($targetMediaID, $Scope, $snapshot, false);
        } catch (Throwable $exception) {
            $this->SendDebug('CreateSharedStyledCopy', $exception->getMessage(), 0);

            return $result . ' ' . $this->Translate('Warning') . ': ' . $exception->getMessage();
        }

        return $result;
    }

    /**
     * @param int $SourceViewID Media object ID of the source IPSView.
     */
    public function LoadSharedExistingView(int $SourceViewID): void
    {
        if ($SourceViewID <= 0) {
            $this->WriteAttributeInteger(self::ATTRIBUTE_SELECTED_SOURCE_VIEW_ID, 0);
            $this->WriteAttributeString(self::ATTRIBUTE_COPY_VIEW_NAME, '');
            $this->WriteAttributeInteger(self::ATTRIBUTE_COPY_TARGET_CATEGORY_ID, 0);

            return;
        }

        $factory = new IPSViewCopyFactory();
        try {
            $factory->inspect($SourceViewID);
            $this->LoadExistingView($SourceViewID);
            $styleMediaID = $this->findPreferredManagedCopy($SourceViewID) ?? $SourceViewID;
            IPS_SetProperty($this->InstanceID, 'IPSViewStyleMediaID', $styleMediaID);
            IPS_SetProperty($this->InstanceID, 'IPSViewStyleSource', self::IPSVIEW_STYLE_SOURCE_MEDIA);
            IPS_ApplyChanges($this->InstanceID);
            $this->ReloadForm();
        } catch (Throwable $exception) {
            $this->SendDebug('LoadSharedExistingView', $exception->getMessage(), 0);
            $this->UpdateFormField(
                'ExistingViewStatus',
                'caption',
                sprintf(
                    $this->Translate('The existing IPSView could not be loaded: %s'),
                    $exception->getMessage()
                )
            );
        }
    }

    /**
     * @param string $Name Style-profile name.
     * @param string $Description Optional style-profile description.
     *
     * @return string Encoded Style Profile V1 JSON document or an error message.
     */
    public function ExportSharedStyleProfileJson(string $Name, string $Description): string
    {
        try {
            $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
            $profile = IPSViewStyleProfileHelper::create(
                trim($Name),
                IPSViewSharedStyleAdapter::profileStyle(
                    $snapshot['style'],
                    $snapshot['nativeTheme'],
                    $snapshot['gradientStrength'],
                    $snapshot['transparentBackground']
                ),
                $this->styleProfileMetadata($Description, $this->readStyleProfileImportState())
            );
            $json = IPSViewStyleProfileHelper::encode($profile);
            $this->UpdateFormField(
                'StyleProfileStatus',
                'caption',
                sprintf($this->Translate('Style profile "%s" was exported as JSON.'), trim($Name))
            );

            return $json;
        } catch (Throwable $exception) {
            $this->SendDebug('ExportSharedStyleProfileJson', $exception->getMessage(), 0);
            $message = sprintf($this->Translate('The style profile could not be exported: %s'), $exception->getMessage());
            $this->UpdateFormField('StyleProfileStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * @param string $Name Style-profile name.
     * @param string $Description Optional style-profile description.
     * @param int $TargetCategoryID Symcon category that receives the target object.
     *
     * @return string Human-readable result of the media export.
     */
    public function SaveSharedStyleProfileMedia(
        string $Name,
        string $Description,
        int $TargetCategoryID
    ): string {
        try {
            $name = trim($Name);
            $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
            $profile = IPSViewStyleProfileHelper::create(
                $name,
                IPSViewSharedStyleAdapter::profileStyle(
                    $snapshot['style'],
                    $snapshot['nativeTheme'],
                    $snapshot['gradientStrength'],
                    $snapshot['transparentBackground']
                ),
                $this->styleProfileMetadata($Description, $this->readStyleProfileImportState())
            );
            $mediaID = $this->writeStyleProfileMedia($name, $TargetCategoryID, IPSViewStyleProfileHelper::encode($profile));
            $message = sprintf(
                $this->Translate('Style profile "%s" was saved as Symcon media object %d.'),
                $name,
                $mediaID
            );
            $this->UpdateFormField('StyleProfileStatus', 'caption', $message);

            return $message;
        } catch (Throwable $exception) {
            $this->SendDebug('SaveSharedStyleProfileMedia', $exception->getMessage(), 0);
            $message = sprintf(
                $this->Translate('The style profile could not be saved as a Symcon media object: %s'),
                $exception->getMessage()
            );
            $this->UpdateFormField('StyleProfileStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * @param string $File Style Profile V1 JSON, Base64 data or a supported data URI.
     *
     * @return string Human-readable result of the profile import.
     */
    public function ImportSharedStyleProfileFile(string $File): string
    {
        $result = $this->ImportStyleProfileFile($File);
        if ($this->IPSViewAssistantWasStyleProfileImportSuccessful($result)) {
            $this->IPSViewAssistantAdoptImportedStyleProfile();
        }

        return $result;
    }

    /**
     * @param int $MediaID Symcon document media object containing a Style Profile V1 document.
     *
     * @return string Human-readable result of the profile import.
     */
    public function ImportSharedStyleProfileMedia(int $MediaID): string
    {
        $result = $this->ImportStyleProfileMedia($MediaID);
        if ($this->IPSViewAssistantWasStyleProfileImportSuccessful($result)) {
            $this->IPSViewAssistantAdoptImportedStyleProfile();
        }

        return $result;
    }

    /**
     * Exports the complete active color palette without typography or visual effects.
     *
     * @param string $Name Palette name.
     * @param string $Description Optional palette description.
     *
     * @return string Encoded Color Palette V1 JSON document or an error message.
     */
    public function ExportColorPaletteJson(string $Name, string $Description): string
    {
        try {
            $data = $this->IPSViewAssistantColorPaletteData();
            $json = IPSViewColorPaletteExchange::exportJson(
                trim($Name),
                $Description,
                $data['semanticColors'],
                $data['nativeColors']
            );
            $message = sprintf($this->Translate('Color palette "%s" was exported as JSON.'), trim($Name));
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $json;
        } catch (Throwable $exception) {
            $this->SendDebug('ExportColorPaletteJson', $exception->getMessage(), 0);
            $message = sprintf(
                $this->Translate('The color palette could not be exported: %s'),
                $exception->getMessage()
            );
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * Saves the complete active color palette as a reusable Symcon document media object.
     *
     * @param string $Name Palette or target object name.
     * @param string $Description Optional palette description.
     * @param int $TargetCategoryID Symcon category that receives the document.
     *
     * @return string Human-readable result of the palette media export.
     */
    public function SaveColorPaletteMedia(string $Name, string $Description, int $TargetCategoryID): string
    {
        try {
            $name = trim($Name);
            $data = $this->IPSViewAssistantColorPaletteData();
            $json = IPSViewColorPaletteExchange::exportJson(
                $name,
                $Description,
                $data['semanticColors'],
                $data['nativeColors']
            );
            $mediaID = $this->IPSViewAssistantWriteColorPaletteMedia($name, $TargetCategoryID, $json);
            $message = sprintf(
                $this->Translate('Color palette "%s" was saved as Symcon media object %d.'),
                $name,
                $mediaID
            );
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $message;
        } catch (Throwable $exception) {
            $this->SendDebug('SaveColorPaletteMedia', $exception->getMessage(), 0);
            $message = sprintf(
                $this->Translate('The color palette could not be saved as a Symcon media object: %s'),
                $exception->getMessage()
            );
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * Imports one Color Palette V1 file and applies only its color values.
     *
     * @param string $File Palette JSON, Base64 data or a supported data URI.
     *
     * @return string Human-readable result of the palette import.
     */
    public function ImportColorPaletteFile(string $File): string
    {
        try {
            $palette = IPSViewColorPaletteExchange::importJson(
                IPSViewColorPaletteExchange::decodeFileData($File)
            );

            return $this->IPSViewAssistantApplyColorPalette($palette);
        } catch (Throwable $exception) {
            $this->SendDebug('ImportColorPaletteFile', $exception->getMessage(), 0);
            $message = sprintf(
                $this->Translate('The color palette file could not be imported: %s'),
                $exception->getMessage()
            );
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * Imports one Color Palette V1 from a Symcon document media object.
     *
     * @param int $MediaID Symcon document media object containing a Color Palette V1 document.
     *
     * @return string Human-readable result of the palette import.
     */
    public function ImportColorPaletteMedia(int $MediaID): string
    {
        try {
            if ($MediaID <= 0 || !IPS_MediaExists($MediaID)) {
                throw new RuntimeException($this->Translate('The selected color palette media object does not exist.'));
            }

            $media = IPS_GetMedia($MediaID);
            $documentType = defined('MEDIATYPE_DOCUMENT') ? MEDIATYPE_DOCUMENT : 5;
            if ((int) ($media['MediaType'] ?? -1) !== $documentType) {
                throw new RuntimeException($this->Translate('The selected color palette media object is not a document.'));
            }

            $content = IPS_GetMediaContent($MediaID);
            $json = is_string($content) ? base64_decode($content, true) : false;
            if (!is_string($json) || trim($json) === '') {
                throw new RuntimeException(
                    $this->Translate('The selected color palette media object does not contain readable content.')
                );
            }

            return $this->IPSViewAssistantApplyColorPalette(IPSViewColorPaletteExchange::importJson($json));
        } catch (Throwable $exception) {
            $this->SendDebug('ImportColorPaletteMedia', $exception->getMessage(), 0);
            $message = sprintf(
                $this->Translate('The color palette media could not be imported: %s'),
                $exception->getMessage()
            );
            $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
            $this->UpdateFormField('ColorPaletteStatus', 'caption', $message);

            return $message;
        }
    }

    /**
     * Replaces the visible legacy design editor with the shared style form.
     *
     * @param array<string,mixed> $form Configuration-form structure modified in place.
     */
    private function ApplyIPSViewSharedStyleForm(array &$form): void
    {
        $sharedItems = $this->IPSViewStyleFormItems('240px');
        $fieldNames = $this->IPSViewAssistantSharedStyleFieldNames($sharedItems);
        $this->IPSViewAssistantPopulateSharedStyleValues($sharedItems, $fieldNames);
        $captureScript = $this->IPSViewAssistantSharedStyleCaptureScript($fieldNames);
        $this->IPSViewAssistantAttachSharedStyleOnChange($sharedItems, $fieldNames, $captureScript);
        $this->IPSViewAssistantAttachNativeListOnEdit($sharedItems);
        $this->IPSViewAssistantAppendReloadToSharedButtons($sharedItems);

        $inserted = false;
        if (isset($form['actions']) && is_array($form['actions'])) {
            $inserted = $this->IPSViewAssistantReplaceDesignPopup($form['actions'], $sharedItems, $captureScript);
        }
        if (!$inserted && isset($form['elements']) && is_array($form['elements'])) {
            $inserted = $this->IPSViewAssistantReplaceDesignPopup($form['elements'], $sharedItems, $captureScript);
        }
        if (!$inserted) {
            throw new RuntimeException('The IPSView Assistant design-details popup could not be found.');
        }

        $this->setConfigurationFormField($form, 'Theme', 'visible', false);
        $this->setConfigurationFormField($form, 'Theme', 'value', IPSViewTheme::THEME_CUSTOM);
        $this->setConfigurationFormField(
            $form,
            'ThemeDescription',
            'caption',
            $this->Translate('Advanced mode shows all design details and the functions for existing IPSViews.')
        );
        $this->setConfigurationFormField(
            $form,
            'CreateViewButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_CreateOrOverwriteSharedStyleView($id, $ViewName, $TargetCategoryID, $AspectRatio, $Orientation, $Template, $MainPageName, $FullScreen, $StartGrid, $OverwriteExistingView);'
        );
        $this->setConfigurationFormField(
            $form,
            'SaveStyledCopyButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_CreateSharedStyledCopy($id, $SourceViewID, $CopyViewName, $CopyTargetCategoryID, $DesignScope);'
        );
        $this->setConfigurationFormField(
            $form,
            'SourceViewID',
            'onChange',
            'IPSVIEWA_LoadSharedExistingView($id, $SourceViewID);'
        );
        $this->setConfigurationFormField(
            $form,
            'ExportStyleProfileJsonButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_ExportSharedStyleProfileJson($id, $StyleProfileName, $StyleProfileDescription);'
        );
        $this->setConfigurationFormField(
            $form,
            'SaveStyleProfileMediaButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_SaveSharedStyleProfileMedia($id, $StyleProfileName, $StyleProfileDescription, $StyleProfileTargetCategoryID);'
        );
        $this->setConfigurationFormField(
            $form,
            'StyleProfileImportFile',
            'onChange',
            'if ($StyleProfileImportFile !== "") { echo IPSVIEWA_ImportSharedStyleProfileFile($id, $StyleProfileImportFile); }'
        );
        $this->setConfigurationFormField(
            $form,
            'ImportStyleProfileMediaButton',
            'onClick',
            'echo IPSVIEWA_ImportSharedStyleProfileMedia($id, $StyleProfileImportMediaID);'
        );
        $this->setConfigurationFormField(
            $form,
            'ExportColorPaletteJsonButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_ExportColorPaletteJson($id, $ColorPaletteName, $ColorPaletteDescription);'
        );
        $this->setConfigurationFormField(
            $form,
            'SaveColorPaletteMediaButton',
            'onClick',
            $captureScript
                . ' echo IPSVIEWA_SaveColorPaletteMedia($id, $ColorPaletteName, $ColorPaletteDescription, $ColorPaletteTargetCategoryID);'
        );
        $this->setConfigurationFormField(
            $form,
            'ColorPaletteImportFile',
            'onChange',
            'if ($ColorPaletteImportFile !== "") { echo IPSVIEWA_ImportColorPaletteFile($id, $ColorPaletteImportFile); }'
        );
        $this->setConfigurationFormField(
            $form,
            'ImportColorPaletteMediaButton',
            'onClick',
            'echo IPSVIEWA_ImportColorPaletteMedia($id, $ColorPaletteImportMediaID);'
        );

        $this->IPSViewAssistantSynchronizeLegacyStyleForm($form);
        $this->IPSViewAssistantApplySharedPreviewToForm($form);
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     * @param array $sharedItems Shared-style form items inserted into the design popup.
     * @param string $captureScript Form script that captures shared-style field changes.
     *
     * @return bool True when the design popup was found and replaced.
     */
    private function IPSViewAssistantReplaceDesignPopup(array &$items, array $sharedItems, string $captureScript): bool
    {
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['type'] ?? null) === 'PopupButton' && ($item['caption'] ?? null) === 'Design details') {
                $item['name'] = 'ThemeDetailsPopup';
                $legacyItems = is_array($item['popup']['items'] ?? null) ? $item['popup']['items'] : [];
                foreach ($legacyItems as &$legacyItem) {
                    if (is_array($legacyItem)) {
                        $legacyItem['visible'] = false;
                    }
                }
                unset($legacyItem);

                $item['popup']['items'] = [
                    ...$sharedItems,
                    [
                        'type'    => 'Button',
                        'name'    => 'ApplySharedStyleButton',
                        'caption' => $this->Translate('Preview'),
                        'onClick' => $captureScript
                    ],
                    ...$legacyItems
                ];

                return true;
            }

            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])
                    && $this->IPSViewAssistantReplaceDesignPopup($item[$key], $sharedItems, $captureScript)) {
                    return true;
                }
            }
            if (isset($item['popup']['items']) && is_array($item['popup']['items'])
                && $this->IPSViewAssistantReplaceDesignPopup($item['popup']['items'], $sharedItems, $captureScript)) {
                return true;
            }
        }
        unset($item);

        return false;
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     * @param array $fieldNames Shared-style field names handled by the form.
     * @param string $captureScript Form script that captures shared-style field changes.
     */
    private function IPSViewAssistantAttachSharedStyleOnChange(
        array &$items,
        array $fieldNames,
        string $captureScript
    ): void {
        $fields = array_flip($fieldNames);
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }

            $name = is_string($item['name'] ?? null) ? $item['name'] : '';
            if ($name !== '' && isset($fields[$name]) && ($item['enabled'] ?? true) !== false) {
                $type = (string) ($item['type'] ?? '');
                if (in_array($type, ['Select', 'SelectColor', 'SelectMedia', 'CheckBox', 'NumberSpinner'], true)) {
                    $existing = $item['onChange'] ?? '';
                    if (is_array($existing)) {
                        $existing[] = $captureScript;
                        $item['onChange'] = $existing;
                    } else {
                        $existing = trim((string) $existing);
                        $item['onChange'] = ($existing === '' ? '' : $existing . ' ') . $captureScript;
                    }
                }
            }

            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])) {
                    $this->IPSViewAssistantAttachSharedStyleOnChange($item[$key], $fieldNames, $captureScript);
                }
            }
        }
        unset($item);
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     */
    private function IPSViewAssistantAttachNativeListOnEdit(array &$items): void
    {
        $nativeProperties = array_flip($this->IPSViewStyleNativeOverrideProperties());
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }

            $name = is_string($item['name'] ?? null) ? $item['name'] : '';
            if (($item['type'] ?? null) === 'List' && isset($nativeProperties[$name])) {
                $item['onEdit'] = sprintf(
                    'IPSVIEWA_ApplySharedNativeColorOverride($id, %s, json_encode($%s, JSON_UNESCAPED_SLASHES));',
                    var_export($name, true),
                    $name
                );
            }

            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])) {
                    $this->IPSViewAssistantAttachNativeListOnEdit($item[$key]);
                }
            }
        }
        unset($item);
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     */
    private function IPSViewAssistantAppendReloadToSharedButtons(array &$items): void
    {
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['type'] ?? null) === 'Button' && array_key_exists('onClick', $item)) {
                $suffix = 'IPSVIEWA_ClearSharedStyleProfileBaseline($id); IPSVIEWA_ReloadSharedStyleForm($id);';
                if (is_array($item['onClick'])) {
                    $item['onClick'][] = $suffix;
                } else {
                    $item['onClick'] = trim((string) $item['onClick']) . ' ' . $suffix;
                }
            }
            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])) {
                    $this->IPSViewAssistantAppendReloadToSharedButtons($item[$key]);
                }
            }
        }
        unset($item);
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     *
     * @return array Shared-style field names found in the form.
     */
    private function IPSViewAssistantSharedStyleFieldNames(array $items): array
    {
        $names = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = is_string($item['name'] ?? null) ? trim($item['name']) : '';
            $type = is_string($item['type'] ?? null) ? $item['type'] : '';
            $inputTypes = ['Select', 'SelectColor', 'SelectMedia', 'CheckBox', 'NumberSpinner'];
            if (
                $name !== ''
                && str_starts_with($name, 'IPSViewStyle')
                && in_array($type, $inputTypes, true)
            ) {
                $names[$name] = true;
            }
            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])) {
                    foreach ($this->IPSViewAssistantSharedStyleFieldNames($item[$key]) as $nestedName) {
                        $names[$nestedName] = true;
                    }
                }
            }
        }

        return array_keys($names);
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     * @param array $fieldNames Shared-style field names handled by the form.
     */
    private function IPSViewAssistantPopulateSharedStyleValues(array &$items, array $fieldNames): void
    {
        $fields = array_flip($fieldNames);
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }

            $name = is_string($item['name'] ?? null) ? $item['name'] : '';
            $type = is_string($item['type'] ?? null) ? $item['type'] : '';
            if (
                $name !== ''
                && isset($fields[$name])
                && $type !== 'List'
                && ($item['enabled'] ?? true) !== false
            ) {
                $item['value'] = $this->IPSViewAssistantReadSharedProperty($name);
            }

            foreach (['items', 'elements', 'actions'] as $key) {
                if (isset($item[$key]) && is_array($item[$key])) {
                    $this->IPSViewAssistantPopulateSharedStyleValues($item[$key], $fieldNames);
                }
            }
        }
        unset($item);
    }

    /**
     * @param array $fieldNames Shared-style field names handled by the form.
     *
     * @return string JavaScript used to capture shared-style form values.
     */
    private function IPSViewAssistantSharedStyleCaptureScript(array $fieldNames): string
    {
        $values = [];
        foreach ($fieldNames as $fieldName) {
            $values[] = var_export($fieldName, true) . ' => $' . $fieldName;
        }

        return 'IPSVIEWA_ApplySharedStyleConfiguration($id, json_encode(['
            . implode(', ', $values)
            . '], JSON_UNESCAPED_SLASHES));';
    }

    /**
     * Resolves the inherited semantic color for one native IPSView field.
     *
     * @param string $field Native IPSView color-field name.
     *
     * @return int Inherited native color encoded as a Symcon integer color.
     */
    private function IPSViewAssistantNativeInheritedColor(string $field): int
    {
        $theme = $this->IPSViewStyleNativeTheme();
        if (!isset($theme['colors'][$field])) {
            throw new RuntimeException('The inherited IPSView color could not be resolved.');
        }

        $hex = IPSViewControlThemeHelper::colorToHex($theme['colors'][$field]);

        return (int) hexdec(substr($hex, 1));
    }

    /**
     * Refreshes one native IPSView color-family list in the open form.
     *
     * @param string $propertyName Shared-style or native override property name.
     */
    private function IPSViewAssistantRefreshNativeList(string $propertyName): void
    {
        $values = $this->IPSViewAssistantFindNativeListValues(
            $this->IPSViewStyleFormItems('240px'),
            $propertyName
        );
        if ($values !== null) {
            $this->UpdateFormField(
                $propertyName,
                'values',
                json_encode($values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            );
        }
    }

    /**
     * @param array $items Configuration-form item list modified in place.
     * @param string $propertyName Shared-style or native override property name.
     *
     * @return ?array Native color list rows or null when the list is unavailable.
     */
    private function IPSViewAssistantFindNativeListValues(array $items, string $propertyName): ?array
    {
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (
                ($item['type'] ?? null) === 'List'
                && ($item['name'] ?? null) === $propertyName
                && is_array($item['values'] ?? null)
            ) {
                return $item['values'];
            }

            foreach (['items', 'elements', 'actions'] as $key) {
                if (!isset($item[$key]) || !is_array($item[$key])) {
                    continue;
                }
                $values = $this->IPSViewAssistantFindNativeListValues($item[$key], $propertyName);
                if ($values !== null) {
                    return $values;
                }
            }
        }

        return null;
    }

    /**
     * @param string $propertyName Shared-style or native override property name.
     * @param string $family Native IPSView color-family identifier.
     *
     * @return array Editable native override rows for the requested family.
     */
    private function IPSViewAssistantNativeOverrideRows(string $propertyName, string $family): array
    {
        $json = trim($this->ReadPropertyString($propertyName));
        if ($json === '') {
            return [];
        }

        try {
            $rows = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
        if (!is_array($rows)) {
            return [];
        }

        $stored = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $field = isset($row['Field']) && is_string($row['Field']) ? trim($row['Field']) : '';
            $definition = $field === '' ? null : IPSViewControlThemeHelper::definition($field);
            if ($definition === null
                || ($definition['family'] ?? null) !== $family
                || !$this->IPSViewAssistantNativeOverrideEnabled($row['Override'] ?? false)) {
                continue;
            }

            $stored[$field] = [
                'Override' => true,
                'Field'    => $field,
                'Color'    => max(0, min(0xFFFFFF, (int) ($row['Color'] ?? 0)))
            ];
        }

        return $stored;
    }

    /**
     * Normalizes the form value that controls a native color override.
     *
     * @param mixed $value New property value.
     *
     * @return bool Normalized native override state.
     */
    private function IPSViewAssistantNativeOverrideEnabled(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * Normalizes one shared-style property received from the form.
     *
     * @param string $propertyName Shared-style or native override property name.
     * @param mixed $value New property value.
     *
     * @return mixed Normalized shared-style property value.
     */
    private function IPSViewAssistantNormalizeSharedProperty(string $propertyName, mixed $value): mixed
    {
        if (str_starts_with($propertyName, 'IPSViewStyleNative')) {
            if (is_string($value)) {
                try {
                    $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    return '[]';
                }

                return is_array($decoded)
                    ? json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                    : '[]';
            }

            return is_array($value)
                ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                : '[]';
        }
        if (in_array($propertyName, self::IPSVIEW_ASSISTANT_SHARED_STRING_PROPERTIES, true)) {
            return (string) $value;
        }
        if (in_array($propertyName, self::IPSVIEW_ASSISTANT_SHARED_FLOAT_PROPERTIES, true)) {
            return (float) $value;
        }
        if ($propertyName === 'IPSViewStyleTransparentBackground') {
            return (bool) $value;
        }

        return (int) $value;
    }

    /**
     * Reads one shared-style property using its registered Symcon type.
     *
     * @param string $propertyName Shared-style or native override property name.
     *
     * @return mixed Current shared-style property value.
     */
    private function IPSViewAssistantReadSharedProperty(string $propertyName): mixed
    {
        if (str_starts_with($propertyName, 'IPSViewStyleNative')
            || in_array($propertyName, self::IPSVIEW_ASSISTANT_SHARED_STRING_PROPERTIES, true)) {
            return $this->ReadPropertyString($propertyName);
        }
        if (in_array($propertyName, self::IPSVIEW_ASSISTANT_SHARED_FLOAT_PROPERTIES, true)) {
            return $this->ReadPropertyFloat($propertyName);
        }
        if ($propertyName === 'IPSViewStyleTransparentBackground') {
            return $this->ReadPropertyBoolean($propertyName);
        }

        return $this->ReadPropertyInteger($propertyName);
    }

    /**
     * @param array $form Configuration-form structure modified in place.
     */
    private function IPSViewAssistantSynchronizeLegacyStyleForm(array &$form): void
    {
        $style = $this->IPSViewResolvedStyle();
        $palette = IPSViewSharedStyleAdapter::palette($style);
        $effects = IPSViewSharedStyleAdapter::effects($style, $this->IPSViewAssistantActiveGradientStrength());
        $appearance = IPSViewSharedStyleAdapter::appearance($style);

        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->setConfigurationFormField($form, $field, 'value', IPSViewTheme::toFormColor($palette[$role]));
        }
        foreach ($this->IPSViewAssistantLegacyEffectFields($effects) as $field => $value) {
            $this->setConfigurationFormField($form, $field, 'value', $value);
        }
        foreach ($this->IPSViewAssistantLegacyAppearanceFields($appearance) as $field => $value) {
            $this->setConfigurationFormField($form, $field, 'value', $value);
        }
    }

    /**
     * @param array $form Configuration-form structure modified in place.
     */
    private function IPSViewAssistantApplySharedPreviewToForm(array &$form): void
    {
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $style = $snapshot['style'];
        $this->setConfigurationFormField(
            $form,
            'ThemePreview',
            'image',
            IPSViewThemePreview::createDataUri(
                IPSViewSharedStyleAdapter::previewPalette($style),
                IPSViewSharedStyleAdapter::previewEffects($style, $snapshot['gradientStrength']),
                IPSViewSharedStyleAdapter::appearance($style),
                $this->backgroundSettings(),
                $this->previewStartGrid(),
                $snapshot['transparentBackground'],
                $this->IPSViewAssistantNativePreviewColors($snapshot['nativeTheme'])
            )
        );
    }

    /**
     * Converts the resolved native IPSView theme to the hexadecimal colors consumed by the preview.
     *
     * @param array<string,mixed> $nativeTheme Resolved native IPSView theme document.
     *
     * @return array<string,string> Resolved native preview colors keyed by native field name.
     */
    private function IPSViewAssistantNativePreviewColors(array $nativeTheme): array
    {
        $nativeColors = [];
        foreach ($nativeTheme['colors'] ?? [] as $field => $color) {
            if (!is_string($field)) {
                continue;
            }

            $nativeColors[$field] = IPSViewControlThemeHelper::colorToHex($color);
        }

        return $nativeColors;
    }

    /**
     * @param array $palette Resolved semantic preview palette.
     * @param array $effects Resolved or JSON-encoded effect settings.
     * @param array $appearance Resolved or JSON-encoded typography and shape settings.
     */
    private function IPSViewAssistantSynchronizeLegacyStyleFields(
        array $palette,
        array $effects,
        array $appearance
    ): void {
        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->UpdateFormField($field, 'value', IPSViewTheme::toFormColor($palette[$role]));
        }
        foreach ($this->IPSViewAssistantLegacyEffectFields($effects) as $field => $value) {
            $this->UpdateFormField($field, 'value', $value);
        }
        foreach ($this->IPSViewAssistantLegacyAppearanceFields($appearance) as $field => $value) {
            $this->UpdateFormField($field, 'value', $value);
        }
        $this->UpdateFormField('Theme', 'value', IPSViewTheme::THEME_CUSTOM);
    }

    /**
     * @param array $effects Resolved or JSON-encoded effect settings.
     *
     * @return array Legacy Assistant effect fields derived from the shared style.
     */
    private function IPSViewAssistantLegacyEffectFields(array $effects): array
    {
        return [
            'ShadowStyle'         => $effects['shadowStyle'],
            'TransparencyMode'    => $effects['transparencyMode'],
            'TransparencyPercent' => $effects['transparencyPercent'],
            'GradientStyle'       => $effects['gradientStyle'],
            'GradientDirection'   => $effects['gradientDirection']
        ];
    }

    /**
     * @param array $appearance Resolved or JSON-encoded typography and shape settings.
     *
     * @return array Legacy Assistant appearance fields derived from the shared style.
     */
    private function IPSViewAssistantLegacyAppearanceFields(array $appearance): array
    {
        return [
            'TypographyStyle'    => $appearance['typographyStyle'],
            'FontFamilyMode'     => $appearance['fontFamilyMode'],
            'CustomFontFamily'   => $appearance['customFontFamily'],
            'CustomFontSize'     => $appearance['customFontSize'],
            'FontBoldMode'       => $appearance['fontBoldMode'],
            'FontItalicMode'     => $appearance['fontItalicMode'],
            'FontUnderlineMode'  => $appearance['fontUnderlineMode'],
            'CornerStyle'        => $appearance['cornerStyle'],
            'CustomCornerRadius' => $appearance['customCornerRadius'],
            'BorderStyle'        => $appearance['borderStyle'],
            'CustomBorderWidth'  => $appearance['customBorderWidth']
        ];
    }

    /**
     * @return int Active shared gradient strength from 0 to 80.
     */
    private function IPSViewAssistantActiveGradientStrength(): int
    {
        $strength = $this->ReadPropertyInteger('IPSViewStyleGradientStrength');
        if ($this->ReadPropertyInteger('IPSViewStyleSource') !== self::IPSVIEW_STYLE_SOURCE_PROFILE) {
            return max(0, min(80, $strength));
        }

        $json = trim($this->ReadIPSViewStyleProfileMediaContent());
        if ($json === '') {
            return max(0, min(80, $strength));
        }

        try {
            $profile = IPSViewStyleProfileHelper::decode($json);
        } catch (Throwable) {
            return max(0, min(80, $strength));
        }

        return max(0, min(80, (int) ($profile['style']['GradientStrength'] ?? $strength)));
    }

    /**
     *     style: array<string,string|float>,
     *     nativeTheme: array<string,mixed>,
     *     gradientStrength: int,
     *     transparentBackground: bool,
     *     preserveNativeColorDetails: bool
     * }
     *
     * @return array{ Complete resolved shared-style snapshot.
     */
    private function IPSViewAssistantSharedStyleSnapshot(): array
    {
        $nativeTheme = $this->IPSViewStyleNativeTheme();
        $preserveNativeColorDetails = false;

        if ($this->ReadPropertyInteger('IPSViewStyleSource') === self::IPSVIEW_STYLE_SOURCE_MEDIA) {
            $mediaDocument = trim($this->ReadIPSViewStyleMediaContent());
            if ($mediaDocument !== '') {
                try {
                    $decoded = json_decode($mediaDocument, false, 512, JSON_THROW_ON_ERROR);
                    if ($decoded instanceof stdClass) {
                        $nativeTheme = IPSViewControlThemeHelper::extract($decoded);
                        $preserveNativeColorDetails = true;
                    }
                } catch (JsonException) {
                    $preserveNativeColorDetails = false;
                }
            }
        }

        return [
            'style'                      => $this->IPSViewResolvedStyle(),
            'nativeTheme'                => $nativeTheme,
            'gradientStrength'           => max(0, min(80, $this->IPSViewAssistantActiveGradientStrength())),
            'transparentBackground'      => $this->ReadPropertyBoolean('IPSViewStyleTransparentBackground'),
            'preserveNativeColorDetails' => $preserveNativeColorDetails
        ];
    }

    /**
     * @param array{
     *     style: array<string,string|float>,
     *     nativeTheme: array<string,mixed>,
     *     gradientStrength: int,
     *     transparentBackground: bool,
     *     preserveNativeColorDetails: bool
     * } $snapshot
     *
     * @param int $mediaID Symcon media object ID.
     * @param int $scope Scope used when applying design or background changes.
     * @param array $snapshot Resolved shared-style snapshot.
     * @param bool $createMissing Whether absent native fields may be created.
     */
    private function IPSViewAssistantFinalizeSharedStyleMedia(
        int $mediaID,
        int $scope,
        array $snapshot,
        bool $createMissing
    ): void {
        if ($mediaID <= 0 || !IPS_MediaExists($mediaID)) {
            throw new RuntimeException('The styled IPSView media object is unavailable.');
        }

        $encoded = IPS_GetMediaContent($mediaID);
        $json = is_string($encoded) ? base64_decode($encoded, true) : false;
        if (!is_string($json) || $json === '') {
            throw new RuntimeException('The styled IPSView media content could not be read.');
        }

        try {
            $document = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The styled IPSView media content contains invalid JSON.', 0, $exception);
        }
        if (!$document instanceof stdClass) {
            throw new RuntimeException('The styled IPSView media content must contain a JSON object.');
        }

        IPSViewSharedStyleAdapter::applyToDocument(
            $document,
            $snapshot['nativeTheme'],
            $snapshot['style'],
            $scope,
            $snapshot['gradientStrength'],
            $snapshot['transparentBackground'],
            $createMissing,
            $snapshot['preserveNativeColorDetails']
        );

        $newJson = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (!IPS_SetMediaContent($mediaID, base64_encode($newJson))) {
            throw new RuntimeException('The shared IPSView style could not be written to the media object.');
        }
        IPS_SendMediaEvent($mediaID);
    }

    /**
     * Resolves all semantic and native colors of the currently active shared style.
     *
     * @return array{semanticColors:array<string,string>,nativeColors:array<string,string>} Complete palette data.
     */
    private function IPSViewAssistantColorPaletteData(): array
    {
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $nativeTheme = $this->IPSViewStyleNativeTheme();

        foreach ($snapshot['nativeTheme']['colors'] ?? [] as $field => $color) {
            if (is_string($field) && IPSViewControlThemeHelper::isKnownField($field)) {
                $nativeTheme['colors'][$field] = IPSViewControlThemeHelper::normalizeColor($color);
            }
        }

        $semanticColors = IPSViewSharedStyleAdapter::semanticColors($snapshot['style']);

        $nativeColors = [];
        foreach (IPSViewControlThemeHelper::fields() as $field) {
            if (!isset($nativeTheme['colors'][$field])) {
                throw new RuntimeException('The native IPSView color ' . $field . ' could not be resolved.');
            }
            $nativeColors[$field] = IPSViewControlThemeHelper::colorToHex($nativeTheme['colors'][$field]);
        }

        return [
            'semanticColors' => $semanticColors,
            'nativeColors'   => $nativeColors,
        ];
    }

    /**
     * Applies one validated Color Palette V1 while preserving all non-color shared-style settings.
     *
     * @param array<string,mixed> $palette Canonical Color Palette V1 document.
     *
     * @return string Human-readable import result.
     */
    private function IPSViewAssistantApplyColorPalette(array $palette): string
    {
        $palette = IPSViewColorPaletteExchange::normalize($palette);
        $snapshot = $this->IPSViewAssistantSharedStyleSnapshot();
        $nativeTheme = $this->IPSViewStyleNativeTheme();
        foreach ($snapshot['nativeTheme']['colors'] ?? [] as $field => $color) {
            if (is_string($field) && IPSViewControlThemeHelper::isKnownField($field)) {
                $nativeTheme['colors'][$field] = IPSViewControlThemeHelper::normalizeColor($color);
            }
        }

        $profileStyle = IPSViewSharedStyleAdapter::profileStyle(
            $snapshot['style'],
            $nativeTheme,
            $snapshot['gradientStrength'],
            $snapshot['transparentBackground']
        );
        foreach ($palette['semanticColors'] as $field => $color) {
            $profileStyle[$field] = $color;
        }

        $source = $this->ReadPropertyInteger('IPSViewStyleSource');
        if ($source === self::IPSVIEW_STYLE_SOURCE_CUSTOM) {
            $properties = IPSViewSharedStyleAdapter::colorPropertyValuesFromProfileStyle($profileStyle);
        } else {
            $properties = IPSViewSharedStyleAdapter::propertyValuesFromProfileStyle($profileStyle);
            $properties['IPSViewStyleTransparentBackground'] = $snapshot['transparentBackground'];
        }
        foreach ($properties as $propertyName => $value) {
            IPS_SetProperty($this->InstanceID, $propertyName, $value);
        }

        $nativeOverrides = IPSViewColorPaletteExchange::nativeOverrides($palette);
        foreach ($this->IPSViewStyleNativeOverrideProperties() as $family => $propertyName) {
            $rows = [];
            foreach (IPSViewControlThemeHelper::fieldsForFamily($family) as $field) {
                if (!isset($nativeOverrides[$field])) {
                    continue;
                }

                $rows[] = [
                    'Override' => true,
                    'Field'    => $field,
                    'Color'    => (int) hexdec(substr($nativeOverrides[$field], 1)),
                ];
            }
            IPS_SetProperty(
                $this->InstanceID,
                $propertyName,
                json_encode(
                    $rows,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                )
            );
        }

        $this->clearStyleProfileImportState();
        IPS_ApplyChanges($this->InstanceID);

        $message = sprintf(
            $this->Translate('Color palette "%s" was imported successfully.'),
            $palette['name']
        );
        $this->WriteAttributeString(self::ATTRIBUTE_COLOR_PALETTE_STATUS, $message);
        $this->ReloadForm();

        return $message;
    }

    /**
     * Writes or safely updates one Color Palette V1 Symcon document media object.
     *
     * @param string $name Palette or target object name.
     * @param int $targetCategoryID Symcon category that receives the target object.
     * @param string $json Encoded Color Palette V1 JSON document.
     *
     * @return int Media object ID of the saved palette.
     */
    private function IPSViewAssistantWriteColorPaletteMedia(
        string $name,
        int $targetCategoryID,
        string $json
    ): int {
        $targetCategoryID = $targetCategoryID === 1 ? 0 : $targetCategoryID;
        $this->IPSViewAssistantValidateColorPaletteTargetCategory($targetCategoryID);
        IPSViewColorPaletteExchange::importJson($json);

        $existingID = IPS_GetObjectIDByName($name, $targetCategoryID);
        if (is_int($existingID) && $existingID > 0) {
            if (!IPS_MediaExists($existingID)) {
                throw new RuntimeException(
                    sprintf(
                        $this->Translate('An object named "%s" already exists in the target category and is not a media object.'),
                        $name
                    )
                );
            }

            $media = IPS_GetMedia($existingID);
            $documentType = defined('MEDIATYPE_DOCUMENT') ? MEDIATYPE_DOCUMENT : 5;
            if ((int) ($media['MediaType'] ?? -1) !== $documentType) {
                throw new RuntimeException(
                    sprintf($this->Translate('The existing media object named "%s" is not a document.'), $name)
                );
            }

            $previousContent = IPS_GetMediaContent($existingID);
            $previousJson = is_string($previousContent) ? base64_decode($previousContent, true) : false;
            try {
                if (!is_string($previousJson) || trim($previousJson) === '') {
                    throw new RuntimeException('empty');
                }
                IPSViewColorPaletteExchange::importJson($previousJson);
            } catch (Throwable) {
                throw new RuntimeException(
                    sprintf(
                        $this->Translate(
                            'The existing document named "%s" is not a valid Color Palette V1 and will not be overwritten.'
                        ),
                        $name
                    )
                );
            }

            try {
                if (!IPS_SetMediaContent($existingID, base64_encode($json))) {
                    throw new RuntimeException($this->Translate('The Color Palette media content could not be written.'));
                }
                IPS_SendMediaEvent($existingID);

                return $existingID;
            } catch (Throwable $exception) {
                try {
                    IPS_SetMediaContent($existingID, $previousContent);
                    IPS_SendMediaEvent($existingID);
                } catch (Throwable) {
                }

                throw $exception;
            }
        }

        $documentType = defined('MEDIATYPE_DOCUMENT') ? MEDIATYPE_DOCUMENT : 5;
        $mediaID = IPS_CreateMedia($documentType);
        try {
            IPS_SetName($mediaID, $name);
            IPS_SetParent($mediaID, $targetCategoryID);
            $mediaFile = IPS_GetKernelDir()
                . 'media'
                . DIRECTORY_SEPARATOR
                . $mediaID
                . '.ipsview-palette.json';
            if (!IPS_SetMediaFile($mediaID, $mediaFile, false)) {
                throw new RuntimeException($this->Translate('The Color Palette media file could not be assigned.'));
            }
            if (!IPS_SetMediaContent($mediaID, base64_encode($json))) {
                throw new RuntimeException($this->Translate('The Color Palette media content could not be written.'));
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
     * Validates one palette-media target category.
     *
     * @param int $targetCategoryID Symcon category ID or zero for the root.
     */
    private function IPSViewAssistantValidateColorPaletteTargetCategory(int $targetCategoryID): void
    {
        if ($targetCategoryID === 0) {
            return;
        }
        if (!IPS_ObjectExists($targetCategoryID)) {
            throw new RuntimeException($this->Translate('The selected Color Palette target category does not exist.'));
        }

        $object = IPS_GetObject($targetCategoryID);
        if ((int) ($object['ObjectType'] ?? -1) !== 0) {
            throw new RuntimeException($this->Translate('The selected Color Palette target object is not a category.'));
        }
    }

    /**
     * Checks whether a View creation result represents success.
     *
     * @param string $result Human-readable operation result.
     * @param string $viewName Name of the target IPSView media object.
     * @param int $mediaID Symcon media object ID.
     * @param bool $wasOverwritten Whether the operation overwrote an existing View.
     *
     * @return bool True when the creation result represents success.
     */
    private function IPSViewAssistantIsCreateSuccess(
        string $result,
        string $viewName,
        int $mediaID,
        bool $wasOverwritten
    ): bool {
        $expected = $wasOverwritten
            ? sprintf(
                $this->Translate('The IPSView "%s" was overwritten successfully while retaining object ID %d.'),
                trim($viewName),
                $mediaID
            )
            : sprintf(
                $this->Translate('The IPSView "%s" was created successfully with object ID %d.'),
                trim($viewName),
                $mediaID
            );

        return $result === $expected;
    }

    /**
     * Checks whether a styled-copy result represents success.
     *
     * @param string $result Human-readable operation result.
     * @param string $copyName Name of the managed styled copy.
     * @param int $mediaID Symcon media object ID.
     * @param bool $updated Whether an existing managed copy was updated.
     *
     * @return bool True when the copy result represents success.
     */
    private function IPSViewAssistantIsCopySuccess(
        string $result,
        string $copyName,
        int $mediaID,
        bool $updated
    ): bool {
        $expected = $updated
            ? sprintf(
                $this->Translate('The styled IPSView copy "%s" was updated successfully with object ID %d.'),
                $copyName,
                $mediaID
            )
            : sprintf(
                $this->Translate('The styled IPSView copy "%s" was created successfully with object ID %d.'),
                $copyName,
                $mediaID
            );

        return str_starts_with($result, $expected);
    }

    /**
     * @param array $value New property value.
     *
     * @return string JSON-encoded action value.
     */
    private function IPSViewAssistantEncodeActionValue(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Checks whether a Style Profile V1 import result represents success.
     *
     * @param string $result Human-readable operation result.
     *
     * @return bool True when the import result represents success.
     */
    private function IPSViewAssistantWasStyleProfileImportSuccessful(string $result): bool
    {
        $state = $this->readStyleProfileImportState();
        if ($state === null || !is_string($state['profile']['name'] ?? null)) {
            return false;
        }

        return $result === sprintf(
            $this->Translate('Style profile "%s" was imported successfully.'),
            $state['profile']['name']
        );
    }

    /**
     * Adopts an imported Style Profile V1 as the current shared style baseline.
     */
    private function IPSViewAssistantAdoptImportedStyleProfile(): void
    {
        $state = $this->readStyleProfileImportState();
        if ($state === null || !is_array($state['profile']['style'] ?? null)) {
            return;
        }

        foreach (IPSViewSharedStyleAdapter::propertyValuesFromProfileStyle($state['profile']['style']) as $propertyName => $value) {
            IPS_SetProperty($this->InstanceID, $propertyName, $value);
        }
        foreach ($this->IPSViewStyleNativeOverrideProperties() as $propertyName) {
            IPS_SetProperty($this->InstanceID, $propertyName, '[]');
        }
        IPS_ApplyChanges($this->InstanceID);
        $this->ReloadForm();
    }
}

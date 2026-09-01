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
     * Replaces the visible legacy design editor with the shared style form.
     *
     * @param array<string,mixed> $form
     */
    private function ApplyIPSViewSharedStyleForm(array &$form): void
    {
        $sharedItems = $this->IPSViewStyleFormItems('240px');
        $fieldNames = $this->IPSViewAssistantSharedStyleFieldNames($sharedItems);
        $captureScript = $this->IPSViewAssistantSharedStyleCaptureScript($fieldNames);
        $this->IPSViewAssistantAttachSharedStyleOnChange($sharedItems, $fieldNames, $captureScript);
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

        $this->IPSViewAssistantSynchronizeLegacyStyleForm($form);
        $this->IPSViewAssistantApplySharedPreviewToForm($form);
    }

    /** Persists the displayed shared style controls and refreshes/reloads the form as needed. */
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

        $previousSource = $this->ReadPropertyInteger('IPSViewStyleSource');
        $previousMediaID = $this->ReadPropertyInteger('IPSViewStyleMediaID');
        $previousProfileMediaID = $this->ReadPropertyInteger('IPSViewStyleProfileMediaID');
        $allowed = array_flip($this->IPSViewAssistantSharedStyleFieldNames($this->IPSViewStyleFormItems('240px')));
        $changed = false;

        foreach ($configuration as $propertyName => $value) {
            if (!is_string($propertyName) || !isset($allowed[$propertyName])) {
                continue;
            }

            $normalized = $this->IPSViewAssistantNormalizeSharedProperty($propertyName, $value);
            if ($this->IPSViewAssistantReadSharedProperty($propertyName) !== $normalized) {
                $changed = true;
            }
            IPS_SetProperty($this->InstanceID, $propertyName, $normalized);
        }

        if ($changed) {
            $this->clearStyleProfileImportState();
        }
        IPS_ApplyChanges($this->InstanceID);

        $sourceChanged = $previousSource !== $this->ReadPropertyInteger('IPSViewStyleSource')
            || $previousMediaID !== $this->ReadPropertyInteger('IPSViewStyleMediaID')
            || $previousProfileMediaID !== $this->ReadPropertyInteger('IPSViewStyleProfileMediaID');
        if ($sourceChanged) {
            $this->ReloadForm();

            return;
        }

        $this->RefreshSharedStylePreview();
    }

    /** Clears a lossless imported-profile baseline after the shared editor changes it. */
    public function ClearSharedStyleProfileBaseline(): void
    {
        $this->clearStyleProfileImportState();
    }

    /** Reloads the shared style form after helper-provided actions such as Copy to custom. */
    public function ReloadSharedStyleForm(): void
    {
        $this->ReloadForm();
    }

    /** Refreshes preview and hidden legacy fields from the active shared style. */
    public function RefreshSharedStylePreview(): void
    {
        $style = $this->IPSViewResolvedStyle();
        $palette = IPSViewSharedStyleAdapter::palette($style);
        $effects = IPSViewSharedStyleAdapter::effects($style, $this->IPSViewAssistantActiveGradientStrength());
        $appearance = IPSViewSharedStyleAdapter::appearance($style);
        $preview = IPSViewThemePreview::createDataUri(
            $palette,
            $effects,
            $appearance,
            $this->backgroundSettings(),
            $this->previewStartGrid()
        );

        $this->IPSViewAssistantSynchronizeLegacyStyleFields($palette, $effects, $appearance);
        $this->UpdateFormField('ThemePreview', 'image', $preview);
    }

    /** Creates a new View using the active shared style configuration. */
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

    /** Creates or explicitly overwrites a View using the active shared style configuration. */
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

    /** Creates or updates a styled copy using the active shared style configuration. */
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
        $factory = new IPSViewCopyFactory();
        $targetBefore = $this->findManagedCopy($SourceViewID, $copyName, $TargetCategoryID);
        if ($targetBefore === null) {
            $targetBefore = $factory->findExistingTarget($copyName, $TargetCategoryID);
        }

        $result = $this->CreateStyledCopy(
            $SourceViewID,
            $CopyViewName,
            $TargetCategoryID,
            IPSViewTheme::THEME_CUSTOM,
            $this->IPSViewAssistantEncodeActionValue($palette),
            $Scope,
            $this->IPSViewAssistantEncodeActionValue($effects),
            $this->IPSViewAssistantEncodeActionValue($appearance)
        );
        $targetMediaID = $this->findManagedCopy($SourceViewID, $copyName, $TargetCategoryID);
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

    /** Loads an existing View and exposes it as the shared IPSView-media style source. */
    public function LoadSharedExistingView(int $SourceViewID): void
    {
        if ($SourceViewID <= 0) {
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

    /** Exports the active shared style as a full Style Profile V1 JSON document. */
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

    /** Saves the active shared style as a full Style Profile V1 Symcon document media object. */
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

    /** Imports a Style Profile V1 file and adopts all fields into the shared custom style. */
    public function ImportSharedStyleProfileFile(string $File): string
    {
        $result = $this->ImportStyleProfileFile($File);
        if ($this->IPSViewAssistantWasStyleProfileImportSuccessful($result)) {
            $this->IPSViewAssistantAdoptImportedStyleProfile();
        }

        return $result;
    }

    /** Imports a Style Profile V1 media object and adopts all fields into the shared custom style. */
    public function ImportSharedStyleProfileMedia(int $MediaID): string
    {
        $result = $this->ImportStyleProfileMedia($MediaID);
        if ($this->IPSViewAssistantWasStyleProfileImportSuccessful($result)) {
            $this->IPSViewAssistantAdoptImportedStyleProfile();
        }

        return $result;
    }

    /** @param array<int,array<string,mixed>> $items */
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

    /** @param array<int,array<string,mixed>> $items @param list<string> $fieldNames */
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

    /** @param array<int,array<string,mixed>> $items */
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

    /** @param array<int,array<string,mixed>> $items @return list<string> */
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
            $isEditableList = $type === 'List'
                && array_reduce(
                    is_array($item['columns'] ?? null) ? $item['columns'] : [],
                    static fn (bool $editable, array $column): bool => $editable || isset($column['edit']),
                    false
                );
            if (
                $name !== ''
                && str_starts_with($name, 'IPSViewStyle')
                && (in_array($type, $inputTypes, true) || $isEditableList)
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

    /** @param list<string> $fieldNames */
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

    /** @param array<string,mixed> $form */
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

    /** @param array<string,mixed> $form */
    private function IPSViewAssistantApplySharedPreviewToForm(array &$form): void
    {
        $style = $this->IPSViewResolvedStyle();
        $this->setConfigurationFormField(
            $form,
            'ThemePreview',
            'image',
            IPSViewThemePreview::createDataUri(
                IPSViewSharedStyleAdapter::palette($style),
                IPSViewSharedStyleAdapter::effects($style, $this->IPSViewAssistantActiveGradientStrength()),
                IPSViewSharedStyleAdapter::appearance($style),
                $this->backgroundSettings(),
                $this->previewStartGrid()
            )
        );
    }

    /** @param array<string,string> $palette @param array<string,int> $effects @param array<string,mixed> $appearance */
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

    /** @param array<string,int> $effects @return array<string,int> */
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

    /** @param array<string,mixed> $appearance @return array<string,mixed> */
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

    /** Returns the effective gradient strength, including Style Profile sources. */
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
     * @return array{
     *     style: array<string,string|float>,
     *     nativeTheme: array<string,mixed>,
     *     gradientStrength: int,
     *     transparentBackground: bool,
     *     preserveNativeColorDetails: bool
     * }
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

    /** @param array<string,mixed> $value */
    private function IPSViewAssistantEncodeActionValue(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

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

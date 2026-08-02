<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/ConfigurationFormHelper.php';
require_once __DIR__ . '/../libs/IPSViewTheme.php';
require_once __DIR__ . '/../libs/IPSViewThemePreview.php';
require_once __DIR__ . '/../libs/IPSViewDocument.php';
require_once __DIR__ . '/../libs/IPSViewFactory.php';

use Burki24\IPSViewAssistant\IPSViewFactory;
use Burki24\SymconModuleHelper\ConfigurationFormHelper;
use Burki24\IPSViewAssistant\IPSViewTheme;
use Burki24\IPSViewAssistant\IPSViewThemePreview;

class IPSViewAssistant extends IPSModuleStrict
{
    use ConfigurationFormHelper;
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
            IPSViewThemePreview::createDataUri($palette)
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
        string $ThemePalette = ''
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
                $this->decodePalette($ThemePalette)
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
     * Loads one preset into the semantic color fields and refreshes the preview.
     */
    public function ApplyThemePreset(int $Theme, string $ThemePalette = ''): void
    {
        try {
            $palette = IPSViewTheme::resolvePalette($Theme, $this->decodePalette($ThemePalette));
            $this->UpdateFormField('Theme', 'value', $Theme);
            $this->updateColorFields($palette);
            $this->UpdateFormField('ThemePreview', 'image', IPSViewThemePreview::createDataUri($palette));
        } catch (Throwable $exception) {
            $this->SendDebug('ApplyThemePreset', $exception->getMessage(), 0);
        }
    }

    /**
     * Switches to a custom theme and refreshes the live preview.
     */
    public function UpdateThemePreview(string $ThemePalette): void
    {
        try {
            $palette = IPSViewTheme::resolvePalette(
                IPSViewTheme::THEME_CUSTOM,
                $this->decodePalette($ThemePalette)
            );

            $this->UpdateFormField('Theme', 'value', IPSViewTheme::THEME_CUSTOM);
            $this->UpdateFormField('ThemePreview', 'image', IPSViewThemePreview::createDataUri($palette));
        } catch (Throwable $exception) {
            $this->SendDebug('UpdateThemePreview', $exception->getMessage(), 0);
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
     * @param array<string, string> $palette
     */
    private function updateColorFields(array $palette): void
    {
        foreach (self::FORM_COLOR_FIELDS as $role => $field) {
            $this->UpdateFormField($field, 'value', IPSViewTheme::toFormColor($palette[$role]));
        }
    }
}

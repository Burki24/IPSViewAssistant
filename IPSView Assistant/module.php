<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/IPSViewDocument.php';
require_once __DIR__ . '/../libs/IPSViewFactory.php';

use Burki24\IPSViewAssistant\IPSViewFactory;

class IPSViewAssistant extends IPSModuleStrict
{
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
     * Creates a ready-initialized IPSView media object from the assistant form.
     */
    public function CreateView(
        string $ViewName,
        int $TargetCategoryID,
        int $AspectRatio,
        int $Orientation,
        int $Template,
        string $MainPageName
    ): string {
        try {
            $factory = new IPSViewFactory(__DIR__ . '/../libs/templates');
            $mediaID = $factory->create(
                $ViewName,
                $TargetCategoryID,
                $AspectRatio,
                $Orientation,
                $Template,
                $MainPageName
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
}

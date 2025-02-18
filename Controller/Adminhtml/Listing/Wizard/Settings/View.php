<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Settings;

use M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Model\Settings $settings
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
        $this->settings = $settings;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SETTINGS_IDENTIFIER;
    }

    protected function process(\M2E\OnBuy\Model\Listing $listing)
    {
        if ($this->isNeedSkipStep()) {
            $this->getWizardManager()
                 ->completeStep(StepDeclarationCollectionFactory::STEP_SETTINGS_IDENTIFIER, true);

            return $this->redirectToIndex($this->getWizardManager()->getWizardId());
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Set Product Identifier'));

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Settings\View::class
            ),
        );

        return $this->getResult();
    }

    private function isNeedSkipStep(): bool
    {
        if (!$this->settings->isIdentifierCodeConfigured()) {
            return false;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\CategoryValidation;

class ResetCategoryValidationData extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        $context = null
    ) {
        parent::__construct($context);
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $wizardManager = $this->wizardManagerFactory->createById($this->getWizardIdFromRequest());
        $wizardManager->resetCategoryValidationData();

        return $this->redirectToIndex($wizardManager->getWizardId());
    }
}

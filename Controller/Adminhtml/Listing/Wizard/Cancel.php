<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard;

class Cancel extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();

        $wizardManager = $this->wizardManagerFactory->createById($id);

        $wizardManager->cancel();

        if ($wizardManager->isWizardTypeGeneral()) {
            return $this->_redirect('*/listing/view', ['id' => $wizardManager->getListing()->getId()]);
        }

        if ($wizardManager->isWizardTypeUnmanaged()) {
            return $this->_redirect($this->urlHelper->getGridUrl());
        }

        return $this->_redirect('*/*/index');
    }
}

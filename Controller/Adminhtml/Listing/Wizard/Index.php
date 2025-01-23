<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard;

class Index extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(\M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory)
    {
        parent::__construct();
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->getMessageManager()->addError(__('Cannot access Wizard, Wizard ID is missing.'));

            return $this->_redirect('*/listing/index');
        }

        try {
            $manager = $this->wizardManagerFactory->createById($id);
        } catch (\M2E\OnBuy\Model\Listing\Wizard\Exception\NotFoundException $e) {
            $this->getMessageManager()->addError(__('Wizard not found.'));

            return $this->_redirect('*/listing/index');
        }

        if ($manager->isCompleted()) {
            return $this->_redirect('*/listing/index');
        }

        $currentStep = $manager->getCurrentStep();

        return $this->_redirect($currentStep->getRoute(), ['id' => $id]);
    }
}

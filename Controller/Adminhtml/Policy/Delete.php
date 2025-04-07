<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;
use M2E\OnBuy\Model\Policy\Manager;

class Delete extends AbstractTemplate
{
    private \M2E\OnBuy\Model\Policy\Synchronization\DeleteService $synchronizationDeleteService;
    private \M2E\OnBuy\Model\Policy\SellingFormat\DeleteService $sellingFormatDeleteService;
    private \M2E\OnBuy\Model\Policy\Shipping\DeleteService $shippingDeleteService;
    private \M2E\OnBuy\Model\Policy\Description\DeleteService $descriptionDeleteService;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Synchronization\DeleteService $synchronizationDeleteService,
        \M2E\OnBuy\Model\Policy\SellingFormat\DeleteService $sellingFormatDeleteService,
        \M2E\OnBuy\Model\Policy\Shipping\DeleteService $shippingDeleteService,
        \M2E\OnBuy\Model\Policy\Description\DeleteService $descriptionDeleteService,
        \M2E\OnBuy\Model\Policy\Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->synchronizationDeleteService = $synchronizationDeleteService;
        $this->sellingFormatDeleteService = $sellingFormatDeleteService;
        $this->shippingDeleteService = $shippingDeleteService;
        $this->descriptionDeleteService = $descriptionDeleteService;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');

        $this->isValidNick($nick);

        try {
            if ($nick === Manager::TEMPLATE_SYNCHRONIZATION) {
                $this->synchronizationDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_SELLING_FORMAT) {
                $this->sellingFormatDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_DESCRIPTION) {
                $this->descriptionDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_SHIPPING) {
                $this->shippingDeleteService->process($id);
            }

            $this->messageManager->addSuccess((string)__('Policy was deleted.'));
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        return $this->_redirect('*/*/index');
    }

    private function isValidNick($nick): void
    {
        $allowed = [
            Manager::TEMPLATE_SYNCHRONIZATION,
            Manager::TEMPLATE_SELLING_FORMAT,
            Manager::TEMPLATE_DESCRIPTION,
            Manager::TEMPLATE_SHIPPING,
        ];

        if (!in_array($nick, $allowed)) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Unknown Policy nick ' . $nick);
        }
    }
}

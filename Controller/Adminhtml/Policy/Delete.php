<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;

class Delete extends AbstractTemplate
{
    private \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationRepository;
    private \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingRepository;

    public function __construct(
        \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatRepository,
        \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationRepository,
        \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingRepository,
        \M2E\OnBuy\Model\Policy\Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->shippingRepository = $shippingRepository;
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->synchronizationRepository = $synchronizationRepository;
    }

    public function execute()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        if ($nick === \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->deleteSynchronizationTemplate($id);
        }

        if ($nick === \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->deleteSellingFormatTemplate($id);
        }

        if ($nick === \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING) {
            return $this->deleteShippingTemplate($id);
        }

        throw new \M2E\OnBuy\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function deleteSynchronizationTemplate($id): \Magento\Framework\App\ResponseInterface
    {
        try {
            $template = $this->synchronizationRepository->get((int)$id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->synchronizationRepository->delete($template);

        $this->messageManager
                ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }

    private function deleteSellingFormatTemplate($id)
    {
        try {
            $template = $this->sellingFormatRepository->get((int)$id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->sellingFormatRepository->delete($template);

        $this->messageManager
            ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }

    private function deleteShippingTemplate($id)
    {
        try {
            $template = $this->shippingRepository->get((int)$id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->shippingRepository->delete($template);

        $this->messageManager
            ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }
}

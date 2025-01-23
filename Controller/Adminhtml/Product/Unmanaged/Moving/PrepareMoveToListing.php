<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Moving;

class PrepareMoveToListing extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Helper\Data\Session $sessionHelper;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\OnBuy\Helper\Data\Session $sessionHelper,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->sessionHelper = $sessionHelper;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $selectedProductsIds = (array)$this->getRequest()->getParam('unmanaged_product_ids');

        $sessionKey = \M2E\OnBuy\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $this->sessionHelper->setValue($sessionKey, $selectedProductsIds);

        $row = $this->unmanagedRepository->findSiteIdByUnmanagedIdsAndAccount($selectedProductsIds, $accountId);

        if ($row !== false) {
            $response = [
                'result' => true,
                'siteId' => (int)$row['site_id']
            ];
        } else {
            $response = [
                'result' => false,
                'message' => __('Magento product not found. Please reload the page.'),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}

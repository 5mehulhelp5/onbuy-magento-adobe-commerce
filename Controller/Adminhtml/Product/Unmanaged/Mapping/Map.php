<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Map extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\OnBuy\Model\UnmanagedProduct\MappingService $unmanagedMappingService;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\MappingService $unmanagedMappingService,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->unmanagedMappingService = $unmanagedMappingService;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id'); // Magento
        $productUnmanagedId = $this->getRequest()->getParam('unmanaged_product_id');
        $accountId = $this->getRequest()->getParam('account_id');

        if (!$productId || !$productUnmanagedId) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);

        $magentoCatalogProductModel = $collection->getFirstItem();
        if ($magentoCatalogProductModel->isEmpty()) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
        }

        $productId = $magentoCatalogProductModel->getId();

        $this->unmanagedMappingService->manualMapProduct((int)$productUnmanagedId, (int)$productId);

        return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
    }
}

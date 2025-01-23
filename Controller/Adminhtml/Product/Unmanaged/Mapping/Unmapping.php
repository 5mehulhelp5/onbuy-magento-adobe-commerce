<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Unmapping extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\MappingService $unmanagedMappingService;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\MappingService $unmanagedMappingService,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper,
        $context = null
    ) {
        parent::__construct($context);

        $this->unmanagedMappingService = $unmanagedMappingService;
        $this->unmanagedRepository = $unmanagedRepository;
        $this->massActionFilter = $massActionFilter;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $products = $this->unmanagedRepository->findForUnmappingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );

        if (empty($products)) {
            return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
        }

        foreach ($products as $product) {
            $this->unmanagedMappingService->unmapProduct($product);
        }

        return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
    }
}

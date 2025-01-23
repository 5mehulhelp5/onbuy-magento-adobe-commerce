<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Mapping;

class AutoMap extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\MappingService $mappingService;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\MappingService $mappingService,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper,
        $context = null
    ) {
        parent::__construct($context);

        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->massActionFilter = $massActionFilter;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $products = $this->unmanagedRepository->findForAutoMappingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );

        if (empty($products)) {
            $this->getMessageManager()->addErrorMessage('You should select one or more Products');

            return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
        }

        if (!$this->mappingService->autoMapUnmanagedProducts($products)) {
            $this->getMessageManager()->addErrorMessage(
                'Some Items were not linked. Please edit Product Linking Settings under Configuration > Account > Unmanaged Listings or try to link manually.'
            );

            return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
        }

        return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
    }
}

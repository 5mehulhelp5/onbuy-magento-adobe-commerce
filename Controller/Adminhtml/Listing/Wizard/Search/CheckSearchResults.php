<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Search;

class CheckSearchResults extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;
    private \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelProductManager;
    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardProductRepository;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelProductManager,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardProductRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct();
        $this->jsonResultFactory = $jsonResultFactory;
        $this->searchChannelProductManager = $searchChannelProductManager;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->wizardProductRepository = $wizardProductRepository;
    }

    public function execute()
    {
        $wizardId = (int)$this->getRequest()->getParam('id');
        $manager = $this->wizardManagerFactory->createById($wizardId);

        $countProductsForCreate = $this->wizardProductRepository->getCountProductsWithoutChannelId($wizardId);

        $newProductPopup = $this->getLayout()
                                ->createBlock(
                                    \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Product\NewProductPopup::class,
                                );

        return $this->jsonResultFactory
            ->create()
            ->setData(
                [
                    'is_search_completed' => $this->searchChannelProductManager->isAllFound($manager),
                    'count_products_for_create' => $countProductsForCreate,
                    'popupHtml' => $newProductPopup->toHtml(),
                ]
            );
    }
}

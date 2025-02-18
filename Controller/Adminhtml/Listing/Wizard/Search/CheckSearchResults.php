<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Search;

class CheckSearchResults extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;
    private \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelProductManager;
    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelProductManager,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct();
        $this->jsonResultFactory = $jsonResultFactory;
        $this->searchChannelProductManager = $searchChannelProductManager;
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $wizardId = (int)$this->getRequest()->getParam('id');
        $manager = $this->wizardManagerFactory->createById($wizardId);

        return $this->jsonResultFactory
            ->create()
            ->setData(
                [
                    'is_search_completed' => $this->searchChannelProductManager->isAllFound($manager),
                ]
            );
    }
}

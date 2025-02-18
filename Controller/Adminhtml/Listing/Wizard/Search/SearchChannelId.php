<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Search;

class SearchChannelId extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing implements
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchOnBuyProductId;
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;
    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchOnBuyProductId,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();
        $this->searchOnBuyProductId = $searchOnBuyProductId;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $findResult = $this->searchOnBuyProductId->find($manager);
        if ($findResult === null) {
            return $this->createJsonResponse(true, 0, 0);
        }

        return $this->createJsonResponse(
            $findResult->isCompleted(),
            $findResult->getTotalProductCount(),
            $findResult->getProcessedProductCount(),
        );
    }

    private function createJsonResponse(
        bool $isCompleted,
        int $totalItems,
        int $processedItems
    ): \Magento\Framework\Controller\Result\Json {
        return $this->jsonResultFactory
            ->create()
            ->setData(
                [
                    'is_complete' => $isCompleted,
                    'total_items' => $totalItems,
                    'processed_items' => $processedItems,
                ],
            );
    }
}

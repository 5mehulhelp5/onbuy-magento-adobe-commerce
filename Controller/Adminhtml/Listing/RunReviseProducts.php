<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing;

class RunReviseProducts extends \M2E\OnBuy\Controller\Adminhtml\Listing\AbstractAction
{
    private \M2E\OnBuy\Controller\Adminhtml\Product\Action\ActionService $actionService;

    public function __construct(
        \M2E\OnBuy\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\OnBuy\Model\Product\Repository $productRepository
    ) {
        parent::__construct($productRepository);

        $this->actionService = $actionService;
    }

    public function execute()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $products = $this->oldGridLoadProducts($listingsProductsIds);

        if ($this->isRealtimeProcessFromOldGrid()) {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->runRevise($products);
        } else {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->scheduleRevise($products);
        }

        $this->setJsonContent(['result' => $resultStatus, 'action_id' => $logsActionId]);

        return $this->getResult();
    }
}

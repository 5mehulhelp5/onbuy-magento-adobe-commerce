<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Action;

class RunRevise extends \M2E\OnBuy\Controller\Adminhtml\Listing\AbstractAction
{
    use ActionTrait;

    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter;
    /** @var \M2E\OnBuy\Controller\Adminhtml\Product\Action\ActionService */
    private ActionService $actionService;

    public function __construct(
        \M2E\OnBuy\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\OnBuy\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter,
        \M2E\OnBuy\Model\Product\Repository $productRepository
    ) {
        parent::__construct($productRepository);

        $this->productRepository = $productRepository;
        $this->massActionFilter = $massActionFilter;
        $this->actionService = $actionService;
    }

    public function execute()
    {
        $products = $this->productRepository->massActionSelectedProducts($this->massActionFilter);

        if ($this->isRealtimeAction($products)) {
            ['result' => $result] = $this->actionService->runRevise($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __('"Revising Selected Items On OnBuy" task has completed.'),
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __('"Revising Selected Items On OnBuy" task has completed with errors.'),
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRevise($products);

        $this->getMessageManager()->addSuccessMessage(
            __('"Revising Selected Items On OnBuy" task has completed.'),
        );

        return $this->redirectToGrid();
    }
}

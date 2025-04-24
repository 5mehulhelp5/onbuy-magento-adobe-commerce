<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Action;

class RunRelist extends \M2E\OnBuy\Controller\Adminhtml\Listing\AbstractAction
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
            ['result' => $result] = $this->actionService->runRelist($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __(
                        '"Relisting Selected Items On %channel_title" task has completed.',
                        [
                            'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                        ]
                    ),
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __(
                        '"Relisting Selected Items On %channel_title" task has completed with errors.',
                        [
                            'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                        ]
                    ),
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRelist($products);

        $this->getMessageManager()->addSuccessMessage(
            __(
                '"Relisting Selected Items On %channel_title" task has completed.',
                [
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            ),
        );

        return $this->redirectToGrid();
    }
}

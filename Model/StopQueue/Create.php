<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\StopQueue;

class Create
{
    private \M2E\OnBuy\Model\StopQueueFactory $stopQueueFactory;
    private Repository $repository;
    private \M2E\OnBuy\Helper\Module\Exception $helperException;
    private \M2E\OnBuy\Helper\Module\Logger $logger;

    public function __construct(
        \M2E\OnBuy\Model\StopQueueFactory $stopQueueFactory,
        \M2E\OnBuy\Model\StopQueue\Repository $repository,
        \M2E\OnBuy\Helper\Module\Exception $helperException,
        \M2E\OnBuy\Helper\Module\Logger $logger
    ) {
        $this->stopQueueFactory = $stopQueueFactory;
        $this->repository = $repository;
        $this->helperException = $helperException;
        $this->logger = $logger;
    }

    public function process(\M2E\OnBuy\Model\Product $product): void
    {
        if (!$product->isRemovableFromChannel()) {
            return;
        }

        try {
            $stopQueue = $this->stopQueueFactory->create(
                $product->getAccount()->getId(),
                $product->getListing()->getSite()->getId(),
                $product->getOnlineSku(),
            );
            $this->repository->create($stopQueue);
        } catch (\Throwable $exception) {
            $sku = $product->getOnlineSku();

            $this->logger->process(
                sprintf(
                    'Product [Listing Product ID: %s, SKU %s] was not added to stop queue because of the error: %s',
                    $product->getId(),
                    $sku,
                    $exception->getMessage()
                ),
                'Product was not added to stop queue'
            );

            $this->helperException->process($exception);
        }
    }
}

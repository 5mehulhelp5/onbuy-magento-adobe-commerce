<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ScheduledAction;

class CreateService
{
    private \M2E\OnBuy\Model\ScheduledActionFactory $scheduledActionFactory;
    /** @var \M2E\OnBuy\Model\ScheduledAction\Repository */
    private Repository $repository;

    public function __construct(
        \M2E\OnBuy\Model\ScheduledActionFactory $scheduledActionFactory,
        Repository $repository
    ) {
        $this->scheduledActionFactory = $scheduledActionFactory;
        $this->repository = $repository;
    }

    public function create(
        \M2E\OnBuy\Model\Product $listingProduct,
        int $action,
        int $statusChanger,
        array $data,
        array $tags = [],
        bool $isForce = false,
        ?\M2E\OnBuy\Model\Product\Action\Configurator  $configurator = null
    ): \M2E\OnBuy\Model\ScheduledAction {
        $scheduledAction = $this->repository->findByListingProductId($listingProduct->getId());
        if ($scheduledAction === null) {
            $scheduledAction = $this->scheduledActionFactory->create();
        }

        $scheduledAction->init(
            $listingProduct,
            $action,
            $statusChanger,
            $data,
            $isForce,
            $tags,
            $configurator,
        );

        $this->repository->create($scheduledAction);

        return $scheduledAction;
    }
}

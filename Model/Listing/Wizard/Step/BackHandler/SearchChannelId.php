<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Wizard\Step\BackHandler;

class SearchChannelId implements \M2E\OnBuy\Model\Listing\Wizard\Step\BackHandlerInterface
{
    private \M2E\OnBuy\Model\Listing\Wizard\Repository $repository;

    public function __construct(\M2E\OnBuy\Model\Listing\Wizard\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function process(\M2E\OnBuy\Model\Listing\Wizard\Manager $manager): void
    {
        $this->repository->resetSearchChannelIdForAllProducts($manager->getWizardId());
    }
}

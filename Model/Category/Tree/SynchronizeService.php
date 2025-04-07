<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Tree;

class SynchronizeService
{
    private \M2E\OnBuy\Model\Channel\Category\Processor $connectionProcessor;
    private \M2E\OnBuy\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\OnBuy\Model\Category\TreeFactory $categoryFactory;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Channel\Category\Processor $connectionProcessor,
        \M2E\OnBuy\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\OnBuy\Model\Category\TreeFactory $categoryFactory,
        \M2E\OnBuy\Model\Site\Repository $siteRepository
    ) {
        $this->connectionProcessor = $connectionProcessor;
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->categoryFactory = $categoryFactory;
        $this->siteRepository = $siteRepository;
    }

    public function synchronize(int $siteId): void
    {
        $site = $this->siteRepository->get($siteId);
        $response = $this->connectionProcessor->process($site->getSiteId());

        $categories = [];
        foreach ($response->getCategories() as $category) {
            $categories[] = $this->categoryFactory->create()->create(
                $siteId,
                $category->getId(),
                $category->getParentId(),
                $category->getTitle(),
                $category->isLeaf()
            );
        }

        $this->categoryTreeRepository->deleteBySiteId($siteId);
        $this->categoryTreeRepository->batchInsert($categories);
    }
}

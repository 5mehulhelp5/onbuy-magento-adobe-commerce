<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category;

class CategoryTreeLoader
{
    private Tree\SynchronizeService $categoryTreeSynchronizeService;
    private Tree\Repository $categoryTreeRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\OnBuy\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService
    ) {
        $this->categoryTreeSynchronizeService = $categoryTreeSynchronizeService;
        $this->categoryTreeRepository = $categoryTreeRepository;
    }

    /**
     * @param int $siteId
     * @param int|null $categoryId
     *
     * @return \M2E\OnBuy\Model\Category\Tree[]
     */
    public function getCategories(int $siteId, ?int $categoryId = null): array
    {
        if (!$this->categoryTreeRepository->categoryTreeExists($siteId)) {
            $this->categoryTreeSynchronizeService->synchronize($siteId);
        }

        return ($categoryId === null)
            ? $this->categoryTreeRepository->getRootCategories($siteId)
            : $this->categoryTreeRepository->getChildCategories($siteId, $categoryId);
    }
}

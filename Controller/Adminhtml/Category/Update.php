<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class Update extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\Dictionary\UpdateService $updateService;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $repository;
    private \M2E\OnBuy\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\UpdateService $updateService,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $repository,
        \M2E\OnBuy\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService
    ) {
        parent::__construct();

        $this->updateService = $updateService;
        $this->repository = $repository;
        $this->categoryTreeSynchronizeService = $categoryTreeSynchronizeService;
    }

    public function execute()
    {
        try {
            $siteIds = [];

            foreach ($this->repository->getAllItems() as $category) {
                $this->updateService->update($category);
                $siteIds[$category->getSiteId()] = true;
            }

            foreach (array_keys($siteIds) as $siteId) {
                $this->categoryTreeSynchronizeService->synchronize($siteId);
            }

            $this->messageManager->addSuccessMessage(__(
                'Category data has been updated.',
            ));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(__(
                'Category data failed to be updated, please try again.',
            ));
        }

        return $this->_redirect('*/template_category/index');
    }
}

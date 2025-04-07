<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class GetRecent extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryRepository
    ) {
        parent::__construct();

        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $siteId = (int)$this->getRequest()->getParam('site_id');
        $categories = $this->categoryRepository->getBySiteId($siteId);

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->getCategoryId(),
                'path' => $category->getPathWithCategoryId(),
                'is_valid' => $category->isCategoryValid(),
            ];
        }

        $this->setJsonContent($result);

        return $this->getResult();
    }
}

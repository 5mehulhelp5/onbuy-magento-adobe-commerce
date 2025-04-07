<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class GetChildCategories extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\CategoryTreeLoader $categoryTreeLoader;

    public function __construct(
        \M2E\OnBuy\Model\Category\CategoryTreeLoader $categoryTreeLoader
    ) {
        parent::__construct();

        $this->categoryTreeLoader = $categoryTreeLoader;
    }

    public function execute()
    {
        $siteId = $this->getRequest()->getParam('site_id');
        $parentCategoryId = $this->getRequest()->getParam('parent_category_id');
        $parentCategoryId = !empty($parentCategoryId) ? (int)$parentCategoryId : null;

        $categories = $this->categoryTreeLoader->getCategories((int)$siteId, $parentCategoryId);

        $response = [];
        foreach ($categories as $category) {
            $response[] = [
                'category_id' => $category->getCategoryId(),
                'title' => $category->getTitle(),
                'is_leaf' => (int)$category->isLeaf()
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}

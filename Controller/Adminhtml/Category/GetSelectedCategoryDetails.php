<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class GetSelectedCategoryDetails extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\Tree\Repository $treeRepository;
    private \M2E\OnBuy\Model\Category\Tree\PathBuilder $pathBuilder;

    public function __construct(
        \M2E\OnBuy\Model\Category\Tree\Repository $treeRepository,
        \M2E\OnBuy\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        parent::__construct();

        $this->treeRepository = $treeRepository;
        $this->pathBuilder = $pathBuilder;
    }

    public function execute()
    {
        $siteId = $this->getRequest()->getParam('site_id');
        $categoryId = $this->getRequest()->getParam('value');

        if (
            empty($siteId)
            || empty($categoryId)
        ) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Invalid input');
        }

        $category = $this->treeRepository->getCategoryBySiteIdAndCategoryId((int)$siteId, (int)$categoryId);
        if ($category === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Category invalid');
        }

        $path = $this->pathBuilder->getPath($category);
        $details = [
            'path' => $path,
            'interface_path' => sprintf('%s (%s)', $path, $categoryId),
            'template_id' => null,
            'is_custom_template' => null,
        ];

        $this->setJsonContent($details);

        return $this->getResult();
    }
}

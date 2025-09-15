<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category\Attribute\Validation;

class ResetValidationData extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('template_category_id');
        $this->productRepository->resetCategoryAttributesValidationData($categoryId);

        return $this->getResult();
    }
}

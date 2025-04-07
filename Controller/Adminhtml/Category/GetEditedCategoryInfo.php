<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class GetEditedCategoryInfo extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\Dictionary\Manager $dictionaryManager;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\Manager $dictionaryManager
    ) {
        parent::__construct();

        $this->dictionaryManager = $dictionaryManager;
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $siteId = $this->getRequest()->getParam('site_id');
        $accountId = $this->getRequest()->getParam('account_id');

        if (empty($categoryId) || empty($siteId) || empty($accountId)) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Invalid input');
        }

        try {
            $dictionary = $this->dictionaryManager->getOrCreateDictionary((int)$accountId, (int)$siteId, (int)$categoryId);
        } catch (\Throwable $e) {
            $this->setJsonContent([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => true,
            'dictionary_id' => $dictionary->getId(),
            'is_all_required_attributes_filled' => $dictionary->isAllRequiredAttributesFilled(),
            'path' => $dictionary->getPath(),
            'value' => $dictionary->getCategoryId(),
        ]);

        return $this->getResult();
    }
}

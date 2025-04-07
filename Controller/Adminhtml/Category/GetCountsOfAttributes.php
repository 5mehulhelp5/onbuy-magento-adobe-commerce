<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class GetCountsOfAttributes extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\Repository $repository
    ) {
        parent::__construct();
        $this->dictionaryRepository = $repository;
    }

    public function execute()
    {
        $dictionaryId = $this->getRequest()->getParam('dictionary_id');
        if (empty($dictionaryId)) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Invalid input');
        }

        $counts = [
            'used' => 0,
            'total' => 0,
        ];

        if ($dictionary = $this->dictionaryRepository->find((int)$dictionaryId)) {
            $counts['used'] = $dictionary->getUsedProductAttributes();
            $counts['total'] = $dictionary->getTotalProductAttributes();
        }

        $this->setJsonContent($counts);

        return $this->getResult();
    }
}

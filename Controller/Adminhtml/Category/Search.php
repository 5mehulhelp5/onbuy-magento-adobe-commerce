<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class Search extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private const SEARCH_LIMIT = 20;

    private \M2E\OnBuy\Model\Category\Search $categorySearch;

    public function __construct(
        \M2E\OnBuy\Model\Category\Search $categorySearch
    ) {
        parent::__construct();

        $this->categorySearch = $categorySearch;
    }

    public function execute()
    {
        $site_id = (int)$this->getRequest()->getParam('site_id');
        $searchQuery = $this->getRequest()->getParam('search_query');

        $result = [
            'categories' => [],
            'has_more' => false,
        ];

        if (empty($searchQuery)) {
            $this->setJsonContent($result);

            return $this->getResult();
        }

        $searchResult = $this->categorySearch->process($site_id, $searchQuery, self::SEARCH_LIMIT + 1);

        $result['categories'] = array_map(static function (\M2E\OnBuy\Model\Category\Search\ResultItem $item) {
            return [
                'id' => $item->categoryId,
                'path' => $item->path,
                'is_invite' => $item->isInviteOnly,
                'is_valid' => $item->isValid,
            ];
        }, $searchResult->getAll());

        $result['has_more'] = count($result['categories']) > self::SEARCH_LIMIT;

        $this->setJsonContent($result);

        return $this->getResult();
    }
}

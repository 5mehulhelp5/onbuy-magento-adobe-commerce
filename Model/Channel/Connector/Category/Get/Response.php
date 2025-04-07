<?php

namespace M2E\OnBuy\Model\Channel\Connector\Category\Get;

class Response
{
    /** @var \M2E\OnBuy\Model\Channel\Category\Item[] */
    private array $categories = [];

    public function addCategory(
        \M2E\OnBuy\Model\Channel\Category\Item $category
    ): void {
        $this->categories[] = $category;
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Category\Item[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}

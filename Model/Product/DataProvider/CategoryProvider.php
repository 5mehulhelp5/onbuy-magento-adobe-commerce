<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class CategoryProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Category';
    private int $onlineCategoryId;

    public function getCategoryData(\M2E\OnBuy\Model\Product $product): int
    {
        $category = $product->getCategoryDictionary();
        $this->onlineCategoryId = (int)$category->getCategoryId();

        return $this->onlineCategoryId;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->onlineCategoryId
        ];
    }
}

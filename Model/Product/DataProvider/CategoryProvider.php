<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class CategoryProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Category';

    public function getCategoryData(\M2E\OnBuy\Model\Product $product): int
    {
        $category = $product->getCategoryDictionary();

        return (int)$category->getCategoryId();
    }
}

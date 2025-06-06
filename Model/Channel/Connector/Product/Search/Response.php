<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product\Search;

class Response
{
    /** @var \M2E\OnBuy\Model\Channel\Connector\Product\Search\Product[] */
    private array $product;

    public function __construct(array $product)
    {
        $this->product = $product;
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Connector\Product\Search\Product[]
     */
    public function getProducts(): array
    {
        return $this->product;
    }
}

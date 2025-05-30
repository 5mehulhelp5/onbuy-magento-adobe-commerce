<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\UpdateFromChannel;

class ChangeResult
{
    private \M2E\OnBuy\Model\Product $product;
    private bool $isChangedProduct;
    private array $instructionsData;
    /** @var \M2E\OnBuy\Model\Listing\Log\Record[] */
    private array $logs;

    public function __construct(
        \M2E\OnBuy\Model\Product $product,
        bool $isChangedProduct,
        array $instructionsData,
        array $logs
    ) {
        $this->product = $product;
        $this->isChangedProduct = $isChangedProduct;
        $this->instructionsData = $instructionsData;
        $this->logs = $logs;
    }

    public function getProduct(): \M2E\OnBuy\Model\Product
    {
        return $this->product;
    }

    public function isChangedProduct(): bool
    {
        return $this->isChangedProduct;
    }

    public function getInstructionsData(): array
    {
        return $this->instructionsData;
    }

    /**
     * @return \M2E\OnBuy\Model\Listing\Log\Record[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}

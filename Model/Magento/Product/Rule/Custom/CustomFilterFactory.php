<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Rule\Custom;

class CustomFilterFactory
{
    private array $customFiltersMap = [
        Magento\Qty::NICK => Magento\Qty::class,
        Magento\Stock::NICK => Magento\Stock::class,
        Magento\TypeId::NICK => Magento\TypeId::class,
        OnBuy\ProductId::NICK => OnBuy\ProductId::class,
        OnBuy\OnlineCategory::NICK => OnBuy\OnlineCategory::class,
        OnBuy\OnlineTitle::NICK => OnBuy\OnlineTitle::class,
        OnBuy\OnlineQty::NICK => OnBuy\OnlineQty::class,
        OnBuy\OnlineSku::NICK => OnBuy\OnlineSku::class,
        OnBuy\OnlinePrice::NICK => OnBuy\OnlinePrice::class,
        OnBuy\Status::NICK => OnBuy\Status::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByType(string $type): \M2E\OnBuy\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
    {
        $filterClass = $this->choiceCustomFilterClass($type);
        if ($filterClass === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic(
                sprintf('Unknown custom filter - %s', $type)
            );
        }

        return $this->objectManager->create($filterClass);
    }

    private function choiceCustomFilterClass(string $type): ?string
    {
        return $this->customFiltersMap[$type] ?? null;
    }
}

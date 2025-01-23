<?php

namespace M2E\OnBuy\Model\Channel\Magento\Product\Rule\Condition;

use M2E\OnBuy\Model\Magento\Product\Rule\Custom\OnBuy as OnBuyCustomFilters;

class Combine extends \M2E\OnBuy\Model\Magento\Product\Rule\Condition\Combine
{
    public function __construct(
        \M2E\OnBuy\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($ruleConditionProductFactory, $objectManager, $context, $data);

        $this->setType(self::class);
    }

    protected function getConditionCombine(): string
    {
        return $this->getType() . '|onbuy|';
    }

    protected function getCustomLabel(): string
    {
        return (string)\__('OnBuy Connect Values');
    }

    protected function getCustomOptions(): array
    {
        $attributes = $this->getCustomOptionsAttributes();

        if (empty($attributes)) {
            return [];
        }

        return $this->getOptions(
            \M2E\OnBuy\Model\Channel\Magento\Product\Rule\Condition\Product::class,
            $attributes,
            ['onbuy']
        );
    }

    protected function getCustomOptionsAttributes(): array
    {
        return [
            OnBuyCustomFilters\OnlineQty::NICK => \__('Available QTY'),
            OnBuyCustomFilters\OnlineTitle::NICK => \__('Title'),
            OnBuyCustomFilters\Status::NICK => \__('Status'),
            OnBuyCustomFilters\OnlinePrice::NICK => \__('Price'),
        ];
    }
}

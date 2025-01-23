<?php

namespace M2E\OnBuy\Model\Channel\Magento\Product;

class Rule extends \M2E\OnBuy\Model\Magento\Product\Rule
{
    private Rule\Condition\CombineFactory $onBuyRuleCombineFactory;

    public function __construct(
        Rule\Condition\CombineFactory $onBuyRuleCombineFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \M2E\OnBuy\Model\Magento\Product\Rule\Condition\CombineFactory $ruleConditionCombineFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $form,
            $productFactory,
            $resourceIterator,
            $ruleConditionCombineFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->onBuyRuleCombineFactory = $onBuyRuleCombineFactory;
    }

    public function getConditionObj(): Rule\Condition\Combine
    {
        return $this->onBuyRuleCombineFactory->create();
    }
}

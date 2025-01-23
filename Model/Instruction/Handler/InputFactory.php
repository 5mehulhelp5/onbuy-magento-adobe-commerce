<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Instruction\Handler;

class InputFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param \M2E\OnBuy\Model\Instruction[] $instructions
     *
     * @return \M2E\OnBuy\Model\Instruction\SynchronizationTemplate\Checker\Input
     */
    public function create(\M2E\OnBuy\Model\Product $product, array $instructions): Input
    {
        return $this->objectManager->create(
            Input::class,
            [
                'product' => $product,
                'instructions' => $instructions,
            ],
        );
    }
}

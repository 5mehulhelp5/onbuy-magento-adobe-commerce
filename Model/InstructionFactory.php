<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class InstructionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Instruction
    {
        return $this->objectManager->create(Instruction::class);
    }
}

<?php

namespace M2E\OnBuy\Model\Instruction\Handler;

use M2E\OnBuy\Model\Product;

class Input extends \Magento\Framework\DataObject
{
    private Product $listingProduct;
    /** @var \M2E\OnBuy\Model\Instruction[] */
    private array $instructions = [];

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param \M2E\OnBuy\Model\Instruction[] $instructions
     */
    public function __construct(
        \M2E\OnBuy\Model\Product $product,
        array $instructions
    ) {
        parent::__construct();

        $this->listingProduct = $product;
        $this->instructions = $instructions;
    }

    public function getListingProduct(): Product
    {
        return $this->listingProduct;
    }

    /**
     * @return \M2E\OnBuy\Model\Instruction[]
     */
    public function getInstructions(): array
    {
        return $this->instructions;
    }

    /**
     * @return string[]
     */
    public function getUniqueInstructionTypes(): array
    {
        $types = [];

        foreach ($this->getInstructions() as $instruction) {
            $types[] = $instruction->getType();
        }

        return array_values(array_unique($types));
    }

    public function hasInstructionWithType(string $instructionType): bool
    {
        return in_array($instructionType, $this->getUniqueInstructionTypes(), true);
    }

    public function hasInstructionWithTypes(array $instructionTypes): bool
    {
        return count(array_intersect($this->getUniqueInstructionTypes(), $instructionTypes)) > 0;
    }
}
